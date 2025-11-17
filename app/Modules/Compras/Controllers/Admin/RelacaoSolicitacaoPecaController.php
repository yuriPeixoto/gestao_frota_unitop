<?php


namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RelacaoSolicitacaoPeca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RelacaoSolicitacaoPecaController extends Controller
{
    public function index(Request $request)
    {
        $solicitacoes = RelacaoSolicitacaoPeca::with(['departamento', 'usuarioAbertura', 'filial'])
            ->when($request->id_solicitacao_pecas, function ($query, $id) {
                $query->where('id_solicitacao_pecas', $id);
            })
            ->when($request->situacao, function ($query, $situacao) {
                $query->where('situacao', $situacao);
            })
            ->paginate(10);

        return view('admin.solicitacoes-materiais.index', compact('admin.solicitacoes'));
    }

    public function create()
    {
        return view('admin.compras.solicitacoes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_departamento' => 'required|exists:departamentos,id',
            'id_usuario_abertura' => 'required|exists:users,id',
            'id_filial' => 'required|exists:filiais,id',
            'observacao' => 'nullable|string',
        ]);

        $solicitacao = RelacaoSolicitacaoPeca::create($validated);

        return redirect()->route('admin.compras.solicitacoes.index')->with('success', 'Solicitação criada com sucesso!');
    }

    public function edit(RelacaoSolicitacaoPeca $solicitacao)
    {
        return view('admin.compras.solicitacoes.edit', compact('solicitacao'));
    }

    public function update(Request $request, RelacaoSolicitacaoPeca $solicitacao)
    {
        $validated = $request->validate([
            'id_departamento' => 'required|exists:departamentos,id',
            'id_usuario_abertura' => 'required|exists:users,id',
            'id_filial' => 'required|exists:filiais,id',
            'observacao' => 'nullable|string',
        ]);

        $solicitacao->update($validated);

        return redirect()->route('admin.compras.solicitacoes.index')->with('success', 'Solicitação atualizada com sucesso!');
    }

    public function destroy(RelacaoSolicitacaoPeca $solicitacao)
    {
        $solicitacao->delete();

        return redirect()->route('admin.solicitacoes-materiais.index')->with('success', 'Solicitação excluída com sucesso!');
    }

    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        $solicitacoes = Cache::remember('solicitacao_pecas_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return RelacaoSolicitacaoPeca::select('id_solicitacao_pecas', 'observacao', 'data_inclusao')
                ->whereRaw('CAST(id_solicitacao_pecas AS TEXT) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('LOWER(observacao) LIKE ?', ["%{$term}%"])
                ->orderByDesc('data_inclusao')
                ->limit(30)
                ->get()
                ->map(function ($sol) {
                    return [
                        'label' => (string) $sol->id_solicitacao_pecas,  // só o número
                        'value' => $sol->id_solicitacao_pecas
                    ];

                    // Se quiser mostrar observação ou data no label, poderia fazer algo como:
                    /*
                    $label = "Solicitação #{$sol->id_solicitacao_pecas}";
                    if (!empty($sol->observacao)) {
                        $label .= ' - ' . mb_substr($sol->observacao, 0, 40);
                    }
                    if (!empty($sol->data_inclusao)) {
                        $label .= ' (' . $sol->data_inclusao->format('d/m/Y') . ')';
                    }
                    return [
                        'label' => $label,
                        'value' => $sol->id_solicitacao_pecas
                    ];
                    */
                })->toArray();
        });

        return response()->json($solicitacoes);
    }

    /**
     * Buscar uma solicitação pelo ID
     */
    public function getById($id)
    {
        $sol = RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $id)->first();

        if (!$sol) {
            return response()->json([], 404);
        }

        return response()->json([
            'value' => $sol->id_solicitacao_pecas,
            'label' => (string) $sol->id_solicitacao_pecas,
            'observacao' => $sol->observacao,
            'situacao' => $sol->situacao,
            'data_inclusao' => optional($sol->data_inclusao)->format('d/m/Y H:i'),
        ]);
    }
}
