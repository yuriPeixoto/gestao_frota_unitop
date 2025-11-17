<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneHelper;
use App\Http\Controllers\Controller;
use App\Models\SolicitacaoCompra;
use App\Models\ItemSolicitacaoCompra;
use App\Models\ItemCompra;
use App\Models\VFilial;
use App\Models\Departamento;
use App\Modules\Manutencao\Models\GrupoServico;
use App\Modules\Manutencao\Models\SubgrupoServico;
use App\Models\Telefone;
use App\Models\User;
use App\Services\IntegracaoWhatssappCarvalimaService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller para gerenciar itens direcionados para compras
 *
 * Este controller trabalha diretamente com a model ItemCompra,
 * que contém os registros de produtos que foram direcionados
 * para compras através do processo de baixa de estoque.
 */

class ItensParaCompraController extends Controller
{

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ItemCompra::query()
            ->with(['produto.grupoServico', 'produto.subgrupoServico', 'relacaoSolicitacoesPecas'])
            ->where(function ($q) {
                $q->where('situacao', 'COMPRAS')
                    ->orWhere('situacao', 'COMPRA PARCIAL');
            });

        // Filtros
        if ($request->filled('id')) {
            $query->where('id_item_compra', $request->id);
        }

        if ($request->filled('codigo_produto')) {
            $query->whereHas('produto', function ($q) use ($request) {
                $q->where('codigo_produto', 'LIKE', '%' . $request->codigo_produto . '%');
            });
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        // Filtro por grupo
        if ($request->filled('grupo_servico')) {
            $query->whereHas('produto', function ($q) use ($request) {
                $q->where('id_grupo_servico', $request->grupo_servico);
            });
        }

        // Filtro por subgrupo
        if ($request->filled('subgrupo_servico')) {
            $query->whereHas('produto', function ($q) use ($request) {
                $q->where('id_produto_subgrupo', $request->subgrupo_servico);
            });
        }

        // Ordenar por grupo primeiro, depois por data
        $itensParaCompra = $query->leftJoin('produto', 'item_compra.id_produto', '=', 'produto.id_produto')
            ->leftJoin('grupo_servico', 'produto.id_grupo_servico', '=', 'grupo_servico.id_grupo')
            ->orderBy('grupo_servico.descricao_grupo', 'asc')
            ->orderBy('item_compra.data_inclusao', 'desc')
            ->select('item_compra.*')
            ->paginate(10);

        // Organizar itens por grupo para exibição
        $itensAgrupados = collect();
        foreach ($itensParaCompra as $item) {
            $grupoNome = $item->produto->grupoServico->descricao_grupo ?? 'Sem Grupo';

            if (!$itensAgrupados->has($grupoNome)) {
                $itensAgrupados[$grupoNome] = collect();
            }

            $itensAgrupados[$grupoNome]->push($item);
        }

        // Ordenar grupos alfabeticamente
        $itensAgrupados = $itensAgrupados->sortKeys();

        // Buscar grupos para o filtro
        $grupos = GrupoServico::orderBy('descricao_grupo')->get();

        // Buscar subgrupos para o filtro (se grupo selecionado)
        $subgrupos = collect();
        if ($request->filled('grupo_servico')) {
            $subgrupos = SubgrupoServico::where('ig_grupo', $request->grupo_servico)
                ->orderBy('descricao_subgrupo')
                ->get();
        }

        // Buscar filiais para o modal
        $filiais = VFilial::orderBy('name')->get();

        // Buscar departamentos para o modal
        $departamentos = Departamento::where('ativo', true)
            ->orderBy('descricao_departamento')
            ->get();

        return view(
            'admin.itensparacompra.index',
            compact('itensParaCompra', 'itensAgrupados', 'grupos', 'subgrupos', 'filiais', 'departamentos')
        );
    }

    /**
     * Buscar subgrupos por grupo via AJAX
     */
    public function getSubgrupos(Request $request)
    {
        $grupoId = $request->get('grupo_id');

        if (!$grupoId) {
            return response()->json([]);
        }

        $subgrupos = SubgrupoServico::where('ig_grupo', $grupoId)
            ->orderBy('descricao_subgrupo')
            ->get(['id_subgrupo', 'descricao_subgrupo']);

        return response()->json($subgrupos);
    }

    /**
     * Criar solicitação de compra com os itens selecionados
     */
    public function criarSolicitacao(Request $request)
    {
        $validated = $request->validate([
            'itens_selecionados' => 'required|array|min:1',
            'itens_selecionados.*' => 'exists:item_compra,id_item_compra',
            'prioridade' => 'required|in:BAIXA,MEDIA,ALTA',
            'filial_entrega' => 'required|exists:filiais,id',
            'filial_faturamento' => 'required|exists:filiais,id',
            'id_departamento' => 'required|exists:departamento,id_departamento',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Criar a solicitação de compra
            $solicitacao = SolicitacaoCompra::create([
                'id_solicitante' => $user->id,
                'id_filial' => $user->filial_id,
                'id_departamento' => $validated['id_departamento'],
                'prioridade' => $validated['prioridade'],
                'filial_entrega' => $validated['filial_entrega'],
                'filial_faturamento' => $validated['filial_faturamento'],
                'tipo_solicitacao' => 1, // Produto
                'situacao_compra' => null,
                'observacao' => 'Solicitação criada automaticamente a partir de itens para compra',
                'data_inclusao' => now(),
            ]);

            // Para cada item selecionado, criar item na solicitação
            foreach ($validated['itens_selecionados'] as $itemId) {
                $itemCompra = ItemCompra::findOrFail($itemId);

                ItemSolicitacaoCompra::create([
                    'id_solicitacao_compra' => $solicitacao->id_solicitacoes_compras,
                    'id_produto' => $itemCompra->id_produto,
                    'quantidade_solicitada' => $itemCompra->quantidade_compra,
                    'id_unidade' => $itemCompra->produto->id_unidade_produto ?? null,
                    'observacao_item' => 'Item adicionado automaticamente a partir do ItemCompra',
                    'data_inclusao' => now(),
                ]);

                // Atualizar situação do ItemCompra para indicar que foi incluído em solicitação
                $itemCompra->update([
                    'situacao' => 'EM SOLICITACAO',
                    'data_alteracao' => now(),
                ]);
            }

            // Registrar log da solicitação
            $solicitacao->registrarLog('INCLUIDA', $user->id, 'Solicitação criada automaticamente');

            DB::commit();

            $numeroDaSolicitacao = $solicitacao->id_solicitacoes_compras;
            $data = now()->format('d/m/Y H:i');

            // Buscar itens da solicitação para montar a lista
            $itensSolicitacao = ItemSolicitacaoCompra::with(['produto.grupoServico'])
                ->where('id_solicitacao_compra', $solicitacao->id_solicitacoes_compras)
                ->get();

            // Montar lista de itens
            $itens = $itensSolicitacao->map(function ($item) {
                return $item->produto->descricao_produto ?? 'Produto não encontrado';
            })->implode(', ');

            // Buscar grupos dos itens
            $grupos = $itensSolicitacao->map(function ($item) {
                return $item->produto->grupoServico->descricao_grupo ?? 'Sem grupo';
            })->unique()->implode(', ');

            $solicitacaoDepartamento = $solicitacao->id_departamento;

            $this->notificationService->sendToDepartments(
                departmentIds: [$solicitacaoDepartamento],
                type: 'solicitacao.criada',
                title: 'Solicitação de Compra Criada',
                message: "Foi criada a solicitação: $numeroDaSolicitacao na data: $data com os itens: $itens do grupo: $grupos",
                data: [
                    'solicitacao_id' => $numeroDaSolicitacao,
                    'url' => route('admin.compras.solicitacoes.show', $numeroDaSolicitacao),
                ],
                priority: 'normal',
                icon: 'file-invoice',
                color: 'blue'
            );

            // // Debug inicial - verificar se existem telefones para id_pessoal 32753
            // $todosTelefones = Telefone::where('id_pessoal', 32753)->get();
            // Log::info('Debug WhatsApp - Todos os telefones da pessoa 32753:', [
            //     'total' => $todosTelefones->count(),
            //     'telefones' => $todosTelefones->map(function ($t) {
            //         return [
            //             'id' => $t->id_telefone,
            //             'celular' => $t->telefone_celular,
            //             'fixo' => $t->telefone_fixo,
            //             'id_pessoal' => $t->id_pessoal
            //         ];
            //     })->toArray()
            // ]);

            // // Buscar os telefones com informações da pessoa específica (id_pessoal = 32753)
            // $telefone = Telefone::with('pessoal')
            //     ->whereNotNull('telefone_celular')
            //     ->where('id_pessoal', 32753)
            //     ->first();

            // if ($telefone) {
            //     // Preparar dados para a mensagem
            //     $numeroDaSolicitacao = $solicitacao->id_solicitacoes_compras;
            //     $data = now()->format('d/m/Y H:i');

            //     // Buscar itens da solicitação para montar a lista
            //     $itensSolicitacao = ItemSolicitacaoCompra::with(['produto.grupoServico'])
            //         ->where('id_solicitacao_compra', $solicitacao->id_solicitacoes_compras)
            //         ->get();

            //     // Montar lista de itens
            //     $itens = $itensSolicitacao->map(function ($item) {
            //         return $item->produto->descricao_produto ?? 'Produto não encontrado';
            //     })->implode(', ');

            //     // Buscar grupos dos itens
            //     $grupos = $itensSolicitacao->map(function ($item) {
            //         return $item->produto->grupoServico->descricao_grupo ?? 'Sem grupo';
            //     })->unique()->implode(', ');

            //     // Preparar dados do telefone
            //     $numeroTelefone = PhoneHelper::sanitizePhone($telefone->telefone_celular);
            //     $nome = $telefone->pessoal->nome ?? 'Nome não encontrado';

            //     Log::info('Debug WhatsApp - Dados preparados:', [
            //         'numeroTelefone' => $numeroTelefone,
            //         'nome' => $nome,
            //         'telefone_original' => $telefone->telefone_celular
            //     ]);

            //     if ($numeroTelefone) {
            //         // Texto da mensagem para o WhatsApp
            //         $texto = "*Atenção:* $nome\n"
            //             . "Foi criada a solicitação: $numeroDaSolicitacao\n"
            //             . "Na data: $data\n"
            //             . "Com os itens: $itens\n"
            //             . "Do grupo: $grupos";


            //         // Envia a mensagem via WhatsApp
            //         try {
            //             $response = IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, $nome, $numeroTelefone);
            //             Log::info('Debug WhatsApp - Resposta da API:', ['response' => $response]);
            //         } catch (\Exception $whatsappError) {
            //             Log::error('Debug WhatsApp - Erro ao enviar:', [
            //                 'error' => $whatsappError->getMessage(),
            //                 'trace' => $whatsappError->getTraceAsString()
            //             ]);
            //         }
            //     } else {
            //         Log::warning('Debug WhatsApp - Número de telefone vazio após sanitização');
            //     }
            // } else {
            //     Log::warning('Debug WhatsApp - Nenhum telefone encontrado para id_pessoal 32753');
            // }

            return response()->json([
                'success' => true,
                'message' => 'Solicitação de compra criada com sucesso!',
                'solicitacao_id' => $solicitacao->id_solicitacoes_compras,
                'redirect_url' => route('admin.itensparacompra.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar solicitação de compra automática', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar solicitação de compra: ' . $e->getMessage()
            ], 500);
        }
    }
}
