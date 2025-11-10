<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\AbastecimentoManual;
use App\Modules\Abastecimentos\Models\Bomba;
use App\Models\Departamento;
use App\Models\Fornecedor;
use App\Models\Motorista;
use App\Modules\Abastecimentos\Models\Tanque;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Models\Veiculo;
use App\Models\VFilial;
use App\Services\AbastecimentoService;
use App\Traits\AbastecimentoValidationTrait;
use App\Traits\ExportableTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AbastecimentoManualController extends Controller
{
    use AbastecimentoValidationTrait;
    use ExportableTrait;

    /**
     * Serviço de Abastecimento
     *
     * @var AbastecimentoService
     */
    protected $abastecimentoService;

    public function __construct(AbastecimentoService $abastecimentoService)
    {
        $this->abastecimentoService = $abastecimentoService;
    }

    /**
     * Lista todos os abastecimentos com filtros
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Normalizar parâmetros do smart-select (converte placas/nomes em IDs)
        $this->normalizeSmartSelectParams($request);

        $query = AbastecimentoManual::query();

        // Aplicar filtros
        if ($request->filled('id_abastecimento')) {
            $query->where('id_abastecimento', $request->id_abastecimento);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('numero_nota_fiscal')) {
            $query->where('numero_nota_fiscal', $request->numero_nota_fiscal);
        }

        // Usar whereRaw para data_inclusao
        if ($request->filled('data_inclusao')) {
            $query->whereRaw("data_inclusao::date >= ?", [$request->data_inclusao]);
        }

        if ($request->filled('data_final_abastecimento')) {
            $query->whereRaw("data_inclusao::date <= ?", [$request->data_final_abastecimento]);
        }

        // Usar whereRaw para data_abastecimento
        if ($request->filled('data_inicial_abastecimento')) {
            $query->whereRaw("data_abastecimento::date >= ?", [$request->data_inicial_abastecimento]);
        }

        if ($request->filled('data_fim_abastecimento')) {
            $query->whereRaw("data_abastecimento::date <= ?", [$request->data_fim_abastecimento]);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        // Executar a consulta com paginação
        $abastecimentos = $query->latest('id_abastecimento')
            ->paginate(40)
            ->appends($request->query());

        // Verificar se é uma requisição HTMX (para atualização parcial)
        if ($request->header('HX-Request')) {
            return view('admin.abastecimentomanual._table', compact('abastecimentos'));
        }

        // Obter dados para os selects
        $referenceDatas = $this->abastecimentoService->getReferenceDatas();

        return view('admin.abastecimentomanual.index', array_merge(
            compact('abastecimentos'),
            $referenceDatas
        ));
    }

    /**
     * Exibe formulário para criar um novo abastecimento
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Obter a filial do usuário logado
        $filialId = auth()->user()->filial_id;

        // Carregar dados com cache para os selects
        $veiculos = $this->abastecimentoService->getVeiculosFrequentes();
        $fornecedores = $this->abastecimentoService->getFornecedoresFrequentes();
        $filiais = $this->abastecimentoService->getFiliais();
        $departamentos = $this->abastecimentoService->getDepartamentosAtivos();
        $bombas = $this->abastecimentoService->getBombasAtivas();
        $tiposCombustivel = $this->abastecimentoService->getTiposCombustivel();
        $motoristas = $this->abastecimentoService->getMotoristasAtivos();

        return view('admin.abastecimentomanual.create', compact(
            'veiculos',
            'fornecedores',
            'filiais',
            'departamentos',
            'bombas',
            'tiposCombustivel',
            'motoristas',
            'filialId'
        ));
    }

    /**
     * Mostra um abastecimento específico
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $abastecimento = AbastecimentoManual::findOrFail($id);

            // Buscar os itens relacionados - USANDO CONEXÃO DE PRODUÇÃO
            $itens = DB::connection('pgsql')
                ->table('abastecimento_itens')
                ->where('id_abastecimento', $id)
                ->orderBy('data_abastecimento')
                ->get();

            return view('admin.abastecimentomanual.show', compact('abastecimento', 'itens'));
        } catch (Exception $e) {
            Log::error('Erro ao exibir abastecimento: ' . $e->getMessage());

            return redirect()
                ->route('admin.abastecimentomanual.index')
                ->with('error', 'Erro ao buscar abastecimento: ' . $e->getMessage());
        }
    }

    /**
     * Remove um abastecimento
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Primeiro deleta os itens relacionados - USANDO CONEXÃO DE PRODUÇÃO
            DB::connection('pgsql')
                ->table('abastecimento_itens')
                ->where('id_abastecimento', $id)
                ->delete();
            Log::info('Itens de abastecimento removidos para ID: ' . $id);

            // Depois deleta o abastecimento - USANDO CONEXÃO DE PRODUÇÃO
            DB::connection('pgsql')
                ->table('abastecimento')
                ->where('id_abastecimento', $id)
                ->delete();
            Log::info('Abastecimento removido com ID: ' . $id);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir abastecimento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista de filtros válidos para exportação
     *
     * @return array
     */
    protected function getValidExportFilters()
    {
        return [
            'id_abastecimento',
            'id_veiculo',
            'id_filial',
            'numero_nota_fiscal',
            'data_inclusao',
            'data_final_abastecimento',
            'data_inicial_abastecimento',
            'data_fim_abastecimento',
            'id_fornecedor'
        ];
    }

    /**
     * Exportar para PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = $this->abastecimentoService->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.abastecimentomanual.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('abastecimentos_' . date('Y-m-d_His') . '.pdf');
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

    /**
     * Exportar para CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        $query = $this->abastecimentoService->buildExportQuery($request);

        $columns = [
            'id_abastecimento' => 'Código',
            'placa' => 'Placa',
            'data_inclusao' => 'Data Inclusão',
            'data_abastecimento' => 'Data Abastecimento',
            'numero_nota_fiscal' => 'Nota Fiscal',
            'nome_fornecedor' => 'Fornecedor',
            'descricao_departamento' => 'Departamento',
            'descricao_tipo' => 'Tipo Equipamento'
        ];

        return $this->exportToCsv($request, $query, $columns, 'abastecimentos', $this->getValidExportFilters());
    }

    /**
     * Exportar para Excel
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportXls(Request $request)
    {
        $query = $this->abastecimentoService->buildExportQuery($request);

        $columns = [
            'id_abastecimento' => 'Código',
            'placa' => 'Placa',
            'data_inclusao' => 'Data Inclusão',
            'data_abastecimento' => 'Data Abastecimento',
            'numero_nota_fiscal' => 'Nota Fiscal',
            'nome_fornecedor' => 'Fornecedor',
            'descricao_departamento' => 'Departamento',
            'descricao_tipo' => 'Tipo Equipamento'
        ];

        return $this->exportToExcel($request, $query, $columns, 'abastecimentos', $this->getValidExportFilters());
    }

    /**
     * Exportar para XML
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportXml(Request $request)
    {
        $query = $this->abastecimentoService->buildExportQuery($request);

        $structure = [
            'id' => 'id_abastecimento',
            'placa' => 'placa',
            'data_inclusao' => 'data_inclusao',
            'data_abastecimento' => 'data_abastecimento',
            'nota_fiscal' => 'numero_nota_fiscal',
            'fornecedor' => 'nome_fornecedor',
            'departamento' => 'descricao_departamento',
            'tipo_equipamento' => 'descricao_tipo'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'abastecimentos',
            'abastecimento',
            'abastecimentos',
            $this->getValidExportFilters()
        );
    }

    /**
     * Armazena um novo abastecimento no banco de dados
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function store(Request $request)
    {
        try {
            // Verificar se estamos processando um lote ou apenas um abastecimento
            $processandoLote = $request->has('processando_lote') ||
                $request->has('abastecimentos');

            if ($processandoLote) {
                return $this->abastecimentoService->processarLote($request);
            }

            // Processar um único abastecimento
            $validated = $this->validateAbastecimento($request);

            // Verificar conteúdo do JSON de itens
            $items = json_decode($validated['items'], true);
            Log::info('Total de itens decodificados: ' . count($items));

            // Se não houver itens, retorne um erro
            if (empty($items)) {
                Log::warning('Nenhum item de abastecimento recebido');
                return back()
                    ->withInput()
                    ->with('error', 'É necessário adicionar pelo menos um abastecimento.');
            }

            // Verificar se NF já existe
            $this->checkDuplicateNF($validated['numero_nota_fiscal'], $validated['id_fornecedor']);

            DB::beginTransaction();
            Log::info('Transação iniciada');

            // Buscar id_pessoal utilizando o relacionamento Eloquent
            $id_pessoal = $this->abastecimentoService->obterIdPessoalPorMotorista($validated['id_motorista'] ?? null);

            Log::info('Nota fiscal obtido: ' . $validated['numero_nota_fiscal']);
            if ($validated['numero_nota_fiscal']) {
                $abastecimento = AbastecimentoManual::where('numero_nota_fiscal', $validated['numero_nota_fiscal'])
                    ->first();
                Log::info('abastecimento obtido: ' . $abastecimento);

                if ($abastecimento) {
                    Log::warning('Número da nota fiscal já cadastrado: ' . $validated['numero_nota_fiscal']);
                    return back()
                        ->withInput()
                        ->with('error', 'Número da nota fiscal já cadastrado.');
                }
            }

            // Preparar dados do abastecimento
            $abastecimentoData = [
                'data_inclusao'      => now(),
                'id_fornecedor'      => $validated['id_fornecedor'],
                'id_filial'          => $validated['id_filial'],
                'numero_nota_fiscal' => $validated['numero_nota_fiscal'],
                'chave_nf'           => $validated['chave_nf'] ?? null,
                'id_veiculo'         => $validated['id_veiculo'],
                'id_pessoal'         => $id_pessoal,
                'id_departamento'    => $validated['id_departamento'],
                'id_user'            => auth()->id()
            ];

            Log::info('Inserindo registro de abastecimento com dados:', $abastecimentoData);

            // USAR CONEXÃO DE PRODUÇÃO
            $abastecimento = DB::connection('pgsql')
                ->table('abastecimento')
                ->insertGetId($abastecimentoData, 'id_abastecimento');

            Log::info('Registro de abastecimento inserido com ID: ' . $abastecimento);

            // Criar itens
            foreach ($items as $index => $item) {
                try {
                    $this->abastecimentoService->salvarItemAbastecimento($abastecimento, $item, $index);
                } catch (\Exception $e) {
                    Log::error('Erro ao inserir item #' . ($index + 1) . ': ' . $e->getMessage());
                    Log::error('Dados do item que falhou: ', $item);
                    throw $e;
                }
            }

            DB::commit();
            Log::info('Transação concluída com sucesso');

            return redirect()
                ->route('admin.abastecimentomanual.index')
                ->with('success', 'Abastecimento cadastrado com sucesso!');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Erro de validação: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERRO AO CADASTRAR ABASTECIMENTO: ' . $e->getMessage());
            Log::error('Detalhes do erro: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar abastecimento: ' . $e->getMessage());
        }
    }

    /**
     * Exibe formulário para editar um abastecimento
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            // Buscar o abastecimento com seus itens
            $abastecimento = AbastecimentoManual::findOrFail($id);

            // Buscar os itens diretamente para garantir dados corretos - USANDO CONEXÃO DE PRODUÇÃO
            $itens = DB::connection('pgsql')
                ->table('abastecimento_itens')
                ->where('id_abastecimento', $id)
                ->get()
                ->map(function ($item) {
                    // Converter os tipos de dados para garantir compatibilidade com o frontend
                    return [
                        'data_abastecimento' => $item->data_abastecimento,
                        'id_combustivel' => (int)$item->id_combustivel,
                        'id_bomba' => $item->id_bomba ? (int)$item->id_bomba : null,
                        'litros' => (float)$item->litros_abastecido,
                        'km_veiculo' => (float)$item->km_veiculo,
                        'valor_unitario' => (float)$item->valor_unitario,
                        'valor_total' => (float)$item->valor_total
                    ];
                })
                ->toArray();

            // Atribuir os itens ao objeto abastecimento
            $abastecimento->itens = $itens;

            // Carregamento dos dados para os selects usando o service
            $veiculos = $this->abastecimentoService->getVeiculosFrequentes();
            $fornecedores = $this->abastecimentoService->getFornecedoresFrequentes();
            $filiais = $this->abastecimentoService->getFiliais();
            $departamentos = $this->abastecimentoService->getDepartamentosAtivos();
            $bombas = $this->abastecimentoService->getBombasAtivas();
            $tiposCombustivel = $this->abastecimentoService->getTiposCombustivel();
            $motoristas = $this->abastecimentoService->getMotoristasAtivos();

            return view('admin.abastecimentomanual.edit', compact(
                'abastecimento',
                'veiculos',
                'fornecedores',
                'filiais',
                'departamentos',
                'bombas',
                'tiposCombustivel',
                'motoristas'
            ));
        } catch (Exception $e) {
            Log::error('Erro ao carregar abastecimento para edição: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()
                ->route('admin.abastecimentomanual.index')
                ->with('error', 'Erro ao carregar abastecimento: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza um abastecimento existente
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Método update iniciado para ID: ' . $id);
            Log::info('Dados recebidos:', $request->all());

            $validated = $this->validateAbastecimento($request);

            // Verificar conteúdo do JSON de itens
            $items = json_decode($validated['items'], true);
            Log::info('Total de itens decodificados: ' . count($items));

            // Se não houver itens, retorne um erro
            if (empty($items)) {
                Log::warning('Nenhum item de abastecimento recebido');
                return back()
                    ->withInput()
                    ->with('error', 'É necessário adicionar pelo menos um abastecimento.');
            }

            // Verificar se NF já existe para outro abastecimento
            $this->checkDuplicateNF($validated['numero_nota_fiscal'], $validated['id_fornecedor'], $id);

            DB::beginTransaction();
            Log::info('Transação iniciada');

            // Buscar id_pessoal utilizando o relacionamento Eloquent
            $id_pessoal = $this->abastecimentoService->obterIdPessoalPorMotorista($validated['id_motorista'] ?? null);

            // Atualizar abastecimento - USANDO CONEXÃO DE PRODUÇÃO
            DB::connection('pgsql')
                ->table('abastecimento')
                ->where('id_abastecimento', $id)
                ->update([
                    'id_fornecedor'      => $validated['id_fornecedor'],
                    'id_filial'          => $validated['id_filial'],
                    'numero_nota_fiscal' => $validated['numero_nota_fiscal'],
                    'chave_nf'           => $validated['chave_nf'] ?? null,
                    'id_veiculo'         => $validated['id_veiculo'],
                    'id_pessoal'         => $id_pessoal,
                    'id_departamento'    => $validated['id_departamento'],
                    'data_alteracao'     => now(),
                    'id_user'            => auth()->id()
                ]);

            // Remover itens antigos - USANDO CONEXÃO DE PRODUÇÃO
            DB::connection('pgsql')
                ->table('abastecimento_itens')
                ->where('id_abastecimento', $id)
                ->delete();

            // Criar novos itens
            foreach ($items as $index => $item) {
                try {
                    $this->abastecimentoService->salvarItemAbastecimento($id, $item, $index);
                } catch (\Exception $e) {
                    Log::error('Erro ao inserir item #' . ($index + 1) . ': ' . $e->getMessage());
                    Log::error('Dados do item que falhou: ', $item);
                    throw $e;
                }
            }

            DB::commit();
            Log::info('Transação concluída com sucesso');

            return redirect()
                ->route('admin.abastecimentomanual.index')
                ->with('success', 'Abastecimento atualizado com sucesso!');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Erro de validação: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERRO AO ATUALIZAR ABASTECIMENTO: ' . $e->getMessage());
            Log::error('Detalhes do erro: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar abastecimento: ' . $e->getMessage());
        }
    }

    /**
     * API para buscar dados do veículo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxGetVeiculoDados(Request $request)
    {
        try {
            if (is_numeric($request->input('id'))) {
                $id = $request->input('id');
            } else {
                $id = Veiculo::where('placa', $request->input('id'))->first()->id_veiculo;
            }

            $data = $request->input('data_abastecimento'); // Data selecionada (opcional)

            if (!$id) {
                Log::warning('ajaxGetVeiculoDados: ID do veículo não fornecido');
                return response()->json(['error' => 'ID do veículo não fornecido'], 400);
            }

            Log::info('Método ajaxGetVeiculoDados chamado para veículo ID: ' . $id);

            // Buscar dados básicos do veículo
            $veiculo = Veiculo::find($id);

            if (!$veiculo) {
                Log::warning('ajaxGetVeiculoDados: Veículo ID ' . $id . ' não encontrado');
                return response()->json(['error' => 'Veículo não encontrado'], 404);
            }

            // 1. Verificar se é carreta (bloqueado para abastecimento)
            if ($this->isVeiculoCarreta($id)) {
                Log::warning('ajaxGetVeiculoDados: Veículo ID ' . $id . ' é uma carreta (bloqueado)');
                // Importante: Retornar 200 (OK) para que o frontend consiga processar a resposta corretamente
                return response()->json([
                    'error' => 'Este veículo é uma carreta e não pode ser abastecido.',
                    'is_carreta' => true
                ], 200); // Alterado de 400 para 200
            }

            // 2. Verificar inconsistências no abastecimento
            $inconsistencia = $this->verificarInconsistenciasAbastecimento($id);
            if ($inconsistencia) {
                Log::warning('ajaxGetVeiculoDados: Veículo ID ' . $id . ' possui inconsistências: ' . $inconsistencia);
                // Importante: Retornar 200 (OK) para que o frontend consiga processar a resposta corretamente
                return response()->json([
                    'error' => "Não é possível prosseguir pois a placa {$veiculo->placa} possui os abastecimentos '{$inconsistencia}' na inconsistência ATS.",
                    'has_inconsistencia' => true
                ], 200); // Alterado de 400 para 200
            }

            // 3. Buscar KM atualizado com tratamento de erro explícito
            try {
                $kmAtual = $veiculo->km_inicial ?: 0; // Valor padrão (fallback)
                $dataConsulta = $data ?: now()->format('Y-m-d H:i:s');

                Log::info('ajaxGetVeiculoDados: Tentando buscar KM do veículo. ID Sascar: ' .
                    ($veiculo->id_sascar ?? 'Não definido') . ', Data: ' . $dataConsulta);

                if ($veiculo->id_sascar && $veiculo->id_sascar != 1533745) { // ID especial tratado na função fc_km_atual
                    $kmAtual = $this->abastecimentoService->buscarKmVeiculo($veiculo->id_sascar, $dataConsulta, $kmAtual);
                    Log::info('ajaxGetVeiculoDados: KM obtido: ' . $kmAtual);
                } else {
                    Log::info('ajaxGetVeiculoDados: Usando KM padrão: ' . $kmAtual);
                }
            } catch (\Exception $kmError) {
                // Se falhar ao buscar o KM, logar o erro mas continuar com o KM padrão
                Log::error('Erro ao buscar KM do veículo ID ' . $id . ': ' . $kmError->getMessage());
                // Não lançar exceção, apenas usar o valor padrão e continuar
            }

            // 4. CORREÇÃO: Buscar KM anterior usando a função correta
            $kmAnterior = 0; // Valor padrão
            try {
                // Buscar o KM anterior baseado no histórico de abastecimentos
                $kmAnterior = $this->abastecimentoService->getBuscarKmHistoricoAbastecimento($id, $dataConsulta);
                Log::info('ajaxGetVeiculoDados: KM anterior obtido: ' . $kmAnterior);
            } catch (\Exception $kmAnteriorError) {
                Log::error('Erro ao buscar KM anterior do veículo ID ' . $id . ': ' . $kmAnteriorError->getMessage());
            }


            // Preparar resposta
            $dados = [
                'id_veiculo' => $veiculo->id_veiculo,
                'capacidade_tanque_principal' => $veiculo->capacidade_tanque_principal,
                'km_atual' => $kmAtual ?? $veiculo->km_inicial ?? 0,
                'km_anterior' => $kmAnterior, // ADICIONAR ESTA LINHA
                'id_departamento' => $veiculo->id_departamento,
                'id_filial' => $veiculo->id_filial,
                'is_terceiro' => (bool)$veiculo->is_terceiro
            ];

            Log::info('Dados do veículo retornados via AJAX:', $dados);

            return response()->json($dados);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo via AJAX: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Fornecer uma mensagem de erro mais detalhada para debugging
            $errorMessage = env('APP_DEBUG')
                ? 'Erro ao buscar dados do veículo: ' . $e->getMessage()
                : 'Erro ao buscar dados do veículo. Por favor, tente novamente.';

            return response()->json([
                'error' => $errorMessage,
                'debug_info' => env('APP_DEBUG') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * API para buscar valor unitário de uma bomba
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBombaValorUnitario(Request $request)
    {
        try {
            $idBomba = $request->input('id_bomba');
            $isTerceiro = $request->input('is_terceiro', 0);

            if (!$idBomba) {
                return response()->json(['error' => 'ID da bomba não fornecido'], 400);
            }

            // Buscar o valor via função fc_valor_posto - USANDO CONEXÃO DE PRODUÇÃO
            $resultado = DB::connection('pgsql')
                ->select("SELECT * FROM fc_valor_posto(?, ?)", [$idBomba, $isTerceiro]);

            if (empty($resultado)) {
                return response()->json(['valor' => 0], 200);
            }

            return response()->json(['valor' => $resultado[0]->fc_valor_posto], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar valor unitário da bomba: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar valor unitário: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API para buscar bombas por tipo de combustível
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBombasPorCombustivel(Request $request)
    {
        try {
            $idCombustivel = $request->input('id_combustivel');

            if (!$idCombustivel) {
                return response()->json(['error' => 'ID do combustível não fornecido'], 400);
            }

            // Buscar bombas baseadas no tipo de combustível, apenas bombas internas
            $bombas = Cache::remember("bombas_internas_combustivel_{$idCombustivel}", now()->addHour(), function () use ($idCombustivel) {
                // Obter a query para tanques internos com o combustível especificado
                $tanquesInternosIds = Tanque::tanquesInternos()
                    ->where('combustivel', $idCombustivel)
                    ->pluck('id_tanque');

                // Filtrar bombas pelos IDs dos tanques internos - USANDO CONEXÃO DE PRODUÇÃO
                return DB::connection('pgsql')
                    ->table('bomba as b')
                    ->whereIn('b.id_tanque', $tanquesInternosIds)
                    ->where('b.is_ativo', true)
                    ->whereRaw("LOWER(b.descricao_bomba) NOT LIKE '%bomba externa%'")
                    ->orderBy('b.descricao_bomba')
                    ->select(['b.id_bomba as value', 'b.descricao_bomba as label'])
                    ->get();
            });

            // Log para depuração
            Log::info("Bombas internas para combustível ID {$idCombustivel}: " . $bombas->count() . " encontradas");

            // Nota: A resposta JSON já converte o resultado em um array de objetos
            // que será acessível usando a notação de objeto no JavaScript
            return response()->json($bombas);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar bombas por combustível: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar bombas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API para buscar um departamento pelo ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartamento($id)
    {
        try {
            $departamento = Cache::remember("departamento_{$id}", now()->addDay(), function () use ($id) {
                return Departamento::select('id_departamento as value', 'descricao_departamento as label')
                    ->findOrFail($id);
            });

            return response()->json($departamento);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar departamento: ' . $e->getMessage());
            return response()->json(['error' => 'Departamento não encontrado'], 404);
        }
    }

    /**
     * API para buscar uma filial pelo ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilial($id)
    {
        try {
            $filial = Cache::remember("filial_{$id}", now()->addDay(), function () use ($id) {
                return VFilial::select('id as value', 'name as label')
                    ->findOrFail($id);
            });

            return response()->json($filial);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar filial: ' . $e->getMessage());
            return response()->json(['error' => 'Filial não encontrada'], 404);
        }
    }

    /**
     * API para buscar um fornecedor pelo ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFornecedor($id)
    {
        try {
            $fornecedor = Cache::remember("fornecedor_{$id}", now()->addHour(), function () use ($id) {
                return Fornecedor::select(
                    'id_fornecedor as value',
                    'nome_fornecedor as label',
                    'cnpj_fornecedor'
                )->findOrFail($id);
            });

            return response()->json($fornecedor);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar fornecedor: ' . $e->getMessage());
            return response()->json(['error' => 'Fornecedor não encontrado'], 404);
        }
    }

    /**
     * API para buscar um motorista pelo ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMotorista($id)
    {
        try {
            $motorista = Cache::remember("motorista_{$id}", now()->addHour(), function () use ($id) {
                return Motorista::select('idobtermotorista as value', 'nome as label')
                    ->findOrFail($id);
            });

            return response()->json($motorista);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar motorista: ' . $e->getMessage());
            return response()->json(['error' => 'Motorista não encontrado'], 404);
        }
    }

    /**
     * API para buscar motoristas por nome (autocomplete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchMotoristas(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $motoristas = Motorista::where('ativo', '1')
            ->where('nome', 'ilike', "%{$term}%")
            ->orderBy('nome')
            ->limit(15)
            ->get(['idobtermotorista as value', 'nome as label']);

        return response()->json($motoristas);
    }

    /**
     * API para buscar fornecedores por nome (autocomplete)
     * *** CORRIGIDO PARA PSR-12 ***
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchFornecedores(Request $request)
    {
        $term = $request->input('term', '');

        // Requisito padrão: o termo precisa ter pelo menos 2 caracteres
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        // Construir a query básica
        $query = Fornecedor::where(function ($subquery) use ($term) {
            $subquery->where('nome_fornecedor', 'ilike', "%{$term}%")
                ->orWhere('apelido_fornecedor', 'ilike', "%{$term}%")
                ->orWhere('cnpj_fornecedor', 'ilike', "%{$term}%");
        });

        // Logica de priorização para Carvalima (mais sutil)
        $buscarPorCarvalima = stripos($term, 'carva') !== false ||
            stripos($term, 'lima') !== false;

        // Garantir que resultados Carvalima sejam bem posicionados
        if ($buscarPorCarvalima) {
            // Busca personalizada que mantém Carvalima no topo da lista
            $fornecedores = $query->get(['id_fornecedor as value', 'nome_fornecedor as label', 'cnpj_fornecedor'])
                ->sortBy(function ($item) {
                    $isCarvalima = stripos($item->label, 'carvalima') !== false;
                    return $isCarvalima ? 0 : 1; // Carvalima fica no topo (0), outros depois (1)
                })
                ->values()
                ->take(15);
        } else {
            // Busca padrão
            $fornecedores = $query->orderBy('nome_fornecedor')
                ->limit(15)
                ->get(['id_fornecedor as value', 'nome_fornecedor as label', 'cnpj_fornecedor']);

            // Se não há nenhum Carvalima nos resultados, checar se o termo parece com Carvalima
            // Por exemplo, termos como "crvlm", "crvl", "crv" podem ser tentativas de buscar Carvalima
            $temCarvalima = $fornecedores->filter(function ($item) {
                return stripos($item->label, 'carvalima') !== false;
            })->count();

            // *** CORRIGIDO PARA PSR-12 ***
            $isCarvaLikeSearch = (
                $temCarvalima == 0 &&
                stripos($term, 'c') === 0 &&
                (stripos($term, 'r') !== false || stripos($term, 'a') !== false)
            );

            if ($isCarvaLikeSearch) {
                // Parece que pode ser uma tentativa de buscar Carvalima
                // Adicionar alguns resultados Carvalima
                $carvalima = Fornecedor::where('nome_fornecedor', 'ilike', '%carvalima%')
                    ->orderBy('nome_fornecedor')
                    ->limit(3)
                    ->get(['id_fornecedor as value', 'nome_fornecedor as label', 'cnpj_fornecedor']);

                if ($carvalima->count() > 0) {
                    // Adicionar ao início dos resultados
                    $fornecedores = $carvalima->concat($fornecedores)->take(15);
                }
            }
        }

        // Log para depuração (opcional)
        Log::debug("Busca de fornecedores por '{$term}': " . $fornecedores->count() . " resultados");

        return response()->json($fornecedores);
    }

    /**
     * API para buscar veículos por placa (autocomplete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchVeiculos(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->where('placa', 'ilike', "%{$term}%")
            ->orderBy('placa')
            ->limit(15)
            ->get(['id_veiculo as value', 'placa as label']);

        return response()->json($veiculos);
    }

    /**
     * API para buscar departamentos por nome (autocomplete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchDepartamentos(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $departamentos = Departamento::where('ativo', true)
            ->where('descricao_departamento', 'ilike', "%{$term}%")
            ->orderBy('descricao_departamento')
            ->limit(15)
            ->get(['id_departamento as value', 'descricao_departamento as label']);

        return response()->json($departamentos);
    }

    /**
     * API para buscar filiais por nome (autocomplete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchFiliais(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $filiais = VFilial::where('name', 'ilike', "%{$term}%")
            ->orderBy('name')
            ->limit(15)
            ->get(['id as value', 'name as label']);

        return response()->json($filiais);
    }

    public function getCombustivelBomba(Request $request)
    {
        try {
            $bombaId = $request->bomba;
            $combustivelId = $request->combustivel;

            $bomba = Bomba::find($bombaId);

            if (!$bomba) {
                return response()->json(['error' => 'Bomba não encontrada'], 404);
            }

            $tanque = Tanque::find($bomba->id_tanque);

            if (!$tanque) {
                return response()->json(['error' => 'Tanque não encontrado'], 404);
            }

            $combustivelBomba = $tanque->combustivel;

            // Verifica se o combustível da bomba é o mesmo selecionado
            if ($combustivelBomba != $combustivelId) {
                return response()->json([
                    'compatible' => false,
                    'error' => 'O tipo de combustível selecionado não é compatível com esta bomba',
                    'combustivel_bomba' => $combustivelBomba,
                    'combustivel_selecionado' => $combustivelId
                ], 400);
            }

            return response()->json([
                'compatible' => true,
                'combustivel' => $combustivelBomba,
                'message' => 'Combustível compatível com a bomba'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao validar compatibilidade combustível/bomba: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    public function getCombustivelData(Request $request)
    {
        try {
            // Buscar todos os veículos do tipo equipamento selecionado
            $combustivel = TipoCombustivel::select('id_tipo_combustivel as value', 'descricao as label')
                ->where('id_tipo_combustivel', $request->tipoEquipamento)
                ->get(); // Adicionar get() para executar a query

            return response()->json([
                'combustivel' => $combustivel,
                'total' => $combustivel->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do tipo de combustível: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }

    public function getBombaData(Request $request)
    {
        try {
            $idFornecedor = $request->idFornecedor;

            Log::info('getBombaData chamado - Parâmetros recebidos:', $request->all());

            if (!$idFornecedor) {
                Log::warning('getBombaData: ID do fornecedor não fornecido');
                return response()->json(['error' => 'ID do fornecedor não fornecido'], 400);
            }

            // Converter para inteiro para garantir comparação correta
            $idFornecedor = (int) $idFornecedor;

            Log::info('Buscando bombas para fornecedor ID: ' . $idFornecedor . ' (tipo: ' . gettype($idFornecedor) . ')');

            $fornecedor = Fornecedor::find($idFornecedor);
            $fornecedorFilial = $fornecedor ? $fornecedor->id_filial : null;

            Log::info('Filial do fornecedor ' . $idFornecedor . ': ' . $fornecedorFilial);

            // Buscar bombas pelo fornecedor - USANDO CONEXÃO DE PRODUÇÃO
            $bombas = DB::connection('pgsql')
                ->table('bomba')
                ->select('id_bomba as value', 'descricao_bomba as label')
                ->where('id_filial', $fornecedorFilial)
                ->where('is_ativo', true)
                ->orderBy('descricao_bomba')
                ->get();

            Log::info('Bombas encontradas para fornecedor ' . $idFornecedor . ': ' . $bombas->count());
            Log::info('Dados das bombas:', $bombas->toArray());

            $response = [
                'combustivel' => $bombas,
                'total' => $bombas->count()
            ];

            Log::info('Resposta que será enviada:', $response);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar bombas por fornecedor: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Erro ao buscar bombas'], 500);
        }
    }

    public function getVeiculos(Request $request)
    {

        try {
            $term = strtolower($request->get('term'));

            // Cache para melhorar performance
            $veiculos = Cache::remember('veiculos_search_' . $term, now()->addMinutes(15), function () use ($term) {
                return Veiculo::whereRaw('LOWER(placa) LIKE ?', ["%{$term}%"])
                    ->whereIn('id_tipo_equipamento', [1, 2, 3, 52, 53, 54, 40, 44, 71, 49])
                    ->whereRaw("TRIM(placa) NOT LIKE '%TK'")
                    ->where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(['id_veiculo as value', 'placa as label']);
            });

            return response()->json($veiculos);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar veículos: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao buscar veículos.'], 500);
        }
    }

    public function retornarPosto()
    {
        Log::debug('Método retornarPosto chamado');
        $fornecedor = Fornecedor::where('nome_fornecedor', '@@', 'carvalima')->where('nome_fornecedor', '@@', 'posto')->where('id_filial', GetterFilial())->first();

        if (!empty($fornecedor)) {
            return json_encode([
                'id_fornecedor' => $fornecedor->id_fornecedor,
                'nome_fornecedor' => $fornecedor->nome_fornecedor . ' - ' . $fornecedor->cnpj_fornecedor
            ]);
        } else {
            $fornecedor = Fornecedor::where('nome_fornecedor', '@@', 'carvalima')->where('id_filial', GetterFilial())->first();

            if (!empty($fornecedor)) {
                return json_encode([
                    'id_fornecedor' => $fornecedor->id_fornecedor,
                    'nome_fornecedor' => $fornecedor->nome_fornecedor . ' - ' . $fornecedor->cnpj_fornecedor
                ]);
            }
        }
    }
}
