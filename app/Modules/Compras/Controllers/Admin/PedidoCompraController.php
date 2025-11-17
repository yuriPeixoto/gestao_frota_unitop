<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Models\ItemSolicitacaoCompra;
use App\Models\ItensPedidos;
use App\Models\PedidoCompra;
use App\Models\SituacaoPedido;
use App\Models\User;
use App\Models\VFilial;
use App\Traits\FilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PedidoCompraController extends Controller
{
    use FilterTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PedidoCompra $compra)
    {
        $query = PedidoCompra::with([
            'fornecedor',
            'comprador',
            'aprovador',
            'filial',
            'filialEntrega',
            'filialFaturamento',
            'situacaoPedido'
        ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('id_pedido_compras', 'like', "%{$search}%")
                    ->orWhereHas('fornecedor', function ($q) use ($search) {
                        $q->where('nome_fornecedor', 'like', "%{$search}%");
                    });
            });

        // Aplica os filtros usando o FilterTrait
        $this->filter($query, $request);

        $pedidos = $query->orderBy('data_inclusao', 'desc')->paginate(15);

        return view('admin.compras.pedidos.index', compact('pedidos'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PedidoCompra $pedido)
    {
        // $this->authorize('view', $pedido);

        $pedido->load([
            'solicitacaoCompra',
            'solicitacaoCompra.solicitante',
            'solicitacaoCompra.departamento',
            'fornecedor',
            'comprador',
            'aprovador',
            'filial',
            'filialEntrega',
            'filialFaturamento',
            'itens',
            'itens.produtos',
            'situacaoPedido',
            'orcamentos',
        ]);

        return view('admin.compras.pedidos.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PedidoCompra $pedido)
    {
        // $this->authorize('update', $pedido);

        $pedido->load([
            'solicitacaoCompra',
            'solicitacaoCompra.solicitante',
            'solicitacaoCompra.departamento',
            'fornecedor',
            'comprador',
            'filial',
            'filialEntrega',
            'filialFaturamento',
            'itens',
            'itens.itemSolicitacao',
        ]);

        $filiais = VFilial::orderBy('name')->get();
        $fornecedores = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->where('is_ativo', true)
            ->limit(30)
            ->orderBy('nome_fornecedor')
            ->get();

        $compradores = User::role('Comprador')->orderBy('name')->get();

        return view('admin.compras.pedidos.edit', compact('pedido', 'filiais', 'fornecedores', 'compradores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PedidoCompra $pedido)
    {
        // $this->authorize('update', $pedido);

        $validated = $request->validate([
            'id_fornecedor' => 'required|exists:fornecedores,id_fornecedor',
            'filial_entrega' => 'required|exists:filiais,id',
            'filial_faturamento' => 'required|exists:filiais,id',
            'observacao_pedido' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Atualiza o pedido
            $pedido->id_fornecedor = $validated['id_fornecedor'];
            $pedido->filial_entrega = $validated['filial_entrega'];
            $pedido->filial_faturamento = $validated['filial_faturamento'];
            $pedido->observacao_pedido = $validated['observacao_pedido'];
            $pedido->data_alteracao = now();

            $pedido->save();

            DB::commit();

            return redirect()->route('admin.compras.pedidos.show', $pedido->id_pedido_compras)
                ->with('success', 'Pedido de compra atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar pedido de compra: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PedidoCompra $pedido) {}

    /**
     * Aprovar pedido de compra
     */
    public function aprovar(Request $request, PedidoCompra $pedido)
    {
        // dd('Aprovar');
        // $this->authorize('approve', $pedido);

        $validated = $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $pedido->is_liberado = true;
            $pedido->id_aprovador_pedido = Auth::id();
            $pedido->data_alteracao = now();
            $pedido->justificativa = $validated['justificativa'] ?? null;
            $pedido->save();

            DB::commit();

            return redirect()->route('admin.compras.pedidos.show', $pedido->id_pedido_compras)
                ->with('success', 'Pedido de compra aprovado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar pedido de compra: ' . $e->getMessage());
            return back()->with('error', 'Erro ao aprovar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Rejeitar pedido de compra
     */
    public function rejeitar(Request $request, PedidoCompra $pedido)
    {
        // dd('Rejeitar');

        // $this->authorize('reject', $pedido);

        $validated = $request->validate([
            'motivo_rejeicao' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $rejeitado = 'REJEITADO';
            $situacaoPedido = SituacaoPedido::where('descricao_situacao_pedido', $rejeitado)->first(); // Rejeitado
            $situacaoPedidoId = $situacaoPedido ? $situacaoPedido->id_situacao_pedido : null;

            // Atualiza situação para rejeitado
            $pedido->situacao_pedido = $situacaoPedidoId;
            $pedido->justificativa = $validated['motivo_rejeicao'];
            $pedido->data_alteracao = now();
            $pedido->save();

            DB::commit();

            return redirect()->route('admin.compras.pedidos.show', $pedido->id_pedido_compras)
                ->with('success', 'Pedido de compra rejeitado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao rejeitar pedido de compra: ' . $e->getMessage());
            return back()->with('error', 'Erro ao rejeitar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Enviar pedido ao fornecedor
     */
    public function enviar(Request $request, PedidoCompra $pedido)
    {
        // dd('Enviar');
        // $this->authorize('send', $pedido);

        DB::beginTransaction();

        try {
            $enviado = 'ENVIADO';
            $situacaoPedido = SituacaoPedido::where('descricao_situacao_pedido', $enviado)->first(); // Enviado
            $situacaoPedidoId = $situacaoPedido ? $situacaoPedido->id_situacao_pedido : null;

            // Atualiza situação para enviado
            $pedido->situacao_pedido = $situacaoPedidoId;
            $pedido->data_alteracao = now();
            $pedido->save();

            DB::commit();

            return redirect()->route('admin.compras.pedidos.show', $pedido->id_pedido_compras)
                ->with('success', 'Pedido de compra enviado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao enviar pedido de compra: ' . $e->getMessage());
            return back()->with('error', 'Erro ao enviar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar pedido de compra
     */
    public function cancelar(Request $request, PedidoCompra $pedido)
    {
        // dd('Cancelar');
        // $this->authorize('cancel', $pedido);

        $validated = $request->validate([
            'motivo_cancelamento' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $cancelado = 'CANCELADO';
            $situacaoPedido = SituacaoPedido::where('descricao_situacao_pedido', $cancelado)->first(); // Cancelado
            $situacaoPedidoId = $situacaoPedido ? $situacaoPedido->id_situacao_pedido : null;

            // Atualiza situação para cancelado
            $pedido->situacao_pedido = $situacaoPedidoId;
            $pedido->is_liberado = false;
            $pedido->justificativa = $validated['motivo_cancelamento'];
            $pedido->data_alteracao = now();
            $pedido->save();

            DB::commit();

            return redirect()->route('admin.compras.pedidos.show', $pedido->id_pedido_compras)
                ->with('success', 'Pedido de compra cancelado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar pedido de compra: ' . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Finalizar pedido (faturado)
     */
    public function finalizar(Request $request, PedidoCompra $pedido)
    {
        // dd('Finalizar');
        // $this->authorize('update', $pedido);

        DB::beginTransaction();

        try {
            $pedido->pedido_faturado = true;
            $pedido->data_alteracao = now();
            $pedido->save();

            DB::commit();

            return redirect()->route('admin.compras.pedidos.show', $pedido->id_pedido_compras)
                ->with('success', 'Pedido de compra finalizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao finalizar pedido de compra: ' . $e->getMessage());
            return back()->with('error', 'Erro ao finalizar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Listar pedidos aprovados para impressão
     */
    public function listarAprovados(Request $request)
    {

        // $this->authorize('viewAny', PedidoCompra::class);

        $query = PedidoCompra::with([
            'solicitacaoCompra',
            'fornecedor',
            'comprador',
            'aprovador',
        ])->aprovados();

        // Filtros
        // if ($request->filled('numero')) {
        //     $query->where('id_pedido_compras', intval(preg_replace('/[^0-9]/', '', $request->numero)));
        // }

        if ($request->filled('fornecedor')) {
            $query->whereHas('fornecedor', function ($q) use ($request) {
                $q->where('nome_fornecedor', 'ilike', '%' . $request->fornecedor . '%');
            });
        }

        if ($request->filled('data_inicial') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial . ' 00:00:00',
                $request->data_final . ' 23:59:59'
            ]);
        }

        $pedidos = $query->orderBy('id_pedido_compras', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.compras.pedidos.aprovados', compact('pedidos'));
    }

    /**
     * Listar pedidos cancelados
     */
    public function listarCancelados(Request $request)
    {

        // $this->authorize('viewAny', PedidoCompra::class);

        $query = PedidoCompra::with([
            'solicitacaoCompra',
            'fornecedor',
            'comprador',
            'aprovador',
        ])->where('situacao_pedido', 4); // Cancelado

        // Filtros
        // if ($request->filled('numero')) {
        //     $query->where('id_pedido_compras', intval(preg_replace('/[^0-9]/', '', $request->numero)));
        // }

        if ($request->filled('fornecedor')) {
            $query->whereHas('fornecedor', function ($q) use ($request) {
                $q->where('nome_fornecedor', 'ilike', '%' . $request->fornecedor . '%');
            });
        }

        if ($request->filled('data_inicial') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial . ' 00:00:00',
                $request->data_final . ' 23:59:59'
            ]);
        }

        $pedidos = $query->orderBy('id_pedido_compras', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('compras.pedidos.cancelados', compact('pedidos'));
    }

    /**
     * Listar pedidos faturados
     */
    public function listarFaturados(Request $request)
    {

        // $this->authorize('viewAny', PedidoCompra::class);

        $query = PedidoCompra::with([
            'solicitacaoCompra',
            'fornecedor',
            'comprador',
            'aprovador',
        ])->finalizados();

        // Filtros
        // if ($request->filled('numero')) {
        //     $query->where('id_pedido_compras', intval(preg_replace('/[^0-9]/', '', $request->numero)));
        // }

        if ($request->filled('fornecedor')) {
            $query->whereHas('fornecedor', function ($q) use ($request) {
                $q->where('nome_fornecedor', 'ilike', '%' . $request->fornecedor . '%');
            });
        }

        if ($request->filled('data_inicial') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial . ' 00:00:00',
                $request->data_final . ' 23:59:59'
            ]);
        }

        $pedidos = $query->orderBy('id_pedido_compras', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('compras.pedidos.faturados', compact('pedidos'));
    }

    /**
     * Listar pedidos pendentes de aprovação
     */
    public function listarPendentesAprovacao(Request $request)
    {
        $user = Auth::user();

        if (!$this->usuarioPodeAprovar($user)) {
            return redirect()->route('admin.compras.pedidos.index')
                ->with('error', 'Você não tem permissão para aprovar pedidos.');
        }

        $query = PedidoCompra::with(['solicitacaoCompra', 'fornecedor', 'comprador'])
            ->pendentesAprovacao()
            ->when($request->filled('fornecedor'), function ($query) use ($request) {
                $query->whereHas('fornecedor', function ($q) use ($request) {
                    $q->where('nome_fornecedor', 'ilike', '%' . $request->fornecedor . '%');
                });
            })
            ->when($request->filled('data_inicial') && $request->filled('data_final'), function ($query) use ($request) {
                $query->whereBetween('data_inclusao', [
                    $request->data_inicial . ' 00:00:00',
                    $request->data_final . ' 23:59:59'
                ]);
            })
            ->where(fn($q) => $this->filtrarPorAlcada($q, $user));

        $pedidos = $query->orderByDesc('id_pedido_compras')
            ->paginate(15)
            ->withQueryString();

        return view('admin.compras.pedidos.pendentes-aprovacao', compact('pedidos'));
    }

    private function usuarioPodeAprovar($user): bool
    {
        return $user->can('aprovar_pedido_compra_nivel_1')
            || $user->can('aprovar_pedido_compra_nivel_2')
            || $user->can('aprovar_pedido_compra_nivel_3')
            || $user->can('aprovar_pedido_compra_nivel_4');
    }

    private function filtrarPorAlcada($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            if ($user->can('aprovar_pedido_compra_nivel_1')) {
                $q->orWhere('valor_total', '<=', 5000);
            }

            if ($user->can('aprovar_pedido_compra_nivel_2')) {
                $q->orWhereBetween('valor_total', [5000.01, 25000]);
            }

            if ($user->can('aprovar_pedido_compra_nivel_3')) {
                $q->orWhereBetween('valor_total', [25000.01, 100000]);
            }

            if ($user->can('aprovar_pedido_compra_nivel_4')) {
                $q->orWhere('valor_total', '>', 100000);
            }

            if ($user->hasRole('Gestor de Frota')) {
                $q->orWhereHas('solicitacaoCompra.departamento', function ($dq) {
                    $dq->where('descricao_departamento', 'ilike', '%frota%');
                });
            }

            if ($user->hasRole('Administrador do Módulo Compras')) {
                $q->orWhereRaw('1=1');
            }
        });
    }


    /**
     * Aprovar múltiplos pedidos em lote
     */
    public function aprovarLote(Request $request)
    {
        // dd('Aprovar Lote');
        $validated = $request->validate([
            'pedidos' => 'required|array|min:1',
            'pedidos.*' => 'required|exists:pedido_compras,id_pedido_compras',
            'justificativa' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $aprovados = 0;
            $naoPodeAprovar = [];

            foreach ($validated['pedidos'] as $idPedido) {
                $pedido = PedidoCompra::findOrFail($idPedido);

                if (Auth::user()->can('approve', $pedido)) {
                    $pedido->is_liberado = true;
                    $pedido->id_aprovador_pedido = Auth::id();
                    $pedido->data_alteracao = now();
                    $pedido->justificativa = $validated['justificativa'] ?? null;
                    $pedido->save();
                    $aprovados++;
                } else {
                    $naoPodeAprovar[] = $pedido->numero;
                }
            }

            DB::commit();

            if (count($naoPodeAprovar) > 0) {
                $mensagem = "Foram aprovados $aprovados pedidos. Os seguintes pedidos não puderam ser aprovados: " . implode(', ', $naoPodeAprovar);
                return redirect()->route('admin.compras.pedidos.pendentes-aprovacao')
                    ->with('warning', $mensagem);
            }

            return redirect()->route('admin.compras.pedidos.pendentes-aprovacao')
                ->with('success', "$aprovados pedidos foram aprovados com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar pedidos em lote: ' . $e->getMessage());
            return back()->with('error', 'Erro ao aprovar pedidos em lote: ' . $e->getMessage());
        }
    }

    /**
     * Finalizar múltiplos pedidos em lote
     */
    public function finalizarLote(Request $request)
    {
        // dd('Finalizar Lote');
        $validated = $request->validate([
            'pedidos' => 'required|array|min:1',
            'pedidos.*' => 'required|exists:pedido_compras,id_pedido_compras',
        ]);

        DB::beginTransaction();

        try {
            $finalizados = 0;
            $naoPodeFinalizar = [];

            foreach ($validated['pedidos'] as $idPedido) {
                $pedido = PedidoCompra::findOrFail($idPedido);

                if ($pedido->is_liberado && !$pedido->pedido_faturado && Auth::user()->can('update', $pedido)) {
                    $pedido->pedido_faturado = true;
                    $pedido->data_alteracao = now();
                    $pedido->save();
                    $finalizados++;
                } else {
                    $naoPodeFinalizar[] = $pedido->numero;
                }
            }

            DB::commit();

            if (count($naoPodeFinalizar) > 0) {
                $mensagem = "Foram finalizados $finalizados pedidos. Os seguintes pedidos não puderam ser finalizados: " . implode(', ', $naoPodeFinalizar);
                return redirect()->back()->with('warning', $mensagem);
            }

            return redirect()->back()->with('success', "$finalizados pedidos foram finalizados com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao finalizar pedidos em lote: ' . $e->getMessage());
            return back()->with('error', 'Erro ao finalizar pedidos em lote: ' . $e->getMessage());
        }
    }

    /**
     * Imprimir pedido de compra
     */
    public function imprimir(PedidoCompra $pedido)
    {
        // dd('Imprimir');
        // $this->authorize('view', $pedido);

        $pedido->load([
            'solicitacaoCompra',
            'solicitacaoCompra.solicitante',
            'solicitacaoCompra.departamento',
            'fornecedor',
            'comprador',
            'aprovador',
            'filial',
            'filialEntrega',
            'filialFaturamento',
            'itens',
            'itens.itemSolicitacao',
            'situacaoPedido',
        ]);

        return view('compras.pedidos.imprimir', compact('pedido'));
    }

    /**
     * Exportar para CSV
     */
    public function exportCsv(Request $request)
    {
        // $this->authorize('viewAny', PedidoCompra::class);

        // Implementar a lógica de exportação para CSV
        // ...

        return response()->download(storage_path('app/pedidos_compra.csv'));
    }

    /**
     * Exportar para XLS
     */
    public function exportXls(Request $request)
    {
        // $this->authorize('viewAny', PedidoCompra::class);

        // Implementar a lógica de exportação para XLS
        // ...

        return response()->download(storage_path('app/pedidos_compra.xlsx'));
    }

    /**
     * Exportar para PDF
     */
    public function exportPdf(Request $request)
    {
        // $this->authorize('viewAny', PedidoCompra::class);

        // Implementar a lógica de exportação para PDF
        // ...

        return response()->download(storage_path('app/pedidos_compra.pdf'));
    }

    /**
     * Listar itens de um pedido
     */
    public function listarItens(PedidoCompra $pedido)
    {

        // $this->authorize('view', $pedido);

        $itens = $pedido->itens()->with(['itemSolicitacao'])->get();

        return response()->json($itens);
    }

    /**
     * Adicionar item a um pedido
     */
    public function adicionarItem(Request $request, PedidoCompra $pedido)
    {
        // dd('Adicionar Item');
        // $this->authorize('update', $pedido);

        $validated = $request->validate([
            'id_item_solicitacao' => 'required|exists:itens_solicitacao_compra,id_item_solicitacao',
            'quantidade' => 'required|numeric|min:0.01',
            'valor_unitario' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:255',
            'unidade_medida' => 'required|string|max:10',
        ]);

        try {
            $itemSolicitacao = ItemSolicitacaoCompra::findOrFail($validated['id_item_solicitacao']);

            $item = new ItensPedidos();
            $item->id_pedido = $pedido->id_pedido_compras;
            $item->id_item_solicitacao = $validated['id_item_solicitacao'];
            $item->tipo = $itemSolicitacao->tipo;
            $item->descricao = $validated['descricao'];
            $item->quantidade = $validated['quantidade'];
            $item->valor_unitario = $validated['valor_unitario'];
            $item->valor_total = $validated['quantidade'] * $validated['valor_unitario'];
            $item->unidade_medida = $validated['unidade_medida'];
            $item->status = 'pendente';
            $item->save();

            // Atualiza o valor total do pedido
            $valorTotal = $pedido->itens()->sum('valor_total');
            $pedido->valor_total = $valorTotal;
            $pedido->save();

            return response()->json(['success' => true, 'item' => $item]);
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar item ao pedido: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao adicionar item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Atualizar item de um pedido
     */
    public function atualizarItem(Request $request, PedidoCompra $pedido, ItensPedidos $item)
    {
        // dd('Atualizar Item');
        // $this->authorize('update', $pedido);

        // Verifica se o item pertence ao pedido
        if ($item->id_pedido != $pedido->id_pedido_compras) {
            return response()->json(['success' => false, 'message' => 'Item não pertence ao pedido informado'], 403);
        }

        $validated = $request->validate([
            'quantidade' => 'required|numeric|min:0.01',
            'valor_unitario' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:255',
            'unidade_medida' => 'required|string|max:10',
        ]);

        try {
            $item->descricao = $validated['descricao'];
            $item->quantidade = $validated['quantidade'];
            $item->valor_unitario = $validated['valor_unitario'];
            $item->valor_total = $validated['quantidade'] * $validated['valor_unitario'];
            $item->unidade_medida = $validated['unidade_medida'];
            $item->save();

            // Atualiza o valor total do pedido
            $valorTotal = $pedido->itens()->sum('valor_total');
            $pedido->valor_total = $valorTotal;
            $pedido->save();

            return response()->json(['success' => true, 'item' => $item]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar item do pedido: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remover item de um pedido
     */
    public function removerItem(PedidoCompra $pedido, ItensPedidos $item)
    {
        // dd('Remover Item');
        // $this->authorize('update', $pedido);

        // Verifica se o item pertence ao pedido
        if ($item->id_pedido != $pedido->id_pedido_compras) {
            return response()->json(['success' => false, 'message' => 'Item não pertence ao pedido informado'], 403);
        }

        try {
            $item->delete();

            // Atualiza o valor total do pedido
            $valorTotal = $pedido->itens()->sum('valor_total');
            $pedido->valor_total = $valorTotal;
            $pedido->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erro ao remover item do pedido: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao remover item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Busca de pedidos para autocomplete
     */
    public function search(Request $request)
    {
        // $this->authorize('viewAny', PedidoCompra::class);

        $query = PedidoCompra::with('fornecedor');

        if ($request->filled('term')) {
            $term = $request->term;
            $query->where(function ($q) use ($term) {
                $q->where('id_pedido_compras', 'ilike', '%' . $term . '%')
                    ->orWhereHas('fornecedor', function ($fq) use ($term) {
                        $fq->where('nome_fornecedor', 'ilike', '%' . $term . '%');
                    });
            });
        }

        $pedidos = $query->limit(10)->get()->map(function ($pedido) {
            return [
                'id' => $pedido->id_pedido_compras,
                'text' => 'Pedido #' . $pedido->numero . ' - ' .
                    ($pedido->fornecedor ? $pedido->fornecedor->nome_fornecedor : 'Sem fornecedor') .
                    ' (' . $pedido->status . ')'
            ];
        });

        return response()->json(['results' => $pedidos]);
    }

    /**
     * Busca de pedido por ID
     */
    public function getById($id)
    {
        $pedido = PedidoCompra::with([
            'fornecedor',
            'comprador',
            'solicitacaoCompra',
            'filial',
            'filialEntrega',
            'filialFaturamento',
        ])->findOrFail($id);

        $this->authorize('view', $pedido);

        return response()->json($pedido);
    }

    /**
     * Obter itens de um pedido
     */
    public function getItens($id)
    {
        $pedido = PedidoCompra::findOrFail($id);
        // $this->authorize('view', $pedido);

        $itens = $pedido->itens()->with(['itemSolicitacao'])->get();

        return response()->json($itens);
    }
}
