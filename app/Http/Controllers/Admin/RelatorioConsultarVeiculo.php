<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class RelatorioConsultarVeiculo extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Veiculo::query();

        // filtro por código individual
        if ($request->filled('cod_veiculo')) {
            $query->where('id_veiculo', $request->input('cod_veiculo'));
        }

        // filtro por múltiplas placas
        if ($request->filled('id_veiculo')) {
            $query->whereIn('id_veiculo', (array) $request->input('id_veiculo'));
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->input('id_categoria'));
        }

        $categoria = CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')
            ->orderBy('descricao_categoria')
            ->get();

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $listagem = $query
            ->with(['condutor'])
            ->orderByDesc('id_veiculo')
            ->paginate(10);

        return view('admin.relatorioconsultarveiculo.index', compact('categoria', 'placa', 'listagem'));
    }


    public function abrirModal($id)
    {
        $veiculo = Veiculo::findOrFail($id);

        return view('admin.relatorioconsultarveiculo._modal', compact('veiculo'));
    }

    public function gerarPdf() {}
    public function gerarExcel() {}
    public function gerarXls() {}
    public function gerarXml() {}
}
