<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Compras\Models\Fornecedor;
use App\Modules\Imobilizados\Models\ManutencaoImobilizado;
use App\Modules\Imobilizados\Models\ManutencaoImobilizadoItens;
use App\Modules\Imobilizados\Models\OrdemServicoPecasImobilizados;
use App\Models\Produto;
use App\Modules\Imobilizados\Models\ProdutosImobilizados;
use App\Modules\Imobilizados\Models\RelacaoImobilizados;
use App\Modules\Imobilizados\Models\TipoManutencaoImobilizado;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdemServicoImobilizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = ManutencaoImobilizado::query();

        if ($request->filled('id_manutencao_imobilizado')) {
            $query->where('id_manutencao_imobilizado', $request->id_manutencao_imobilizado);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        $manutencaoImobilizados = $query->latest('id_manutencao_imobilizado')
            ->orderBy('id_manutencao_imobilizado', 'desc')
            ->paginate(15)
            ->appends($request->query());


        $id_manutencao_imobilizado = $this->getIdManutencaoImobilizados();

        $fornecedor = $this->getFornecedor();

        $filial = $this->getFiliais();

        return view(
            'admin.ordemservicoimobilizado.index',
            compact(
                'manutencaoImobilizados',
                'id_manutencao_imobilizado',
                'fornecedor',
                'filial'
            )
        );
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipoManutencaoImobilizado = $this->getTipoManutencaoImobilizado();

        $fornecedor = $this->getFornecedor();

        $filial = $this->getFiliais();

        $produtosImobilizados = $this->getIdProdutosImobilizados();

        $produtos = $this->getProduto();

        $produtosDescricao = $this->getProduto(true);

        $produtoImobilizadoDescricao = $this->getProdutoImobilizadoDescricao();

        $tipoDescricao = $this->getDescricaoTipo();

        return view(
            'admin.ordemservicoimobilizado.create',
            compact(
                'tipoManutencaoImobilizado',
                'fornecedor',
                'filial',
                'produtosImobilizados',
                'produtos',
                'produtoImobilizadoDescricao',
                'tipoDescricao',
                'produtosDescricao'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'situacao' => 'required|string|max:255',
            'id_filial' => 'required|integer',
            'id_fornecedor' => 'required|integer',
            'imobilizados' => 'required|json',
            'produto' => 'required|json'
        ]);

        try {
            $imobilizados = json_decode($request->imobilizados);
            $produtos = json_decode($request->produto);

            if (!is_array($imobilizados) || !is_array($produtos)) {
                throw new \Exception("Dados de imobilizados ou produtos inválidos");
            }

            DB::beginTransaction();

            $manutencao = ManutencaoImobilizado::create([
                'situacao' => $validatedData['situacao'],
                'id_filial' => $validatedData['id_filial'],
                'id_fornecedor' => $validatedData['id_fornecedor'],
                'data_inclusao' => now(),
            ]);

            $manutencaoId = $manutencao->id_manutencao_imobilizado;

            // --- Insere as peças ---
            foreach ($produtos as $pecaData) {
                OrdemServicoPecasImobilizados::create([
                    'data_inclusao' => now(),
                    'id_produto' => $pecaData->id_produto,
                    'quantidade' => $pecaData->quantidade,
                    'ja_solicitada' => $pecaData->ja_solicitada ?? false,
                    'data_solicitacao' => $pecaData->data_solicitacao ?? null,
                    'situacao_pecas' => $pecaData->situacao_pecas ?? 'PENDENTE',
                    'id_manutencao_imobilizado' => $manutencaoId,
                ]);
            }

            // --- Insere os imobilizados ---
            foreach ($imobilizados as $itemData) {
                ManutencaoImobilizadoItens::create([
                    'data_inclusao' => now(),
                    'id_manutencao_imobilizado' => $manutencaoId,
                    'id_produtos_imobilizados' => $itemData->id_produtos_imobilizados,
                    'id_tipo_manutencao_imobilizado' => $itemData->id_tipo_manutencao_imobilizado,
                ]);

                ProdutosImobilizados::where('id_produtos_imobilizados', $itemData->id_produtos_imobilizados)
                    ->update(['status' => 'EM MANUTENCAO']);
            }

            DB::commit();

            return redirect()
                ->route('admin.ordemservicoimobilizado.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Ordem de serviço imobilizado cadastrada com sucesso!'
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao salvar manutenção de imobilizado', [
                'mensagem' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro ao cadastrar a ordem de serviço: ' . $e->getMessage(),
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
        $manutencaoImobilizado = ManutencaoImobilizado::find($id);

        $tipoManutencaoImobilizado = $this->getTipoManutencaoImobilizado();

        $fornecedor = $this->getFornecedor();

        $filial = $this->getFiliais();

        $produtosImobilizados = $this->getIdProdutosImobilizados();

        $produtos = $this->getProduto();

        $produtosDescricao = $this->getProduto(true);

        $produtoImobilizadoDescricao = $this->getProdutoImobilizadoDescricao(true);

        $tipoDescricao = $this->getDescricaoTipo();

        $ordemServicoPecasImobilizados = $this->getOrdemServicoPecasImobilizados($id);

        $manutencaoImobilizadoItens = $this->getManutencaoImobilizadoItens($id);

        return view(
            'admin.ordemservicoimobilizado.edit',
            compact(
                'manutencaoImobilizado',
                'tipoManutencaoImobilizado',
                'fornecedor',
                'filial',
                'produtosImobilizados',
                'produtos',
                'produtosDescricao',
                'produtoImobilizadoDescricao',
                'tipoDescricao',
                'ordemServicoPecasImobilizados',
                'manutencaoImobilizadoItens'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validação mais robusta
        $validatedData = $request->validate([
            'situacao' => 'required|string|max:255',
            'id_filial' => 'required|integer',
            'id_fornecedor' => 'required|integer',
            'imobilizados' => 'required|json',
            'produto' => 'required|json'
        ]);

        try {
            // Decodifica os JSONs
            $imobilizados = json_decode($request->imobilizados);
            $produtos = json_decode($request->produto);

            if (!is_array($imobilizados) || !is_array($produtos)) {
                throw new \Exception("Dados de imobilizados ou produtos inválidos");
            }

            DB::beginTransaction();

            // Atualiza a manutenção existente
            $manutencao = ManutencaoImobilizado::findOrFail($id);
            $manutencao->update([
                'situacao' => $validatedData['situacao'],
                'id_filial' => $validatedData['id_filial'],
                'id_fornecedor' => $validatedData['id_fornecedor'],
                'data_alteracao' => now(),
            ]);
            $manutencaoId = $manutencao->id_manutencao_imobilizado;

            // Remove todas as peças existentes para recriá-las
            OrdemServicoPecasImobilizados::where('id_manutencao_imobilizado', $manutencaoId)->delete();

            // Prepara dados para inserção em massa (mais eficiente)
            $pecasToInsert = [];

            foreach ($produtos as $pecaData) {
                $pecasToInsert[] = [
                    'data_inclusao' => now(),
                    'id_produto' => $pecaData->id_produto,
                    'quantidade' => $pecaData->quantidade,
                    'ja_solicitada' => $pecaData->ja_solicitada ?? false,
                    'data_solicitacao' => $pecaData->data_solicitacao ?? null,
                    'situacao_pecas' => $pecaData->situacao_pecas ?? 'PENDENTE',
                    'id_manutencao_imobilizado' => $manutencaoId,
                ];
            }

            // Insere todas as peças atualizadas de uma vez
            OrdemServicoPecasImobilizados::insert($pecasToInsert);

            // Obtém os imobilizados atuais para comparação
            $currentImobilizados = ManutencaoImobilizadoItens::where('id_manutencao_imobilizado', $manutencaoId)
                ->pluck('id_produtos_imobilizados')
                ->toArray();

            $newImobilizadosIds = array_map(function ($item) {
                return $item->id_produtos_imobilizados;
            }, $imobilizados);

            // Identifica imobilizados removidos
            $removedImobilizados = array_diff($currentImobilizados, $newImobilizadosIds);

            // Identifica imobilizados adicionados
            $addedImobilizados = array_diff($newImobilizadosIds, $currentImobilizados);

            // Remove os imobilizados que não estão mais na lista
            ManutencaoImobilizadoItens::where('id_manutencao_imobilizado', $manutencaoId)
                ->whereIn('id_produtos_imobilizados', $removedImobilizados)
                ->delete();

            // Atualiza status dos imobilizados removidos
            if (!empty($removedImobilizados)) {
                ProdutosImobilizados::whereIn('id_produtos_imobilizados', $removedImobilizados)
                    ->update([
                        'status' => 'ATIVO' // ou outro status apropriado
                    ]);
            }

            // Prepara dados dos novos imobilizados
            $imobilizadosToInsert = [];
            foreach ($imobilizados as $itemData) {
                if (in_array($itemData->id_produtos_imobilizados, $addedImobilizados)) {
                    $imobilizadosToInsert[] = [
                        'data_inclusao' => now(),
                        'id_manutencao_imobilizado' => $manutencaoId,
                        'id_produtos_imobilizados' => $itemData->id_produtos_imobilizados,
                        'id_tipo_manutencao_imobilizado' => $itemData->id_tipo_manutencao_imobilizado,
                    ];
                }
            }

            // Insere os novos imobilizados
            if (!empty($imobilizadosToInsert)) {
                ManutencaoImobilizadoItens::insert($imobilizadosToInsert);
            }

            // Atualiza o status dos produtos imobilizados (tanto os novos quanto os existentes)
            if (!empty($newImobilizadosIds)) {
                ProdutosImobilizados::whereIn('id_produtos_imobilizados', $newImobilizadosIds)
                    ->update([
                        'status' => 'EM MANUTENCAO'
                    ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.ordemservicoimobilizado.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Ordem de serviço imobilizado atualizada com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar manutenção de imobilizado: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return back()
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível atualizar a Ordem de serviço imobilizado." . $e->getMessage()
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function onFinalizar(Request $request)
    // : JsonResponse
    {
        $id = $request->input('id');

        Log::info('Finalizando ordem de serviço imobilizado', ['id' => $id]);
        DB::beginTransaction();

        try {
            // 1. Busca a ordem de serviço
            $ordemServico = ManutencaoImobilizado::findOrFail($id);

            // Buscar todos os itens da manutenção
            $manutencaoImobilizadoItens = ManutencaoImobilizadoItens::where('id_manutencao_imobilizado', $id)->get();


            // 2. Verifica se está no status correto para finalizar
            // if ($ordemServico->situacao != 'MANUTENÇÃO INICIADA') {
            //     return ('Só é possível finalizar ordens de serviço com status "MANUTENÇÃO INICIADA"');
            // }
            if ($ordemServico->situacao != 'MANUTENÇÃO INICIADA') {
                return response()->json([
                    'notification' => [
                        'title' => 'Erro',
                        'message' => 'Só é possível finalizar ordens de serviço com status "MANUTENÇÃO INICIADA"',
                        'type' => 'error'
                    ]
                ], 422);
            }

            // 3. Verifica se há peças pendentes (opcional)
            $pecasPendentes = DB::connection('pgsql')->table('ordem_servico_pecas_imobilizados')
                ->where('id_manutencao_imobilizado', $id)
                ->where(function ($query) {
                    $query->where('ja_solicitada', false)
                        ->orWhereNull('ja_solicitada');
                })
                ->exists();

            if ($pecasPendentes) {
                throw new Exception('Não é possível finalizar: existem peças pendentes!');
            }

            // 4. Atualiza o status no banco de dados
            $ordemServico->update([
                'situacao' => 'MANUTENÇÃO FINALIZADA',
                'data_finalizacao' => now()
            ]);


            // 5. Chama a procedure para atualizar vida útil do imobilizado
            DB::connection('pgsql')->statement('SELECT fc_atualizar_vida_imobilizado(?)', [$id]);

            foreach ($manutencaoImobilizadoItens as $item) {
                // Supondo que o item tenha um relacionamento com ProdutosImobilizados
                $produto = $item->produtoImobilizado;

                if ($produto) {
                    $produto->status = 'EM MANUTENCAO';
                    $produto->save();
                }
            }

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Ordem de serviço finalizada com sucesso!!'
                ],
                'redirect' => route('admin.ordemservicoimobilizado.index')
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao finalizar ordem de serviço', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollBack();

            return response()->json([
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'Erro ao finalizar: ' . $e->getMessage(),
                    'message' => 'Erro ao finalizar a Ordem de serviço!'
                ],
            ]);
        }
    }

    public function onVoltarEstoque(Request $request)
    {
        $idManutencao = $request->input('id');

        try {
            $manutencao = DB::connection('pgsql')->table('manutencao_imobilizado')
                ->where('id_manutencao_imobilizado', $idManutencao)
                ->first();

            if (!$manutencao) {
                return response()->json([
                    'notification' => [
                        'title' => 'Não Encontrado',
                        'message' => 'Manutenção não encontrada',
                        'type' => 'error'
                    ]
                ], 404);
            }

            if ($manutencao->situacao != 'MANUTENÇÃO FINALIZADA') {
                return response()->json([
                    'notification' => [
                        'title' => 'Ação Não Permitida',
                        'message' => 'A manutenção não está finalizada',
                        'type' => 'warning'
                    ]
                ], 400);
            }

            DB::beginTransaction();

            $produtos = DB::connection('pgsql')->table('manutencao_imobilizado_itens')
                ->where('id_manutencao_imobilizado', $idManutencao)
                ->pluck('id_produtos_imobilizados');

            $produtosAfetados = 0;

            if ($produtos->isNotEmpty()) {
                $produtosAfetados = DB::connection('pgsql')->table('produtos_imobilizados')
                    ->whereIn('id_produtos_imobilizados', $produtos)
                    ->update(['status' => 'EM ESTOQUE']);
            }

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Sucesso',
                    'message' => "{$produtosAfetados} produtos retornados ao estoque",
                    'type' => 'success'
                ],
                'redirect' => route('admin.ordemservicoimobilizado.index') // Opcional
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'message' => 'Erro ao retornar produtos ao estoque: ' . $e->getMessage(),
                    'type' => 'error'
                ]
            ], 500);
        }
    }

    private function getProdutoImobilizadoDescricao($edit = false)
    {
        if ($edit) {
            return ProdutosImobilizados::with('produto')
                ->orderBy('id_produtos_imobilizados')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->id_produtos_imobilizados => $item->produto->descricao_produto ?? 'Não Informado',
                    ];
                })
                ->toArray();
        } else {
            return ProdutosImobilizados::with('produto')
                ->where('status', '!=', 'EM ESTOQUE')
                ->orderBy('id_produtos_imobilizados')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->id_produtos_imobilizados => $item->produto->descricao_produto ?? 'Não Informado',
                    ];
                })
                ->toArray();
        }
    }

    private function getManutencaoImobilizadoItens($id)
    {
        return ManutencaoImobilizadoItens::where('id_manutencao_imobilizado', $id)
            ->get()
            ->toArray();
    }

    private function getOrdemServicoPecasImobilizados($id)
    {
        return OrdemServicoPecasImobilizados::where('id_manutencao_imobilizado', $id)
            ->with('produto')
            ->get()
            ->toArray();
    }

    private function getTipoManutencaoImobilizado()
    {
        return TipoManutencaoImobilizado::select('id_tipo_manutencao_imobilizado as value', 'descricao as label')
            ->orderBy('descricao', 'desc')
            ->get()
            ->toArray();
    }

    private function getProduto($desc = false)
    {
        if ($desc) {
            return Produto::where('is_imobilizado', true)
                ->orderBy('descricao_produto')
                ->get()
                ->keyBy('id_produto') // <-- chaveando pelo id
                ->map(function ($produto) {
                    return $produto->descricao_produto;
                });
        } else {
            return Produto::select('id_produto as value', 'descricao_produto as label')
                ->where('is_imobilizado', '=', true)
                ->orderBy('descricao_produto')
                ->limit(30)
                ->get()
                ->toArray();
        }
    }

    private function getDescricaoTipo()
    {
        return TipoManutencaoImobilizado::all()
            ->pluck('descricao', 'id_tipo_manutencao_imobilizado')
            ->toArray();
    }

    private function getIdManutencaoImobilizados()
    {
        return ManutencaoImobilizado::select('id_manutencao_imobilizado as value', 'id_manutencao_imobilizado as label')
            ->orderBy('id_manutencao_imobilizado', 'desc')
            ->get()
            ->toArray();
    }

    private function getFiliais()
    {
        return Filial::select('id as value', 'name as label')
            ->get();
    }

    private function getFornecedor()
    {
        return Fornecedor::select(
            'id_fornecedor as value',
            'nome_fornecedor as label'
        )
            ->orderBy('nome_fornecedor', 'asc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    private function getIdProdutosImobilizados()
    {
        return ProdutosImobilizados::with('produto')
            ->where('status', '=', 'EM ESTOQUE')
            ->orderBy('id_produtos_imobilizados')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_produtos_imobilizados,
                    'label' => $item->id_produtos_imobilizados . ' - ' . ($item->produto->descricao_produto ?? 'Não Informado'),
                ];
            })
            ->toArray();
    }

    public function solicitarPecas(Request $request, $id)
    {

        $ordemServico = ManutencaoImobilizado::find($id);
        try {

            if ($this->existeProduto($ordemServico->id_manutencao_imobilizado) != 0) {

                Log::info('Verificando produtos...', ['id' => $ordemServico->id_manutencao_imobilizado]);


                $idOrdem = $ordemServico->id_manutencao_imobilizado;
                $idUser = Auth::id();
                $idFilialManutencao = $request->id_filial;

                $result = DB::select(
                    'SELECT * FROM fc_inserir_produtos_solicitacao_manutencao_imobilizado(?, ?, ?)',
                    [$idOrdem, $idUser, $idFilialManutencao]
                );

                Log::info('Entrou no result');

                $retorno = null;

                if ($result && isset($result[0]->fc_inserir_produtos_solicitacao_manutencao_imobilizado)) {
                    $retorno = $result[0]->fc_inserir_produtos_solicitacao_manutencao_imobilizado;
                }

                // Retorna mensagens equivalentes às do Adianti
                if ($retorno == 0) {
                    return response()->json(['message' => 'Erro: não foi possível fazer a solicitação.'], 400);
                } else {
                    return response()->json(['message' => 'Peças solicitadas ao Estoque!'], 200);
                }
            } else {
                return response()->json(['message' => 'Não existem produtos para solicitar ao estoque.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function existeProduto($idOrdemServico)
    {
        $retorno = 0;
        try {
            $result = DB::table('ordem_servico_pecas_imobilizados as osp')->join('manutencao_imobilizado as mi', 'mi.id_manutencao_imobilizado', '=', 'osp.id_manutencao_imobilizado')->join('fornecedor as f', 'f.id_fornecedor', '=', 'mi.id_fornecedor')->where('osp.id_manutencao_imobilizado', $idOrdemServico)->whereRaw("(f.nome_fornecedor @@ 'Carvalima')")->where('f.nome_fornecedor', 'not like', '%Posto%')->where(function ($query) {
                $query->where('osp.ja_solicitada', false)->orWhereNull('osp.ja_solicitada');
            })->select('osp.id_ordem_servico_pecas_imobilizado')->first();
            Log::info('Verificando produtos query', ['idOrdemServico' => $idOrdemServico,]);
            if ($result) {
                $retorno = $result->id_ordem_servico_pecas_imobilizado;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao verificar produto: ' . $e->getMessage());
        }
        return $retorno;
    }
}
