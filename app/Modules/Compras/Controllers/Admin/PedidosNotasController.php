<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Compras\Models\Fornecedor;
use App\Modules\Manutencao\Models\NfOrdemServico;
use App\Models\NotaFiscalAvulsa;
use App\Modules\Manutencao\Models\OrdemServico;
use App\Modules\Manutencao\Models\OrdemServicoPecas;
use App\Modules\Manutencao\Models\OrdemServicoServicos;
use App\Modules\Compras\Models\PedidoCompra;
use App\Modules\Veiculos\Models\Veiculo;
use App\Models\VListarPedidosNf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PedidosNotasController extends Controller
{
    use ExportableTrait, LoteDownloadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        // Obter parâmetros de filtragem
        $filtros = $request->only([
            'id_pedido_compras',
            'nome_fornecedor',
            'chave_nf',
            'numero_nf',
            'os',
            'placa',
            'filial',
            'id_solicitacao',
            'tipo_pedido',
            'id_pedido_geral'
        ]);

        // Iniciar query com as relações necessárias
        $pedidosNotas = VListarPedidosNf::with('solicitante', 'user')->get();

        $query = VListarPedidosNf::query();
        // Aplicar filtros conforme fornecidos na requisição
        if (!empty($filtros['id_pedido_compras'])) {
            $query->porPedido($filtros['id_pedido_compras']);
        }

        if (!empty($filtros['nome_fornecedor'])) {
            $query->where('nome_fornecedor', 'like', "%{$filtros['nome_fornecedor']}%");
        }

        if (!empty($filtros['chave_nf'])) {
            $query->porChaveNf($filtros['chave_nf']);
        }

        if (!empty($filtros['numero_nf'])) {
            $query->porNumeroNf($filtros['numero_nf']);
        }

        if (!empty($filtros['os'])) {
            $query->porOs($filtros['os']);
        }

        if (!empty($filtros['placa'])) {
            $query->porPlaca($filtros['placa']);
        }

        if (!empty($filtros['filial'])) {
            $query->porFilial($filtros['filial']);
        }

        if (!empty($filtros['id_solicitacao'])) {
            $query->porSolicitacao($filtros['id_solicitacao']);
        }

        if (!empty($filtros['tipo_pedido'])) {
            $query->porTipoPedido($filtros['tipo_pedido']);
        }

        if (!empty($filtros['id_pedido_geral'])) {
            $query->where('id_pedido_geral', $filtros['id_pedido_geral']);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        // Executar query paginada
        $pedidosNotas = $query->paginate(15)->withQueryString();

        foreach ($pedidosNotas as $pedido) {
            $pedido->xml_integrado = DB::table('nfe_core')
                ->whereRaw("infnfe @@ ?", [$pedido->chave_nf])
                ->exists() ? 'Sim' : 'Não';
        }

        $placas = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();
        // Obter fornecedores para o filtro
        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        // Fornecedores frequentes (top 10 mais usados)
        $fornecedoresFrequentes = VListarPedidosNf::select('id_fornecedor', DB::raw('count(*) as total'))
            ->groupBy('id_fornecedor')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $fornecedor = Fornecedor::find($item->id_fornecedor);
                return $fornecedor ? [
                    'value' => $fornecedor->id_fornecedor,
                    'label' => $fornecedor->nome_fornecedor
                ] : null;
            })
            ->filter();

        // Obter tipos de pedido para o filtro
        $tiposPedido = DB::connection('pgsql')
            ->table('tipo_pedido')
            ->orderBy('nome')
            ->pluck('nome', 'id') // retorna [id => nome]
            ->toArray();



        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $chaves = VListarPedidosNf::select('chave_nf as value', 'chave_nf as label')
            ->whereNotNull('chave_nf')
            ->distinct()
            ->orderBy('chave_nf')
            ->limit(200)
            ->get();

        $numeros = VListarPedidosNf::select('numero_nf as value', 'numero_nf as label')
            ->whereNotNull('numero_nf')
            ->distinct()
            ->orderBy('numero_nf')
            ->limit(200)
            ->get();

        return view('admin.compras.pedidos-notas.index', compact(
            'pedidosNotas',
            'fornecedores',
            'fornecedoresFrequentes',
            'tiposPedido',
            'filtros',
            'placas',
            'filial',
            'chaves',
            'numeros'

        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pedidoNota = VListarPedidosNf::where('id_pedido_compras', $id)->firstOrFail();

        $tipo = PedidoCompra::where('tipo_pedido', $id)
            ->value('tipo_pedido'); // dessa maneira pegamos o valor


        if ($tipo == 1 || $tipo == null) {

            return view('admin.compras.pedidos-notas.compras', compact('pedidoNota'));
        } else {

            return view('admin.compras.pedidos-notas.servico', compact('pedidoNota'));
        }
    }

    /**
     * Export the data to PDF.
     */
    protected function getValidExportFilters()
    {
        return [
            'placa',
            'id_pedido_compras',
            'id_ordem_servico',
            'nome_fornecedor'
        ];
    }



    protected function buildExportQuery(Request $request)
    {
        $query = VListarPedidosNf::query();

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }



        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->motorista_nome);
        }

        if ($request->filled('nome_fornecedor')) {
            $query->where('nome_fornecedor', $request->orgao);
        }


        return $query;
    }

    public function exportPdf(Request $request)
    {
        try {
            $ids = $request->input('id_pedido_compras', []);

            // 🔹 Converte string "1,2,3" em array
            if (is_string($ids)) {
                $ids = array_filter(explode(',', $ids));
            }

            $query = $this->buildExportQuery($request);

            if (!empty($ids)) {
                $query->whereIn('id_pedido_compras', $ids);
            }

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
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
                $pdf->loadView('admin.compras.pedidos-notas.pdf', compact('data'));

                return $pdf->download('notas_compras_' . date('Y-m-d_His') . '.pdf');
            } else {
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }


    public function exportCsv(Request $request)
    {


        $ids = $request->input('id_pedido_compras', []);

        $query = $this->buildExportQuery($request);

        if (!empty($ids)) {
            $query->whereIn('id_pedido_compras', $ids);
        }

        $query->orderBy('id_pedido_compras');

        $columns = [
            'id_pedido_compras'          =>      'Pedido',
            'nome_fornecedor'            =>      'Fornecedor',
            'numero_nf'                  =>      'Numero NF',
            'chave_nf'                   =>      'Chave Nota',
            'id_pedido_geral'            =>      'Pedido Geral',
            'valor_total'                =>      'Valor Total',
            'valor_total_desconto'       =>      'Valor Total Com Desconto',
            'data_solicitacao'           =>      'Data Solicitacao',
            'id_solicitacao'             =>      'Cód. Solicitação',
            'solicitante'                =>      'Solicitante',
            'solicitacao'                =>      'Solicitação',
            'tipo_pedido'                =>      'Tipo Pedido',
            'xml_integrado'              =>      'XML Integrado',
            'data_inclusao'              =>      'Data Inclusão',
            'name'                       =>      'Usuário Lançamento',
            'os'                         =>      'OS',
            'filial'                     =>      'Filial',
            'placa'                      =>      'Placa',
            'valor_nota_fiscal'          =>      'Valor Total Nota'

        ];

        return $this->exportToCsv($request, $query, $columns, 'lancamento-notas', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {

        $ids = $request->input('id_pedido_compras', []);

        $query = $this->buildExportQuery($request);

        if (!empty($ids)) {
            $query->whereIn('id_pedido_compras', $ids);
        }

        $query->orderBy('id_pedido_compras');

        $columns = [
            'id_pedido_compras'          =>      'Pedido',
            'nome_fornecedor'            =>      'Fornecedor',
            'numero_nf'                  =>      'Numero NF',
            'chave_nf'                   =>      'Chave Nota',
            'id_pedido_geral'            =>      'Pedido Geral',
            'valor_total'                =>      'Valor Total',
            'valor_total_desconto'       =>      'Valor Total Com Desconto',
            'data_solicitacao'           =>      'Data Solicitacao',
            'id_solicitacao'             =>      'Cód. Solicitação',
            'solicitante'                =>      'Solicitante',
            'solicitacao'                =>      'Solicitação',
            'tipo_pedido'                =>      'Tipo Pedido',
            'xml_integrado'              =>      'XML Integrado',
            'data_inclusao'              =>      'Data Inclusão',
            'name'                       =>      'Usuário Lançamento',
            'os'                         =>      'OS',
            'filial'                     =>      'Filial',
            'placa'                      =>      'Placa',
            'valor_nota_fiscal'          =>      'Valor Total Nota'
        ];

        return $this->exportToExcel($request, $query, $columns, 'lancamento-notas', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {


        $ids = $request->input('id_pedido_compras', []);

        $query = $this->buildExportQuery($request);

        if (!empty($ids)) {
            $query->whereIn('id_pedido_compras', $ids);
        }

        $query->orderBy('id_pedido_compras');

        $structure = [
            'id_pedido_compras'          =>      'id_pedido_compras',
            'nome_fornecedor'            =>      'nome_fornecedor',
            'numero_nf'                  =>      'numero_nf',
            'chave_nf'                   =>      'chave_nf',
            'id_pedido_geral'            =>      'id_pedido_geral',
            'valor_total'                =>      'valor_total',
            'valor_total_desconto'       =>      'valor_total_desconto',
            'data_solicitacao'           =>      'data_solicitacao',
            'id_solicitacao'             =>      'id_solicitacao',
            'solicitante'                =>      'solicitante',
            'solicitacao'                =>      'solicitacao',
            'tipo_pedido'                =>      'tipo_pedido',
            'xml_integrado'              =>      'xml_integrado',
            'data_inclusao'              =>      'data_inclusao',
            'name'                       =>      'name',
            'os'                         =>      'os',
            'filial'                     =>      'filial',
            'placa'                      =>      'placa',
            'valor_nota_fiscal'          =>      'valor_nota_fiscal'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'lancamento-notas', // Elemento raiz (apenas um)
            'nota',             // Elemento para cada item (diferente do raiz)
            'lancamento-notas', // Nome do arquivo
            $this->getValidExportFilters()
        );
    }


    public function excluirNota(Request $request, $id)
    {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');


        $idpedido = $id;

        Log::info('ID recebido no excluirNota: ' . $idpedido);

        if (empty($idpedido)) {
            return response()->json(['error' => 'ID do pedido não informado'], 400);
        }

        DB::beginTransaction();

        try {
            $pedidoNf = VListarPedidosNf::where('id_pedido_compras', $idpedido)->first();

            Log::info('Resultado da consulta VListarPedidosNf:', [$pedidoNf ? $pedidoNf->toArray() : null]);


            if (!$pedidoNf) {
                return redirect()->back()->with('error', 'Pedido não encontrado');
            }

            // Verificação dos dados essenciais
            $dadosFaltantes = [];

            if (empty($pedidoNf->numero_nf)) {
                $dadosFaltantes[] = 'Número da NF';
            }

            if (empty($pedidoNf->id_fornecedor)) {
                $dadosFaltantes[] = 'ID do Fornecedor';
            }

            if (empty($pedidoNf->tipo_pedido)) {
                $dadosFaltantes[] = 'Tipo de Pedido';
            }

            if (empty($pedidoNf->solicitacao)) {
                $dadosFaltantes[] = 'Tipo de Solicitação';
            }

            if (!empty($dadosFaltantes)) {
                throw new \Exception('Dados essenciais faltando: ' . implode(', ', $dadosFaltantes));
            }

            // Esses campos já vêm diretos no Eloquent, sem Reflection
            $os     = $pedidoNf->os;
            $nf     = $pedidoNf->numero_nf;
            $idfor  = $pedidoNf->id_fornecedor;
            $id     = $pedidoNf->id;
            $idped  = $pedidoNf->id_pedido_compras;
            $idger  = $pedidoNf->id_pedido_geral;
            $tipo   = $pedidoNf->tipo_pedido;
            $tiponf = $pedidoNf->solicitacao;

            Log::info('Dados para exclusão:', [
                'id' => $id,
                'idger' => $idger,
                'os' => $os,
                'nf' => $nf,
                'idfor' => $idfor
            ]);
            if (empty($id) && empty($idger) && empty($os)) {
                return redirect()->back()->with('error', 'Dados insuficientes para exclusão - NF ou Ordem de Serviço!');
            }

            // Verificação específica para o fluxo de exclusão
            if (empty($id) && empty($idger) && empty($os)) {
                throw new \Exception('Dados insuficientes para exclusão - faltam ID da NF, ID geral ou OS');
            }

            // Buscar peças (apenas se houver OS)
            $pecas = collect();
            if (!empty($os)) {
                $pecas = OrdemServicoPecas::where('id_ordem_servico', $os)
                    ->where('id_fornecedor', $idfor)
                    ->where('numero_nota_fiscal_pecas', $nf)
                    ->get();
            }

            // Buscar serviços (apenas se houver OS)
            $servicos = collect();
            if (!empty($os)) {
                $servicos = OrdemServicoServicos::where('id_ordem_servico', $os)
                    ->where('id_fornecedor', $idfor)
                    ->where('numero_nota_fiscal_servicos', $nf)
                    ->get();
            }

            // Excluir NF da tabela nf_ordem_servico (se existir)
            if (!empty($nf) && !empty($idfor)) {
                $idsOs = DB::table('nf_ordem_servico')
                    ->where('numero_nf', $nf)
                    ->where('id_fornecedor', $idfor)
                    ->pluck('id_ordem_servico');

                if ($idsOs->isNotEmpty()) {
                    NfOrdemServico::whereIn('id_ordem_servico', $idsOs)
                        ->where('numero_nf', $nf)
                        ->where('id_fornecedor', $idfor)
                        ->delete();
                }
            }

            // Excluir lançamento NF (se existir ID)
            if (!empty($id)) {
                $deleted = DB::table('nf_compra_servico')
                    ->where('id', $id)
                    ->delete();

                if ($deleted === 0) {
                    Log::warning("Nenhum registro encontrado em nf_compra_servico com ID: $id");
                }
            }

            if (!empty($idger)) {
                // Deletar pedidos_ordem_aux
                DB::table('pedidos_ordem_aux')
                    ->where('id_pedido_geral', $idger)
                    ->where('id_nf_compra_servico', $id)
                    ->whereNotNull('id_ordem_servico')
                    ->delete();

                // Atualizar PedidoCompras
                $pedidoGeral = PedidoCompra::find($idger);
                if ($pedidoGeral) {
                    $pedidoGeral->nota_servico_processado = false;
                    $pedidoGeral->data_alteracao = now();
                    $pedidoGeral->save();
                }

                if ($tipo == 2) {
                    // Serviço → limpa NF nos serviços
                    foreach ($servicos as $serv) {
                        $serv->numero_nota_fiscal_servicos = null;
                        $serv->data_alteracao = now();
                        $serv->save();
                    }
                } else {
                    // Peças → limpa NF nas peças
                    foreach ($pecas as $peca) {
                        $peca->numero_nota_fiscal_pecas = null;
                        $peca->data_alteracao = now();
                        $peca->save();
                    }
                }

                // Excluir NF Avulsa
                if ($tiponf == 'Notas Avulsas') {
                    DB::table('nf_avulsa')
                        ->where('id_fornecedor', $idfor)
                        ->where('numero_nf', $nf)
                        ->delete();
                }
            } else {
                // NF lançada no pedido faturado 1x1
                if ($tiponf == 'Compras pela Ordem') {
                    DB::table('pedidos_ordem_aux')
                        ->where('id_pedido_compras', $idped)
                        ->where('id_ordem_servico', $os)
                        ->where('id_nf_compra_servico', $id)
                        ->delete();
                }

                $pedidoCompra = PedidoCompra::find($idped);
                if ($pedidoCompra) {
                    $pedidoCompra->nota_servico_processado = false;
                    $pedidoCompra->data_alteracao = now();
                    $pedidoCompra->save();
                }

                if ($tipo == 2) {
                    foreach ($servicos as $serv) {
                        $serv->numero_nota_fiscal_servicos = null;
                        $serv->data_alteracao = now();
                        $serv->save();
                    }
                } else {
                    foreach ($pecas as $peca) {
                        $peca->numero_nota_fiscal_pecas = null;
                        $peca->data_alteracao = now();
                        $peca->save();
                    }
                }

                if ($tiponf == 'Notas Avulsas') {
                    DB::table('nf_avulsa')
                        ->where('id_fornecedor', $idfor)
                        ->where('numero_nf', $nf)
                        ->delete();
                }
            }
            Log::info('Dados para exclusão:', [
                'id' => $id,
                'idger' => $idger,
                'os' => $os,
                'nf' => $nf,
                'idfor' => $idfor
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Nota Fiscal Desvinculada com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir nota: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function searchChaves(Request $request)
    {
        try {
            $term = strtolower($request->get('term'));

            // Cache para melhorar performance
            $chaves = Cache::remember('chaves_search_' . $term, now()->addMinutes(30), function () use ($term) {
                return VListarPedidosNf::where('chave_nf', $term)
                    ->orderBy('chave_nf')
                    ->limit(30)
                    ->get([
                        'id_pedido_compras as value',
                        'chave_nf as label'
                    ]);
            });

            return response()->json($chaves);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar chaves NF: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar chaves NF.'], 500);
        }
    }

    public function getChaveById($id)
    {
        // Cache para melhorar performance
        $chave = Cache::remember('chave_nf_' . $id, now()->addHours(24), function () use ($id) {
            return VListarPedidosNf::where('id_pedido_compras', $id)
                ->select('id_pedido_compras as value', 'chave_nf as label')
                ->firstOrFail();
        });

        return response()->json($chave);
    }
}
