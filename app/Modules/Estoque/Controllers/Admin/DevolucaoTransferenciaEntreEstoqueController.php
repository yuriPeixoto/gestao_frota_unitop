<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Estoque\Models\DevolucaoTransferenciaEstoqueRequisicao;

class DevolucaoTransferenciaEntreEstoqueController extends Controller
{

    protected $devolucao;

    public function __construct(DevolucaoTransferenciaEstoqueRequisicao $devolucao)
    {
            $this->devolucao = $devolucao;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $devolucao = $this->devolucao->paginate();

        return view('admin.devolucaoTransferenciaEntreEstoque.index',compact('devolucao'));
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
}
