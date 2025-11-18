<?php

namespace App\Modules\RelatoriosGerenciais\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class RelatorioExtratoContaFornecedor extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Veiculo::query();

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        return view('admin.relatorioextratocontafornecedor.index', compact('placa', 'filial'));
    }
}
