<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Servico;
use App\Models\Manutencao;
use App\Models\TipoCategoria;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ServicoController extends Controller
{
    protected $servico;
    protected $manutencao;
    protected $tipocategoria;

    public function __construct(Servico $servico, Manutencao $manutencao, TipoCategoria $tipocategoria)
    {
        $this->servico = $servico;
        $this->tipocategoria = $tipocategoria;
        $this->manutencao = $manutencao;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servicos = $this->servico
            ->join('filiais', 'servico.id_filial', '=', 'filiais.id')
            ->join('grupo_servico', 'grupo_servico.id_grupo', '=', 'servico.id_grupo')
            ->join('manutencao', 'manutencao.id_manutencao', '=', 'servico.id_manutencao')
            ->select(
                'servico.*',
                'grupo_servico.id_grupo as id_grupo',
                'grupo_servico.descricao_grupo as grupo',
                'filiais.id as id_filial',
                'filiais.name as filial',
                'manutencao.id_manutencao',
                'manutencao.descricao_manutencao as manutencao'
            )
            ->paginate();

        return view('admin.servicos.index', compact('servicos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $manutencao = $this->getManutecao();
        $grupo = $this->getGrupo();
        $matriz = $this->getfilial();
        $categoria = $this->getTipocategoria();
        $veiculos = $this->getCategoriaVeiculo();

        return view('admin.servicos.create', compact('manutencao', 'matriz', 'grupo', 'categoria', 'veiculos'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function getCategoriaVeiculo()
    {
        return DB::connection('pgsql')->table('categoria_veiculo')
            ->where('descricao_categoria', '!=', null)
            ->get();
        // ->map(function($item){
        //     return (object)[
        //         'value'=>$item->id_categoria,
        //         'label'=>"$item->id_categoria - $item->descricao_categoria"
        //     ];
        // });
    }

    public function getTipocategoria()
    {
        return $this->tipocategoria
            ->where('descricao_categoria', '!=', null)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'value' => $item->id_categoria,
                    'label' => "$item->id_categoria - $item->descricao_categoria"
                ];
            });
    }

    public function getfilial()
    {
        return DB::connection('pgsql')->table('filiais')
            ->where('id', 1)
            ->first();
    }

    public function getManutecao()
    {
        return $this->manutencao
            ->where('descricao_manutencao', '!=', null)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'value' => $item->id_manutencao,
                    'label' => "$item->id_manutencao - $item->descricao_manutencao"
                ];
            });
    }

    public function getGrupo()
    {
        return DB::connection('pgsql')->table('grupo_servico')
            ->where('descricao_grupo', '!=', null)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'value' => $item->id_grupo,
                    'label' => "$item->id_grupo - $item->descricao_grupo"
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
        $manutencao = $this->getManutecao();
        $servico = $servicos = $this->servico
            ->join('filial', 'servico.id_filial', '=', 'filial.id_filial')
            ->join('grupo_servico', 'grupo_servico.id_grupo', '=', 'servico.id_grupo')
            ->join('manutencao', 'manutencao.id_manutencao', '=', 'servico.id_manutencao')
            ->select(
                'servico.*',
                'grupo_servico.id_grupo as id_grupo',
                'grupo_servico.descricao_grupo as grupo',
                'filial.id_filial',
                'filial.descricao_filial as filial',
                'manutencao.id_manutencao',
                'manutencao.descricao_manutencao as manutencao'
            )
            ->where('servico.id_servico', $id)
            ->first();


        return view('admin.servicos.edit', compact('servico', 'manutencao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    /**
     * Buscar fornecedores para autocompletar
     */
    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $servicos = Cache::remember('servico_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return Servico::whereRaw('LOWER(descricao_servico) LIKE ?', ["%{$term}%"])
                ->where('ativo_servico', true)
                ->orderBy('descricao_servico')
                ->limit(30)
                ->get(['id_servico as value', 'descricao_servico as label']);
        });

        return response()->json($servicos);
    }

    public function single($id)
    {
        $servicos = Servico::select('id_servico as value', 'descricao_servico as label')
            ->findOrFail($id);

        return response()->json($servicos);
    }

    /**
     * Buscar um fornecedor pelo ID
     */
    public function getById($id)
    {
        $servicos = Servico::findOrFail($id);

        return response()->json([
            'value' => $servicos->id_servico,
            'label' => $servicos->descricao_servico
        ]);
    }
}
