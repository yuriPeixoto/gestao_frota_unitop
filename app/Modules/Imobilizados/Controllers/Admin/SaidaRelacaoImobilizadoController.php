<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Manutencao\Models\OrdemServicoPecas;
use App\Models\Pessoal;
use App\Models\Produto;
use App\Modules\Imobilizados\Models\ProdutosImobilizados;
use App\Modules\Imobilizados\Models\RelacaoImobilizados;
use App\Modules\Imobilizados\Models\RelacaoImobilizadosItens;
use App\Modules\Imobilizados\Models\TransferenciaEstoqueImobilizadoAux;
use App\Modules\Configuracoes\Models\User;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class SaidaRelacaoImobilizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = RelacaoImobilizados::query();

        if ($request->filled('id_relacao_imobilizados')) {
            $query->where('id_relacao_imobilizados', $request->id_relacao_imobilizados);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $relacaoImobilizados = $query->latest('id_relacao_imobilizados')
            ->where('status', '!=', 'FINALIZADA')
            ->where('status', '!=', 'BAIXA COMPLETA')
            ->where('aprovado', '=', true)
            ->where('finalizado_aprovacao', '=', true)
            ->where('aprovado_gestor', '=', true)
            ->paginate(15)
            ->appends($request->query());


        $id_relacao_imobilizados = $this->getIdRelacaoImobilizados();

        $requisicaoImobilizadosItens = $this->getRequisicaoItens();

        $status = $this->getStatus();

        return view(
            'admin.saidarelacaoimobilizado.index',
            compact(
                'relacaoImobilizados',
                'id_relacao_imobilizados',
                'requisicaoImobilizadosItens',
                'status'
            )
        );
    }



    public function edit(string $id)
    {
        $relacaoImobilizados = RelacaoImobilizados::with('relacaoImobilizadosItens')->find($id);

        $users = $this->getIdUsuario();

        $filial = $this->getIdFilial();

        $produto = $this->getIdProduto();

        $produtosImobilizados = $this->getIdProdutosImobilizados();

        $produtoDescricao = $this->getProdutoDescricao();

        $pessoal = $this->getIdPessoal();

        $liderSetor = $this->getIdLiderSetor();

        $departamento = $this->getIdDepartamento();

        $veiculosFrequentes = $this->getIdVeiculosFrequentes();

        $requisicaoImobilizadosTransferencia = $this->getRequisicaoImobilizadosTransferencia($id);

        $requisicaoImobilizadosItens = $this->getRequisicaoItens($id);

        return view(
            'admin.saidarelacaoimobilizado.edit',
            compact(
                'relacaoImobilizados',
                'users',
                'filial',
                'produto',
                'produtosImobilizados',
                'produtoDescricao',
                'pessoal',
                'liderSetor',
                'departamento',
                'veiculosFrequentes',
                'requisicaoImobilizadosTransferencia',
                'requisicaoImobilizadosItens'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request)
    // {

    //     DB::beginTransaction();

    //     try {

    //         $historicoRelacao = json_decode($request->historicos);
    //         Log::info('→ Histórico recebido', ['historico' => $historicoRelacao]);

    //         $relacaoImobilizados = RelacaoImobilizados::find($request->id_relacao_imobilizados);
    //         $relacaoImobilizados->update([
    //             'data_alteracao' => now()
    //         ]);

    //         Log::info('→ Iniciando processamento do histórico');
    //         foreach ($historicoRelacao as $index => $itemHistorico) {
    //             Log::info("→ Processando item $index", ['item' => $itemHistorico]);

    //             $itemExistente = RelacaoImobilizadosItens::find($itemHistorico->id_relacao_imobilizados_itens);
    //             Log::info('→ Item existente no banco', ['item' => $itemExistente]);

    //             $dadosParaAtualizar = ['data_alteracao' => now()];

    //             if ($itemHistorico->id_veiculo !== null && $itemHistorico->id_veiculo !== '' && $itemHistorico->id_veiculo != $itemExistente->id_veiculo) {
    //                 $dadosParaAtualizar['id_veiculo'] = $itemHistorico->id_veiculo;
    //             }

    //             if ($itemHistorico->id_departamento !== null && $itemHistorico->id_departamento !== '' && $itemHistorico->id_departamento != $itemExistente->id_departamento) {
    //                 $dadosParaAtualizar['id_departamento'] = $itemHistorico->id_departamento;
    //             }

    //             if ($itemHistorico->id_reponsavel !== null && $itemHistorico->id_reponsavel !== '' && $itemHistorico->id_reponsavel != $itemExistente->id_reponsavel) {
    //                 $dadosParaAtualizar['id_reponsavel'] = $itemHistorico->id_reponsavel;
    //             }

    //             if ($itemHistorico->id_lider !== null && $itemHistorico->id_lider !== '' && $itemHistorico->id_lider != $itemExistente->id_lider) {
    //                 $dadosParaAtualizar['id_lider'] = $itemHistorico->id_lider;
    //             }

    //             if ($itemHistorico->id_produtos_imobilizados !== null && $itemHistorico->id_produtos_imobilizados !== '' && $itemHistorico->id_produtos_imobilizados != $itemExistente->id_produtos_imobilizados) {
    //                 $dadosParaAtualizar['id_produtos_imobilizados'] = $itemHistorico->id_produtos_imobilizados;
    //             }

    //             // Só atualiza se houver mudanças além da data
    //             if (count($dadosParaAtualizar) > 1) {
    //                 $itemExistente->update($dadosParaAtualizar);
    //                 Log::info('→ Item atualizado com sucesso', ['atualizado' => $dadosParaAtualizar]);
    //             } else {
    //                 Log::info('→ Nenhuma alteração detectada, item não atualizado.');
    //             }
    //         }
    //         DB::commit();

    //         return redirect()
    //             ->route('admin.saidarelacaoimobilizado.index')
    //             ->withNotification([
    //                 'title'   => 'Sucesso!',
    //                 'type'    => 'success',
    //                 'message' => 'Baixa de Imobilizado alterada com sucesso!',
    //             ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Erro na alteração na Baixa de Imobilizado:', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return redirect()
    //             ->route('admin.saidarelacaoimobilizado.index')
    //             ->withNotification([
    //                 'title'   => 'Erro!',
    //                 'type'    => 'error',
    //                 'message' => "Não foi possível alterar a Baixa de Imobilizado." . $e->getMessage()
    //             ]);
    //     }
    // }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id_relacao_imobilizados' => 'required',
            'id_usuario' => 'required',
            'id_filial' => 'required',
            'id_orderm_servico' => 'nullable',
            'historicos' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            // 1. Cria a relação principal
            $relacao = RelacaoImobilizados::find($validated['id_relacao_imobilizados']);

            // 2. Verifica transferência
            $isTransferencia = $this->verificarTransferencia($relacao->id_relacao_imobilizados);

            // 3. Atualiza status dos imobilizados
            $this->atualizarProdutosImobilizados($relacao, $isTransferencia);

            // 4. Verifica consistência 
            $this->verificarConsistencia($relacao);

            DB::commit();

            return redirect()
                ->route('admin.saidarelacaoimobilizado.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Baixa de Imobilizado alterada com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na alteração na Baixa de Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.saidarelacaoimobilizado.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível alterar a Baixa de Imobilizado." . $e->getMessage()
                ]);
        }
    }

    private function verificarTransferencia(int $relacaoId): bool
    {
        return TransferenciaEstoqueImobilizadoAux::where('id_relacao_antigo', $relacaoId)
            ->orWhere('id_relacao_novo', $relacaoId)
            ->exists();
    }

    private function atualizarProdutosImobilizados(RelacaoImobilizados $relacao, bool $isTransferencia): void
    {
        $status = $isTransferencia ? 'EM TRANSITO' : 'APLICADO';

        $relacao->relacaoImobilizadosItens()->each(function ($item) use ($status) {
            if ($item->id_produtos_imobilizados) {
                $updateData = [
                    'status' => $status,
                    'id_departamento' => $item->id_departamento,
                    'id_responsavel_imobilizado' => $item->id_reponsavel,
                    'id_lider_setor' => $item->id_lider
                ];

                if ($item->id_veiculo) {
                    $updateData['id_veiculo'] = $item->id_veiculo;
                }

                ProdutosImobilizados::where('id_produtos_imobilizados', $item->id_produtos_imobilizados)
                    ->update($updateData);
            }
        });
    }

    private function verificarConsistencia(RelacaoImobilizados $relacao): void
    {
        $countItens = $relacao->relacaoImobilizadosItens()->count();
        $countImobilizados = $relacao->relacaoImobilizadosItens()->whereNotNull('id_produtos_imobilizados')->count();

        if (
            $countItens === $countImobilizados &&
            $relacao->status !== 'FINALIZADA' &&
            $relacao->status !== 'BAIXA COMPLETA'
        ) {
            $relacao->update([
                'status' => 'AGUARDANDO ENVIO',
                'data_alteracao' => now()
            ]);
        }
    }

    public function onFinalizar(Request $request): JsonResponse
    {

        $produtosImobilizados = $request->input('produtosImobilizados');

        DB::beginTransaction();

        $id = $request->input('id');
        Log::info('→ Iniciando processamento da Baixa de Imobilizado', ['id' => $id]);
        Log::info('-> Pegando o historico', ['produtosImobilizados' => $produtosImobilizados]);

        try {
            // 1. Verificar se todos os itens têm termo anexado
            $todosComTermo = RelacaoImobilizadosItens::where('id_relacao_imobilizados', $id)
                ->whereNull('caminho_imobilizado')
                ->doesntExist();

            if (!$todosComTermo) {
                return response()->json([
                    'notification' => [
                        'title' => 'Erro',
                        'message' => 'Existem produtos sem Termo de Responsabilidade anexado.',
                        'type' => 'error'
                    ]
                ], 422);
            }

            // 2. Verificar se todos os itens têm imobilizados vinculados
            $relacao = RelacaoImobilizados::with('relacaoImobilizadosItens')->findOrFail($id);

            $isFinalizada = $relacao->status === 'FINALIZADA';


            if ($isFinalizada) {
                return response()->json([
                    'notification' => [
                        'title' => 'Erro',
                        'message' => 'Relação finalizada.',
                        'type' => 'error'
                    ]
                ], 422);
            }

            $todosComImobilizado = $relacao->relacaoImobilizadosItens->every(function ($item) {
                return !is_null($item->id_produtos_imobilizados);
            });

            Log::info('→ Verificar se todos estao vinculado ao Imobilizado', ['relacao' => $relacao]);

            if (!$todosComImobilizado) {
                return response()->json([
                    'notification' => [
                        'title' => 'Erro',
                        'message' => 'Existem itens sem imobilizados vinculados.',
                        'type' => 'error'
                    ]
                ], 422);
            }

            // 3. Atualizar status da relação
            $relacao->data_alteracao = now();
            $relacao->update(['status' => 'FINALIZADA']);

            // 4. Atualizar status dos imobilizados
            $idsImobilizados = $relacao->relacaoImobilizadosItens->pluck('id_produtos_imobilizados')->filter();

            ProdutosImobilizados::whereIn('id_produtos_imobilizados', $idsImobilizados)
                ->update(['status' => 'APLICADO', 'data_alteracao' => now()]);

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Relação de imobilizados finalizada com sucesso!!'
                ],
                'redirect' => route('admin.saidarelacaoimobilizado.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao finalizar a Requisição Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'Erro ao finalizar: ' . $e->getMessage(),
                    'message' => 'Erro ao finalizar a Requisição Imobilizado!'
                ],
            ]);
        }
    }

    public function onEstornarOs(Request $request): JsonResponse
    {

        $id_relacao_imobilizados = $request->input('idRelacaoImobilizados');

        $validated = $request->validate([
            'idRelacaoImobilizados' => 'nullable',
            'idRelacaoImobilizadosItens' => 'nullable'
        ]);

        DB::beginTransaction();

        try {
            // 1. Busca o item com relacionamentos
            $item = RelacaoImobilizadosItens::with(['relacaoImobilizados', 'produtoImobilizado'])
                ->findOrFail($validated['idRelacaoImobilizadosItens']);

            // 2. Valida se pode estornar
            $validationError = $this->validateEstorno($item);
            if ($validationError) {
                return response()->json([
                    'success' => false,
                    'message' => $validationError
                ], 422);
            }

            // 3. Atualiza a OS
            $this->updateOrdemServico($item);

            // 4. Atualiza o imobilizado
            $this->updateProdutoImobilizado($item);

            // 5. Remove o item da relação
            $item->delete();

            DB::commit();

            $this->onEdit($id_relacao_imobilizados);

            return response()->json([
                'notification' => [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Estorno realizado com sucesso!'
                ],
                'redirect' => route('admin.saidarelacaoimobilizado.edit', [
                    'saidarelacaoimobilizado' => $id_relacao_imobilizados
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na alteração na Baixa de Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro no estorno!'
                ],
            ]);
        }
    }

    /**
     * Valida as condições para estorno
     */
    private function validateEstorno(RelacaoImobilizadosItens $item): ?string
    {
        if (!$item->relacaoImobilizados->id_orderm_servico) {
            return 'Item não está vinculado a uma Ordem de Serviço';
        }

        if (!is_null($item->quantidade_baixa)) {
            return 'Item já foi baixado e não pode ser estornado';
        }

        if ($item->relacaoImobilizados->status === 'FINALIZADA') {
            return 'Relação já foi finalizada';
        }

        return null;
    }

    /**
     * Atualiza a Ordem de Serviço relacionada
     */
    private function updateOrdemServico(RelacaoImobilizadosItens $item): void
    {
        OrdemServicoPecas::where('id_ordem_servico', $item->relacaoImobilizados->id_orderm_servico)
            ->where('id_produto', $item->id_produtos)
            ->update([
                'jasolicitada' => false,
                'situacao_pecas' => 'ESTORNADA',
                'data_alteracao' => now()
            ]);
    }

    /**
     * Atualiza o status do produto imobilizado
     */
    private function updateProdutoImobilizado(RelacaoImobilizadosItens $item): void
    {
        if ($item->id_produtos_imobilizados) {
            ProdutosImobilizados::where('id_produtos_imobilizados', $item->id_produtos_imobilizados)
                ->update([
                    'status' => 'EM ESTOQUE',
                    'data_alteracao' => now()
                ]);
        }
    }

    /**
     * Registra o histórico da operação
     */
    public function onSalvarTermo(Request $request): JsonResponse
    {
        $id_requisicao = $request->input('relacaoImobilizados');
        $id = $request->input('relacaoImobilizadosItens');

        Log::info('→ Iniciando upload de arquivos...', $request->all());

        if (!$request->hasFile('arquivo')) {
            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'message' => 'Nenhum arquivo enviado.',
                    'type' => 'error'
                ]
            ], 422);
        }

        $file = $request->file('arquivo');

        if (!$file->isValid()) {
            Log::warning('→ Arquivo inválido enviado.');
            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'message' => 'Arquivo inválido.',
                    'type' => 'error'
                ]
            ], 422);
        }

        Log::info('→ Upload ok:', [
            'nome' => $file->getClientOriginalName(),
            'tamanho' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        if ($file->getSize() > 10 * 1024 * 1024) {
            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'message' => 'Arquivo excede o tamanho máximo permitido de 10MB.',
                    'type' => 'error'
                ]
            ], 422);
        }

        try {
            DB::beginTransaction();

            $relacaoImobilizadosItens = RelacaoImobilizadosItens::find($id);

            if (!$relacaoImobilizadosItens) {
                throw new \Exception("Item com ID $id não encontrado.");
            }

            if ($relacaoImobilizadosItens->caminho_arquivo && Storage::disk('public')->exists($relacaoImobilizadosItens->caminho_arquivo)) {
                Storage::disk('public')->delete($relacaoImobilizadosItens->caminho_arquivo);
            }


            $path = $file->store('laudos', 'public');
            $relacaoImobilizadosItens->caminho_imobilizado = $path;
            $relacaoImobilizadosItens->save();

            Log::info('→ Termo salvo com sucesso', ['id' => $id]);

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Termo salvo com sucesso!'
                ],
                'redirect' => route('admin.saidarelacaoimobilizado.edit', [
                    'saidarelacaoimobilizado' => $id_requisicao
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro no salvamento do termo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro no salvamento do termo!'
                ],
            ]);
        }
    }

    public function onSalvarProduto(Request $request): JsonResponse
    {

        $id = $request->input('id_relacao_imobilizados_itens');
        $id_relacao_imobilizados = $request->input('id_relacao_imobilizados');
        $id_reponsavel = $request->input('id_reponsavel');
        $id_produtos_imobilizados = $request->input('id_produtos_imobilizados');
        $id_lider = $request->input('id_lider');
        $id_departamento = $request->input('id_departamento');
        $id_veiculo = $request->input('id_veiculo');

        Log::info('→ Iniciando onSalvarProduto...', $request->all());

        try {
            DB::beginTransaction();

            $relacaoImobilizadosItens = RelacaoImobilizadosItens::find($id);
            $relacaoImobilizadosItens->update([
                'data_alteracao' => now()
            ]);

            Log::info('→ Item existente no banco', ['item' => $relacaoImobilizadosItens]);
            Log::info('→ Item existente dados', ['dados' => $request->all()]);

            if (!$relacaoImobilizadosItens) {
                throw new \Exception("Item com ID $id não encontrado.");
            }

            $dadosParaAtualizar = ['data_alteracao' => now()];

            $campos = [
                'id_reponsavel' => $id_reponsavel,
                'id_produtos_imobilizados' => $id_produtos_imobilizados,
                'id_lider' => $id_lider,
                'id_departamento' => $id_departamento,
                'id_veiculo' => $id_veiculo,
            ];

            $dadosParaAtualizar = [];

            foreach ($campos as $campo => $valorNovo) {
                $valorAtual = $relacaoImobilizadosItens->$campo;

                // Verifica se já existe valor e se está sendo alterado
                if ($valorNovo !== $valorAtual) {
                    $dadosParaAtualizar[$campo] = $valorNovo;
                }
                Log::info('→ Dados para atualizar', ['dados' => $dadosParaAtualizar]);
            }

            if (count($dadosParaAtualizar) > 0) {
                $relacaoImobilizadosItens->update($dadosParaAtualizar);
                Log::info('→ Item atualizado com sucesso', ['atualizado' => $dadosParaAtualizar]);
            } else {
                Log::info('→ Nenhuma alteração detectada, item não atualizado.', ['Não atualizado' => $dadosParaAtualizar]);
            }

            Log::info('→ Termo salvo com sucesso', ['id' => $id]);

            DB::commit();

            $this->onEdit($id_relacao_imobilizados);

            return response()->json([
                'notification' => [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Termo salvo com sucesso!'
                ],
                'redirect' => route('admin.saidarelacaoimobilizado.edit', [
                    'saidarelacaoimobilizado' => $id_relacao_imobilizados
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro no salvamento do termo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro no salvamento do termo!'
                ],
            ]);
        }
    }

    private function getProdutoDescricao()
    {
        return Produto::all()
            ->pluck('descricao_produto', 'id_produto')
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

    private function getIdProduto()
    {
        return Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('is_imobilizado', '=', true)
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();
    }

    private function getIdFilial()
    {
        return Filial::select('id as value', 'name as label')
            ->get()
            ->toArray();
    }

    private function getIdUsuario()
    {
        return User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();
    }

    private function getIdRelacaoImobilizados()
    {
        return RelacaoImobilizados::select('id_relacao_imobilizados as value', 'id_relacao_imobilizados as label')
            ->orderBy('id_relacao_imobilizados', 'desc')
            ->where('status', '!=', 'FINALIZADA')
            ->where('status', '!=', 'BAIXA COMPLETA')
            ->where('aprovado', '=', true)
            ->where('finalizado_aprovacao', '=', true)
            ->where('aprovado_gestor', '=', true)
            ->get()
            ->toArray();
    }

    private function getStatus()
    {
        return RelacaoImobilizados::select('status as value', 'status as label')
            ->orderBy('status', 'desc')
            ->where('status', '!=', 'FINALIZADA')
            ->where('status', '!=', 'BAIXA COMPLETA')
            ->where('aprovado', '=', true)
            ->where('finalizado_aprovacao', '=', true)
            ->where('aprovado_gestor', '=', true)
            ->distinct()
            ->get()
            ->toArray();
    }

    private function getRequisicaoItens($id = null)
    {

        if ($id !== null) {
            return RelacaoImobilizadosItens::where('id_relacao_imobilizados', $id)
                ->orderBy('id_relacao_imobilizados_itens')
                ->with('produto')
                ->with('produtoImobilizado')
                ->get()
                ->map(function ($itens) {
                    return [
                        'label' => $itens->id_relacao_imobilizados,
                        'value' => $itens->id_relacao_imobilizados_itens,
                        'produto' => $itens->produto->descricao_produto ?? "Não informado",
                        'produtoImobilizado' => $itens->id_produtos_imobilizados ?? "Não informado",
                        'patrimonio' => $itens->produtoImobilizado->cod_patrimonio ?? "Não informado",
                    ];
                })
                ->values()
                ->toArray();
        } else {
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
    }

    private function getRequisicaoImobilizadosTransferencia($id)
    {
        return RelacaoImobilizados::with('transferenciaEstoqueImobilizadoAux')
            ->findOrFail($id)
            ->toArray();
    }

    private function getIdPessoal()
    {
        return Pessoal::select('id_pessoal as value', 'nome as label')
            ->orderBy('label')
            ->where('ativo', '=', true)
            ->get()
            ->toArray();
    }

    private function getIdLiderSetor()
    {
        return Pessoal::select('id_pessoal as value', 'nome as label')
            ->orderBy('nome', 'asc')
            ->where('ativo', '=', true)
            ->orderBy('label')
            ->get()
            ->toArray();
    }

    private function getIdDepartamento()
    {
        return Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(20)
            ->get()
            ->toArray();
    }

    private function getIdVeiculosFrequentes()
    {
        return Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);
    }

    public function onEdit($id)
    {
        return DB::transaction(function () use ($id) {
            // 1. Carrega o objeto principal
            $relacao = RelacaoImobilizados::findOrFail($id);

            // 2. Atualiza status se for 'INICIADA'
            if ($relacao->status == 'INICIADA') {
                $relacao->update(['status' => 'EM BAIXA']);
            }

            // 3. Carrega os itens relacionados (master-detail)
            $itens = RelacaoImobilizadosItens::where('id_relacao_imobilizados', $id)->get();

            // 4. Verifica status para atualizações adicionais
            $countProdutos = $itens->count();
            $countImobilizados = $itens->whereNotNull('id_produtos_imobilizados')->count();

            if ($countProdutos != $countImobilizados) {
                $relacao->update(['status' => 'EM BAIXA PARCIAL']);
            } elseif ($countProdutos == $countImobilizados) {
                $relacao->update(['status' => 'BAIXA COMPLETA']);
            }
        });
    }
}
