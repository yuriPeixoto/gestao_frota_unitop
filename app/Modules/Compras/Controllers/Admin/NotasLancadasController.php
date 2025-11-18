<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Models\NotaFiscalAvulsa;
use App\Modules\Configuracoes\Models\User;
use App\Models\VListarPedidosNf;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotasLancadasController extends Controller
{
    use ExportableTrait, LoteDownloadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obter parâmetros de filtragem
        $filtros = $request->only([
            'data_inclusao',
            'data_solicitacao',
            'id_solicitante',
            'nome_fornecedor',
            'id_nf_avulsa',
            'numero_nf',
            'serie_nf',
            'data_emissao',
        ]);

        // Detecta se nenhum filtro foi aplicado (para evitar cargas massivas e timeout)
        $semFiltros = true;
        foreach ($filtros as $v) {
            if (!empty($v)) { $semFiltros = false; break; }
        }

        // Quantidade máxima a carregar sem filtros
        $limiteSemFiltro = 300; // ajuste conforme necessidade/performace

        // ---------------------------
        // 🔹 NOTAS AVULSAS
        // ---------------------------
        $queryAvulsas = NotaFiscalAvulsa::with(['fornecedor', 'pedidoCompra', 'usuario'])
            ->orderBy('data_inclusao', 'desc');

        if (! empty($filtros['data_inclusao'])) {
            $dataInicio = \Carbon\Carbon::parse($filtros['data_inclusao'])->startOfDay();
            $dataFim = \Carbon\Carbon::parse($filtros['data_inclusao'])->endOfDay();
            $queryAvulsas->whereBetween('data_inclusao', [$dataInicio, $dataFim]);
        }

        if (! empty($filtros['id_nf_avulsa'])) {
            $queryAvulsas->where('id_nf_avulsa', $filtros['id_nf_avulsa']);
        }

        if (! empty($filtros['nome_fornecedor'])) {
            $queryAvulsas->whereHas('fornecedor', function ($query) use ($filtros) {
                $query->where('nome_fornecedor', 'like', "%{$filtros['nome_fornecedor']}%");
            });
        }

        if (! empty($filtros['numero_nf'])) {
            $queryAvulsas->porNumeroNf($filtros['numero_nf']);
        }

        if (! empty($filtros['serie_nf'])) {
            $queryAvulsas->where('serie_nf', $filtros['serie_nf']);
        }

        if (! empty($filtros['data_emissao'])) {
            $queryAvulsas->whereDate('data_emissao', $filtros['data_emissao']);
        }

        if ($semFiltros) {
            $queryAvulsas->limit($limiteSemFiltro);
        }

        $notasAvulsas = $queryAvulsas->get()->map(function ($nota) {
            return [
                'id' => $nota->id_nf_avulsa,
                'tipo' => 'Avulsa',
                'data_inclusao' => $nota->data_inclusao,
                'numero_pedido' => $nota->numero_do_pedido,
                'fornecedor' => $nota->fornecedor?->nome_fornecedor ?? 'N/A',
                'numero_nf' => $nota->numero_nf,
                'serie_nf' => $nota->serie_nf,
                'data_emissao' => $nota->data_emissao,
                'valor_total' => $nota->valor_total_nf,
                'valor_servico' => $nota->valor_pecas,
                'usuario' => $nota->usuario?->name ?? 'Sistema',
                'origem' => 'Nota Avulsa',
                'objeto' => $nota,
            ];
        });

        // ---------------------------
        // 🔹 PEDIDOS COM NOTAS (VIEW)
        // ---------------------------
        $queryPedidosNf = VListarPedidosNf::query()
            ->orderBy('data_inclusao', 'desc');

        if (! empty($filtros['data_inclusao'])) {
            $dataInicio = \Carbon\Carbon::parse($filtros['data_inclusao'])->format('Y-m-d');
            $queryPedidosNf->whereDate('data_inclusao', $dataInicio);
        }

        if (! empty($filtros['data_solicitacao'])) {
            $dataInicio = \Carbon\Carbon::parse($filtros['data_solicitacao'])->format('Y-m-d');
            $queryPedidosNf->whereDate('data_solicitacao', $dataInicio);
        }

        if (! empty($filtros['id_solicitante'])) {
            $queryPedidosNf->where('id_user', $filtros['id_solicitante']);
        }

        if (! empty($filtros['nome_fornecedor'])) {
            $queryPedidosNf->where('nome_fornecedor', 'like', "%{$filtros['nome_fornecedor']}%");
        }

        if (! empty($filtros['numero_nf'])) {
            $queryPedidosNf->where('numero_nf', $filtros['numero_nf']);
        }

        if ($semFiltros) {
            $queryPedidosNf->limit($limiteSemFiltro);
        }

        $pedidosNf = $queryPedidosNf->get()->map(function ($nota) {
            return [
                'id' => $nota->id ?? null,
                'tipo' => $nota->solicitacao, // "Compras pela Ordem", "Entrada Estoque", etc.
                'data_inclusao' => $nota->data_inclusao,
                'numero_pedido' => $nota->id_pedido_compras,
                'fornecedor' => $nota->nome_fornecedor,
                'numero_nf' => $nota->numero_nf,
                'serie_nf' => $nota->serie_nf,
                'data_emissao' => $nota->data_emissao,
                'valor_total' => $nota->valor_nota_fiscal,
                'valor_servico' => $nota->valorservico,
                'usuario' => $nota->solicitante,
                'origem' => $nota->solicitacao,
                'objeto' => $nota,
            ];
        });

        // ---------------------------
        // 🔹 JUNTAR AS DUAS FONTES
        // ---------------------------
        $notasLancadas = $notasAvulsas
            ->concat($pedidosNf)
            ->sortByDesc('data_inclusao');

        // Paginação manual
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $items = $notasLancadas->slice($offset, $perPage)->all();
        $paginatedNotasLancadas = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $notasLancadas->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 🔹 Fornecedores para filtro
        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        // 🔹 Solicitantes para filtro
        $solicitantes = User::select('id as value', 'name as label')
            ->orderBy('name', 'desc')
            ->limit(30)
            ->get();

        // 🔹 Números de NF (avulsas)
        $numeros = NotaFiscalAvulsa::select('numero_nf as value', 'numero_nf as label')
            ->whereNotNull('numero_nf')
            ->distinct()
            ->orderBy('numero_nf')
            ->limit(200)
            ->get();

        return view('admin.compras.notas-lancadas.index', compact(
            'paginatedNotasLancadas',
            'fornecedores',
            'solicitantes',
            'filtros',
            'numeros'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, string $tipo)
    {
        if ($tipo === 'avulsa') {
            $nota = NotaFiscalAvulsa::with(['fornecedor', 'pedidoCompra', 'usuario'])->findOrFail($id);

            return view('admin.notafiscalavulsa.show', ['notaFiscal' => $nota]);
        } else {
            $nota = VListarPedidosNf::where('id', $id)->firstOrFail();

            return view('admin.compras.pedidos-notas.show', ['pedidoNota' => $nota]);
        }
    }

    /**
     * Export the data to PDF.
     */
    protected function getValidExportFilters()
    {
        return [
            'nome_fornecedor',
            'id',
            'numero_nf',
            'serie_nf',
        ];
    }

    protected function buildExportQuery(Request $request, array $ids = [])
    {
        $query = NotaFiscalAvulsa::query();

        if ($request->filled('nome_fornecedor')) {
            $query->where('nome_fornecedor', $request->nome_fornecedor);
        }

        if ($request->filled('numero_nf')) {
            $query->where('numero_nf', $request->numero_nf);
        }

        if (! empty($ids)) {
            $query->whereIn('id_nf_avulsa', $ids);
        }

        return $query;
    }

    public function exportPdf(Request $request)
    {
        try {
            $ids = $request->input('id', []);

            if (is_string($ids)) {
                $ids = array_filter(explode(',', $ids));
            }

            $query = $this->buildExportQuery($request);

            if (! empty($ids)) {
                $query->whereIn('id_nf_avulsa', $ids);
            }

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (! $this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true,
                ]);
            }

            if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');
                $pdf->loadView('admin.compras.notas-lancadas.pdf', compact('data'));

                return $pdf->download('notas_lancadas_'.date('Y-m-d_His').'.pdf');
            } else {
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => 'Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.',
                    'export_confirmation' => true,
                    'export_url' => $currentUrl,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: '.$e->getMessage(),
                'export_error' => true,
            ]);
        }
    }

    public function exportCsv(Request $request)
    {
        $ids = $request->input('id', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        $query = $this->buildExportQuery($request, $ids);

        if (! empty($ids)) {
            $query->whereIn('id_nf_avulsa', $ids);
        }

        $query->orderBy('id_nf_avulsa');

        // Carrega os dados das notas fiscais com o usuário relacionado
        $notas = NotaFiscalAvulsa::with('usuario') // assumindo que há um relacionamento 'usuario' no modelo
            ->whereIn('id_nf_avulsa', $ids)
            ->get();

        $fornecedor = NotaFiscalAvulsa::with('fornecedor') // assumindo que há um relacionamento 'usuario' no modelo
            ->whereIn('id_nf_avulsa', $ids)
            ->get();

        $columns = [
            'id_nf_avulsa' => 'ID',
            'data_inclusao' => 'Data Inclusao',
            'usuario.name' => 'Solicitante', // assumindo que o relacionamento retorna um modelo User com campo 'nome'
            'fornecedor.nome_fornecedor' => 'Nome Fornecedor',
            'numero_nf' => 'Numero NF',
            'chave_nf' => 'Chave NF',
            'serie_nf' => 'Serie NF',
            'data_emissao' => 'Data Emissão',
            'valor_pecas' => 'Valor Serviço',
            'valor_total_nf' => 'Valor Nota Fiscal',
        ];

        return $this->exportToCsv($request, $query, $columns, 'lancamento-notas', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {

        $ids = $request->input('id', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        $query = $this->buildExportQuery($request, $ids);

        if (! empty($ids)) {
            $query->whereIn('id_nf_avulsa', $ids);
        }

        $query->orderBy('id_nf_avulsa');

        // Carrega os dados das notas fiscais com o usuário relacionado
        $notas = NotaFiscalAvulsa::with('usuario') // assumindo que há um relacionamento 'usuario' no modelo
            ->whereIn('id_nf_avulsa', $ids)
            ->get();

        $fornecedor = NotaFiscalAvulsa::with('fornecedor') // assumindo que há um relacionamento 'usuario' no modelo
            ->whereIn('id_nf_avulsa', $ids)
            ->get();

        $columns = [
            'id_nf_avulsa' => 'ID',
            'data_inclusao' => 'Data Inclusao',
            'usuario.name' => 'Solicitante', // assumindo que o relacionamento retorna um modelo User com campo 'nome'
            'fornecedor.nome_fornecedor' => 'Nome Fornecedor',
            'numero_nf' => 'Numero NF',
            'chave_nf' => 'Chave NF',
            'serie_nf' => 'Serie NF',
            'data_emissao' => 'Data Emissão',
            'valor_pecas' => 'Valor Serviço',
            'valor_total_nf' => 'Valor Nota Fiscal',
        ];

        return $this->exportToExcel($request, $query, $columns, 'lancamento-notas', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {

        $ids = $request->input('id', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        $query = $this->buildExportQuery($request, $ids);

        if (! empty($ids)) {
            $query->whereIn('id_nf_avulsa', $ids);
        }

        $query->orderBy('id_nf_avulsa');

        // Carrega os dados das notas fiscais com o usuário relacionado
        $notas = NotaFiscalAvulsa::with('usuario') // assumindo que há um relacionamento 'usuario' no modelo
            ->whereIn('id_nf_avulsa', $ids)
            ->get();

        $fornecedor = NotaFiscalAvulsa::with('fornecedor') // assumindo que há um relacionamento 'usuario' no modelo
            ->whereIn('id_nf_avulsa', $ids)
            ->get();

        $structure = [
            'id_nf_avulsa' => 'id_nf_avulsa',
            'data_inclusao' => 'data_inclusao',
            'usuario.name' => 'usuario.name', // assumindo que o relacionamento retorna um modelo User com campo 'nome'
            'fornecedor.nome_fornecedor' => 'fornecedor.nome_fornecedor',
            'numero_nf' => 'numero_nf',
            'chave_nf' => 'chave_nf',
            'serie_nf' => 'serie_nf',
            'data_emissao' => 'data_emissao',
            'valor_pecas' => 'valor_pecas',
            'valor_total_nf' => 'valor_total_nf',
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

    public function listaCompra(Request $request)
    {
        // Filtros gerais
        $filtros = $request->only([
            'data_inclusao',
            'data_solicitacao',
            'id_solicitante',
            'nome_fornecedor',
            'id_nf_avulsa',
            'numero_nf',
            'serie_nf',
            'data_emissao'
        ]);

        // Checkbox tipo de compra
        $tipoCompra = $request->input('tipo_compra');
        if (is_string($tipoCompra)) {
            $tipoCompra = explode(',', $tipoCompra);
        }
        if (!is_array($tipoCompra)) {
            $tipoCompra = [];
        }

        // Detecta se nenhum filtro foi aplicado (para evitar cargas massivas e timeout)
        $semFiltros = empty(array_filter($filtros)) && empty($tipoCompra);
        $limiteSemFiltro = 300;

        // ---------------------------
        // 🔹 NOTAS AVULSAS
        // ---------------------------
        $queryAvulsas = NotaFiscalAvulsa::with(['fornecedor', 'pedidoCompra', 'usuario'])
            ->orderBy('data_inclusao', 'desc');

        // Filtros específicos
        if (!empty($filtros['data_inclusao'])) {
            $dataInicio = \Carbon\Carbon::parse($filtros['data_inclusao'])->startOfDay();
            $dataFim = \Carbon\Carbon::parse($filtros['data_inclusao'])->endOfDay();
            $queryAvulsas->whereBetween('data_inclusao', [$dataInicio, $dataFim]);
        }

        if (!empty($filtros['id_nf_avulsa'])) {
            $queryAvulsas->where('id_nf_avulsa', $filtros['id_nf_avulsa']);
        }

        if (!empty($filtros['nome_fornecedor'])) {
            $queryAvulsas->whereHas('fornecedor', function ($query) use ($filtros) {
                $query->where('nome_fornecedor', 'like', "%{$filtros['nome_fornecedor']}%");
            });
        }

        if (!empty($filtros['numero_nf'])) {
            $queryAvulsas->porNumeroNf($filtros['numero_nf']);
        }

        if (!empty($filtros['serie_nf'])) {
            $queryAvulsas->where('serie_nf', $filtros['serie_nf']);
        }

        if (!empty($filtros['data_emissao'])) {
            $queryAvulsas->whereDate('data_emissao', $filtros['data_emissao']);
        }

        // Se "Notas Avulsas" não foi selecionado, exclui tudo
        if (!in_array('Notas Avulsas', $tipoCompra)) {
            $queryAvulsas->whereRaw('1=0');
        }

        if ($semFiltros) {
            $queryAvulsas->limit($limiteSemFiltro);
        }

        $notasAvulsas = $queryAvulsas->get()->map(function ($nota) {
            return [
                'id'            => $nota->id_nf_avulsa,
                'tipo'          => 'Notas Avulsas',
                'data_inclusao' => $nota->data_inclusao,
                'numero_pedido' => $nota->numero_do_pedido,
                'fornecedor'    => $nota->fornecedor?->nome_fornecedor ?? 'N/A',
                'numero_nf'     => $nota->numero_nf,
                'serie_nf'      => $nota->serie_nf,
                'data_emissao'  => $nota->data_emissao,
                'valor_total'   => $nota->valor_total_nf,
                'valor_servico' => $nota->valor_pecas,
                'usuario'       => $nota->usuario?->name ?? 'Sistema',
                'origem'        => 'Notas Avulsas',
                'objeto'        => $nota
            ];
        });

        // ---------------------------
        // 🔹 PEDIDOS COM NOTAS (VIEW)
        // ---------------------------
        $queryPedidosNf = VListarPedidosNf::query()
            ->orderBy('data_inclusao', 'desc');

        if (!empty($filtros['data_inclusao'])) {
            $dataInicio = \Carbon\Carbon::parse($filtros['data_inclusao'])->format('Y-m-d');
            $queryPedidosNf->whereDate('data_inclusao', $dataInicio);
        }

        if (!empty($filtros['data_solicitacao'])) {
            $dataInicio = \Carbon\Carbon::parse($filtros['data_solicitacao'])->format('Y-m-d');
            $queryPedidosNf->whereDate('data_solicitacao', $dataInicio);
        }

        if (!empty($filtros['id_solicitante'])) {
            $queryPedidosNf->where('id_user', $filtros['id_solicitante']);
        }

        if (!empty($filtros['nome_fornecedor'])) {
            $queryPedidosNf->where('nome_fornecedor', 'like', "%{$filtros['nome_fornecedor']}%");
        }

        if (!empty($filtros['numero_nf'])) {
            $queryPedidosNf->where('numero_nf', $filtros['numero_nf']);
        }

        // Filtrar pela coluna "solicitacao" se tipoCompra foi selecionado
        if (!empty($tipoCompra)) {
            $queryPedidosNf->whereIn('solicitacao', $tipoCompra);
        }

        if ($semFiltros) {
            $queryPedidosNf->limit($limiteSemFiltro);
        }

        // 🔹 Fornecedores para filtro
        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        // 🔹 Solicitantes para filtro
        $solicitantes = User::select('id as value', 'name as label')
            ->orderBy('name', 'desc')
            ->limit(30)
            ->get();

        // 🔹 Números de NF (avulsas)
        $numeros = NotaFiscalAvulsa::select('numero_nf as value', 'numero_nf as label')
            ->whereNotNull('numero_nf')
            ->distinct()
            ->orderBy('numero_nf')
            ->limit(200)
            ->get();

        $pedidosNf = $queryPedidosNf->get()->map(function ($nota) {
            return [
                'id'            => $nota->id ?? null,
                'tipo'          => $nota->solicitacao,
                'data_inclusao' => $nota->data_inclusao,
                'numero_pedido' => $nota->id_pedido_compras,
                'fornecedor'    => $nota->nome_fornecedor,
                'numero_nf'     => $nota->numero_nf,
                'serie_nf'      => $nota->serie_nf,
                'data_emissao'  => $nota->data_emissao,
                'valor_total'   => $nota->valor_nota_fiscal,
                'valor_servico' => $nota->valorservico,
                'usuario'       => $nota->solicitante,
                'origem'        => $nota->solicitacao,
                'objeto'        => $nota
            ];
        });

        // ---------------------------
        // 🔹 JUNTAR AS DUAS FONTES
        // ---------------------------
        $notasLancadas = $notasAvulsas
            ->concat($pedidosNf)
            ->sortByDesc('data_inclusao');

        // Paginação manual
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $items = $notasLancadas->slice($offset, $perPage)->all();
        $paginatedNotasLancadas = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $notasLancadas->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // ---------------------------
        // 🔹 RETORNO
        // ---------------------------
        if ($request->ajax()) {
            return view('admin.compras.notas-lancadas._table', compact(
                'paginatedNotasLancadas',
                'solicitantes',
                'fornecedores',
                'numeros'
            ));
        }

        return view('admin.compras.notas-lancadas.index', compact(
            'paginatedNotasLancadas',
            'solicitantes',
            'fornecedores',
            'numeros',
            'filtros'
        ));
    }
}
