<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Models\PedidoCompra;
use App\Models\SituacaoPedido;
use App\Models\SolicitacaoCompra;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ListaPedidoComprasController extends Controller
{
    public function index(Request $request)
    {
        $query = PedidoCompra::with('fornecedor', 'comprador', 'situacaoPedido', 'solicitacaoCompra', 'itens');

        if ($request->filled('id_pedido_compras')) {
            $query->where('id_pedido_compras', $request->id_pedido_compras);
        }

        if ($request->filled('data_inclusao')) {
            $query->where('data_inclusao', $request->data_inclusao);
        }

        if ($request->filled('id_solicitacoes_compras')) {
            $query->where('id_solicitacoes_compras', $request->id_solicitacoes_compras);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        if ($request->filled('situacao_pedido')) {
            $query->where('situacao_pedido', $request->situacao_pedido);
        }

        if ($request->filled('id_comprador')) {
            $query->where('id_comprador', $request->id_comprador);
        }

        $listagempedido = $query->paginate(10);

        $pedido = PedidoCompra::select('id_pedido_compras as value', 'id_pedido_compras as label')->orderBy('id_pedido_compras', 'asc')->limit(30)->get();
        $solicitacao = SolicitacaoCompra::select('id_solicitacoes_compras as value', 'id_solicitacoes_compras as label')->orderBy('id_solicitacoes_compras', 'asc')->limit(30)->get();
        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')->orderBy('nome_fornecedor', 'asc')->limit(30)->get();
        $comprador = User::select('id as value', 'name as label')->orderBy('name', 'asc')->limit(30)->get();
        $situacao = SituacaoPedido::select('id_situacao_pedido as value', 'descricao_situacao_pedido as label')->orderBy('descricao_situacao_pedido', 'asc')->limit(30)->get();

        return view('admin.listapedidocompra.index', compact(
            'fornecedor',
            'comprador',
            'situacao',
            'pedido',
            'solicitacao',
            'listagempedido'
        ));
    }

    public function visualizarModal(Request $request, $id)
    {


        $listagempedido = PedidoCompra::with(['itens.produtos', 'fornecedor', 'solicitacaoCompra', 'filial'])
            ->findOrFail($id);

        return view('admin.listapedidocompra.modal-view', compact('listagempedido'));
    }

    public function gerarPdf($id)
    {
        $listagempedido = PedidoCompra::with(['itens.produtos', 'fornecedor', 'comprador'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.listapedidocompra.pdf', compact('listagempedido'));
        return $pdf->download("pedido_{$id}.pdf");
    }
}
