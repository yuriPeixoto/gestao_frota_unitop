<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Modules\Estoque\Models\HistoricoMovimentacaoEstoque;
use App\Models\RelacaoSolicitacaoPeca;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoqueAux;
use App\Models\TransferenciaPneuItens;

ini_set('memory_limit', '256M');

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Estoque\Models\TransferenciaEstoque;
use App\Modules\Estoque\Models\TransferenciaEstoqueItens;
use App\Models\VFilial;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoque;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoqueItens;
use App\Modules\Estoque\Models\TransferenciaEstoqueAuxEnvio;
use App\Traits\ExportableTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Exception;
use Illuminate\Support\Facades\Auth;

class TransferenciaEntreEstoqueController extends Controller
{
    use ExportableTrait;

    protected $transferencia;
    protected $filial;
    protected $produto;

    public function __construct(TransferenciaEstoque $transferencia, Produto $produto, TransferenciaEstoqueItens $transferenciaItem)
    {
        $this->transferencia = $transferencia;
        $this->produto = $produto;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TransferenciaEstoque::query()
            ->with(['usuario', 'filial', 'filialBaixa', 'departamento', 'relacaoSolicitacaoPecas'])
            ->where('id_filial', GetterFilial())
            ->orderBy('id_tranferencia', 'desc');

        if ($request->has('search')) {
            $query->whereRaw('LOWER(departamento.descricao_departamento) LIKE LOWER(?)', ['%' . $request->search . '%']);
        }

        if ($request->filled('id_tranferencia')) {
            $query->where('id_tranferencia', $request->id_tranferencia);
        }

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        $transferencias = $query->paginate();

        return view('admin.transferenciaEntreEstoque.index', compact('transferencias'));
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
        $transferencia = TransferenciaEstoque::join('departamento as d', 'te.id_departamento', '=', 'd.id_departamento')
            ->join('users as uc', 'te.id_usuario', '=', 'uc.id')
            ->select('uc.name', 'd.descricao_departamento', 'te.*')
            ->from('transferencia_estoque as te')
            ->where('te.id_tranferencia', $id)
            ->first();

        $transferenciaItens = TransferenciaEstoqueItens::where('id_transferencia', $id)->get();

        $produtos = $this->produto
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->descricao_produto,
                    'value' => $item->id_produto
                ];
            })->toArray();


        return view('admin.transferenciaEntreEstoque.edit', compact('transferencia', 'transferenciaItens', 'produtos', 'id'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'recebido' => 'nullable',
            'quantidade_baixa' => 'nullable|array',
            'quantidade_baixa.*' => 'nullable|numeric|min:0',
            'observacao_inconsistencia' => 'nullable|string|max:1000',
        ]);

        try {
            // Usa o ID da URL como padrão, mas permite override pelo request
            $id_transferencia = $request->input('id_tranferencia') ?: $request->input('id_transferencia') ?: $id;
            $filial = Auth::user()->id_filial;

            // Processa checkbox: se marcado = 1, se não marcado = 0
            $recebido = $request->has('recebido') ? 1 : 0;

            // Verifica se há inconsistências
            $temInconsistencia = $this->verificarInconsistencias($request, $id_transferencia);

            // Processa valor médio se necessário
            $this->processarValorMedio($id_transferencia, $filial);

            // Atualiza transferência e itens
            $this->atualizarTransferencia($request, $id, $id_transferencia, $recebido, $temInconsistencia);

            // Processa estoque e histórico
            $this->processarEstoqueEHistorico($id_transferencia);

            // Confirma recebimento se solicitado
            if ($recebido == 1) {
                $this->processarConfirmacaoRecebimento($id_transferencia);
            }

            // Define mensagem baseada no resultado
            if ($temInconsistencia) {
                $mensagem = 'Registro salvo com sucesso! Status alterado para "Recebido Parcial" devido à inconsistência detectada.';
            } elseif ($recebido == 1) {
                $mensagem = 'Registro salvo com sucesso! Status alterado para "Recebido".';
            } else {
                $mensagem = 'Registro salvo com sucesso!';
            }

            Log::info('Redirecionando para index após salvar transferência', [
                'id_transferencia' => $id_transferencia,
                'tem_inconsistencia' => $temInconsistencia,
                'recebido' => $recebido,
                'mensagem' => $mensagem
            ]);

            return redirect()->route('admin.transferenciaEntreEstoque.index')->with('success', $mensagem);
        } catch (Exception $e) {
            Log::error('Erro ao atualizar transferência', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
    private function verificarInconsistencias(Request $request, $id_transferencia)
    {
        $temInconsistencia = false;

        if ($request->has('quantidade_baixa')) {
            $quantidadesBaixa = $request->input('quantidade_baixa');

            foreach ($quantidadesBaixa as $idItem => $quantidadeBaixa) {
                if ($quantidadeBaixa !== null) {
                    // Busca o item para comparar as quantidades
                    $item = TransferenciaEstoqueItens::find($idItem);
                    if ($item && $quantidadeBaixa < $item->quantidade && $quantidadeBaixa > 0) {
                        $temInconsistencia = true;
                        break;
                    }
                }
            }
        }

        return $temInconsistencia;
    }

    private function processarValorMedio($id_transferencia, $filial)
    {
        if (!$id_transferencia || $filial == 1) {
            return;
        }

        if ($this->verificaTransferencia($id_transferencia) == $id_transferencia) {
            $this->inserirValorMedio($id_transferencia, $filial);
        }
    }

    private function atualizarTransferencia(Request $request, $id, $id_transferencia, $recebido = null, $temInconsistencia = false)
    {
        // Atualiza transferência
        $transferencia = TransferenciaEstoque::findOrFail($id);

        // Salva o campo recebido diretamente
        $valorRecebido = $recebido !== null ? $recebido : ($request->has('recebido') ? 1 : 0);
        $transferencia->recebido = $valorRecebido;

        // Atualiza situação baseada na inconsistência e status de recebido
        if ($temInconsistencia) {
            $transferencia->situacao = 'RECEBIDO PARCIAL';
        } elseif ($valorRecebido == 1) {
            // Se marcado como recebido e sem inconsistências, status = RECEBIDO
            $transferencia->situacao = 'RECEBIDO';
        } else {
            // Se não marcado como recebido, mantém como AGUARDANDO
            $transferencia->situacao = 'AGUARDANDO';
        }

        // Salva observação de inconsistência se fornecida
        if ($request->filled('observacao_inconsistencia')) {
            $transferencia->observacao_inconsistencia = $request->input('observacao_inconsistencia');
        }

        $transferencia->save();

        // Atualiza itens relacionados com quantidade_baixa
        if ($request->has('quantidade_baixa')) {
            $quantidadesBaixa = $request->input('quantidade_baixa');

            foreach ($quantidadesBaixa as $idItem => $quantidadeBaixa) {
                if ($quantidadeBaixa !== null) {
                    TransferenciaEstoqueItens::where('id_transferencia_itens', $idItem)
                        ->update(['quantidade_baixa' => $quantidadeBaixa]);
                }
            }
        }

        // Processa lista de itens se existir (para compatibilidade)
        if ($request->has('transferencia_estoque_itens_transferencia_list')) {
            $itens = $request->input('transferencia_estoque_itens_transferencia_list');
            foreach ($itens as $item) {
                TransferenciaEstoqueItens::updateOrCreate(
                    ['id_transferencia' => $id_transferencia, 'id_produto' => $item['id_produto']],
                    $item
                );
            }
        }
    }

    private function processarEstoqueEHistorico($id_transferencia)
    {
        // Atualiza estoque
        DB::select("SELECT * FROM fc_atualizar_estoque_transferencia(?)", [$id_transferencia]);

        // Insere histórico
        $transferencia = TransferenciaEstoque::with(['itens'])->where('id_tranferencia', $id_transferencia)->first();

        if (!$transferencia) return;

        $idRelacaoSolicitacoesNovo = TransferenciaEstoqueAuxEnvio::where('id_tranferencia_envio', $id_transferencia)
            ->value('id_relacao_solicitacoes_novo');

        foreach ($transferencia->itens as $item) {
            $quantidadeEstoque = ProdutosPorFilial::where('id_produto_unitop', $item->id_produto)
                ->where('id_filial', $transferencia->id_filial)
                ->value('quantidade_produto');

            HistoricoMovimentacaoEstoque::create([
                'data_inclusao' => now(),
                'id_produto' => $item->id_produto,
                'id_filial' => $transferencia->id_filial,
                'qtde_estoque' => $quantidadeEstoque,
                'qtde_entrada' => $item->quantidade_baixa,
                'saldo_total' => $quantidadeEstoque + $item->quantidade_baixa,
                'id_transferencia' => $transferencia->id_tranferencia,
                'tipo' => 'ENTRADA POR TRANSFERENCIA DE ESTOQUE',
                'id_relacaosolicitacoespecas' => $idRelacaoSolicitacoesNovo,
            ]);
        }

        // Baixa requisições
        DB::select("SELECT * FROM fc_baixar_envio_estoque(?)", [$id_transferencia]);
    }

    private function processarConfirmacaoRecebimento($id_transferencia)
    {
        Log::info('IDs para confirmação de recebimento', ['ids' => $id_transferencia]);

        $this->confirmaRecebimento($id_transferencia);
    }

    private function verificaTransferencia($id_transferencia)
    {
        // Verifica transferência aux envio
        $transferencia = TransferenciaEstoqueAuxEnvio::where('id_tranferencia_envio', $id_transferencia)->first();
        if ($transferencia) {
            return $this->validaRecebimento($id_transferencia) ? $id_transferencia : 0;
        }

        // Verifica transferência direta
        $transferenciaDireta = TransferenciaDiretaEstoqueAux::where('id_transferencia_recebimento', $id_transferencia)->first();
        if ($transferenciaDireta) {
            return $this->validaRecebimento($id_transferencia) ? $id_transferencia : 0;
        }

        return 0;
    }

    private function validaRecebimento($id_transferencia)
    {
        return TransferenciaEstoque::where('id_tranferencia', $id_transferencia)
            ->whereNull('recebido')
            ->exists();
    }

    private function inserirValorMedio($id_transferencia, $unidade)
    {
        // Obtém produtos da transferência
        $idsProdutos = TransferenciaEstoqueItens::where('id_transferencia', $id_transferencia)
            ->pluck('id_produto')
            ->toArray();

        if (empty($idsProdutos)) return 2;

        // Obtém valores médios da filial matriz
        $valoresMedios = ProdutosPorFilial::whereIn('id_produto_unitop', $idsProdutos)
            ->where('id_filial', 1)
            ->pluck('valor_medio', 'id_produto_unitop')
            ->toArray();

        if (empty($valoresMedios)) return 2;

        // Atualiza valores médios na filial destino
        foreach ($valoresMedios as $idProduto => $valorMedio) {
            // Converte o valor para número, removendo R$, espaços e trocando vírgula por ponto
            $valorMedio = str_replace(['R$', ' '], '', $valorMedio);
            $valorMedio = str_replace(',', '.', $valorMedio);

            // Força tipo numérico
            $valorMedio = (float) $valorMedio;

            ProdutosPorFilial::where('id_produto_unitop', $idProduto)
                ->where('id_filial', $unidade)
                ->update(['valor_medio' => $valorMedio]);
        }
    }

    public function confirmaRecebimento($id)
    {


        RelacaoSolicitacaoPeca::where('id_transferencia', $id)
            ->update(['situacao' => 'RECEBIMENTO CONFIRMADO']);

        return $id;
    }

    public function visualizarModal($id)
    {
        Log::info('passou aqui');
        if (!is_numeric($id)) {
            abort(400, 'ID inválido');
        }
        $transferencia = TransferenciaEstoque::with(['itens.produto'])
            ->where('id_tranferencia', $id)
            ->firstOrFail();

        return view('components.transferencia.moda-visualizar-estoque', compact('transferencia'));
    }

    public function buildQueryWithFilters(Request $request)
    {
        $query = TransferenciaDiretaEstoque::query();


        if ($request->filled('id_tranferencia')) {
            $query->where('id_tranferencia', $request->id_tranferencia);
        }

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [$request->data_inclusao, $request->data_final]);
        } elseif ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        } elseif ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        return $query->with([
            'usuario',
            'filial',
            'departamento',
        ])->orderByDesc('id_tranferencia');
    }

    public function exportPdf(Request $request)
    {
        try {
            Log::info('Iniciando exportação de PDF', $request->all());

            // Corrigido: model correto
            $resultados = \App\Models\TransferenciaEstoque::where('id_tranferencia', $request->id_tranferencia)
                ->with(['filial', 'usuario', 'departamento']) // se precisar dos relacionamentos
                ->get();

            Log::info('Total resultados:', ['count' => $resultados->count()]);

            $html = View::make('PDFS.transferenciaEstoque', compact('resultados'))->render();

            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="transferenciaEstoque.pdf"');
        } catch (Exception $e) {
            Log::error('Erro ao exportar PDF: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportCsv(Request $request)
    {
        try {
            $query = TransferenciaEstoque::query();

            // Aplica os filtros normalmente
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('id_tranferencia')) {
                $query->where('id_tranferencia', $request->id_tranferencia);
            }

            // Remove a paginação aqui
            $resultados = $query->with([
                'usuario',
                'filial',
                'departamento'
            ])->orderBy('id_tranferencia', 'desc')
                ->get();

            if ($resultados->isEmpty()) {
                throw new \Exception('Nenhum registro encontrado para exportação.');
            }

            $filename = 'transferencias_' . uniqid() . '.csv';
            $filepath = storage_path('app/output/' . $filename);

            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $handle = fopen($filepath, 'w');

            $header = [
                'Cod_Transferencia',
                'Filial_Solicitacao',
                'Usuario_Solicitacao',
                'Departamento',
                'Recebido',
                'Data_Inclusao'
            ];
            fputcsv($handle, $header);

            foreach ($resultados as $item) {
                fputcsv($handle, [
                    $item->id_tranferencia,
                    optional($item->filial)->name ?? '',
                    optional($item->usuario)->name ?? '',
                    optional($item->departamento)->name ?? '',
                    $item->recebido == 1 ? 'sim' : '',
                    optional($item->data_inclusao)->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($handle);

            return response()->download($filepath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Erro ao exportar CSV: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportXls(Request $request)
    {
        $query = TransferenciaEstoque::query();

        if ($request->filled('id_tranferencia')) {
            $query->where('id_tranferencia', $request->id_tranferencia);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        $query->with(['usuario', 'filial', 'departamento'])
            ->orderBy('id_tranferencia', 'desc');

        $columns = [
            'id_tranferencia' => 'ID',
            'filial.name' => 'Filial',
            'departamento.descricao_departamento' => 'Departamento',
            'usuario.name' => 'Usuário',
            'recebimento' => 'Recebimento',
            'data_inclusao' => 'Data de Inclusão',
        ];

        return $this->exportToExcel($request, $query, $columns, 'transferencias');
    }


    public function exportXml(Request $request)
    {
        $query = TransferenciaEstoque::query();

        if ($request->filled('id_tranferencia')) {
            $query->where('id_tranferencia', $request->id_tranferencia);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        $query->with(['usuario', 'filial', 'departamento'])
            ->orderBy('id_tranferencia', 'desc');

        $columns = [
            'id_tranferencia' => 'ID',
            'filial.name' => 'Filial',
            'departamento.descricao_departamento' => 'Departamento',
            'usuario.name' => 'Usuário',
            'recebimento' => 'Recebimento',
            'data_inclusao' => 'DataInclusao',
        ];

        return $this->exportToXml($request, $query, $columns, 'transferencias');
    }

    protected function exportToXml(Request $request, $query, array $columns, string $filenamePrefix, array $filters = [])
    {
        $registros = $query->get();

        if ($registros->isEmpty()) {
            return back()->with('error', 'Nenhum registro encontrado para exportação.');
        }

        $filename = $filenamePrefix . '_' . uniqid() . '.xml';

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Início do XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><registros></registros>');

        foreach ($registros as $item) {
            $registroXml = $xml->addChild('registro');

            foreach ($columns as $key => $label) {
                $valor = data_get($item, $key);

                if ($valor instanceof \Carbon\Carbon) {
                    $valor = $valor->format('d/m/Y H:i');
                }

                // Remove caracteres especiais do nome do campo para XML
                $tag = preg_replace('/[^a-zA-Z0-9_]/', '_', $label);
                $registroXml->addChild($tag, htmlspecialchars($valor ?? ''));
            }
        }

        echo $xml->asXML();
        exit;
    }
}
