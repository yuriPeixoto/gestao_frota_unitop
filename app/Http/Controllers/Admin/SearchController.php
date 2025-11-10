<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'table' => 'required|string',
            'field' => 'required|string',
            'query' => 'nullable|string'
        ]);

        try {
            $results = DB::connection('pgsql')->table($validated['table'])
                ->where(DB::raw('LOWER(' . $validated['field'] . ')'), 'like', '%' . strtolower($validated['query']) . '%')
                ->get();

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred during the search.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
