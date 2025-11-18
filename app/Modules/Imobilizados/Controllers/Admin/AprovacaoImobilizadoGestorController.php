<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\Produto;
use App\Modules\Imobilizados\Models\RelacaoImobilizados;
use App\Modules\Configuracoes\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AprovacaoImobilizadoGestorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RelacaoImobilizados::query();

        if ($request->filled('id_relacao_imobilizados')) {
            $query->where('id_relacao_imobilizados', $request->id_relacao_imobilizados);
        }

        $relacaoImobilizados = $query->latest('id_relacao_imobilizados')
            ->where('status', '=', 'AGUARDANDO APROVAÇÃO GESTOR')
            ->where('finalizado_aprovacao', true)
            ->where('aprovado', true)
            ->paginate(15)
            ->appends($request->query());


        $id_relacao_imobilizados = $this->getIdRelacaoImobilizados();


        return view(
            'admin.aprovacaoimobilizadogestor.index',
            compact(
                'relacaoImobilizados',
                'id_relacao_imobilizados',
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

        $userId = Auth::id();
        $filial = Auth::user()->filial_id;

        $idPermitido = [1, 2, 255, 295, 275, 337, 150, 162, 95, 347, 198, 256, 212, 329, 170];

        $cargo = $this->buscarCargo($userId);
        $departamento = $this->buscarDepartamento($userId);

        $relacaoImobilizados = RelacaoImobilizados::find($id);

        $departamentoRegistro = $relacaoImobilizados->id_departamento;

        try {
            // dd([
            //     'userId' => $userId,
            //     'cargo' => $cargo,
            //     'filial' => $filial,
            //     'departamento_usuario' => $departamento,
            //     'departamento_registro' => $departamentoRegistro,
            //     'condicao_in_array' => in_array($userId, $idPermitido),
            //     'condicao_cargo' => $cargo == 30,
            //     'condicao_departamento' => $departamento == $departamentoRegistro,
            //     'condicao_filial' => in_array($filial, [1, 4]),
            // ]);


            // if () {
            //     return $this->carregarViewEdit($id);
            // }

            if (in_array($userId, $idPermitido) || ($cargo == 30 && $departamento == $departamentoRegistro && in_array($filial, [1, 4]))) {

                return $this->carregarViewEdit($id);
            }

            return redirect()->route('admin.aprovacaoimobilizadogestor.index')
                ->with('error', 'Você não possui permissão para Aprovar essa Requisição!');
        } catch (Exception $e) {
            return redirect()->route('admin.aprovacaoimobilizadogestor.index')
                ->with('error', 'Erro ao validar permissão: ' . $e->getMessage());
        }
    }

    /**
     * Função auxiliar para carregar a view de edição
     */
    private function carregarViewEdit($id)
    {
        $relacaoImobilizados = RelacaoImobilizados::with('relacaoImobilizadosItens')->find($id);

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->get()
            ->toArray();

        $users = User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')
            ->get()
            ->toArray();

        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('is_imobilizado', true)
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();

        $produtoDescricao = Produto::pluck('descricao_produto', 'id_produto')->toArray();

        return view(
            'admin.aprovacaoimobilizadogestor.edit',
            compact('relacaoImobilizados', 'departamento', 'users', 'filial', 'produto', 'produtoDescricao')
        );
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

    public function onAprovar(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            $aprovado_gestor = true;
            $status = 'APROVADA';

            $id = $request->input('id');
            $relacaoImobilizados = RelacaoImobilizados::find($id);
            $relacaoImobilizados->update([
                'aprovado_gestor'        => $aprovado_gestor,
                'status'                 => $status
            ]);
            Log::info('→ Atualizou a relação de imobilizados');

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado aprovada com sucesso!'
                ],
                'redirect' => route('admin.aprovacaoimobilizadogestor.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na aprovação de Requisição Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro na aprovação da Requisição Imobilizado!'
                ],
            ]);
        }
    }

    public function onReprovar(Request $request): JsonResponse
    {
        try {

            $id = $request->input('id');

            DB::beginTransaction();

            $observacao_gestor = $request->input('observacao_lider');
            $aprovado_gestor = false;
            $status = 'REPROVADA';

            $relacaoImobilizados = RelacaoImobilizados::find($id);
            $relacaoImobilizados->update([
                'aprovado_gestor'              => $aprovado_gestor,
                'status'                 => $status,
                'observacao_gestor'      => $observacao_gestor
            ]);

            Log::info('→ Atualizou a relação de imobilizados');

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado reprovada com sucesso!'
                ],
                'redirect' => route('admin.aprovacaoimobilizadogestor.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na reprovação de Requisição Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro na reprovação da Requisição Imobilizado!'
                ],
            ]);
        }
    }

    private function getIdRelacaoImobilizados()
    {
        return RelacaoImobilizados::select('id_relacao_imobilizados as value', 'id_relacao_imobilizados as label')
            ->orderBy('id_relacao_imobilizados', 'desc')
            ->get()
            ->toArray();
    }

    public function buscarCargo($userId)
    {
        // Busca o usuário pelo ID
        $user = User::where('id', $userId)
            ->with('tipoPessoal')
            ->first();

        // Retorna o cargo (pessoal_id)
        return $user ? $user->pessoal_id : 0;
    }

    public function buscarDepartamento($userId)
    {
        // Busca o usuário pelo ID
        $user = User::where('id', $userId)
            ->with('departamento')
            ->first();

        // Retorna o departamento (departamento_id)
        return $user ? $user->departamento_id : 0;
    }
}
