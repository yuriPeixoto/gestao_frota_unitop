<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexOrcamentoRequest;
use App\Models\Fornecedor;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\PedidoCompra;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrcamentoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Orcamento::query()
            ->orderBy('data_orcamento', 'desc');

        // Aplicar filtros
        if ($request->filled('id_pedido')) {
            $query->where('id_pedido', $request->id_pedido);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        if ($request->filled('data_inicial') && $request->filled('data_final')) {
            $query->whereBetween('data_orcamento', [$request->data_inicial, $request->data_final]);
        } elseif ($request->filled('data_inicial')) {
            $query->where('data_orcamento', '>=', $request->data_inicial);
        } elseif ($request->filled('data_final')) {
            $query->where('data_orcamento', '<=', $request->data_final);
        }

        if ($request->filled('selecionado')) {
            $query->where('selecionado', $request->selecionado == 1);
        }

        $orcamentos = $query->latest('id_orcamento')
            ->paginate(40)
            ->appends($request->query());

        // Debug temporário
        Log::info('Orçamentos carregados na página: ' . count($orcamentos->items()) . ' de ' . $orcamentos->total());

        $fornecedores = Fornecedor::orderBy('nome_fornecedor')->get();

        $pedidosCompra = PedidoCompra::with('fornecedor')
            ->orderBy('id_pedido_compras', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($pedido) {
                return [
                    'value' => $pedido->id_pedido_compras,
                    'label' => $pedido->fornecedor->nome_fornecedor ?? 'Sem fornecedor',
                ];
            })
            ->toArray();

        return view('admin.compras.orcamentos.index', compact(
            'orcamentos',
            'fornecedores',
            'pedidosCompra'
        ));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Orcamento::class);

        if (!$request->filled('pedido_id')) {
            return redirect()->route('admin.compras.pedidos.index')
                ->with('error', 'Selecione um pedido para criar um orçamento');
        }

        $pedidoCompra = PedidoCompra::with(['itens'])->findOrFail($request->pedido_id);
        $fornecedores = Fornecedor::where('ativo', true)->orderBy('nome_fornecedor')->get();

        return view('admin.compras.orcamentos.create', compact('pedidoCompra', 'fornecedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Orcamento::class);

        $validated = $request->validate([
            'id_pedido' => 'required|exists:pedidos_compra,id_pedido',
            'id_fornecedor' => 'required|exists:fornecedores,id_fornecedor',
            'data_orcamento' => 'required|date',
            'prazo_entrega' => 'required|integer|min:1',
            'validade' => 'required|date|after_or_equal:data_orcamento',
            'observacao' => 'nullable|string|max:500',
            'itens' => 'required|array|min:1',
            'itens.*.id_item_pedido' => 'required|exists:itens_pedido_compra,id_item_pedido',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
            'itens.*.valor_unitario' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $orcamento = Orcamento::create([
                'id_pedido' => $validated['id_pedido'],
                'id_fornecedor' => $validated['id_fornecedor'],
                'data_orcamento' => $validated['data_orcamento'],
                'prazo_entrega' => $validated['prazo_entrega'],
                'validade' => $validated['validade'],
                'observacao' => $validated['observacao'] ?? null,
                'selecionado' => false,
                'data_inclusao' => now(),
            ]);

            $valorTotal = 0;
            foreach ($validated['itens'] as $item) {
                $valorItem = $item['quantidade'] * $item['valor_unitario'];
                $valorTotal += $valorItem;

                ItemOrcamento::create([
                    'id_orcamento' => $orcamento->id_orcamento,
                    'id_item_pedido' => $item['id_item_pedido'],
                    'valor_unitario' => $item['valor_unitario'],
                    'quantidade' => $item['quantidade'],
                    'valor_total' => $valorItem,
                    'data_inclusao' => now(),
                ]);
            }

            $orcamento->valor_total = $valorTotal;
            $orcamento->save();

            DB::commit();
            return redirect()->route('admin.compras.orcamentos.show', $orcamento->id_orcamento)
                ->with('success', 'Orçamento cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar orçamento: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Erro ao cadastrar orçamento. Por favor, tente novamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Orcamento $orcamento)
    {
        $this->authorize('view', $orcamento);
        $orcamento->load(['itens.itemPedidoCompra', 'fornecedor', 'pedidoCompra']);

        return view('admin.compras.orcamentos.show', compact('orcamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orcamento $orcamento)
    {
        $this->authorize('update', $orcamento);

        if ($orcamento->selecionado) {
            return redirect()->route('admin.compras.orcamentos.show', $orcamento->id_orcamento)
                ->with('error', 'Não é possível editar um orçamento já selecionado.');
        }

        $orcamento->load(['itens.itemPedidoCompra', 'pedidoCompra.itens']);
        $fornecedores = Fornecedor::where('ativo', true)->orderBy('nome_fornecedor')->get();

        return view('admin.compras.orcamentos.edit', compact('orcamento', 'fornecedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Orcamento $orcamento)
    {
        $this->authorize('update', $orcamento);

        if ($orcamento->selecionado) {
            return redirect()->route('admin.compras.orcamentos.show', $orcamento->id_orcamento)
                ->with('error', 'Não é possível editar um orçamento já selecionado.');
        }

        $validated = $request->validate([
            'id_fornecedor' => 'required|exists:fornecedores,id_fornecedor',
            'data_orcamento' => 'required|date',
            'prazo_entrega' => 'required|integer|min:1',
            'validade' => 'required|date|after_or_equal:data_orcamento',
            'observacao' => 'nullable|string|max:500',
            'itens' => 'required|array|min:1',
            'itens.*.id_item_pedido' => 'required|exists:itens_pedido_compra,id_item_pedido',
            'itens.*.quantidade' => 'required|numeric|min:0.01',
            'itens.*.valor_unitario' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $orcamento->update([
                'id_fornecedor' => $validated['id_fornecedor'],
                'data_orcamento' => $validated['data_orcamento'],
                'prazo_entrega' => $validated['prazo_entrega'],
                'validade' => $validated['validade'],
                'observacao' => $validated['observacao'] ?? null,
                'data_alteracao' => now(),
            ]);

            // Remove os itens existentes
            $orcamento->itens()->delete();

            $valorTotal = 0;
            foreach ($validated['itens'] as $item) {
                $valorItem = $item['quantidade'] * $item['valor_unitario'];
                $valorTotal += $valorItem;

                ItemOrcamento::create([
                    'id_orcamento' => $orcamento->id_orcamento,
                    'id_item_pedido' => $item['id_item_pedido'],
                    'valor_unitario' => $item['valor_unitario'],
                    'quantidade' => $item['quantidade'],
                    'valor_total' => $valorItem,
                    'data_inclusao' => now(),
                ]);
            }

            $orcamento->valor_total = $valorTotal;
            $orcamento->save();

            DB::commit();
            return redirect()->route('admin.compras.orcamentos.show', $orcamento->id_orcamento)
                ->with('success', 'Orçamento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar orçamento: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Erro ao atualizar orçamento. Por favor, tente novamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Orcamento $orcamento)
    {
        $this->authorize('delete', $orcamento);

        if ($orcamento->selecionado) {
            return redirect()->route('admin.compras.orcamentos.index')
                ->with('error', 'Não é possível excluir um orçamento já selecionado.');
        }

        DB::beginTransaction();
        try {
            $orcamento->itens()->delete();
            $orcamento->delete();
            DB::commit();

            return redirect()->route('admin.compras.orcamentos.index')
                ->with('success', 'Orçamento excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir orçamento: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erro ao excluir orçamento. Por favor, tente novamente.');
        }
    }

    /**
     * Compare multiple budgets.
     */
    public function comparativo(Request $request)
    {
        $pedidoId = $request->input('pedido_id');

        // Se não há pedido_id, redireciona para a tela de orçamentos onde pode selecionar
        if (!$pedidoId || !is_numeric($pedidoId)) {
            return redirect()->route('admin.compras.orcamentos.index')
                ->with('info', 'Selecione um pedido de compra na seção "Comparativo de Orçamentos" para visualizar a análise comparativa.');
        }

        $pedidoId = (int) $pedidoId;

        $this->authorize('compareQuotations', [Orcamento::class, $pedidoId]);

        $pedido = PedidoCompra::with(['itens'])->findOrFail($pedidoId);
        $orcamentos = Orcamento::with(['itens.itemPedidoCompra', 'fornecedor'])
            ->where('id_pedido', $pedidoId)
            ->get();

        if ($orcamentos->isEmpty()) {
            return redirect()->route('admin.compras.pedidos.show', $pedidoId)
                ->with('warning', 'Não há orçamentos cadastrados para este pedido.');
        }

        // Montar o array de comparativo
        $comparativo = [];
        $fornecedores = [];

        foreach ($orcamentos as $orcamento) {
            $fornecedores[$orcamento->id_orcamento] = [
                'id' => $orcamento->id_orcamento,
                'fornecedor_id' => $orcamento->id_fornecedor,
                'nome' => $orcamento->fornecedor->nome_fornecedor,
                'prazo_entrega' => $orcamento->prazo_entrega,
                'validade' => $orcamento->validade->format('d/m/Y'),
                'valor_total' => $orcamento->valor_total,
                'data_orcamento' => $orcamento->data_orcamento->format('d/m/Y'),
                'selecionado' => $orcamento->selecionado,
            ];

            foreach ($orcamento->itens as $item) {
                $idItemPedido = $item->id_item_pedido;

                if (!isset($comparativo[$idItemPedido])) {
                    $comparativo[$idItemPedido] = [
                        'id' => $idItemPedido,
                        'descricao' => $item->itemPedidoCompra->descricao,
                        'quantidade' => $item->itemPedidoCompra->quantidade,
                        'unidade_medida' => $item->itemPedidoCompra->unidade_medida,
                        'fornecedores' => []
                    ];
                }

                $comparativo[$idItemPedido]['fornecedores'][$orcamento->id_orcamento] = [
                    'valor_unitario' => $item->valor_unitario,
                    'valor_total' => $item->valor_total,
                    'quantidade' => $item->quantidade
                ];
            }
        }

        return view('admin.compras.orcamentos.comparativo', compact('pedido', 'comparativo', 'fornecedores', 'orcamentos'));
    }

    /**
     * Select winning supplier.
     */
    public function selecionar(Orcamento $orcamento)
    {
        $this->authorize('select', $orcamento);

        if ($orcamento->selecionado) {
            return redirect()->route('admin.compras.orcamentos.show', $orcamento->id_orcamento)
                ->with('info', 'Este orçamento já está selecionado.');
        }

        DB::beginTransaction();
        try {
            // Desmarcar outros orçamentos do mesmo pedido
            Orcamento::where('id_pedido', $orcamento->id_pedido)
                ->where('id_orcamento', '!=', $orcamento->id_orcamento)
                ->update(['selecionado' => false]);

            // Marcar este orçamento como selecionado
            $orcamento->selecionado = true;
            $orcamento->save();

            // Atualizar o fornecedor do pedido
            $orcamento->pedidoCompra->id_fornecedor = $orcamento->id_fornecedor;
            $orcamento->pedidoCompra->save();

            DB::commit();
            return redirect()->route('admin.compras.orcamentos.show', $orcamento->id_orcamento)
                ->with('success', 'Orçamento selecionado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao selecionar orçamento: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erro ao selecionar orçamento. Por favor, tente novamente.');
        }
    }

    /**
     * Reject quotation.
     */
    public function rejeitar(Orcamento $orcamento, Request $request)
    {
        $this->authorize('reject', $orcamento);

        if ($orcamento->selecionado) {
            return redirect()->route('admin.compras.orcamentos.show', $orcamento->id_orcamento)
                ->with('error', 'Não é possível rejeitar um orçamento já selecionado.');
        }

        $validated = $request->validate([
            'motivo_rejeicao' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $orcamento->motivo_rejeicao = $validated['motivo_rejeicao'];
            $orcamento->rejeitado = true;
            $orcamento->data_rejeicao = now();
            $orcamento->id_usuario_rejeicao = auth()->id();
            $orcamento->save();

            DB::commit();
            return redirect()->route('admin.compras.orcamentos.index')
                ->with('success', 'Orçamento rejeitado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao rejeitar orçamento: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erro ao rejeitar orçamento. Por favor, tente novamente.');
        }
    }
}
