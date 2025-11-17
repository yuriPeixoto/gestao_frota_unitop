<?php

namespace App\Modules\Veiculos\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Certificados\Models\IpvaVeiculo;
use App\Models\ParcelasIpva;
use App\Models\Veiculo;
use App\Models\VFilial;
use App\Traits\ExportableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class IpvaVeiculoController extends Controller
{
    use ExportableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = IpvaVeiculo::query()
            ->with('veiculo', 'veiculo.filial')
            ->orderBy('id_ipva_veiculo', 'desc');

        // Filtros
        if ($request->filled('id_ipva_veiculo')) {
            $query->where('id_ipva_veiculo', $request->id_ipva_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('renavam')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('renavam', 'like', '%' . $request->renavam . '%');
            });
        }

        if ($request->filled('ano_validade')) {
            $query->where('ano_validade', $request->ano_validade);
        }

        if ($request->filled('status_ipva')) {
            $query->where('status_ipva', $request->status_ipva);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('data_pagamento_ipva', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_pagamento_ipva', '<=', $request->data_final);
        }

        if ($request->filled('filial_veiculo')) {
            $query->whereHas('veiculo.filial', function ($query) use ($request) {
                $query->where('id', $request->filial_veiculo);
            });
        }

        if ($request->filled('valor_pago')) {
            $valorMinimo = SanitizeToDouble($request->valor_pago);
            $query->where('valor_pago_ipva', '>=', $valorMinimo);
        }

        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->ativos();
            } else {
                $query->inativos();
            }
        } else {
            $query->todos();
        }

        // Executa a query com paginação
        $ipvaveiculos = $query->paginate(15)->withQueryString();

        // Carrega veículos frequentes para o filtro de busca
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(24), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('placa')
                ->limit(20)
                ->get(['id_veiculo as value', 'placa as label']);
        });

        $filiais = VFilial::select(['id as value', 'name as label'])->get();

        // Retorna apenas a tabela se for uma requisição HTMX
        if ($request->header('HX-Request')) {
            return view('admin.ipvaveiculos._table', compact('ipvaveiculos'));
        }

        return view('admin.ipvaveiculos.index', compact('ipvaveiculos', 'veiculosFrequentes', 'filiais'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->where('is_terceiro', false)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        return view('admin.ipvaveiculos.create', compact('veiculos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Sanitizar valores monetários
            $this->sanitizarValoresMonetarios($request);

            // Validação dos dados principais
            $validatedData = $this->validateIpvaVeiculo($request);

            DB::beginTransaction();

            // Criar registro de IPVA
            $ipvaVeiculo = new IpvaVeiculo();
            $ipvaVeiculo->data_inclusao = now();
            $ipvaVeiculo->id_veiculo = $validatedData['id_veiculo'];
            $ipvaVeiculo->status_ipva = 'PENDENTE';
            $ipvaVeiculo->quantidade_parcelas = $validatedData['quantidade_parcelas'];
            $ipvaVeiculo->intervalo_parcelas = $validatedData['intervalo_parcelas'];
            $ipvaVeiculo->data_primeira_parcela = $validatedData['data_primeira_parcela'];
            $ipvaVeiculo->ano_validade = $validatedData['ano_validade'];
            $ipvaVeiculo->data_base_vencimento = $validatedData['data_base_vencimento'];
            $ipvaVeiculo->data_pagamento_ipva = $validatedData['data_pagamento_ipva'];
            $ipvaVeiculo->valor_previsto_ipva = $validatedData['valor_previsto_ipva'];
            $ipvaVeiculo->valor_juros_ipva = $validatedData['valor_juros_ipva'];
            $ipvaVeiculo->valor_desconto_ipva = $validatedData['valor_desconto_ipva'];
            $ipvaVeiculo->valor_pago_ipva = $validatedData['valor_pago_ipva'];
            $ipvaVeiculo->save();

            // Processar parcelas, caso existam
            if ($request->has('ipvaveiculosinput') && !empty($request->ipvaveiculosinput)) {
                $this->processarParcelas($request->ipvaveiculosinput, $ipvaVeiculo->id_ipva_veiculo);
            }

            // Atualizar o status do IPVA baseado nas parcelas
            $this->atualizarStatusIpva($ipvaVeiculo->id_ipva_veiculo);

            DB::commit();

            return redirect()->route('admin.ipvaveiculos.edit', ['ipvaveiculos' => $ipvaVeiculo->id_ipva_veiculo])
                ->with('success', 'IPVA cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar IPVA: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar IPVA: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IpvaVeiculo $ipvaveiculos)
    {
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->where('is_terceiro', false)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        $filialVeiculo = Veiculo::with('filialVeiculo')->where('id_veiculo', $ipvaveiculos->id_veiculo)->first()->filialVeiculo->name;

        $parcelas = ParcelasIpva::where('id_ipva_veiculo', $ipvaveiculos->id_ipva_veiculo)
            ->orderBy('numero_parcela')
            ->get();

        return view('admin.ipvaveiculos.edit', compact('ipvaveiculos', 'veiculos', 'parcelas', 'filialVeiculo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Sanitizar valores monetários
            $this->sanitizarValoresMonetarios($request);

            // Validação dos dados principais
            $validatedData = $this->validateIpvaVeiculo($request);

            $ipvaveiculo = IpvaVeiculo::findOrFail($id);
            DB::beginTransaction();

            // Atualizar registro de IPVA
            $ipvaveiculo = IpvaVeiculo::findOrFail($id);

            $ipvaveiculo->data_alteracao        = now();
            $ipvaveiculo->id_veiculo            = $validatedData['id_veiculo'];
            $ipvaveiculo->quantidade_parcelas   = $validatedData['quantidade_parcelas'];
            $ipvaveiculo->intervalo_parcelas    = $validatedData['intervalo_parcelas'];
            $ipvaveiculo->data_primeira_parcela = $validatedData['data_primeira_parcela'];
            $ipvaveiculo->ano_validade          = $validatedData['ano_validade'];
            $ipvaveiculo->data_base_vencimento  = $validatedData['data_base_vencimento'];
            $ipvaveiculo->data_pagamento_ipva   = $validatedData['data_pagamento_ipva'];
            $ipvaveiculo->valor_previsto_ipva   = $validatedData['valor_previsto_ipva'];
            $ipvaveiculo->valor_juros_ipva      = $validatedData['valor_juros_ipva'];
            $ipvaveiculo->valor_desconto_ipva   = $validatedData['valor_desconto_ipva'];
            $ipvaveiculo->valor_pago_ipva       = $validatedData['valor_pago_ipva'];

            $ipvaveiculo->save();

            // Remover parcelas existentes e processar novas parcelas
            ParcelasIpva::where('id_ipva_veiculo', $ipvaveiculo->id_ipva_veiculo)->delete();

            DB::commit();

            if ($request->has('ipvaveiculosinput') && !empty($request->ipvaveiculosinput)) {
                $this->processarParcelas($request->ipvaveiculosinput, $ipvaveiculo->id_ipva_veiculo);
            }

            // Atualizar o status do IPVA baseado nas parcelas
            $this->atualizarStatusIpva($ipvaveiculo->id_ipva_veiculo);


            return redirect()->route('admin.ipvaveiculos.edit', ['ipvaveiculos' => $ipvaveiculo->id_ipva_veiculo])
                ->with('success', 'IPVA atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar IPVA: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar IPVA: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $ipva = IpvaVeiculo::findOrFail($id);

            if (!empty($ipva)) {
                $ipva->delete();
            }

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'IPVA Desativado',
                    'type' => 'success',
                    'message' => 'IPVA desativado com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();
            Log::error('Erro ao desativar IPVA: ' . $mensagem);

            if ($errorCode == 23503) {
                $mensagem = 'Não foi possível desativar a IPVA. Ela está sendo utilizada em outro registro.';
            }

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $mensagem
                ]
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obtém dados do RENAVAM do veículo via API.
     */
    public function getRenavamData(Request $request)
    {
        try {
            $veiculo = Veiculo::select(['renavam'])
                ->where('id_veiculo', $request->placa)
                ->firstOrFail();

            return response()->json([
                'renavam' => $veiculo->renavam ?? 'Não informado'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }

    /**
     * Gera as parcelas do IPVA através da função do banco de dados.
     * MODIFICADO: Agora inclui a divisão automática do valor entre as parcelas
     */
    public function gerarParcelasIPVA(Request $request)
    {
        try {
            if (!$request->has('id_ipva_veiculo') || $request->id_ipva_veiculo <= 0) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'ID do veículo não informado!'
                ], 400);
            }

            $ipvaVeiculos = IpvaVeiculo::where('id_ipva_veiculo', $request->id_ipva_veiculo)->first();
            if (!$ipvaVeiculos) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Veículo não encontrado!'
                ], 404);
            }

            $cod_ipva_param = $ipvaVeiculos->id_ipva_veiculo;
            if (empty($ipvaVeiculos->quantidade_parcelas) && empty($ipvaVeiculos->intervalo_parcelas)) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Favor atualize os dados do IPVA antes de gerar as parcelas!'
                ]);
            } else {
                $numero_parcelas_param = $ipvaVeiculos->quantidade_parcelas;
                $intervalo_parcelas_param = $ipvaVeiculos->intervalo_parcelas;
                $data_primeiro_vencimento_param = date('Y-m-d', strtotime($ipvaVeiculos->data_primeira_parcela));
                $valor_total_ipva = $ipvaVeiculos->valor_previsto_ipva;

                // NOVA LÓGICA: Calcular valor individual da parcela
                $valor_parcela_individual = $valor_total_ipva / $numero_parcelas_param;
            }

            // Usar a função original do banco, mas com valor individual da parcela
            $sql = "SELECT * FROM fc_lancamento_cotas_ipva(?, ?, ?, ?, ?)";
            $retorno = DB::connection('pgsql')->select($sql, [
                $cod_ipva_param,
                $numero_parcelas_param,
                $intervalo_parcelas_param,
                $data_primeiro_vencimento_param,
                $valor_parcela_individual // Usar valor individual em vez do total
            ]);

            $retorno = json_encode($retorno);
            $retorno = json_decode($retorno, true);

            if (isset($retorno[0]['fc_lancamento_cotas_ipva'])) {
                if ($retorno[0]['fc_lancamento_cotas_ipva'] == 3) {
                    $titulo = 'Erro ao gerar parcelas';
                    $type = 'error';
                    $message = 'Data base do vencimento não pode ser menor que o primeiro vencimento, faça a alteração e tente novamente!';
                    $cod = 299;
                } elseif ($retorno[0]['fc_lancamento_cotas_ipva'] == 2) {
                    $titulo = 'Atenção';
                    $type = 'warning';
                    $message = 'Parcelas já foram cadastradas!';
                    $cod = 299;
                } elseif ($retorno[0]['fc_lancamento_cotas_ipva'] == 1) {
                    $titulo = 'Sucesso';
                    $type = 'success';
                    $message = 'Parcelas geradas com sucesso!';
                    $cod = 200;

                    // NOVA FUNCIONALIDADE: Atualizar os valores das parcelas com divisão correta
                    $this->atualizarValoresParcelasDivididas($cod_ipva_param, $valor_total_ipva, $numero_parcelas_param);

                    // Atualizar o status do IPVA
                    $this->atualizarStatusIpva($cod_ipva_param);
                } else {
                    $titulo = 'Erro ao gerar parcelas';
                    $type = 'error';
                    $message = 'Erro ao gerar parcelas!';
                    $cod = 500;
                }
            }

            return response()->json([
                'title' => $titulo ?? 'Sucesso',
                'type' => $type ?? 'success',
                'message' => $message ?? 'Parcelas geradas com sucesso',
            ], $cod ?? 200);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar parcelas de IPVA: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Erro ao gerar parcelas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Nova função para atualizar os valores das parcelas com divisão correta
     */
    private function atualizarValoresParcelasDivididas($idIpvaVeiculo, $valorTotal, $quantidadeParcelas)
    {
        try {
            DB::beginTransaction();

            // Buscar todas as parcelas geradas
            $parcelas = ParcelasIpva::where('id_ipva_veiculo', $idIpvaVeiculo)
                ->orderBy('numero_parcela')
                ->get();

            if ($parcelas->isEmpty()) {
                Log::warning("Nenhuma parcela encontrada para IPVA ID: {$idIpvaVeiculo}");
                return;
            }

            // Calcular valor base de cada parcela
            $valorBaseParcela = floor(($valorTotal * 100) / $quantidadeParcelas) / 100; // Arredondar para baixo
            $diferenca = $valorTotal - ($valorBaseParcela * $quantidadeParcelas);

            Log::info("Atualizando parcelas - Valor total: {$valorTotal}, Quantidade: {$quantidadeParcelas}, Valor base: {$valorBaseParcela}, Diferença: {$diferenca}");

            foreach ($parcelas as $index => $parcela) {
                $valorParcela = $valorBaseParcela;

                // Distribuir a diferença nas primeiras parcelas (centavos restantes)
                if ($index < $diferenca * 100) {
                    $valorParcela += 0.01;
                }

                // Atualizar a parcela
                $parcela->valor_parcela = $valorParcela;
                $parcela->save();

                Log::debug("Parcela {$parcela->numero_parcela} atualizada com valor: {$valorParcela}");
            }

            DB::commit();

            // Verificar se a soma está correta
            $somaVerificacao = $parcelas->sum('valor_parcela');
            Log::info("Verificação: Soma das parcelas: {$somaVerificacao}, Valor original: {$valorTotal}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar valores das parcelas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Valida os dados do IPVA.
     */
    private function validateIpvaVeiculo(Request $request)
    {
        return $request->validate([
            'id_veiculo' => 'required|exists:veiculo,id_veiculo',
            'quantidade_parcelas' => 'required|integer|min:1',
            'intervalo_parcelas' => 'required|integer|min:1',
            'data_primeira_parcela' => 'required|date',
            'ano_validade' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'data_base_vencimento' => 'nullable|date',
            'data_pagamento_ipva' => 'nullable|date',
            'valor_previsto_ipva' => 'nullable|numeric|min:0',
            'valor_juros_ipva' => 'nullable|numeric|min:0',
            'valor_desconto_ipva' => 'nullable|numeric|min:0',
            'valor_pago_ipva' => 'required|numeric|min:0',
        ]);
    }

    /**
     * Processa as parcelas do IPVA.
     */
    private function processarParcelas($parcelasJson, $idIpvaVeiculo)
    {
        $parcelas = json_decode($parcelasJson, true);

        if (!is_array($parcelas)) {
            Log::error('Erro ao processar parcelas: JSON inválido');
            throw new \Exception('Formato de parcelas inválido');
        }

        foreach ($parcelas as $parcela) {
            if ($parcela['data_pagamento'] == "" || $parcela['data_pagamento'] == '') {
                $parcela['data_pagamento'] = null;
            }
            // Sanitizar valores monetários
            $valorParcela = $this->converterParaDouble($parcela['valor_parcela'] ?? 0);
            $valorDesconto = $this->converterParaDouble($parcela['valor_desconto'] ?? 0);
            $valorJuros = $this->converterParaDouble($parcela['valor_juros'] ?? 0);
            $valorPagamento = $this->converterParaDouble($parcela['valor_pagamento'] ?? 0);

            // Criar nova parcela
            $parcelaIpva = new ParcelasIpva();
            $parcelaIpva->data_inclusao = now();
            $parcelaIpva->id_ipva_veiculo = $idIpvaVeiculo;
            $parcelaIpva->numero_parcela = intval($parcela['numero_parcela']);
            $parcelaIpva->data_vencimento = $parcela['data_vencimento'] ?? null;
            $parcelaIpva->valor_parcela = $valorParcela;
            $parcelaIpva->data_pagamento = $parcela['data_pagamento'] ?? null;
            $parcelaIpva->valor_desconto = $valorDesconto;
            $parcelaIpva->valor_juros = $valorJuros;
            $parcelaIpva->valor_pagamento = $valorPagamento;
            $parcelaIpva->save();
        }
    }

    /**
     * Atualiza o status do IPVA baseado nas parcelas.
     */
    private function atualizarStatusIpva($idIpvaVeiculo)
    {
        $ipva = IpvaVeiculo::findOrFail($idIpvaVeiculo);
        $parcelas = ParcelasIpva::where('id_ipva_veiculo', $idIpvaVeiculo)->get();

        if ($parcelas->isEmpty()) {
            $ipva->status_ipva = 'PENDENTE';
            $ipva->save();
            return;
        }

        $totalParcelas = $parcelas->count();
        $parcelasPagas = $parcelas->filter(function ($p) {
            return !empty($p->data_pagamento);
        })->count();

        if ($parcelasPagas === 0) {
            $ipva->status_ipva = 'PENDENTE';
        } elseif ($parcelasPagas < $totalParcelas) {
            $ipva->status_ipva = 'PARCIAL';
        } else {
            $ipva->status_ipva = 'QUITADO';
        }

        $ipva->save();
    }

    /**
     * Sanitiza valores monetários no request.
     */
    private function sanitizarValoresMonetarios(Request $request)
    {
        $camposMonetarios = [
            'valor_previsto_ipva',
            'valor_juros_ipva',
            'valor_desconto_ipva',
            'valor_pago_ipva'
        ];

        foreach ($camposMonetarios as $campo) {
            if ($request->has($campo)) {
                $request->merge([
                    $campo => $this->converterParaDouble($request->input($campo))
                ]);
            }
        }
    }

    /**
     * Converte valor em formato brasileiro para double.
     */
    private function converterParaDouble($valor)
    {
        if (empty($valor)) {
            return 0;
        }

        // Se já for um número, retorna ele mesmo
        if (is_numeric($valor)) {
            return floatval($valor);
        }

        // Remove formatação (R$, pontos, etc) e substitui vírgula por ponto
        $numeroLimpo = preg_replace('/[^\d,]/', '', $valor);
        $numeroLimpo = str_replace(',', '.', $numeroLimpo);

        return floatval($numeroLimpo);
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF de forma mais simples
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.ipvaveiculos.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('ipvaveiculos_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    protected function buildExportQuery(Request $request)
    {
        $query = IpvaVeiculo::query()
            ->with('veiculo', 'veiculo.filial');

        // Filtros
        if ($request->filled('id_ipva_veiculo')) {
            $query->where('id_ipva_veiculo', $request->id_ipva_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('renavam')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('renavam', 'like', '%' . $request->renavam . '%');
            });
        }

        if ($request->filled('ano_validade')) {
            $query->where('ano_validade', $request->ano_validade);
        }

        if ($request->filled('status_ipva')) {
            $query->where('status_ipva', $request->status_ipva);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('data_pagamento_ipva', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_pagamento_ipva', '<=', $request->data_final);
        }

        if ($request->filled('filial_veiculo')) {
            $query->whereHas('veiculo.filial', function ($query) use ($request) {
                $query->where('id', $request->filial_veiculo);
            });
        }

        if ($request->filled('valor_pago')) {
            $valorMinimo = SanitizeToDouble($request->valor_pago);
            $query->where('valor_pago_ipva', '>=', $valorMinimo);
        }

        return $query->latest('id_ipva_veiculo');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_ipva_veiculo',
            'id_veiculo',
            'id_filial',
            'ano_validade',
            'status_ipva',
            'quantidade_parcelas',
            'data_pagamento_ipva',
            'valor_previsto_ipva',
            'valor_pago_ipva',
            'renavam',
            'data_inicial',
            'data_final',
            'filial_veiculo',
            'valor_pago'
        ];
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_ipva_veiculo' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'ano_validade' => 'Ano de Validade',
            'status_ipva' => 'Status IPVA',
            'quantidade_parcelas' => 'Quantidade de Parcelas',
            'data_pagamento_ipva' => 'Data Pagamento IPVA',
            'valor_previsto_ipva' => 'Valor Previsto IPVA',
            'valor_pago_ipva' => 'Valor Pago IPVA'
        ];

        return $this->exportToCsv($request, $query, $columns, 'ipvaveiculos', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_ipva_veiculo' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'ano_validade' => 'Ano de Validade',
            'status_ipva' => 'Status IPVA',
            'quantidade_parcelas' => 'Quantidade de Parcelas',
            'data_pagamento_ipva' => 'Data Pagamento IPVA',
            'valor_previsto_ipva' => 'Valor Previsto IPVA',
            'valor_pago_ipva' => 'Valor Pago IPVA'
        ];

        return $this->exportToExcel($request, $query, $columns, 'ipvaveiculos', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'Código' => 'id_ipva_veiculo',
            'Placa' => 'veiculo.placa',
            'Filial do Veiculo' => 'veiculo.filial.name',
            'Ano de Validade' => 'ano_validade',
            'Status IPVA' => 'status_ipva',
            'Quantidade de Parcelas' => 'quantidade_parcelas',
            'Data Pagamento IPVA' => 'data_pagamento_ipva',
            'Valor Previsto IPVA' => 'valor_previsto_ipva',
            'Valor Pago IPVA' => 'valor_pago_ipva'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'ipvaveiculos',
            'ipvaveiculo',
            'ipvaveiculos',
            $this->getValidExportFilters()
        );
    }
}
