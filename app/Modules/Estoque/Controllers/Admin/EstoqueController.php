<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Estoque\Models\Estoque;
use App\Modules\Estoque\Models\EstoqueItem;
use App\Modules\Estoque\Models\EstoqueMovimento;
use App\Models\Filial;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use App\Models\VFilial;
use App\Modules\Compras\Models\SolicitacaoCompra;
use App\Modules\Compras\Models\PedidoCompra;
use App\Modules\Compras\Models\ProdutosSolicitacoes;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoque;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoqueItens;
use App\Modules\Estoque\Models\TransferenciaEstoqueItens;
use App\Modules\Estoque\Services\EstoqueService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstoqueController extends Controller
{
    protected $estoque;
    protected $filial;
    protected $produto;
    protected $estoqueService;

    public function __construct(
        Estoque $estoque,
        VFilial $filial,
        Produto $produto,
        EstoqueService $estoqueService
    ) {
        $this->estoque = $estoque;
        $this->filial = $filial;
        $this->produto = $produto;
        $this->estoqueService = $estoqueService;
    }

    /**
     * Exibe o dashboard do módulo de estoque.
     */
    public function dashboard()
    {
        // Preparar as consultas para o dashboard
        $solicitacoesPendentes = SolicitacaoCompra::where('situacao_compra', 'em_analise')
            // ->orWhere('situacao_compra', 'em_analise')
            ->count();

        $pedidosPendentes = PedidoCompra::where('situacao', 'AGUARDANDO APROVAÇÃO')
            // ->orWhere('status', 'aguardando_aprovacao')
            ->count();

        $itensBaixoEstoque = Produto::contarEstoqueBaixo();

        // Buscar os estoques para o dashboard
        $estoques = $this->estoque
            ->with('filial')
            ->withCount('itens')
            ->paginate(15);

        // Buscar as filiais para o filtro
        $filiais = $this->getFilial();

        return view('admin.estoque.dashboard', compact(
            'estoques',
            'filiais',
            'solicitacoesPendentes',
            'pedidosPendentes',
            'itensBaixoEstoque'
        ));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Preparar as consultas para o dashboard
        $solicitacoesPendentes = SolicitacaoCompra::where('situacao_compra', 'AGUARDANDO INÍCO DE COMPRAS')
            ->count();

        $pedidosPendentes = PedidoCompra::where('situacao', 'AGUARDANDO APROVAÇÃO')
            ->count();

        $itensBaixoEstoque = Produto::contarEstoqueBaixo();

        // Aplicar filtros se necessário
        $query = $this->estoque
            ->with('filial')
            ->withCount('itens');

        if ($request->filled('id_estoque')) {
            $query->where('id_estoque', $request->id_estoque);
        }

        if ($request->filled('descricao_estoque')) {
            $query->where('descricao_estoque', 'like', '%' . $request->descricao_estoque . '%');
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        $estoques = $query->paginate(15);

        // Buscar as filiais para o filtro
        $filiais = $this->getFilial();

        return view('admin.estoque.index', compact(
            'estoques',
            'filiais',
            'solicitacoesPendentes',
            'pedidosPendentes',
            'itensBaixoEstoque'
        ));
    }

    /**
     * Retorna os dados de filiais para selects
     */
    public function getFilial()
    {
        return $this->filial
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->name,
                    'value' => $item->id
                ];
            });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $filiais = $this->getFilial();
        return view('admin.estoque.create', compact('filiais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_filial' => 'required|max:255',
            'descricao_estoque' => 'required|max:255',
        ]);

        $this->estoque->create([
            'id_filial' => $request->id_filial,
            'descricao_estoque' => $request->descricao_estoque,
            'data_inclusao' => now()
        ]);

        return redirect()->route('admin.estoque.index')
            ->with('success', 'Estoque criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $estoque = $this->estoque->where('id_estoque', $id)
            ->with(['filial', 'itens'])
            ->first();

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        // Buscar os itens deste estoque
        $itens = EstoqueItem::where('id_estoque', $id)
            ->with('produto')
            ->paginate(15);

        // Buscar movimentações recentes
        $movimentacoes = EstoqueMovimento::whereHas('estoqueItem', function ($query) use ($id) {
            $query->where('id_estoque', $id);
        })
            ->with(['estoqueItem.produto', 'usuario'])
            ->orderBy('data_movimento', 'desc')
            ->limit(10)
            ->get();

        return view('admin.estoque.show', compact('estoque', 'itens', 'movimentacoes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $estoque = $this->estoque->where('id_estoque', $id)->first();

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        $filiais = $this->getFilial();
        return view('admin.estoque.edit', compact('filiais', 'estoque'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'id_filial' => 'required|max:255',
            'descricao_estoque' => 'required|max:255',
        ]);

        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        $estoque->update([
            'id_filial' => $request->id_filial,
            'descricao_estoque' => $request->descricao_estoque,
            'data_alteracao' => now()
        ]);

        return redirect()->route('admin.estoque.index')
            ->with('success', 'Estoque atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $estoque = $this->estoque->find($id);

            if (!$estoque) {
                return redirect()->route('admin.estoque.index')
                    ->with('error', 'Estoque não encontrado!');
            }

            // Verificar se existem itens vinculados
            $temItens = EstoqueItem::where('id_estoque', $id)->exists();

            if ($temItens) {
                return redirect()->route('admin.estoque.index')
                    ->with('error', 'Não é possível excluir o estoque pois existem itens vinculados a ele!');
            }

            $estoque->delete();

            return redirect()->route('admin.estoque.index')
                ->with('success', 'Estoque excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir estoque: ' . $e->getMessage());

            return redirect()->route('admin.estoque.index')
                ->with('error', 'Erro ao excluir estoque. Verifique se não existem itens vinculados.');
        }
    }

    /**
     * Exibe o dashboard de itens com filtros de estoque
     * Lista produtos da filial do usuário com informações de estoque
     */
    public function estoqueBaixo(Request $request)
    {
        // Capturar parâmetros de filtro
        $filtros = [
            'id_estoque' => $request->get('id_estoque'),
            'id_produto' => $request->get('id_produto'),
            'ordem' => $request->get('ordem', 'desc') // padrão: maior para menor
        ];

        // Usar o método do modelo para buscar produtos da filial com paginação
        $itensBaixoEstoque = Produto::buscarProdutosDaFilialPaginado(null, 15, $filtros);

        // Preservar parâmetros da query string na paginação
        $itensBaixoEstoque->appends($request->query());

        return view('admin.estoque.estoque-baixo-view', compact('itensBaixoEstoque'));
    }

    /**
     * Exibe a tela de gestão de itens de um estoque
     */
    public function itens(string $id)
    {
        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        $itens = EstoqueItem::where('id_estoque', $id)
            ->with('produto')
            ->paginate(15);

        $produtos = $this->produto
            ->where('is_ativo', true)
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => "#{$item->id_produto} - {$item->descricao_produto}",
                    'value' => $item->id_produto
                ];
            });

        return view('admin.estoque.itens', compact('estoque', 'itens', 'produtos'));
    }

    /**
     * Adiciona um item ao estoque
     */
    public function adicionarItem(Request $request, string $id)
    {
        $request->validate([
            'id_produto' => 'required|integer|exists:produto,id_produto',
            'quantidade_minima' => 'required|numeric|min:0',
            'quantidade_maxima' => 'nullable|numeric|min:0',
            'localizacao' => 'nullable|string|max:100',
        ]);

        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        try {
            // Verificar se o item já existe
            $itemExistente = EstoqueItem::where('id_estoque', $id)
                ->where('id_produto', $request->id_produto)
                ->first();

            if ($itemExistente) {
                return redirect()->route('admin.estoque.itens', $id)
                    ->with('error', 'Este produto já está cadastrado neste estoque!');
            }

            // Criar o item
            EstoqueItem::create([
                'id_estoque' => $id,
                'id_produto' => $request->id_produto,
                'quantidade_atual' => 0,
                'quantidade_minima' => $request->quantidade_minima,
                'quantidade_maxima' => $request->quantidade_maxima,
                'localizacao' => $request->localizacao,
                'data_inclusao' => now(),
                'ativo' => true
            ]);

            return redirect()->route('admin.estoque.itens', $id)
                ->with('success', 'Item adicionado ao estoque com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar item ao estoque: ' . $e->getMessage());

            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Erro ao adicionar item ao estoque: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza um item do estoque
     */
    public function atualizarItem(Request $request, string $id, string $idItem)
    {
        $request->validate([
            'quantidade_minima' => 'required|numeric|min:0',
            'quantidade_maxima' => 'nullable|numeric|min:0',
            'localizacao' => 'nullable|string|max:100',
            'ativo' => 'required|boolean',
        ]);

        $item = EstoqueItem::where('id_estoque_item', $idItem)
            ->where('id_estoque', $id)
            ->first();

        if (!$item) {
            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Item não encontrado!');
        }

        try {
            $item->update([
                'quantidade_minima' => $request->quantidade_minima,
                'quantidade_maxima' => $request->quantidade_maxima,
                'localizacao' => $request->localizacao,
                'ativo' => $request->ativo,
                'data_alteracao' => now()
            ]);

            return redirect()->route('admin.estoque.itens', $id)
                ->with('success', 'Item atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar item do estoque: ' . $e->getMessage());

            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Erro ao atualizar item do estoque: ' . $e->getMessage());
        }
    }

    /**
     * Remove um item do estoque
     */
    public function removerItem(string $id, string $idItem)
    {
        $item = EstoqueItem::where('id_estoque_item', $idItem)
            ->where('id_estoque', $id)
            ->first();

        if (!$item) {
            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Item não encontrado!');
        }

        try {
            // Verificar se há movimentações para este item
            $temMovimentos = EstoqueMovimento::where('id_estoque_item', $idItem)->exists();

            if ($temMovimentos) {
                // Se tiver movimentações, apenas inativa o item
                $item->update([
                    'ativo' => false,
                    'data_alteracao' => now()
                ]);

                return redirect()->route('admin.estoque.itens', $id)
                    ->with('warning', 'Item possui movimentações e foi inativado em vez de excluído.');
            }

            // Se não tiver movimentações, pode excluir
            $item->delete();

            return redirect()->route('admin.estoque.itens', $id)
                ->with('success', 'Item removido do estoque com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao remover item do estoque: ' . $e->getMessage());

            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Erro ao remover item do estoque: ' . $e->getMessage());
        }
    }

    /**
     * Exibe a tela de movimentações de um item do estoque
     */
    public function movimentacoes(string $id, string $idItem)
    {
        $item = EstoqueItem::where('id_estoque_item', $idItem)
            ->where('id_estoque', $id)
            ->with(['estoque', 'produto'])
            ->first();

        if (!$item) {
            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Item não encontrado!');
        }

        $movimentacoes = EstoqueMovimento::where('id_estoque_item', $idItem)
            ->with('usuario')
            ->orderBy('data_movimento', 'desc')
            ->paginate(15);

        return view('admin.estoque.movimentacoes', compact('item', 'movimentacoes'));
    }

    /**
     * Exibe a tela de entrada de produtos no estoque
     */
    public function entradaForm(string $id)
    {
        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        $produtos = $this->produto
            ->where('is_ativo', true)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => "#{$item->id_produto} - {$item->descricao_produto}",
                    'value' => $item->id_produto
                ];
            });

        // Buscar pedidos de compra aprovados para vincular
        $pedidosCompra = PedidoCompra::where('status', 'aprovado')
            ->orWhere('status', 'parcial')
            ->with('fornecedor')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => "#{$item->id_pedido} - " . ($item->fornecedor ?? 'Sem fornecedor'),
                    'value' => $item->id_pedido
                ];
            });

        $origens = [
            ['label' => 'Compra', 'value' => 'compra'],
            ['label' => 'Devolução', 'value' => 'devolucao'],
            ['label' => 'Ajuste de Inventário', 'value' => 'ajuste'],
            ['label' => 'Transferência', 'value' => 'transferencia'],
            ['label' => 'Outro', 'value' => 'outro']
        ];

        return view('admin.estoque.entrada-form', compact('estoque', 'produtos', 'pedidosCompra', 'origens'));
    }

    /**
     * Processa a entrada de produtos no estoque
     */
    public function registrarEntrada(Request $request, string $id)
    {
        $request->validate([
            'id_produto' => 'required|integer|exists:produto,id_produto',
            'quantidade' => 'required|numeric|min:0.01',
            'origem' => 'required|string|max:50',
            'id_referencia' => 'nullable|integer',
            'observacao' => 'nullable|string|max:500',
        ]);

        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        try {
            $resultado = $estoque->entradaProduto(
                $request->id_produto,
                $request->quantidade,
                $request->origem,
                $request->id_referencia,
                $request->observacao
            );

            if ($resultado) {
                return redirect()->route('admin.estoque.itens', $id)
                    ->with('success', 'Entrada registrada com sucesso!');
            } else {
                return redirect()->route('admin.estoque.entrada-form', $id)
                    ->with('error', 'Não foi possível registrar a entrada!');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao registrar entrada no estoque: ' . $e->getMessage());

            return redirect()->route('admin.estoque.entrada-form', $id)
                ->with('error', 'Erro ao registrar entrada no estoque: ' . $e->getMessage());
        }
    }

    /**
     * Exibe a tela de saída de produtos do estoque
     */
    public function saidaForm(string $id)
    {
        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        // Buscar apenas os produtos que existem neste estoque
        $itensEstoque = EstoqueItem::where('id_estoque', $id)
            ->where('ativo', true)
            ->where('quantidade_atual', '>', 0)
            ->with('produto')
            ->get();

        $produtos = $itensEstoque->map(function ($item) {
            return [
                'label' => "#{$item->produto->id_produto} - {$item->produto->descricao_produto} - Disp: {$item->quantidade_atual}",
                'value' => $item->produto->id_produto
            ];
        });

        $destinos = [
            ['label' => 'Requisição', 'value' => 'requisicao'],
            ['label' => 'Ordem de Serviço', 'value' => 'ordem_servico'],
            ['label' => 'Transferência', 'value' => 'transferencia'],
            ['label' => 'Ajuste de Inventário', 'value' => 'ajuste'],
            ['label' => 'Outro', 'value' => 'outro']
        ];

        return view('admin.estoque.saida-form', compact('estoque', 'produtos', 'destinos'));
    }

    /**
     * Processa a saída de produtos do estoque
     */
    public function registrarSaida(Request $request, string $id)
    {
        $request->validate([
            'id_produto' => 'required|integer|exists:produto,id_produto',
            'quantidade' => 'required|numeric|min:0.01',
            'destino' => 'required|string|max:50',
            'id_referencia' => 'nullable|integer',
            'observacao' => 'nullable|string|max:500',
        ]);

        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        try {
            // Verificar se tem quantidade suficiente
            $verificacao = $this->estoqueService->verificarDisponibilidade(
                $request->id_produto,
                $request->quantidade,
                $id
            );

            if (!$verificacao['disponivel']) {
                return redirect()->route('admin.estoque.saida-form', $id)
                    ->with('error', 'Quantidade solicitada não disponível no estoque!');
            }

            $resultado = $estoque->saidaProduto(
                $request->id_produto,
                $request->quantidade,
                $request->destino,
                $request->id_referencia,
                $request->observacao
            );

            if ($resultado) {
                return redirect()->route('admin.estoque.itens', $id)
                    ->with('success', 'Saída registrada com sucesso!');
            } else {
                return redirect()->route('admin.estoque.saida-form', $id)
                    ->with('error', 'Não foi possível registrar a saída!');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao registrar saída do estoque: ' . $e->getMessage());

            return redirect()->route('admin.estoque.saida-form', $id)
                ->with('error', 'Erro ao registrar saída do estoque: ' . $e->getMessage());
        }
    }

    /**
     * Exibe a tela de transferência entre estoques
     */
    public function transferenciaForm(string $id)
    {
        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        // Buscar outros estoques para transferência
        $outrosEstoques = $this->estoque
            ->where('id_estoque', '!=', $id)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => "#{$item->id_estoque} - {$item->descricao_estoque}",
                    'value' => $item->id_estoque
                ];
            });

        // Buscar apenas os produtos que existem neste estoque
        $itensEstoque = EstoqueItem::where('id_estoque', $id)
            ->where('ativo', true)
            ->where('quantidade_atual', '>', 0)
            ->with('produto')
            ->get();

        $produtos = $itensEstoque->map(function ($item) {
            return [
                'label' => "#{$item->produto->id_produto} - {$item->produto->descricao_produto} - Disp: {$item->quantidade_atual}",
                'value' => $item->produto->id_produto
            ];
        });

        return view('admin.estoque.transferencia-form-view', compact('estoque', 'produtos', 'outrosEstoques'));
    }

    /**
     * Processa a transferência entre estoques
     */
    public function registrarTransferencia(Request $request, string $id)
    {
        $request->validate([
            'id_estoque_destino' => 'required|integer|exists:estoque,id_estoque|different:' . $id,
            'id_produto' => 'required|integer|exists:produto,id_produto',
            'quantidade' => 'required|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        $estoque = $this->estoque->find($id);

        if (!$estoque) {
            return redirect()->route('admin.estoque.index')
                ->with('error', 'Estoque não encontrado!');
        }

        try {
            $resultado = $estoque->transferirProduto(
                $request->id_produto,
                $request->quantidade,
                $request->id_estoque_destino,
                $request->observacao
            );

            if ($resultado) {
                return redirect()->route('admin.estoque.itens', $id)
                    ->with('success', 'Transferência realizada com sucesso!');
            } else {
                return redirect()->route('admin.estoque.transferencia-form', $id)
                    ->with('error', 'Não foi possível realizar a transferência!');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao realizar transferência entre estoques: ' . $e->getMessage());

            return redirect()->route('admin.estoque.transferencia-form', $id)
                ->with('error', 'Erro ao realizar transferência: ' . $e->getMessage());
        }
    }

    /**
     * Verifica a disponibilidade de um produto em estoque (API)
     */
    public function verificarDisponibilidade(Request $request)
    {
        $request->validate([
            'id_produto' => 'required|integer|exists:produto,id_produto',
            'quantidade' => 'required|numeric|min:0.01',
            'id_estoque' => 'nullable|integer|exists:estoque,id_estoque',
        ]);

        try {
            $resultado = $this->estoqueService->verificarDisponibilidade(
                $request->id_produto,
                $request->quantidade,
                $request->id_estoque
            );

            return response()->json($resultado);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar disponibilidade: ' . $e->getMessage());

            return response()->json([
                'disponivel' => false,
                'erro' => 'Erro ao verificar disponibilidade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gera automaticamente solicitações de compra para itens com estoque baixo
     */

    /**
     * Registra a entrada de produtos no estoque a partir de um pedido de compra
     */
    public function entradaPedidoCompra(Request $request)
    {
        $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos_compra,id_pedido',
            'id_estoque' => 'required|integer|exists:estoque,id_estoque',
            'itens' => 'required|array',
            'itens.*.id_item_pedido' => 'required|integer|exists:itens_pedido_compra,id_item_pedido',
            'itens.*.quantidade_recebida' => 'required|numeric|min:0.01',
            'observacao' => 'nullable|string|max:500',
        ]);

        try {
            // Preparar os itens para o serviço
            $itens = [];
            foreach ($request->itens as $item) {
                $itens[$item['id_item_pedido']] = $item['quantidade_recebida'];
            }

            $resultado = $this->estoqueService->registrarEntradaCompra(
                $request->id_pedido,
                $request->id_estoque,
                $itens
            );

            if ($resultado['sucesso']) {
                return redirect()->route('admin.compras.pedidos.show', $request->id_pedido)
                    ->with('success', 'Entrada de produtos registrada com sucesso!');
            } else {
                return redirect()->route('admin.compras.pedidos.show', $request->id_pedido)
                    ->with('warning', 'Alguns itens não puderam ser registrados. Verifique os detalhes.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao registrar entrada de pedido: ' . $e->getMessage());

            return redirect()->route('admin.compras.pedidos.show', $request->id_pedido)
                ->with('error', 'Erro ao registrar entrada: ' . $e->getMessage());
        }
    }

    /**
     * Busca produto por ID para AJAX
     */
    public function getProduto(Request $request, $id)
    {
        try {
            $produto = $this->produto->find($id);

            if (!$produto) {
                return response()->json(['error' => 'Produto não encontrado'], 404);
            }

            // Verificar a disponibilidade em todos os estoques
            $disponibilidade = EstoqueItem::where('id_produto', $id)
                ->where('ativo', true)
                ->with('estoque')
                ->get()
                ->map(function ($item) {
                    return [
                        'id_estoque' => $item->id_estoque,
                        'descricao_estoque' => $item->estoque->descricao_estoque,
                        'quantidade_disponivel' => $item->quantidade_atual,
                        'localizacao' => $item->localizacao
                    ];
                });

            $totalDisponivel = $disponibilidade->sum('quantidade_disponivel');

            return response()->json([
                'produto' => $produto,
                'disponibilidade' => [
                    'total' => $totalDisponivel,
                    'detalhamento' => $disponibilidade
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao buscar produto: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Realizar ajuste de inventário para um item
     */
    public function ajusteInventario(Request $request, string $id, string $idItem)
    {
        $request->validate([
            'quantidade_atual' => 'required|numeric|min:0',
            'motivo_ajuste' => 'required|string|max:500',
        ]);

        $item = EstoqueItem::where('id_estoque_item', $idItem)
            ->where('id_estoque', $id)
            ->first();

        if (!$item) {
            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Item não encontrado!');
        }

        try {
            DB::beginTransaction();

            // Calcular a diferença para o ajuste
            $diferencaQuantidade = $request->quantidade_atual - $item->quantidade_atual;

            if ($diferencaQuantidade == 0) {
                DB::rollBack();
                return redirect()->route('admin.estoque.itens', $id)
                    ->with('info', 'Nenhum ajuste necessário pois a quantidade é a mesma.');
            }

            // Registrar o movimento de ajuste
            EstoqueMovimento::create([
                'id_estoque_item' => $item->id_estoque_item,
                'tipo_movimento' => 'ajuste',
                'quantidade' => abs($diferencaQuantidade),
                'origem' => $diferencaQuantidade > 0 ? 'ajuste_inventario_entrada' : 'ajuste_inventario_saida',
                'id_usuario' => Auth::user()->id,
                'observacao' => $request->motivo_ajuste,
                'data_movimento' => now(),
            ]);

            // Atualizar a quantidade do item
            $item->quantidade_atual = $request->quantidade_atual;
            $item->data_alteracao = now();
            $item->save();

            DB::commit();

            return redirect()->route('admin.estoque.itens', $id)
                ->with('success', 'Ajuste de inventário realizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao realizar ajuste de inventário: ' . $e->getMessage());

            return redirect()->route('admin.estoque.itens', $id)
                ->with('error', 'Erro ao realizar ajuste de inventário: ' . $e->getMessage());
        }
    }

    /**
     * Verifica a disponibilidade e gera uma solicitação quando necessário
     */
    public function verificarEGerarSolicitacao(Request $request)
    {
        $request->validate([
            'itens' => 'required|array',
            'itens.*.id_produto' => 'required|integer|exists:produto,id_produto',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
            'id_departamento' => 'required|integer',
            'id_filial' => 'required|integer',
            'observacao' => 'nullable|string',
        ]);

        try {
            // Preparar os itens para o serviço
            $produtosQuantidades = [];
            foreach ($request->itens as $item) {
                $produtosQuantidades[$item['id_produto']] = $item['quantidade'];
            }

            $resultado = $this->estoqueService->verificarDisponibilidadeEGerarSolicitacao(
                $produtosQuantidades,
                Auth::user()->id,
                $request->id_departamento,
                $request->id_filial,
                $request->observacao ?? ''
            );

            if ($resultado['solicitacao_gerada']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação de compra gerada automaticamente para os itens indisponíveis',
                    'resultado' => $resultado
                ]);
            } else if (empty($resultado['itens_indisponiveis'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Todos os itens estão disponíveis em estoque',
                    'resultado' => $resultado
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível gerar a solicitação de compra',
                    'resultado' => $resultado
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao verificar disponibilidade e gerar solicitação: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar disponibilidade e gerar solicitação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function visualizarTransferencia(Request $request)
    {
        try {
            $tab = $request->get('tab', 'transferidos'); // Default para aba transferidos

            if ($tab === 'recebidos') {
                return $this->getItensRecebidos($request);
            } else {
                return $this->getItensTransferidos($request);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatorio de transferencia de itens: ' . $e->getMessage());

            return redirect()->route('admin.estoque.dashboard')
                ->with('error', 'Erro ao gerar relatorio: ' . $e->getMessage());
        }
    }

    private function getItensTransferidos(Request $request)
    {
        // Iniciar a query base para itens transferidos (ProdutosSolicitacoes)
        $query = ProdutosSolicitacoes::query()
            ->with(['produto', 'relacaoSolicitacoesPecas.filial', 'filialTransferencia', 'user']);

        $query->where('situacao_pecas', 'TRANSFERENCIA');

        if ($request->filled('id_protudos')) {
            $query->where('id_protudos', $request->id_protudos);
        }

        if ($request->filled('filial_transferencia')) {
            $query->where('filial_transferencia', $request->filial_transferencia);
        }

        $itensTransferidos = $query->orderBy('data_inclusao', 'desc')->paginate(15)->appends($request->query());

        $produtos = $this->getProdutoSolicitacao();
        $filiais = $this->getFiliais();

        return view('admin.estoque.transferencia-view', compact('itensTransferidos', 'produtos', 'filiais'))
            ->with('activeTab', 'transferidos');
    }

    private function getItensRecebidos(Request $request)
    {
        // Query para itens recebidos (TransferenciaEstoqueItens)
        $query = TransferenciaEstoqueItens::query()
            ->with(['produto', 'transferencia.filial', 'transferencia.usuario']);

        // Filtrar apenas itens da filial do usuário logado
        $query->whereHas('transferencia', function ($q) {
            $q->where('id_filial', GetterFilial());
        });

        // Filtros
        if ($request->filled('id_protudos')) {
            $query->where('id_produto', $request->id_protudos);
        }

        if ($request->filled('filial_transferencia')) {
            $query->whereHas('transferencia', function ($q) use ($request) {
                $q->where('id_filial', $request->filial_transferencia);
            });
        }

        // Ordenar por inconsistência primeiro (itens com problemas aparecem no topo)
        $query->orderByRaw('
            CASE
                WHEN quantidade_baixa IS NOT NULL
                     AND quantidade_baixa < quantidade
                     AND quantidade_baixa > 0
                THEN 0
                ELSE 1
            END
        ')->orderBy('id_transferencia_itens', 'desc');

        // Adicionar informação sobre inconsistências
        $itensRecebidos = $query->paginate(15)->appends($request->query());

        // Marcar itens com inconsistência
        foreach ($itensRecebidos as $item) {
            $item->tem_inconsistencia = $this->verificarInconsistenciaItem($item);
        }

        $produtos = $this->getProdutoSolicitacao();
        $filiais = $this->getFiliais();

        return view('admin.estoque.transferencia-view', compact('itensRecebidos', 'produtos', 'filiais'))
            ->with('activeTab', 'recebidos');
    }

    /**
     * Verifica se um item de transferência possui inconsistência
     *
     * @param $item
     * @return bool
     */
    private function verificarInconsistenciaItem($item)
    {
        return $item->quantidade_baixa &&
            $item->quantidade_baixa < $item->quantidade &&
            $item->quantidade_baixa > 0;
    }

    private function getProdutoSolicitacao()
    {
        return ProdutosSolicitacoes::with('produto')
            ->where('situacao_pecas', 'TRANSFERENCIA')
            ->select('id_protudos')
            ->distinct()
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => "#{$item->id_protudos} - {$item->produto->descricao_produto}",
                    'value' => $item->id_protudos
                ];
            });
    }

    private function getFiliais()
    {
        return Filial::select('id', 'name')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->name,
                    'value' => $item->id
                ];
            });
    }

    public function transferenciaPage($id)
    {
        $estoque = Estoque::findOrFail($id);

        // Busca todos os itens relacionados a esse estoque
        $estoqueItems = EstoqueItem::where('id_estoque', $id)
            ->with('produto') // carrega o produto vinculado
            ->get();

        return view('admin.estoque.transferir', compact('estoque', 'estoqueItems'));
    }

    public function enviarTransferencia(Request $request)
    {
        DB::beginTransaction();

        try {

            $userFilial = Auth::user();
            //$filialId = $userFilial?->filial_id;
            $usuario = Auth::user();
            $departamentoId = $usuario->departamento_id;

            if (!$request->has('id_produto') || count($request->id_produto) === 0) {
                return redirect()->back()->with('error', 'Selecione pelo menos um produto para transferir.');
            }
            // Criando transferencia
            $transferencia = TransferenciaDiretaEstoque::create([
                'observacao' => $request->observacao,
                'filial' => 1, // destino: matriz
                'filial_solicita' => Auth::user()->filial_id ?? null,
                'id_usuario'    => Auth::id(),
                'id_departamento' => $departamentoId,
                'data_inclusao' =>  Carbon::now(),
                'data_alteracao' =>  Carbon::now(),
                'status' => 'AGUARDANDO TRANSFERENCIA',
            ]);

            foreach ($request->id_produto as $produtoId) {
                TransferenciaDiretaEstoqueItens::create([
                    'id_transferencia_direta_estoque' => $transferencia->id_transferencia_direta_estoque,
                    'id_produto'    =>  $produtoId,
                    //'qtd_baixa' =>  $produto['quantidade'],
                    'data_inclusao' => Carbon::now(),
                    //'data_alteracao' =>  Carbon::now(),

                ]);
            }
            DB::commit();

            return redirect()->route('admin.transferenciaDiretoEstoque.edit', $transferencia->id_transferencia_direta_estoque)->with('success', 'Transferência registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Erro ao registrar a transferência: ' . $e->getMessage());
        }
    }
}
