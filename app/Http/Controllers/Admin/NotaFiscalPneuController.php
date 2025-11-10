<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaFiscalPneu;

class NotaFiscalPneuController extends Controller
{
    public function search(Request $request)
    {
        $term = strtolower($request->get('term', ''));

        $notasFiscais = NotaFiscalPneu::with('fornecedor')
            ->whereRaw('LOWER(numero_nf) LIKE ?', ["%{$term}%"])
            ->orderBy('data_inclusao', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($nf) {
                return [
                    'value' => $nf->id_nota_fiscal_pneu,
                    'label' => 'NF: ' . $nf->numero_nf . ' - Série: ' . $nf->serie
                ];
            });

        return response()->json($notasFiscais);
    }


    public function getById($id)
    {
        $nf = NotaFiscalPneu::with('fornecedor')->findOrFail($id);

        return response()->json([
            'value' => $nf->id_nota_fiscal_pneu,
            'label' => 'NF: ' . $nf->numero_nf . ' - Série: ' . $nf->serie
        ]);
    }
}
