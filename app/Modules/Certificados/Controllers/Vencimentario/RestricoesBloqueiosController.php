<?php

namespace App\Modules\Certificados\Controllers\Vencimentario;

use App\Http\Controllers\Controller;

use App\Modules\Certificados\Models\RestricoesBloqueios;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class RestricoesBloqueiosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query =  RestricoesBloqueios::query();

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        $veiculos = Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->limit(30)->get();

        $restricoes = $query->paginate(20); //

        return view('admin.restricoesbloqueios.index', [
            'veiculos' => $veiculos,
            'restricoes' => $restricoes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RestricoesBloqueios $restricoesBloqueios)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RestricoesBloqueios $restricoesBloqueios)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RestricoesBloqueios $restricoesBloqueios)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RestricoesBloqueios $restricoesBloqueios)
    {
        //
    }
}
