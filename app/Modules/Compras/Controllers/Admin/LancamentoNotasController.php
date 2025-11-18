<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Compras\Models\Fornecedor;
use App\Modules\Manutencao\Models\NfCompraServico;
use App\Modules\Manutencao\Models\OrdemServico;
use App\Modules\Manutencao\Models\OrdemServicoPecas;
use App\Modules\Manutencao\Models\OrdemServicoServicos;
use App\Modules\Compras\Models\PedidoCompra;
use App\Models\PedidosOrdemAux;
use App\Models\VPedidosServicosNota;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LancamentoNotasController extends Controller
{
    protected $limit = 20;
    use ExportableTrait, LoteDownloadTrait;

    public function index(Request $request)
    {

        // Dados para os filtros
        $query = VPedidosServicosNota::query();

        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderByDesc('nome_fornecedor')
            ->limit(30)
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        $placas = VPedidosServicosNota::distinct('placa')
            ->limit(30)
            ->whereNotNull('placa')
            ->pluck('placa');

        $ordemServico = VPedidosServicosNota::distinct('id_ordem_servico')
            ->limit(30)
            ->orderBy('id_ordem_servico')
            ->pluck('id_ordem_servico');



        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        if ($request->filled('placa')) {
            $query->whereIn('placa', (array)$request->placa);
        }

        if ($request->filled('nome_fornecedor')) {
            $query->where('id_fornecedor', $request->nome_fornecedor);
        }

        if ($request->filled('id_pedido_compras')) {
            $pedidos = array_map('trim', explode(',', $request->id_pedido_compras));
            if (!preg_match('/[a-zA-Z!@#$%^&*()_+|~=`{}\[\]:";\'<>?.\/\\-]/', $request->id_pedido_compras)) {
                $query->whereIn('id_pedido_compras', $pedidos);
            } else {
                return back()->with('error', 'Formato de código de pedido inválido');
            }
        }

        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->id_ordem_servico);
        }

        $listaCompra = $query->latest('data_inclusao')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.compras.lancamento-notas.index', compact('listaCompra'), [
            'fornecedores' => $fornecedores,
            'placas' => $placas,
            'ordemServico' => $ordemServico,
            'pedidos' => collect() // Coleção vazia inicial
        ]);
    }

    public function search(Request $request)
    {
        $query = VPedidosServicosNota::query()
            ->with('fornecedor');

        // Aplicar filtros
        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        if ($request->filled('placa')) {
            $query->whereIn('placa', (array)$request->placa);
        }

        if ($request->filled('nome_fornecedor')) {
            $query->where('id_fornecedor', $request->nome_fornecedor);
        }

        if ($request->filled('id_pedido_compras')) {
            $pedidos = array_map('trim', explode(',', $request->id_pedido_compras));
            if (!preg_match('/[a-zA-Z!@#$%^&*()_+|~=`{}\[\]:";\'<>?.\/\\-]/', $request->id_pedido_compras)) {
                $query->whereIn('id_pedido_compras', $pedidos);
            } else {
                return back()->with('error', 'Formato de código de pedido inválido');
            }
        }

        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->id_ordem_servico);
        }

        $pedidos = $query->orderByDesc('id_pedido_compras')
            ->limit(30)
            ->paginate($this->limit);

        // Dados para os filtros
        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();

        $placas = VPedidosServicosNota::distinct('placa')
            ->whereNotNull('placa')
            ->limit(30)
            ->pluck('placa');

        return view('admin.compras.lancamento-notas.index', [
            'pedidos' => $pedidos,
            'fornecedores' => $fornecedores,
            'placas' => $placas,
            'filters' => $request->all()
        ]);
    }

    public function confirmarSelecao(Request $request)
    {
        if (empty($request->selected_ids)) {
            return back()->with('error', 'Selecione pelo menos um pedido');
        }

        $pedidos = VPedidosServicosNota::whereIn('id_pedido_compras', $request->selected_ids)
            ->get();

        // Validar fornecedor único
        if ($pedidos->pluck('id_fornecedor')->unique()->count() > 1) {
            return back()->with('error', 'Não é possível selecionar pedidos de fornecedores diferentes');
        }

        // Validar tipo único
        if ($pedidos->pluck('tipo_compra')->unique()->count() > 1) {
            return back()->with('error', 'Selecione pedidos do mesmo tipo');
        }

        $dados = [
            'array_pedidos' => $request->selected_ids,
            'total_pedido' => $pedidos->sum('valor_total_desconto'),
            'id_fornecedor_' => $pedidos->first()->id_fornecedor,
            'tipo_' => $pedidos->first()->tipo_compra
        ];

        return redirect()->route('compras.pedidos-notas.simple-list', $dados);
    }


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
        $query = VPedidosServicosNota::query();

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

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.compras.lancamento-notas.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('notas_compras_' . date('Y-m-d_His') . '.pdf');
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

    public function exportCsv(Request $request)
    {


        $ids = $request->input('id_pedido_compras', []);

        $query = $this->buildExportQuery($request);

        if (!empty($ids)) {
            $query->whereIn('id_pedido_compras', $ids);
        }

        $query->orderBy('id_pedido_compras');

        $columns = [
            'id_pedido_compras'                     =>      'Cód. Pedido',
            'id_ordem_servico'                      =>      'N° O.S',
            'valor_total_desconto'                  =>      'Vlr Pedido',
            'nome_fornecedor'                       =>      'Nome Fornecedor',
            'cnpj_fornecedor'                       =>      'CNPJ',
            'data_inclusao'                         =>      'Data Pedido',
            'placa'                                 =>      'Placa',
            'tipo_compra'                           =>      'Tipo Compra'
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
            'id_pedido_compras'                     =>      'Cód. Pedido',
            'id_ordem_servico'                      =>      'N° O.S',
            'valor_total_desconto'                  =>      'Vlr Pedido',
            'nome_fornecedor'                       =>      'Nome Fornecedor',
            'cnpj_fornecedor'                       =>      'CNPJ',
            'data_inclusao'                         =>      'Data Pedido',
            'placa'                                 =>      'Placa',
            'tipo_compra'                           =>      'Tipo Compra'
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
            'id_pedido_compras'     => 'id_pedido_compras',  // Nome real da coluna
            'id_ordem_servico'      => 'id_ordem_servico',
            'valor_total_desconto'  => 'valor_total_desconto',
            'nome_fornecedor'       => 'nome_fornecedor',
            'cnpj_fornecedor'       => 'cnpj_fornecedor',
            'data_inclusao'         => 'data_inclusao',
            'placa'                 => 'placa',
            'tipo_compra'          => 'tipo_compra'
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

    public function lancarNotaFiscalServico(Request $request, $id)
    {
        $request->validate([
            'numero_nf'     => 'required|integer',
            'serie_nf'      => 'required|string|max:20',
            'id_fornecedor' => 'required|integer|exists:fornecedores,id',
            'valor_servico' => 'required|string',
            'valor_total_nota'  => 'required|string',
            'chave_nf'      => 'required|string|max:44',
        ]);

        $valorServico = floatval(str_replace(',', '.', str_replace('.', '', $request->valor_servico)));
        $valorNota = floatval(str_replace(',', '.', str_replace('.', '', $request->valor_total_nota)));
        $valorTotalPedido = session('valor_total_pedido');

        $chave = $request->chave_nf ?? '0';

        //validar chave
        if (preg_match('/[a-zA-Z!@#$%^&*(),.?":{}|<>]/', $chave)) {
            return back()->with('error', 'A CHAVE contém letras ou caracteres especiais.');
        }

        if ($valorServico != $valorTotalPedido) {
            return redirect()->back()->with('error', 'A soma dos valores dos pedidos é diferente do valor total da nota fiscal. ');
        }

        if ($valorNota > $valorServico) {
            return redirect()->back()->with('error', 'Valor liquido não pode ser maior que o Valor Bruto. ');
        }

        $existe = NfCompraServico::where('numero_nf', $request->numero_nf)
            ->where('serie_nf', $request->serie_nf)
            ->where('id_fornecedor', $request->id_fornecedor)
            ->first();

        if ($existe) {
            return redirect()->back()->with('error', 'Essa nota já foi lançada.');
        }

        DB::beginTransaction();
        try {
            $nota = new NfCompraServico();
            $nota->numero_nf    = $request->numero_nf;
            $nota->serie_nf     = $request->serie_nf;
            $nota->id_fornecedor = $request->id_fornecedor;
            $nota->valor_servico    = $request->valor_servico;
            $nota->valor_total_nota = $request->valor_total_nota;
            $nota->chave_nf         = $request->chave_nf;

            $nota->save();

            DB::commit();

            return redirect()->route('admin.compras.lancamento-notas.index')->back()->with('sucess', 'Nota Lançada com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.compras.lancamento-notas.index')->back()->with('error', 'Erro ao lançar nota: ' . $e->getMessage());
        }
    }

    public function lancarNotaFiscalReforma(Request $request, $id)
    {
        Log::info('Dados recebidos para lançamento de nota:', $request->all());
        Log::info('Campo id_fornecedor recebido:', ['id_fornecedor' => $request->id_fornecedor]);

        // Validação corrigida
        $request->validate([
            'numero_nf'     => 'required|integer',
            'serie_nf'      => 'required|string|max:20',
            'id_fornecedor' => 'required|integer',
            'valor_servico' => 'required|numeric',
            'valor_total_nota'  => 'required|numeric',
            'chave_nf'      => 'nullable|string|max:44', // NFe tem 44 caracteres
            'data_emissao'  => 'required|date_format:Y-m-d',
        ]);

        // Verifica se nota já existe
        $existe = NfCompraServico::where('numero_nf', $request->numero_nf)
            ->where('serie_nf', $request->serie_nf)
            ->where('id_fornecedor', $request->id_fornecedor)
            ->first();

        if ($existe) {
            return redirect()->back()->with('error', 'Essa nota já foi lançada.');
        }

        DB::beginTransaction();
        try {
            $nota = new NfCompraServico();
            $nota->numero_nf        = $request->numero_nf;
            $nota->serie_nf         = $request->serie_nf;
            $nota->id_fornecedor    = $request->id_fornecedor;
            $nota->valor_servico    = $request->valor_servico;
            $nota->valor_total_nota = $request->valor_total_nota;
            $nota->chave_nf         = $request->chave_nf;
            $nota->data_emissao     = $request->data_emissao; // 
            $nota->id_user          = auth()->id();
            $nota->data_inclusao     = now(); // 



            $nota->save();

            // agora relaciona com o pedido
            $pedidoOrdem = PedidosOrdemAux::create([
                //'id_ordem_servico',
                //'id_pedido_compras'        => ,
                'id_nf_compra_servico'     => $nota->nf_compra_servico,
                //'id_nf_compra_servico',
                //'id_pedido_geral'
            ]);

            $pedido = VPedidosServicosNota::where('id_pedido_compras', $id)->first();


            if (!$pedido) {
                throw new Exception('Pedido de compra não encontrado.');
            }

            $tipoCompra = strtoupper(trim($pedido->tipo_compra)); ///Remove espações e coloca em maiusculo
            $ordemServicoId = $pedido->id_ordem_servico;

            if ($tipoCompra === 'COMPRA DE SERVIÇOS') {
                OrdemServicoServicos::where('id_ordem_servico', $ordemServicoId)
                    ->update([
                        'numero_nota_fiscal_servicos' => $request->numero_nf,
                    ]);
                PedidoCompra::where('id_pedido_compras', $id)
                    ->update([
                        'nota_servico_processado' => true,
                    ]);
            } elseif ($tipoCompra === 'COMPRA DE PRODUTOS') {
                OrdemServicoPecas::where('id_ordem_servico', $ordemServicoId)
                    ->update([
                        'numero_nota_fiscal_pecas' => $request->numero_nf,
                    ]);
                PedidoCompra::where('id_pedido_compras', $id)
                    ->update([
                        'nota_servico_processado' => true,
                    ]);
            } else {
                Log::info("NF lançada sem vínculo com ordem de serviço específica (tipo: {$tipoCompra})");
            }

            $ordem = OrdemServico::where('id_ordem_servico', $ordemServicoId)->first();

            if ($ordem) {
                $ordem->id_status_ordem_servico = 7; // novo status
                $ordem->save();
            }


            DB::commit();

            Log::info("Nota lançada com sucesso", ['id' => $nota->id]);

            // Redirecionamento corrigido
            return redirect()->route('admin.compras.lancamento-notas.index')
                ->with('success', 'Nota Lançada com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao lançar nota: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erro ao lançar nota: ' . $e->getMessage());
        }
    }

    public function visualizarModalReforma($id)
    {
        $pedido = VPedidosServicosNota::where('id_pedido_compras', $id)->first();

        if (!$pedido) {
            return response('<p class="text-red-500 text-center py-6">Pedido não encontrado.</p>', 404);
        }

        $reforma = VPedidosServicosNota::where('id_pedido_compras', $id)->get();

        // Busca qualquer nota lançada para esta ordem de serviço
        $notaExistente = OrdemServicoServicos::where('id_ordem_servico', $pedido->id_ordem_servico)
            ->whereNotNull('numero_nota_fiscal_servicos') // somente notas preenchidas
            ->exists();

        return view('components.notafiscal.modal-visualizar', [
            'reforma'   => $reforma,
            'pedido'    => $pedido,
            'bloqueado' => $notaExistente,
            'mensagem'  => $notaExistente
                ? 'Esta nota fiscal já foi lançada para este pedido.'
                : null,
        ]);
    }





    public function visualizarModalServico($id)
    {
        $reforma = VPedidosServicosNota::where('id_pedido_compras', $id)->get();



        return view('components.notafiscal.modal-visualizar', compact('reforma'));
    }

    public function visualizarModalProduto($id)
    {
        $reforma = VPedidosServicosNota::where('id_pedido_compras', $id)->get();


        return view('components.notafiscal.modal-visualizar', compact('reforma'));
    }

    public function listaCompra(Request $request)
    {
        $query = VPedidosServicosNota::query();

        if ($request->filled('tipo_compra')) {
            $tipos = (array) $request->tipo_compra;
            $query->whereIn('tipo_compra', $tipos);
        }

        $pedidos = $query->orderByDesc('id_pedido_compras')
            ->limit(30)
            ->paginate($this->limit);

        // Dados para os filtros
        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();

        $placas = VPedidosServicosNota::distinct('placa')
            ->whereNotNull('placa')
            ->limit(30)
            ->pluck('placa');

        $ordemServico = VPedidosServicosNota::distinct('id_ordem_servico')
            ->limit(30)
            ->orderBy('id_ordem_servico')
            ->pluck('id_ordem_servico');

        $listaCompra = $query->paginate(15)->withQueryString();
        if ($request->ajax()) {
            return view('admin.compras.lancamento-notas._table', compact('listaCompra'));
        }

        return view('admin.compras.lancamento-notas.index', compact(
            'listaCompra',
            'placas',
            'fornecedores',
            'pedidos',
            'ordemServico'
        ));
    }
}
