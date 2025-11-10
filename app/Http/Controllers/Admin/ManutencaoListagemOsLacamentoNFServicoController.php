<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrdemServicoServicos;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ManutencaoListagemOsLacamentoNFServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemServicoServicos::query();


        if ($request->filled('id_ordem_servico_serv')) {
            $query->where('id_ordem_servico_serv', $request->id_ordem_servico_serv);
        }


        $cadastros = $query->latest('id_ordem_servico_serv')
            ->paginate(10)
            ->withQueryString();

        return view('admin.listagemoslacamentoservico.index', array_merge(
            [
                'cadastros'         => $cadastros,
            ]
        ));
    }
}