<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Models\NotaFiscalAvulsa;
use App\Models\PedidoCompra;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotaFiscalAvulsaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obter parâmetros de filtragem
        $filtros = $request->only([
            'data_inclusao',
            'data_emissao',
            'id_fornecedor',
            'numero_nf',
            'chave_nf',
            'numero_do_pedido'
        ]);

        // Iniciar query com as relações necessárias
        $query = NotaFiscalAvulsa::with(['fornecedor', 'pedidoCompra'])
            ->orderBy('data_inclusao', 'desc');

        // Aplicar filtros conforme fornecidos na requisição
        if (!empty($filtros['data_inclusao'])) {
            // Converter para início do dia (00:00:00)
            $dataInicio = \Carbon\Carbon::parse($filtros['data_inclusao'])->startOfDay();
            $dataFim = \Carbon\Carbon::parse($filtros['data_inclusao'])->endOfDay();
            $query->whereBetween('data_inclusao', [$dataInicio, $dataFim]);
        }

        if (!empty($filtros['data_emissao'])) {
            $query->whereDate('data_emissao', $filtros['data_emissao']);
        }

        if (!empty($filtros['id_fornecedor'])) {
            $query->doFornecedor($filtros['id_fornecedor']);
        }

        if (!empty($filtros['numero_nf'])) {
            $query->porNumeroNf($filtros['numero_nf']);
        }

        if (!empty($filtros['chave_nf'])) {
            $query->porChaveNf($filtros['chave_nf']);
        }

        if (!empty($filtros['numero_do_pedido'])) {
            $query->doPedido($filtros['numero_do_pedido']);
        }

        // Executar query paginada
        $notasFiscais = $query->paginate(15)->withQueryString();

        // Obter fornecedores para o filtro
        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        // Fornecedores frequentes (top 10 mais usados)
        $fornecedoresFrequentes = NotaFiscalAvulsa::select('id_fornecedor', DB::raw('count(*) as total'))
            ->groupBy('id_fornecedor')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $fornecedor = Fornecedor::find($item->id_fornecedor);
                return [
                    'value' => $fornecedor->id_fornecedor,
                    'label' => $fornecedor->nome_fornecedor
                ];
            });

        return view('admin.compras.notasfiscais.index', compact(
            'notasFiscais',
            'fornecedores',
            'fornecedoresFrequentes',
            'filtros'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fornecedores = Fornecedor::where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        return view('admin.compras.notasfiscais.create', compact('fornecedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $request->merge([
            'numero_nf' => (int) $request->numero_nf,
            'valor_total_nf' => (float) str_replace(['.', ','], ['', '.'], $request->valor_total_nf),
            'valor_pecas' => $request->valor_pecas ? (float) str_replace(['.', ','], ['', '.'], $request->valor_pecas) : null
        ]);

        // Validar dados
        $validator = Validator::make($request->all(), [
            'numero_do_pedido' => 'required|exists:pedido_compras,id_pedido_compras',
            'chave_nf'         => ['nullable', 'string', 'digits:44', 'regex:/^[0-9]+$/'],
            'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
            'numero_nf' => 'required|numeric',
            'serie_nf' => 'nullable|integer',
            'data_emissao' => 'required|date',
            'valor_total_nf' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $pedido = PedidoCompra::find($request->numero_do_pedido);

        if (!$pedido) {
            return redirect()->back()
                ->with('error', 'Pedido não encontrado.')
                ->withInput();
        }

        if ($pedido->tipo_pedido == 2) {
            return redirect()->back()
                ->with('error', 'Não é possivel lançar nota fiscal para o pedido de Ordem de Serviço!')
                ->withInput();
        }

        try {
            // Verificar se o pedido já tem nota fiscal
            $pedidoComNota = NotaFiscalAvulsa::where('numero_do_pedido', $request->numero_do_pedido)->exists();
            if ($pedidoComNota) {
                return redirect()->back()
                    ->with('error', 'Este pedido já possui uma nota fiscal avulsa vinculada.')
                    ->withInput();
            }

            // Verificar se a nota fiscal já existe
            $notaExistente = NotaFiscalAvulsa::where('numero_nf', $request->numero_nf)
                ->where('id_fornecedor', $request->id_fornecedor)
                ->exists();

            if ($notaExistente) {
                return redirect()->back()
                    ->with('error', 'Já existe uma nota fiscal com este número para este fornecedor.')
                    ->withInput();
            }

            // Criar a nota fiscal
            $notaFiscal = new NotaFiscalAvulsa();
            $notaFiscal->fill($request->all());
            $notaFiscal->id_user_lancamento = Auth::id();
            $notaFiscal->data_inclusao = now();

            // Garantir que valores numéricos sejam tratados corretamente

            $notaFiscal->save();

            /* Atualizar o pedido de compra, se necessário
            $pedido = PedidoCompra::find($request->numero_do_pedido);
            if ($pedido) {
                // Lógica para atualizar o pedido, se necessário
                // Por exemplo: $pedido->status = 'com_nota_fiscal';
                // $pedido->save();
            }


            $id_nf_compra_servico = DB::table('nf_compra_servico')->insertGetId([
                'numero_nf'         =>  $request->numero_nf,
                'serie_nf'          =>  $request->serie_nf,
                'id_fornecedor'     =>  $request->id_fornecedor,
                'data_emissao'      =>  $request->data_emissao,
                'valor_servico'     =>  $request->valor_servico,
                'valor_total_nota'  =>  $request->valor_total_nota
            ]);

            DB::table('pedidos_ordem_aux')->insert([
                'id_pedido_compras'    =>  $request->numero_do_pedido,
                'id_nf_compra_servico' =>  $id_nf_compra_servico
            ]);
             */
            return redirect()->route('admin.notafiscalavulsa.create')
                ->with('success', 'Nota fiscal avulsa cadastrada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar nota fiscal avulsa: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erro ao cadastrar nota fiscal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notaFiscal = NotaFiscalAvulsa::with(['fornecedor', 'pedidoCompra', 'usuario'])->findOrFail($id);
        return view('admin.notafiscalavulsa.show', compact('notaFiscal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $notaFiscal = NotaFiscalAvulsa::findOrFail($id);

        $fornecedores = Fornecedor::where('ativo', true)
            ->orderBy('nome_fornecedor')
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        return view('admin.notafiscalavulsa.edit', compact('notaFiscal', 'fornecedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar dados
        $validator = Validator::make($request->all(), [
            'chave_nf' => [
                'nullable',
                'string',
                'digits:44',
                'regex:/^[0-9]+$/'
            ],
            'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
            'numero_nf' => 'required|integer',
            'serie_nf' => 'nullable|integer',
            'data_emissao' => 'required|date',
            'valor_total_nf' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $notaFiscal = NotaFiscalAvulsa::findOrFail($id);

            // Verificar se houve mudança no número da nota e fornecedor e se já existe essa combinação
            if (($notaFiscal->numero_nf != $request->numero_nf ||
                    $notaFiscal->id_fornecedor != $request->id_fornecedor) &&
                NotaFiscalAvulsa::where('numero_nf', $request->numero_nf)
                ->where('id_fornecedor', $request->id_fornecedor)
                ->where('id_nf_avulsa', '!=', $id)
                ->exists()
            ) {
                return redirect()->back()
                    ->with('error', 'Já existe uma nota fiscal com este número para este fornecedor.')
                    ->withInput();
            }

            // Atualizar a nota fiscal
            $notaFiscal->fill($request->all());
            $notaFiscal->data_alteracao = now();

            // Garantir que valores numéricos sejam tratados corretamente
            if (isset($request->valor_pecas)) {
                $notaFiscal->valor_pecas = str_replace(',', '.', $request->valor_pecas);
            }

            $notaFiscal->valor_total_nf = str_replace(',', '.', $request->valor_total_nf);

            $notaFiscal->save();


            DB::table('nf_compra_servico')
                ->where('numero_nf', $request->numero_nf)
                ->where('id_fornecedor', $request->id_fornecedor)
                ->update([
                    'serie_nf'        => $request->serie_nf,
                    'data_emissao'    => $request->data_emissao,
                    'valor_servico'   => $request->valor_servico ?? 0,
                    'valor_total_nota' => $request->valor_total_nf
                ]);

            return redirect()->route('admin.notafiscalavulsa.index')
                ->with('success', 'Nota fiscal avulsa atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar nota fiscal avulsa: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erro ao atualizar nota fiscal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $notaFiscal = NotaFiscalAvulsa::findOrFail($id);
            $notaFiscal->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir nota fiscal avulsa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir nota fiscal: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Busca informações do pedido de compra
     */
    public function buscarPedido(Request $request)
    {
        $numeroPedido = $request->numero_pedido;

        if (!$numeroPedido) {
            return response()->json(['error' => 'Número do pedido não informado'], 400);
        }

        try {
            $pedido = PedidoCompra::with('fornecedor')
                ->where('id_pedido_compras', $numeroPedido)
                ->first();

            if (!$pedido) {
                return response()->json(['error' => 'Pedido não encontrado'], 404);
            }

            if ($pedido->tipo_pedido == 1) {
                return response()->json([
                    'error' => 'Não é possível lançar nota fiscal para pedidos do tipo Ordem de Serviço.'
                ], 403);
            }

            // Busca a nota fiscal se existir
            $notaFiscal = NotaFiscalAvulsa::with('fornecedor')
                ->where('numero_do_pedido', $numeroPedido)
                ->first();

            return response()->json([
                'pedido' => $pedido,
                'fornecedor'            => $pedido->fornecedor ? [
                    'id_fornecedor'     => $pedido->fornecedor->id_fornecedor,
                    'nome_fornecedor'   => $pedido->fornecedor->nome_fornecedor
                ] : null,
                'nota_fiscal'           => $notaFiscal ? [
                    'numero_nf'         => $notaFiscal->numero_nf,
                    'serie_nf'          => $notaFiscal->serie_nf,
                    'chave_nf'          => $notaFiscal->chave_nf,
                    'data_emissao'      => $notaFiscal->data_emissao ?
                        Carbon::parse($notaFiscal->data_emissao)->format('Y-m-d') : null,
                    'valor_total_nf'    => $notaFiscal->valor_total_nf,
                    'valor_pecas'       => $notaFiscal->valor_pecas,
                    'fornecedor'        => $notaFiscal->fornecedor ? [
                        'id_fornecedor' => $notaFiscal->id_fornecedor,
                        'nome_fornecedor'   => $notaFiscal->nome_fornecedor
                    ] : null,

                ] : null
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar pedido: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar pedido: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exporta dados para PDF
     */
    public function exportPdf(Request $request)
    {
        // Obter filtros da requisição
        $filtros = $request->only([
            'data_inclusao',
            'data_emissao',
            'id_fornecedor',
            'numero_nf',
            'chave_nf',
            'numero_do_pedido'
        ]);

        // Construir a consulta com filtros
        $query = NotaFiscalAvulsa::with(['fornecedor', 'pedidoCompra']);

        // Aplicar filtros (mesmo código do método index)
        if (!empty($filtros['data_inclusao'])) {
            $dataInicio = \Carbon\Carbon::parse($filtros['data_inclusao'])->startOfDay();
            $dataFim = \Carbon\Carbon::parse($filtros['data_inclusao'])->endOfDay();
            $query->whereBetween('data_inclusao', [$dataInicio, $dataFim]);
        }

        // Aplicar demais filtros
        // ... (código semelhante ao método index)

        // Obter dados para o relatório
        $notasFiscais = $query->get();

        // Gerar o PDF usando uma view específica
        $pdf = Pdf::loadView('admin.notafiscalavulsa.pdf', ['data' => $notasFiscais]);

        // Configurar o PDF
        $pdf->setPaper('a4', 'landscape');

        // Download do PDF
        return $pdf->download('notas_fiscais_avulsas_' . date('YmdHis') . '.pdf');
    }
}
