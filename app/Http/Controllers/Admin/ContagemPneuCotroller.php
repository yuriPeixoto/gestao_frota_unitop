<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContagemPneu;
use App\Models\Filial;
use App\Models\ModeloPneu;
use App\Modules\Pessoal\Models\Pessoal;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class ContagemPneuCotroller extends Controller
{
    use SanitizesMonetaryValues;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContagemPneu::query();

        if ($request->filled('id_contagem_pneu')) {
            $query->where('id_contagem_pneu', $request->id_contagem_pneu);
        }

        if ($request->filled(['data_inclusao_inicial', 'data_inclusao_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inclusao_inicial,
                $request->data_inclusao_final
            ]);
        }

        if ($request->filled('id_modelo_pneu')) {
            $query->where('id_modelo_pneu', $request->id_modelo_pneu);
        }

        if ($request->filled('contagem_usuario')) {
            $query->where('contagem_usuario', $request->contagem_usuario);
        }

        $contagemPneus = $query->latest('id_contagem_pneu')
            ->with('modelopneu')
            ->paginate(40)
            ->appends($request->query());

        $modeloPneu = ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray();


        if ($request->header('HX-Request')) {
            return view('admin.pneus._table', compact('contagemPneus', 'modeloPneu'));
        }

        return view('admin.contagempneus.index', compact('contagemPneus', 'modeloPneu'));
    }

    public function create()
    {

        $formOptions = [
            'modelopneu'    => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray(),
            'filiais'       => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
        ];

        $pessoasFrequentes = $this->getPessoasFrequentes();

        return view('admin.contagempneus.create', compact('formOptions', 'pessoasFrequentes'));
    }

    public function store(Request $request)
    {
        $contagemPneu = $request->validate([
            'id_modelo_pneu'            => 'required|integer',
            'contagem_usuario'          => 'required|integer',
            'id_responsavel_contagem'   => 'required|integer',
            'id_filial'                 => 'required|integer',
        ]);


        DB::beginTransaction();

        try {
            $idModeloPneu = $contagemPneu['id_modelo_pneu'];
            $idFilial = $contagemPneu['id_filial'];

            $result = DB::connection('pgsql')->select("
                WITH infoRegistrosPneu AS (
                    WITH ultimosregistros_pneu AS (
                        SELECT MAX(hp.id_historico_pneu) AS id_historico_pneu, hp.id_pneu
                        FROM historicopneu hp
                        WHERE hp.id_pneu IS NOT NULL
                        GROUP BY hp.id_pneu
                    )
                    SELECT htp.*
                    FROM historicopneu AS htp
                    JOIN ultimosregistros_pneu AS urp
                    ON htp.id_historico_pneu = urp.id_historico_pneu AND htp.id_pneu = urp.id_pneu
                )
                SELECT
                    COUNT(*) AS qtd_pneus
                FROM pneu AS p
                JOIN infoRegistrosPneu AS ht
                ON ht.id_pneu = p.id_pneu
                WHERE ht.id_modelo = ?
                AND p.id_filial = ?
                AND p.status_pneu = 'ESTOQUE'
            ", [$idModeloPneu, $idFilial]);


            foreach ($result as $resultado) {
                $contagemPneu['quantidade_sistema'] = $resultado->qtd_pneus;
            }

            if ($contagemPneu['quantidade_sistema'] >= 0) {
                $contagemPneu['is_igual'] = ($contagemPneu['quantidade_sistema'] == intval($contagemPneu['contagem_usuario'])) ? TRUE : FALSE;
            }

            $contagemPneu['data_inclusao'] = now();
            $contagemPneu['id_usuario'] = Auth::user()->id;

            ContagemPneu::create($contagemPneu);
            DB::commit();

            return redirect()
                ->route('admin.contagempneus.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Contagem de pneus cadastrado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na criação da contagem:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.contagempneus.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar a contagem de pneus."
                ]);
        }
    }

    public function edit($id)
    {
        $contagemPneus = ContagemPneu::find($id);

        $pessoasFrequentes = $this->getPessoasFrequentes();

        $formOptions = [
            'modelopneu'    => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray(),
            'filiais'       => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
        ];

        return view('admin.contagempneus.edit', compact('formOptions', 'contagemPneus', 'pessoasFrequentes'));
    }

    public function update(Request $request, $id)
    {
        $contagemPneu = $request->validate([
            'id_modelo_pneu'            => 'required|integer',
            'contagem_usuario'          => 'required|integer',
            'id_responsavel_contagem'   => 'required|integer',
            'id_filial'                 => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $idModeloPneu = $contagemPneu['id_modelo_pneu'];
            $idFilial = $contagemPneu['id_filial'];

            $result = DB::connection('pgsql')->select("
                WITH infoRegistrosPneu AS (
                    WITH ultimosregistros_pneu AS (
                        SELECT MAX(hp.id_historico_pneu) AS id_historico_pneu, hp.id_pneu
                        FROM historicopneu hp
                        WHERE hp.id_pneu IS NOT NULL
                        GROUP BY hp.id_pneu
                    )
                    SELECT htp.*
                    FROM historicopneu AS htp
                    JOIN ultimosregistros_pneu AS urp
                    ON htp.id_historico_pneu = urp.id_historico_pneu AND htp.id_pneu = urp.id_pneu
                )
                SELECT
                    COUNT(*) AS qtd_pneus
                FROM pneu AS p
                JOIN infoRegistrosPneu AS ht
                ON ht.id_pneu = p.id_pneu
                WHERE ht.id_modelo = ?
                AND p.id_filial = ?
                AND p.status_pneu = 'ESTOQUE'
            ", [$idModeloPneu, $idFilial]);

            foreach ($result as $resultado) {
                $contagemPneu['quantidade_sistema'] = $resultado->qtd_pneus;
            }


            if ($contagemPneu['quantidade_sistema'] >= 0) {
                $contagemPneu['is_igual'] = ($contagemPneu['quantidade_sistema'] == intval($contagemPneu['contagem_usuario'])) ? TRUE : FALSE;
            }

            $contagemPneu['data_alteracao'] = now();
            $contagemPneu['id_usuario'] = Auth::user()->id;

            $contagem = ContagemPneu::find($id);
            $contagem->update($contagemPneu);

            DB::commit();

            return redirect()
                ->route('admin.contagempneus.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Descarte cadastrado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na criação do Descarte:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.contagempneus.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o Descarte."
                ]);
        }
    }

    public function destroy($id)
    {
        try {

            DB::beginTransaction();

            $contagemPneu = ContagemPneu::find($id);
            $contagemPneu->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Contagem de pneu excluído!',
                    'type'    => 'success',
                    'message' => 'Contagem de pneu excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir a Contagem de Pneu: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir a Contagem de Pneu: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    private function getPessoasFrequentes()
    {
        return Cache::remember('pessoas_frequentes', now()->addHours(12), function () {
            return Pessoal::where('ativo', true)
                ->limit(20)
                ->orderBy('nome')
                ->get(['id_pessoal as value', 'nome as label'])
                ->toArray();
        });
    }
}
