<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AprovarPedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AprovarPedidoController extends Controller
{
    protected $aprovarPedidoService;

    public function __construct(AprovarPedidoService $aprovarPedidoService)
    {
        $this->aprovarPedidoService = $aprovarPedidoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $solicitacoes = $this->aprovarPedidoService->buscarSolicitacoesPendentes($request);
            $filterData = $this->aprovarPedidoService->getFilterData();

            return view('admin.compras.aprovarpedido.index', array_merge(
                compact('solicitacoes'),
                $filterData,
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar listagem de validação de cotações:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao carregar a aprovação de pedido.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $solicitacao = $this->aprovarPedidoService->buscarSolicitacaoCompleta($id);
        $dadosCotacoes = $this->aprovarPedidoService->buscarCotacoesCompletas($id);

        return view('admin.compras.aprovarpedido.show', array_merge(
            compact('solicitacao'),
            $dadosCotacoes
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $dadosEdicao = $this->aprovarPedidoService->buscarDadosEdicao($id);
            $filterData = $this->aprovarPedidoService->getFilterData();

            return view('admin.compras.aprovarpedido.edit', array_merge(
                $dadosEdicao,
                [
                    'action' => route('admin.compras.aprovarpedido.update', $id),
                    'method' => 'PUT'
                ],
                $filterData
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar cotação para edição:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Cotação não encontrada.');
        }
    }

    public function onCancelar(Request $request)
    {
        try {
            $idsolicitacoescompras = $request->input('id_solicitacao_compras');
            $resultado = $this->aprovarPedidoService->cancelarSolicitacao($idsolicitacoescompras);

            if ($resultado['success']) {
                return redirect()
                    ->route('admin.compras.aprovarpedido.index')
                    ->with('success', $resultado['message']);
            }

            return back()->with('error', $resultado['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao recusar cotação: ' . $e->getMessage());
        }
    }

    public function getCotacoes($id)
    {
        try {
            $cotacoes = $this->aprovarPedidoService->getCotacoes($id);
            return response()->json($cotacoes);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar cotações:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erro ao buscar cotações'], 500);
        }
    }

    /**
     * Buscar todas as cotações com seus itens detalhados para o modal
     */
    public function getCotacoesCompletas($id)
    {
        try {
            $resultado = $this->aprovarPedidoService->getCotacoesCompletas($id);
            return response()->json($resultado);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar cotações completas:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Erro ao buscar cotações'], 500);
        }
    }

    public function aprovarCotacao(Request $request)
    {
        Log::info('Processando aprovação de cotação:', [
            'data' => $request->all()
        ]);

        try {
            $resultado = $this->aprovarPedidoService->aprovarCotacao($request);

            Log::info('Resultado da aprovação:', [
                'success' => $resultado['success'],
                'message' => $resultado['message']
            ]);

            if ($request->wantsJson() || $request->isJson()) {
                if ($resultado['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => $resultado['message'],
                        'redirect' => route('admin.compras.aprovarpedido.index')
                    ], 200);
                }

                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 422);
            }

            if ($resultado['success']) {
                return redirect()->route('admin.compras.aprovarpedido.index')->with('success', $resultado['message']);
            }

            return back()->with('error', $resultado['message']);
        } catch (\Exception $e) {
            Log::error('Erro ao processar aprovação de cotação:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro interno do servidor: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Erro ao processar aprovação de cotação.');
        }
    }

    public function gerarCotacao(Request $request)
    {
        try {
            $resultado = $this->aprovarPedidoService->gerarCotacao($request);

            return response()->json($resultado, $resultado['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar cotação no controller:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar cotação: ' . $e->getMessage()
            ], 500);
        }
    }
}
