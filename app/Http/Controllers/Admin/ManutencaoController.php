<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Manutencao;

class ManutencaoController extends Controller
{
    protected $manutencao;

    public function __construct(Manutencao $manutencao)
    {
        $this->manutencao = $manutencao;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $results = $this->manutencao
            ->join('tipomanutencao', 'manutencao.id_tipo_manutencao', '=', 'tipomanutencao.id_tipo_manutencao')
            ->select('manutencao.*', 'tipomanutencao.tipo_manutencao_descricao as tipo_manutencao_descricao', 'tipomanutencao.id_tipo_manutencao');

        $results->when($request->filled('search'), function ($results) use ($request) {
            $results->where('descricao_manutencao', $request->search);
        });

        $results = $results->orderBy('data_inclusao', 'desc')->paginate();

        $results->getCollection()->transform(function ($item) {
            $item->data_inclusao = Carbon::parse($item->data_inclusao)->format('d/m/Y H:i');
            $item->data_alteracao = Carbon::parse($item->data_alteracao)->format('d/m/Y H:i');
            return $item;
        });
        return view('admin.manutencao.index', compact('results'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipoManutencao = $this->getTipoManutencao();
        return view('admin.manutencao.create', compact('tipoManutencao'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tipo_manutencao' => 'required|max:255',
            'descricao_manutencao' => 'required|max:255',
        ]);

        $this->manutencao->create([
            'data_inclusao' => now(),
            'id_tipo_manutencao' => $request->id_tipo_manutencao,
            'descricao_manutencao' => $request->descricao_manutencao,
            'ativar' => $request->ativar,
            'km_configuracao' => $request->km_configuracao,
            'tempo_configuracao' => $request->tempo_configuracao,
            'horas' => $request->horas,
            'eventos' => $request->eventos,
            'combustivel' => $request->combustivel,
            'auxiliar' => $request->auxiliar,
        ]);
        return redirect()->route('admin.manutencoes.index');
    }

    public function getTipoManutencao()
    {
        return DB::connection('pgsql')->table('tipomanutencao')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'value' => $item->id_tipo_manutencao,
                    'label' => "$item->id_tipo_manutencao - $item->tipo_manutencao_descricao"
                ];
            });
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tipoManutencao = $this->getTipoManutencao();
        $manutencao = $this->manutencao->where('id_manutencao', $id)->first();

        return  view('admin.manutencao.edit', compact('tipoManutencao', 'manutencao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'id_tipo_manutencao' => 'required|max:255',
            'descricao_manutencao' => 'required|max:255',
        ]);

        $this->manutencao->where('id_manutencao', $id)->update([
            'data_alteracao' => now(),
            'id_tipo_manutencao' => $request->id_tipo_manutencao,
            'descricao_manutencao' => $request->descricao_manutencao,
            'ativar' => $request->ativar,
            'km_configuracao' => $request->km_configuracao,
            'tempo_configuracao' => $request->tempo_configuracao,
            'horas' => $request->horas,
            'eventos' => $request->eventos,
            'combustivel' => $request->combustivel,
            'auxiliar' => $request->auxiliar,
        ]);

        return redirect()->route('admin.manutencoes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
