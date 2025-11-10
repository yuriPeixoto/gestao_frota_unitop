<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use Illuminate\Support\Facades\Cache;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        $produtos = Cache::remember('produtos_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return Produto::whereRaw('LOWER(descricao_produto) LIKE ?', ["%{$term}%"])
                ->orderBy('descricao_produto')
                ->limit(30)
                ->get(['id_produto as value', 'descricao_produto as label']);
        });

        return response()->json($produtos);
    }

    public function getById($id)
    {
        // Cache para melhorar performance
        $produtos = Cache::remember('produto_' . $id, now()->addHours(24), function () use ($id) {
            return Produto::findOrFail($id);
        });

        return response()->json([
            'value' => $produtos->id_produto,
            'label' => $produtos->descricao_produto
        ]);
    }
}
