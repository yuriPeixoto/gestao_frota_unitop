<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecebimentoImobilizado;
use Illuminate\Http\Request;

class RecebimentoImobilizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = RecebimentoImobilizado::query();

        // Aplicar filtros
        if ($request->filled('id_recebimento_imobilizado')) {
            $query->where('id_recebimento_imobilizado', $request->id_recebimento_imobilizado);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereRaw("data_inclusao::date >= ?", [$request->data_inclusao]);
        }

        if ($request->filled('data_alteracao')) {
            $query->whereRaw("data_alteracao::date <= ?", [$request->data_alteracao]);
        }

        if ($request->filled('id_usuario')) {
            $query->where('id_usuario', $request->id_usuario);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $relacaoImobilizados = $query->latest('id_recebimento_imobilizado')
            ->orderBy('id_recebimento_imobilizado', 'desc')
            ->where('is_recebimento', '=', true)
            ->paginate(15)
            ->appends($request->query());


        $id_recebimento_imobilizado = $this->getIdrecebimentoImobilizados();

        return view(
            'admin.recebimentoimobilizado.index',
            compact(
                'relacaoImobilizados',
                'id_recebimento_imobilizado',
            )
        );
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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

    private function getIdrecebimentoImobilizados()
    {
        return RecebimentoImobilizado::select('id_recebimento_imobilizado as value', 'id_recebimento_imobilizado as label')
            ->where('is_recebimento', '=', true)
            ->orderBy('id_recebimento_imobilizado', 'desc')
            ->get()
            ->toArray();
    }
}
