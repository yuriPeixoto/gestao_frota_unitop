<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;

class ConsultaProdutosTransferencia extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query();

        if ($request->filled('id_produto')) {
            $query->where('id_produto', $request->input('id_produto'));
        }
        if ($request->filled('descricao_produto')) {
            $query->where('descricao_produto', $request->input('descricao_produto'));
        }

        $produto = $query->select('id_produto as value', 'descricao_produto as label')
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get();

        $listagem = Produto::with([
            'produtosImobilizados',
            'itensSolicitacaoCompra',
            'ordemServicoPecas',
            'produtoPorFilial'
        ])->orderByDesc('id_produto')->paginate(10);

        return view('admin.consultaprodutostransferencia.index', compact('produto', 'listagem'));
    }
}
