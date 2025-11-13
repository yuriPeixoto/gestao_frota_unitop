<?php

namespace App\Modules\Multas\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Certificados\Models\ClassificacaoMulta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClassificacaoMultaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize   = $request->input('pageSize', 10);
        $searchTerm = $request->input('search');

        $query    = ClassificacaoMulta::query()
            ->select('id_classificacao_multa', 'data_inclusao', 'data_alteracao', 'descricao_multa', 'pontos')
            ->distinct();

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $searchTermLower = strtolower($searchTerm);

                $query->whereRaw('LOWER(CAST(id_classificacao_multa AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(descricao_multa) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(pontos AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%']);
            });
        };

        $query->orderBy('id_classificacao_multa', 'desc');

        $classificacaoMultas = $query->paginate($pageSize)->withQueryString();

        $classificacaoMultasData = $classificacaoMultas->map(function ($classificacaoMultas) {
            return [
                'id'                    => $classificacaoMultas->id_classificacao_multa,
                'data_inclusao'          => format_date($classificacaoMultas->data_inclusao),
                'data_alteracao'         => format_date($classificacaoMultas->data_alteracao) ?? 'Não informado',
                'descricao_multa'        => $classificacaoMultas->descricao_multa,
                'pontos'                 => $classificacaoMultas->pontos
            ];
        })->toArray();

        $totalRegistros = $classificacaoMultas->total();

        $actionIcons = [
            "icon:pencil | tip:Editar | click:editClassificacaoMulta({id})",
            "icon:trash | tip:Excluir | color:red | click:destroyClassificacaoMulta({id}, '{descricao_multa}')",
        ];

        $column_aliases = [
            'id'              => 'ID',
            'data_inclusao'   => 'Data Inclusão',
            'data_alteracao'  => 'Data Alteração',
            'descricao_multa' => 'Descrição Multa',
            'pontos'          => 'Pontos'
        ];
        // dd($classificacaoMultasData);
        return view('admin.classificacaomultas.index', compact('classificacaoMultas', 'actionIcons', 'column_aliases', 'classificacaoMultasData', 'totalRegistros', 'searchTerm'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.classificacaomultas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $classificacaoMultasData = $request->validate([
            'descricao_multa' => 'required|max:500',
            'pontos'          => 'required|max:32'
        ]);

        $classificacaoMultas = new ClassificacaoMulta();

        $classificacaoMultas->data_inclusao   = now();
        $classificacaoMultas->descricao_multa = $classificacaoMultasData['descricao_multa'];
        $classificacaoMultas->pontos          = $classificacaoMultasData['pontos'];

        $classificacaoMultas->save();

        return redirect()->route('admin.classificacaomultas.index')->with('success', 'Classificação de Multa criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassificacaoMulta $classificacaoMultas)
    {
        $classificacaoMultas = ClassificacaoMulta::findOrFail($classificacaoMultas);
        return view('admin.classificacaomultas.show', compact('classificacaoMultas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $classificacaoMultas)
    {
        $classificacaoMultas = ClassificacaoMulta::findOrFail($classificacaoMultas);

        return view('admin.classificacaomultas.edit', compact('classificacaoMultas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $classificacaoMultas)
    {
        // Validação dos dados do formulário
        $classificacaoMultaData = $request->validate([
            'descricao_multa' => 'required|max:500',
            'pontos'          => 'required|max:32',
        ]);

        // Atualizar o modelo com os dados validados
        try {
            $classificacaoMultas = ClassificacaoMulta::findOrFail($classificacaoMultas);
            $classificacaoMultas->data_alteracao = now();
            $classificacaoMultas->update($classificacaoMultaData);
        } catch (\Exception $e) {
            // Captura de exceções e depuração
            Log::error('Erro na atualização:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors('Erro ao atualizar a classificação de multa.');
        }

        // Redirecionar com mensagem de sucesso
        return redirect()
            ->route('admin.classificacaomultas.index')
            ->with('success', 'Classificação de Multa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $classificacaoMultas = ClassificacaoMulta::where('id_classificacao_multa', $id);
            $classificacaoMultas->delete();

            DB::commit();
            return redirect()
                ->route('admin.classificacaomultas.index')
                ->with('notification', [
                    'title'   => 'Classificação de Multa excluída',
                    'type'    => 'success',
                    'message' => 'Classificação de Multa excluída com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();

            if ($errorCode == 23503) {
                $mensagem = 'Não foi possivel excluir a Classificação de Multa, pois existem registros dependentes';
            }

            return redirect()
                ->route('admin.classificacaomultas.index')
                ->with('notification', [
                    'title'   => 'Erro ao excluir Multa',
                    'type'    => 'error',
                    'message' => $mensagem
                ]);
        }
    }

    //  public function destroi(string $id)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $classificacaoMultas = ClassificacaoMulta::where('id_classificacao_multa', $id);
    //         $classificacaoMultas->delete();

    //         DB::commit();
    //         return redirect()
    //             ->route('admin.classificacaomultas.index')
    //             ->with(['success' => 'Multa excluída com Sucesso!']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Erro ao Excluir Multa:', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return redirect()
    //             ->route('admin.classificacaomultas.index')
    //             ->with(['error' => "Não foi possível Excluir Multa."]);
    //     }
    // }
}
