<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Produto;
use App\Modules\Imobilizados\Models\RelacaoImobilizados;
use App\Modules\Imobilizados\Models\RelacaoImobilizadosItens;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequisicaoImobilizadosController extends Controller
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

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', $request->data_inclusao);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $relacaoImobilizados = $query->latest('id_relacao_imobilizados')
            ->where('status', '!=', 'FINALIZADA')
            ->paginate(15)
            ->appends($request->query());

        $id_relacao_imobilizados = $this->getIdRelacaoImobilizados();

        $id_usuario_relacao_imobilizados = $this->getIdUsuarioRelacaoImobilizados();

        $status_relacao_imobilizados = $this->getStatusRelacaoImobilizados();

        $requisicaoImobilizadosItens = $this->getRequisicaoItens();


        return view(
            'admin.requisicaoimobilizados.index',
            compact(
                'relacaoImobilizados',
                'id_relacao_imobilizados',
                'id_usuario_relacao_imobilizados',
                'status_relacao_imobilizados',
                'requisicaoImobilizadosItens'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(20)
            ->get()
            ->toArray();

        $users = User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')->get();

        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('is_imobilizado', '=', true)
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();

        $produtoDescricao = Produto::all()
            ->pluck('descricao_produto', 'id_produto')
            ->toArray();

        return view(
            'admin.requisicaoimobilizados.create',
            compact('departamento', 'users', 'filial', 'produto', 'produtoDescricao')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        $filial = Auth::user()->filial_id;
        $cargo = $this->buscarCargo($userId);
        $arrayCargosPermitidos = [30, 32];
        $idMaster = 25;

        $validated = $request->validate([
            'id_usuario'           => 'required',
            'id_departamento'      => 'required',
            'id_filial'            => 'required',
            'motivo_transferencia' => 'required',
        ]);

        DB::beginTransaction();

        try {
            // Define status e flags conforme o cargo/filial
            if ($userId == $idMaster || (in_array($cargo, $arrayCargosPermitidos) && $filial == 1)) {
                $status = 'APROVADA';
                $aprovadoGestor = true;
                $aprovado = true;
            } elseif (in_array($cargo, $arrayCargosPermitidos)) {
                $status = 'AGUARDANDO APROVAÇÃO GESTOR';
                $aprovadoGestor = false;
                $aprovado = true;
            } else {
                $status = 'INICIADA';
                $aprovadoGestor = false;
                $aprovado = null;
            }

            $historicoRelacao = json_decode($request->historicos);

            if (empty($historicoRelacao)) {
                throw new \Exception('Nenhum item informado na requisição.');
            }

            $relacaoImobilizados = null;

            foreach ($historicoRelacao as $index => $item) {
                if ($index === 0) {
                    $relacaoImobilizados = RelacaoImobilizados::create([
                        'data_inclusao'        => now(),
                        'id_usuario'           => $validated['id_usuario'],
                        'id_departamento'      => $validated['id_departamento'],
                        'id_filial'            => $validated['id_filial'],
                        'motivo_transferencia' => $validated['motivo_transferencia'],
                        'finalizado_aprovacao' => true,
                        'aprovado_gestor'      => $aprovadoGestor,
                        'status'               => $status,
                        'aprovado'             => $aprovado

                    ]);
                }

                RelacaoImobilizadosItens::create([
                    'data_inclusao'           => now(),
                    'id_produtos'             => $item->id_produtos,
                    'id_relacao_imobilizados' => $relacaoImobilizados->id_relacao_imobilizados,
                    'id_departamento'         => $relacaoImobilizados->id_departamento,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.requisicaoimobilizados.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado cadastrada com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação de Requisição Imobilizado:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.requisicaoimobilizados.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível cadastrar a Requisição Imobilizado. ' . $e->getMessage(),
                ]);
        }
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
            ->where('is_imobilizado', '=', true)
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();

        $produtoDescricao = Produto::all()
            ->pluck('descricao_produto', 'id_produto')
            ->toArray();

        return view(
            'admin.requisicaoimobilizados.edit',
            compact('relacaoImobilizados', 'departamento', 'users', 'filial', 'produto', 'produtoDescricao')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // dd($request);


        DB::beginTransaction();

        try {

            $relacaoImobilizadosValidate = $request->validate([
                'id_usuario'          => 'required',
                'id_departamento'      => 'required',
                'id_filial'           => 'required',
                'motivo_transferencia'     => 'required',
            ]);

            $historicoRelacao = json_decode($request->historicos);
            Log::info('→ Histórico recebido', ['historico' => $historicoRelacao]);

            $relacaoImobilizados = RelacaoImobilizados::find($id);

            // Obtenha os IDs dos produtos do histórico para comparação
            $idsProdutosHistorico = collect($historicoRelacao)->pluck('id_produtos')->toArray();
            Log::info('→ IDs dos produtos do histórico', ['ids' => $idsProdutosHistorico]);

            // Busque os itens da relação e remova os que não estão no histórico
            $itens = RelacaoImobilizadosItens::where('id_relacao_imobilizados', $id)->get();
            Log::info('→ Itens existentes no banco', ['itens' => $itens]);

            foreach ($itens as $item) {
                if (!in_array($item->id_produtos, $idsProdutosHistorico)) {
                    Log::warning('→ Removendo item que não está mais no histórico', ['item' => $item]);
                    $item->delete();
                }
            }

            Log::info('→ Iniciando processamento do histórico');
            foreach ($historicoRelacao as $index => $item) {
                Log::info("→ Processando item $index", ['item' => $item]);

                // Atualização da relação
                if ($index == 0) {
                    $relacaoImobilizados->update([
                        'data_alteracao'              => now(),
                        'id_usuario'                 => $relacaoImobilizadosValidate['id_usuario'] ?? null,
                        'id_departamento'            => $relacaoImobilizadosValidate['id_departamento'] ?? null,
                        'id_filial'                  => $relacaoImobilizadosValidate['id_filial'] ?? null,
                        'motivo_transferencia'       => $relacaoImobilizadosValidate['motivo_transferencia'] ?? null,
                    ]);
                    Log::info('→ Atualizou a relação de imobilizados');
                }

                // Cria novo item
                if (!isset($item->id_relacao_imobilizados_itens)) {
                    $novoItem = RelacaoImobilizadosItens::create([
                        'data_inclusao'              => now(),
                        'data_alteracao'             => now(),
                        'id_produtos'                => $item->id_produtos,
                        'id_relacao_imobilizados'    => $relacaoImobilizados->id_relacao_imobilizados,
                        'id_departamento'            => $relacaoImobilizados->id_departamento,
                    ]);
                    Log::info('→ Item criado', ['novoItem' => $novoItem]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.requisicaoimobilizados.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado alterado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na alteração de Requisição Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.requisicaoimobilizados.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível alterar a Requisição Imobilizado." . $e->getMessage()
                ]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            // Remover itens relacionados
            DB::connection('pgsql')->table('relacao_imobilizados_itens')->where('id_relacao_imobilizados', $id)->delete();

            // Remover a Requisição Imobilizado
            DB::connection('pgsql')->table('relacao_imobilizados')->where('id_relacao_imobilizados', $id)->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Requisição Imobilizado excluída',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function onEnviaraprovacao(Request $request): JsonResponse
    {
        try {

            $id = $request->input('id');

            // Se o ID vim vazio é porque não foi salvo
            if (empty($id)) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Salvar a Requisição Imobilizado antes de enviar para aprovação !'
                    ]
                ], 404);
            }

            DB::beginTransaction();

            $finalizado_aprovacao = true;
            $status = 'AGUARDANDO APROVAÇÃO';

            $relacaoImobilizados = RelacaoImobilizados::find($id);

            // verificar se o status esta como AGUARDANDO APROVAÇÃO e se o finalizado_aprovacao esta como true
            // para não enviar para aprovação novamente
            if (
                $relacaoImobilizados->finalizado_aprovacao == true &&
                $relacaoImobilizados->status == 'AGUARDANDO APROVAÇÃO'
            ) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Requisição Imobilizado ja foi enviado para aprovação !'
                    ]
                ], 404);
            }

            $relacaoImobilizados->update([
                'finalizado_aprovacao'  => $finalizado_aprovacao,
                'status'    => $status
            ]);
            Log::info('→ Atualizou a relação de imobilizados');

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado enviado com sucesso!'
                ],
                'redirect' => route('admin.requisicaoimobilizados.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação de Requisição Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro no envio da Requisição Imobilizado!'
                ],
            ]);
        }
    }


    private function getRequisicaoItens()
    {
        return RelacaoImobilizadosItens::orderBy('id_relacao_imobilizados_itens')
            ->with('produto')
            ->get()
            ->map(function ($itens) {
                return [
                    'label' => $itens->id_relacao_imobilizados_itens,
                    'value' => $itens->id_relacao_imobilizados,
                    'produto' => $itens->produto->descricao_produto ?? "Não informado",
                    'data_inclusao' => $itens->data_inclusao
                ];
            })
            ->values()
            ->toArray();
    }

    private function getIdRelacaoImobilizados()
    {
        return RelacaoImobilizados::select('id_relacao_imobilizados as value', 'id_relacao_imobilizados as label')
            ->orderBy('id_relacao_imobilizados', 'desc')
            ->get()
            ->toArray();
    }

    private function getStatusRelacaoImobilizados()
    {
        return RelacaoImobilizados::select('status as value', 'status as label')
            ->distinct()
            ->orderBy('status', 'desc')
            ->get()
            ->toArray();
    }

    private function getIdUsuarioRelacaoImobilizados()
    {
        return RelacaoImobilizados::join('users', 'relacao_imobilizados.id_usuario', '=', 'users.id')
            ->select('relacao_imobilizados.id_usuario as value', 'users.name as label')
            ->orderBy('relacao_imobilizados.id_usuario', 'desc')
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
