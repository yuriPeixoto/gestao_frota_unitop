<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VFilial;
use App\Modules\Configuracoes\Models\User;
use App\Models\RequisicaoPneu;
use App\Models\RequisicaoPneuModelos;
use App\Models\RequisicaoPneuItens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RequisicaoPneusVendasController extends Controller
{
    use SanitizesMonetaryValues;

    public function index(Request $request)
    {
        $query = RequisicaoPneu::select(
            'id_requisicao_pneu',
            'data_inclusao',
            'data_alteracao',
            'id_usuario_solicitante',
            'situacao',
            'id_usuario_estoque',
            'id_filial',
        )
            ->with(['filial', 'usuarioSolicitante', 'usuarioVendas'])
            ->where('is_cancelada', false)
            ->where('is_aprovado', false)
            ->where('venda', true)
            ->orderBy('id_requisicao_pneu', 'desc');


        if ($request->filled(['data_inicial', 'data_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial,
                $request->data_final
            ]);
        }

        if ($request->filled('id_requisicao_pneu')) {
            $query->where("id_requisicao_pneu", $request->id_requisicao_pneu);
        }

        if ($request->filled('id_situacao')) {
            $query->where("situacao", $request->id_situacao);
        }

        if ($request->filled('id_filial')) {
            $query->where("id_filial", $request->id_filial);
        }

        if ($request->filled('id_usuario')) {
            $query->where("id_usuario_solicitante", $request->id_usuario);
        }

        $requisicaoPneus = $query->paginate();

        $form = [
            'filial' => VFilial::select('id as value', 'name as label')->get(),
            'pessoa' => User::select('id as value', 'name as label')->get(),
            'situacao' => RequisicaoPneu::select('situacao as value', 'situacao as label')->where('situacao', '!=', null)->where('venda', true)->distinct()->get()
        ];



        return view('admin.requisicaopneusvendas.index', compact('requisicaoPneus', 'form'));
    }


    public function onAction(Request $request, $requisicaoId, $acao)
    {
        try {

            // Validar ação
            if (!in_array($acao, ['aprovar', 'reprovar'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ação inválida. Use "aprovar" ou "reprovar".'
                ], 400);
            }

            // Validar dados da requisição
            $validated = $request->validate([
                'justificativa' => $acao === 'reprovar' ? 'required|string|max:1000' : 'nullable|string|max:1000'
            ], [
                'justificativa.required' => 'A justificativa é obrigatória para reprovação.',
                'justificativa.max' => 'A justificativa não pode ter mais de 1000 caracteres.'
            ]);

            // Buscar a requisição
            $requisicao = RequisicaoPneu::find($requisicaoId);

            if (!$requisicao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requisição não encontrada.'
                ], 404);
            }

            $modelosRequisicao = RequisicaoPneuModelos::where('id_requisicao_pneu', $requisicaoId)->get();

            $itensPneu = RequisicaoPneuItens::whereIn(
                'id_requisicao_pneu_modelos',
                $modelosRequisicao->pluck('id_requisicao_pneu_modelos')
            )->get();

            if ($modelosRequisicao->isEmpty()) {
                Log::debug("ERRO: Nenhum modelo de pneu encontrado na requisição");
                return response()->json([
                    'success' => false,
                    'message' => 'Não existem modelos de pneus selecionados para esta requisição.'
                ], 422);
            }

            if ($itensPneu->isEmpty() && $acao === 'aprovar') {
                Log::debug("ERRO: Nenhum pneu específico encontrado nos modelos");
                return response()->json([
                    'success' => false,
                    'message' => 'Não existem pneus específicos selecionados para esta requisição, não é possível aprovar.'
                ], 422);
            }

            // Verificar se a requisição pode ser alterada
            if (in_array($requisicao->situacao, ['AGUARDANDO DOCUMENTO DE VENDA'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta requisição não pode mais ser alterada.'
                ], 422);
            }

            // Determinar nova situação
            $novaSituacao = $acao === 'aprovar' ? 'AGUARDANDO DOCUMENTO DE VENDA' : 'AGUARDANDO FINALIZAÇÃO';

            // Atualizar requisição
            $requisicao->update([
                'situacao' => $novaSituacao,
                'is_aprovado' => $acao === 'aprovar' ? true : false,
                'id_usuario_estoque' => Auth::user()->id ?? 0,
                'data_alteracao' => now(),
                'observacao' => $validated['justificativa'] ?? null,
            ]);

            Log::info("Requisição {$requisicaoId} {$acao}da por " . (Auth::user()->name ?? 'Sistema'), [
                'requisicao_id' => $requisicaoId,
                'acao' => $acao,
                'usuario' => Auth::user()->name ?? Auth::user()->usuario ?? 'Sistema',
                'justificativa' => $validated['justificativa'] ?? null,
                'modelos_count' => $modelosRequisicao->count(),
                'itens_count' => $itensPneu->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Requisição {$acao}da com sucesso!",
                'data' => [
                    'id' => $requisicaoId,
                    'situacao' => $novaSituacao,
                    'acao' => $acao,
                    'data_alteracao' => $requisicao->data_alteracao->format('d/m/Y H:i:s'),
                    'modelos_processados' => $modelosRequisicao->count(),
                    'pneus_processados' => $itensPneu->count()
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Erro ao {$acao} requisição {$requisicaoId}: " . $e->getMessage(), [
                'requisicao_id' => $requisicaoId,
                'acao' => $acao,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ], 500);
        }
    }

    public function getDados($id)
    {
        try {
            $requisicaoPneu = RequisicaoPneu::where('id_requisicao_pneu', $id)
                ->with(['filial', 'usuarioSolicitante', 'usuarioVendas'])
                ->first();

            if (!$requisicaoPneu) {
                return response()->json(['error' => 'Requisição não encontrada'], 404);
            }

            // Buscar os modelos da requisição
            $requisicaoPneusItens = DB::connection('pgsql')->table('requisicao_pneu_modelos as rpm')
                ->leftJoin('modelopneu as mp', 'mp.id_modelo_pneu', '=', 'rpm.id_modelo_pneu')
                ->where('rpm.id_requisicao_pneu', $requisicaoPneu->id_requisicao_pneu)
                ->select(
                    'rpm.id_requisicao_pneu_modelos',
                    'rpm.data_inclusao',
                    'rpm.data_alteracao',
                    'rpm.quantidade',
                    'rpm.quantidade_baixa',
                    'rpm.data_baixa',
                    'mp.descricao_modelo as modelo_pneu'
                )
                ->get();

            // Buscar os pneus vinculados a cada modelo
            $itensComPneus = $requisicaoPneusItens->map(function ($item) {
                // Buscar os pneus específicos deste item - SEM o join com controle_vida_pneu para evitar duplicatas
                $pneus = DB::connection('pgsql')->table('requisicao_pneu_itens as rpi')
                    ->leftJoin('pneu as p', 'p.id_pneu', '=', 'rpi.id_pneu')
                    ->leftJoin('modelopneu as mp', 'mp.id_modelo_pneu', '=', 'p.id_modelo_pneu')
                    ->leftJoin('controle_vida_pneu as cvp', 'cvp.id_controle_vida_pneu', '=', 'p.id_controle_vida_pneu')
                    ->where('rpi.id_requisicao_pneu_modelos', $item->id_requisicao_pneu_modelos)
                    ->select(
                        'rpi.id_requisicao_pneu_itens',
                        'rpi.data_inclusao as data_inclusao_item',
                        'rpi.data_alteracao as data_alteracao_item',
                        'rpi.valor_venda',
                        'p.id_pneu',
                        'p.status_pneu',
                        'mp.descricao_modelo',
                        'cvp.descricao_vida_pneu'
                    )
                    ->distinct() // Garantir registros únicos
                    ->get();

                // Formatar os pneus
                $pneusFormatados = $pneus->map(function ($pneu) {
                    return [
                        'id_requisicao_pneu_itens' => $pneu->id_requisicao_pneu_itens,
                        'id_pneu' => $pneu->id_pneu,
                        'numero_fogo' => $pneu->descricao_modelo, // Usando id_pneu como identificador
                        'status_pneu' => $pneu->status_pneu ?? 'N/A',
                        'vida_pneu' => $pneu->descricao_vida_pneu ?? 'N/A', // Temporariamente removendo vida até resolver o problema da consulta
                        'modelo_pneu_individual' => trim($pneu->descricao_modelo ?? 'N/A'), // Modelo específico do pneu
                        'valor_venda' => $pneu->valor_venda ? 'R$ ' . number_format($pneu->valor_venda, 2, ',', '.') : 'R$ 0,00',
                        'data_inclusao_item' => $pneu->data_inclusao_item ? format_date($pneu->data_inclusao_item) : '',
                        'data_alteracao_item' => $pneu->data_alteracao_item ? format_date($pneu->data_alteracao_item) : ''
                    ];
                });

                return [
                    'id_requisicao_pneu_modelos' => $item->id_requisicao_pneu_modelos,
                    'data_inclusao' => $item->data_inclusao ? format_date($item->data_inclusao) : '',
                    'data_alteracao' => $item->data_alteracao ? format_date($item->data_alteracao) : '',
                    'quantidade' => $item->quantidade,
                    'quantidade_baixa' => $item->quantidade_baixa ?? '0',
                    'data_baixa' => $item->data_baixa ? format_date($item->data_baixa) : '',
                    'modelo_pneu' => trim($item->modelo_pneu ?? 'Modelo não encontrado'),
                    'pneus' => $pneusFormatados // Adicionar os pneus específicos
                ];
            });

            return response()->json([
                'requisicao' => [
                    'id_requisicao_pneu' => $requisicaoPneu->id_requisicao_pneu,
                    'data_inclusao' => format_date($requisicaoPneu->data_inclusao),
                    'data_alteracao' => format_date($requisicaoPneu->data_alteracao),
                    'situacao' => $requisicaoPneu->situacao,
                    'usuario_solicitante' => $requisicaoPneu->usuarioSolicitante->name ?? 'N/A',
                    'usuario_vendas' => $requisicaoPneu->usuarioVendas->name ?? 'N/A',
                    'filial' => $requisicaoPneu->filial->name ?? 'N/A',
                    'observacao' => $requisicaoPneu->observacao ?? '',
                    'observacao_solicitante' => $requisicaoPneu->observacao_solicitante ?? '',
                ],
                'itens' => $itensComPneus
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Buscar dados dos pneus para o modal de valores de venda
     */
    public function obterPneusParaValores($requisicaoId)
    {
        try {
            // Verificar se a requisição existe
            $requisicao = DB::connection('pgsql')->table('requisicao_pneu')
                ->where('id_requisicao_pneu', $requisicaoId)
                ->first();

            if (!$requisicao) {
                return response()->json([
                    'sucesso' => false,
                    'message' => 'Requisição não encontrada'
                ], 404);
            }

            // Buscar pneus da requisição com valores atuais
            $pneus = $this->buscarPneusComValores($requisicaoId);

            return response()->json([
                'sucesso' => true,
                'requisicao' => [
                    'id' => $requisicao->id_requisicao_pneu,
                    'situacao' => $requisicao->situacao
                ],
                'pneus' => $pneus
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar pneus para valores: " . $e->getMessage());

            return response()->json([
                'sucesso' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Buscar pneus da requisição com informações para valores de venda
     */
    private function buscarPneusComValores($requisicaoId)
    {
        $resultados = RequisicaoPneu::with(['requisicaoPneuModelos.requisicaoItens.pneu.controleVidaPneus', 'requisicaoPneuModelos.modelo'])->where('id_requisicao_pneu', 3029)->get();

        $pneusSelecionados = [];

        foreach ($resultados as $item) {
            foreach ($item->requisicaoPneuModelos as $modelo) {
                foreach ($modelo->requisicaoItens as $requisicaoItem) {
                    $pneusSelecionados[] = [
                        'id_pneu' => $requisicaoItem->id_pneu,
                        'numero_fogo' => $requisicaoItem->id_pneu,
                        'modelo' => $modelo->modelo->descricao_modelo,
                        'status' => $item->situacao,
                        'vida' => $requisicaoItem->pneu->controleVidaPneus->descricao_vida_pneu,
                        'valor_venda' => $requisicaoItem->valor_venda,
                        'data_inclusao_item' => $requisicaoItem->data_inclusao
                    ];
                }
            }
        }

        return $pneusSelecionados;
    }

    /**
     * Atualizar valores de venda dos pneus
     */
    public function atualizarValores(Request $request, $id)
    {
        try {
            // Validar requisição
            $request->validate([
                'valores' => 'required|array',
                'valores.*' => 'required|numeric|min:0|max:99999.99'
            ]);

            // Buscar a requisição
            $requisicao = RequisicaoPneu::with('requisicaoPneuModelos.requisicaoItens')->where('id_requisicao_pneu', $id)->first();

            // Verificar se a requisição pode ser editada
            if ($requisicao->situacao !== 'AGUARDANDO APROVACAO') {
                return response()->json([
                    'sucesso' => false,
                    'message' => 'Apenas requisições pendentes podem ter valores editados'
                ], 422);
            }

            $valoresAtualizados = [];
            $totalAtualizado = 0;

            DB::beginTransaction();

            try {
                foreach ($requisicao->requisicaoPneuModelos as $modelo) {
                    foreach ($request->valores as $pneuId => $novoValor) {
                        // Buscar o item da requisição
                        $item = RequisicaoPneuItens::where('id_requisicao_pneu_modelos', $modelo->id_requisicao_pneu_modelos)
                            ->where('id_pneu', $pneuId)
                            ->first();

                        if (!$item) {
                            throw new \Exception("Pneu ID {$pneuId} não encontrado nesta requisição");
                        }

                        // Atualizar o valor
                        $valorAnterior = $item->valor_venda;
                        $item->valor_venda    = $novoValor;
                        $item->data_alteracao = now();
                        $item->id_user_edit   = Auth::user()->id;
                        $item->save();

                        $valoresAtualizados[] = [
                            'pneu_id' => $pneuId,
                            'valor_anterior' => $valorAnterior,
                            'valor_novo' => $novoValor
                        ];

                        $totalAtualizado += $novoValor;

                        // Log da alteração (opcional)
                        Log::info("Valor do pneu {$pneuId} alterado", [
                            'requisicao_id' => $id,
                            'pneu_id' => $pneuId,
                            'valor_anterior' => $valorAnterior,
                            'valor_novo' => $novoValor,
                            'id_user_edit' => Auth::user()->id
                        ]);
                    }
                }

                // Atualizar total da requisição se necessário
                $totalRequisicao = 0;
                $totalRequisicao += $novoValor;

                DB::commit();

                return response()->json([
                    'sucesso' => true,
                    'message' => 'Valores atualizados com sucesso',
                    'dados' => [
                        'valores_atualizados' => $valoresAtualizados,
                        'total_requisicao' => $totalRequisicao,
                        'quantidade_alteracoes' => count($valoresAtualizados)
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'sucesso' => false,
                'message' => 'Dados inválidos',
                'erros' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar valores dos pneus', [
                'requisicao_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'sucesso' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $requisicoes = Cache::remember('requisicao_pneu_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return RequisicaoPneu::select('id_requisicao_pneu', 'observacao', 'data_inclusao')
                ->whereRaw('CAST(id_requisicao_pneu AS TEXT) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('LOWER(observacao) LIKE ?', ["%{$term}%"])
                ->orderByDesc('data_inclusao')
                ->limit(30)
                ->get()
                ->map(function ($req) {
                    // Se quiser apenas o número:
                    return [
                        'label' => (string) $req->id_requisicao_pneu,
                        'value' => $req->id_requisicao_pneu
                    ];

                    // 👉 se preferir mostrar mais infos no autocomplete (ex: observação e data), 
                    // basta descomentar o bloco abaixo:
                    /*
                    $label = "REQ #{$req->id_requisicao_pneu}";
                    if (!empty($req->observacao)) {
                        $label .= ' - ' . mb_substr($req->observacao, 0, 40);
                    }
                    if (!empty($req->data_inclusao)) {
                        $label .= ' (' . $req->data_inclusao->format('d/m/Y') . ')';
                    }

                    return [
                        'label' => $label,
                        'value' => $req->id_requisicao_pneu
                    ];
                    */
                })->toArray();
        });

        return response()->json($requisicoes);
    }

    /**
     * Buscar uma requisição pelo ID
     */
    public function getById($id)
    {
        $req = RequisicaoPneu::where('id_requisicao_pneu', $id)->first();

        if (!$req) {
            return response()->json([], 404);
        }

        return response()->json([
            'value' => $req->id_requisicao_pneu,
            'label' => (string) $req->id_requisicao_pneu,
            'data_inclusao' => optional($req->data_inclusao)->format('d/m/Y H:i'),
            'situacao' => $req->situacao,
        ]);
    }
}
