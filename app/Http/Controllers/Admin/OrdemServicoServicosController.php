<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Models\NfOrdemServico;
use App\Models\OrdemServicoServicos;
use App\Models\OrdemServico;
use App\Models\Servico;
use App\Traits\ExportableTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrdemServicoServicosController extends Controller
{
    use ExportableTrait;

    /**
     * Exibe a listagem de serviços para lançamento de NF
     */
    public function index(Request $request)
    {
        $query = OrdemServicoServicos::query();

        // Aplicar filtros de busca
        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->id_ordem_servico);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        // Ordenação e paginação
        $servicos = $query->latest('id_ordem_servico_serv')
            ->paginate(50)
            ->appends($request->query());

        // Se for uma requisição HTMX, retornar apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.ordemservicoservicos._table', compact('servicos'));
        }

        // Dados de referência para os selects
        $referenceDatas = $this->getReferenceDatas();

        // Retornar a view completa com todos os dados
        return view('admin.ordemservicoservicos.index', array_merge(
            compact('servicos'),
            $referenceDatas
        ));
    }

    /**
     * Obtém dados de referência para os selects do formulário
     */
    protected function getReferenceDatas()
    {
        return Cache::remember('ordemservico_reference_datas', now()->addHours(12), function () {
            return [
                'fornecedores' => Fornecedor::select(
                    'id_fornecedor as value',
                    DB::raw("CONCAT(cnpj_fornecedor, ' / ', nome_fornecedor) as label")
                )->orderBy('nome_fornecedor')
                    ->limit(100)
                    ->get(),
            ];
        });
    }

    /**
     * Exibe a tela para lançar NF agrupada
     */
    public function lancarNF(Request $request)
    {
        // Validar os IDs de serviços selecionados
        $request->validate([
            'servicos' => 'required|array',
            'servicos.*' => 'required|exists:ordem_servico_servicos,id_ordem_servico_serv'
        ]);

        $servicosIds = $request->servicos;

        // Armazenar os IDs na sessão (equivalente ao TSession::setValue do Adianti)
        Session::put('LISTA_SERVICO', $servicosIds);

        // Buscar os serviços para exibir na tela
        $servicos = OrdemServicoServicos::with(['servicos', 'fornecedor', 'ordemServico'])
            ->whereIn('id_ordem_servico_serv', $servicosIds)
            ->get();

        // Obter um ID de OS para pré-preencher o formulário
        $ordemServicoId = $servicos->first()->id_ordem_servico;

        // Verificar se todos os serviços são do mesmo fornecedor
        if ($servicos->pluck('id_fornecedor')->unique()->count() > 1) {
            return redirect()
                ->route('admin.ordemservicoservicos.index')
                ->with('error', 'Os serviços selecionados devem pertencer ao mesmo fornecedor!');
        }

        // Carregar dados de referência
        $fornecedores = Cache::remember('fornecedores_list', now()->addDay(), function () {
            return Fornecedor::select(
                'id_fornecedor as value',
                DB::raw("CONCAT(cnpj_fornecedor, ' / ', nome_fornecedor) as label")
            )->orderBy('nome_fornecedor')
                ->get();
        });

        // Calcular o valor total dos serviços
        $valorTotal = $servicos->sum('valor_total_com_desconto');
        $fornecedorId = $servicos->first()->id_fornecedor;

        return view('admin.ordemservicoservicos.lancar-nf', compact(
            'servicos',
            'ordemServicoId',
            'valorTotal',
            'fornecedorId',
            'fornecedores'
        ));
    }

    /**
     * Grava a NF nos serviços selecionados
     */
    public function gravarNF(Request $request)
    {
        $validated = $request->validate([
            'numero_nf' => 'required|integer',
            'serie' => 'required|string|max:10',
            'data_emissao_nf' => 'required|date_format:Y-m-d\TH:i',
            'chave_nf' => 'nullable|string|max:44',
            'id_ordem_servico' => 'required|exists:ordem_servico,id_ordem_servico',
            'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
            'valor_bruto_nf' => 'required|string',
            'valor_descontonf' => 'nullable|string',
            'valor_liquido_nf' => 'required|string',
            'observacao' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Recuperar os IDs dos serviços da sessão
            $servicosIds = Session::get('LISTA_SERVICO');

            if (!$servicosIds || !is_array($servicosIds) || count($servicosIds) == 0) {
                throw new Exception('Nenhum serviço selecionado para lançamento de NF.');
            }

            // Calcular o valor que cada serviço terá na NF
            $quantidadeServicos = count($servicosIds);
            $valorBruto = $this->convertCurrencyToFloat($validated['valor_bruto_nf']);
            $valorLiquido = $this->convertCurrencyToFloat($validated['valor_liquido_nf']);
            $valorDesconto = $this->convertCurrencyToFloat($validated['valor_descontonf'] ?? '0,00');

            // Calcular o valor por serviço (distribuição igual)
            $valorPorServico = $valorLiquido / $quantidadeServicos;

            // Data de emissão formatada para o banco
            $dataEmissao = date('Y-m-d H:i:s', strtotime($validated['data_emissao_nf']));

            // Criar registro principal da NF
            $nfOrdemServicoPrincipal = new NfOrdemServico();
            $nfOrdemServicoPrincipal->data_inclusao = now();
            $nfOrdemServicoPrincipal->numero_nf = $validated['numero_nf'];
            $nfOrdemServicoPrincipal->serie = $validated['serie'];
            $nfOrdemServicoPrincipal->data_emissao_nf = $dataEmissao;
            $nfOrdemServicoPrincipal->observacao = $validated['observacao'] ?? null;
            $nfOrdemServicoPrincipal->id_ordem_servico = $validated['id_ordem_servico'];
            $nfOrdemServicoPrincipal->id_fornecedor = $validated['id_fornecedor'];
            $nfOrdemServicoPrincipal->valor_previo = $valorLiquido;
            $nfOrdemServicoPrincipal->valor_descontonf = $valorDesconto;
            $nfOrdemServicoPrincipal->valor_bruto_nf = $valorBruto;
            $nfOrdemServicoPrincipal->valor_liquido_nf = $valorLiquido;
            $nfOrdemServicoPrincipal->chave_nf = $validated['chave_nf'] ?? null;
            $nfOrdemServicoPrincipal->save();

            // Atualizar a ordem de serviço com a referência da NF
            $ordemServico = OrdemServico::find($validated['id_ordem_servico']);
            if ($ordemServico) {
                $ordemServico->id_nf_ordem = $nfOrdemServicoPrincipal->id_nf_ordem;
                $ordemServico->data_alteracao = now();
                $ordemServico->save();
            }

            // Atualizar cada serviço com o número da NF e valor
            foreach ($servicosIds as $servicoId) {
                // Buscar o serviço para obter o id_servicos
                $servico = OrdemServicoServicos::find($servicoId);

                if ($servico) {
                    // Atualizar o serviço
                    $servico->valor_servico = $valorPorServico;
                    $servico->valor_total = $valorPorServico;
                    $servico->numero_nota_fiscal_servicos = $validated['numero_nf'];
                    $servico->status_servico = 'FATURADO';
                    $servico->data_alteracao = now();
                    $servico->save();

                    // Criar registro de detalhes da NF para cada serviço
                    if ($servico->id_servicos) {
                        $nfOrdemServico = new NfOrdemServico();
                        $nfOrdemServico->data_inclusao = now();
                        $nfOrdemServico->numero_nf = $validated['numero_nf'];
                        $nfOrdemServico->serie = $validated['serie'];
                        $nfOrdemServico->data_emissao_nf = $dataEmissao;
                        $nfOrdemServico->observacao = $validated['observacao'] ?? null;
                        $nfOrdemServico->id_ordem_servico = $validated['id_ordem_servico'];
                        $nfOrdemServico->id_fornecedor = $validated['id_fornecedor'];
                        $nfOrdemServico->id_servico = $servico->id_servicos;
                        $nfOrdemServico->valor_previo = $valorPorServico;
                        $nfOrdemServico->valor_descontonf = $valorDesconto;
                        $nfOrdemServico->valor_bruto_nf = $valorBruto;
                        $nfOrdemServico->valor_liquido_nf = $valorLiquido;
                        $nfOrdemServico->chave_nf = $validated['chave_nf'] ?? null;
                        $nfOrdemServico->save();
                    }
                }
            }

            // Limpar a sessão
            Session::forget('LISTA_SERVICO');

            DB::commit();

            return redirect()
                ->route('admin.ordemservicoservicos.index')
                ->with('success', 'Nota fiscal lançada com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erro ao gravar nota fiscal: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza o serviço com o número da NF e valor
     */
    private function atualizarServicoComNF($idServicoServico, $valorServico, $numeroNF)
    {
        $servico = OrdemServicoServicos::find($idServicoServico);

        if ($servico) {
            $servico->valor_servico = $valorServico;
            $servico->valor_total = $valorServico;
            $servico->numero_nota_fiscal_servicos = $numeroNF;
            $servico->status_servico = 'FATURADO';
            $servico->save();
        }

        return $servico;
    }

    /**
     * Converte valores formatados em moeda para float
     */
    private function convertCurrencyToFloat($value)
    {
        if (is_numeric($value)) {
            return floatval($value);
        }

        // Remove pontos e substitui vírgula por ponto
        return floatval(str_replace(',', '.', str_replace('.', '', $value)));
    }

    /**
     * Constrói a query para exportação
     */
    protected function buildExportQuery(Request $request)
    {
        $query = OrdemServicoServicos::query();

        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->id_ordem_servico);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        return $query->latest('id_ordem_servico_serv');
    }

    /**
     * Retorna os filtros válidos para exportação
     */
    protected function getValidExportFilters()
    {
        return [
            'id_ordem_servico',
            'id_fornecedor'
        ];
    }

    /**
     * Exporta para PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');
                $pdf->loadView('admin.ordemservicoservicos.pdf', compact('data'));

                return $pdf->download('ordemservico_servicos_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    /**
     * Exporta para CSV
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_ordem_servico_serv' => 'Código',
            'id_ordem_servico' => 'O.S',
            'nome_fornecedor' => 'Fornecedor',
            'descricao_servico' => 'Serviço',
            'valor_servico' => 'Valor Serviço',
            'valor_descontoservico' => 'Valor c/ Desconto',
            'valor_total_com_desconto' => 'Valor Total',
            'status_servico' => 'Status Serviço'
        ];

        return $this->exportToCsv($request, $query, $columns, 'ordemservico_servicos', $this->getValidExportFilters());
    }

    /**
     * Exporta para XLS
     */
    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_ordem_servico_serv' => 'Código',
            'id_ordem_servico' => 'O.S',
            'nome_fornecedor' => 'Fornecedor',
            'descricao_servico' => 'Serviço',
            'valor_servico' => 'Valor Serviço',
            'valor_descontoservico' => 'Valor c/ Desconto',
            'valor_total_com_desconto' => 'Valor Total',
            'status_servico' => 'Status Serviço'
        ];

        return $this->exportToExcel(
            $request,
            $query,
            $columns,
            'ordemservico_servicos',
            $this->getValidExportFilters()
        );
    }

    /**
     * Exporta para XML
     */
    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_ordem_servico_serv',
            'ordem_servico' => 'id_ordem_servico',
            'fornecedor' => 'nome_fornecedor',
            'servico' => 'descricao_servico',
            'valor_servico' => 'valor_servico',
            'valor_desconto' => 'valor_descontoservico',
            'valor_total' => 'valor_total_com_desconto',
            'status' => 'status_servico'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'ordemservico_servicos',
            'servico',
            'servicos',
            $this->getValidExportFilters()
        );
    }
}
