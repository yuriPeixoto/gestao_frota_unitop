<?php

namespace App\Modules\Checklist\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Checklist\Models\TipoChecklist;
use App\Modules\Configuracoes\Models\Departamento;
use Illuminate\Validation\Rule;


class TipoChecklistController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoChecklist::query();

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('nome')) {
            $query->where('nome', $request->nome);
        }

        if ($request->filled('descricao')) {
            $query->where('descricao', $request->descricao);
        }

        if ($request->filled('multiplas_etapas')) {
            $query->where('multiplas_etapas', $request->multiplas_etapas);
        }

        $tipoChecklist = $query->latest('id')
            ->paginate(40)
            ->appends($request->query());

        $nome = TipoChecklist::select('nome as label', 'nome as value')->orderBy('label')->get()->toArray();

        $descricao = TipoChecklist::select('descricao as label', 'descricao as value')->orderBy('label')->get()->toArray();

        $multiplas_etapas = TipoChecklist::select('multiplas_etapas as label', 'multiplas_etapas as value')->orderBy('label')->get()->toArray();

        return view('admin.tipoChecklist.index', compact('tipoChecklist', 'nome', 'descricao', 'multiplas_etapas'));
    }

    public function findDepartamento()
    {
        $departamento = Departamento::orderBy('descricao_departamento', 'asc')
            ->get();

        $departamento = $departamento->map(function ($departamento) {
            return [
                'label' => $departamento->descricao_departamento,
                'value' => $departamento->id_departamento
            ];
        });

        return $departamento;
    }


    public function findCargo()
    {
        $cargos = DB::connection('pgsql')->table('cargo_usuario')
            ->get()
            ->map(function ($cargo) {
                return [
                    'label' => $cargo->descricao_cargo,
                    'value' => $cargo->id_cargo_usuario
                ];
            });

        return $cargos;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $departamentos = $this->findDepartamento();

        $cargos = $this->findCargo();


        $multiplas_etapas = [
            [
                'label' => 'Sim',
                'value' => 1,
            ],
            [
                'label' => 'Não',
                'value' => 2
            ]
        ];

        $filtro = [
            [
                'label' => 'Departamento',
                'value' => 'Departamento',
            ],
            [
                'label' => 'Cargo',
                'value' => 'Cargo',
            ]
        ];

        return view('admin.tipoChecklist.create', compact('departamentos', 'cargos', 'multiplas_etapas', 'filtro'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'nome' => 'required|max:255',
            'descricao' => 'required|max:255',
            'departamento_id' => Rule::when($request->input('filtro') === 'Departamento', [
                'required',
            ]),
            'cargo_id' => Rule::when($request->input('filtro') === 'Cargo', [
                'required',
            ]),
            'multiplas_etapas' => 'required',
            'filtro' => 'required'
        ]);

        TipoChecklist::create($request->all());

        return redirect('/admin/tipo_checklist');
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
        $departamentos = $this->findDepartamento();
        $cargos = $this->findCargo();


        $checkList = TipoChecklist::where('id', $id)->first();

        $multiplas_etapas = [
            [
                'label' => 'Sim',
                'value' => 1,
            ],
            [
                'label' => 'Não',
                'value' => 2
            ]
        ];

        $filtro = [
            [
                'label' => 'Departamento',
                'value' => 'Departamento',
            ],
            [
                'label' => 'Cargo',
                'value' => 'Cargo',
            ]
        ];

        return view('admin.tipoChecklist.edit', compact('checkList', 'departamentos', 'multiplas_etapas', 'cargos', 'filtro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nome' => 'required|max:255',
            'descricao' => 'required|max:255',
            'departamento_id' => Rule::when($request->input('filtro') === 'Departamento', [
                'required',
            ]),
            'cargo_id' => Rule::when($request->input('filtro') === 'Cargo', [
                'required',
            ]),
            'multiplas_etapas' => 'required',
            'filtro' => 'required'
        ]);




        $result = TipoChecklist::where('id', $id)
            ->update([
                "nome" => $request->nome,
                "descricao" => $request->descricao,
                "departamento_id" => $request->departamento_id,
                'multiplas_etapas' => $request->multiplas_etapas

            ]);

        return redirect('/admin/tipo_checklist');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = TipoChecklist::where('id', $id);
        $result->delete();
        return redirect('/admin/tipo_checklist');
    }
}
