<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Checklist\Models\CheckListRecebimentoFornecedor;
use App\Models\DescartePneu;
use App\Models\DesenhoPneu;
use App\Models\Filial;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\HistoricoPneu;
use App\Modules\Compras\Models\ItemSolicitacaoCompra;
use App\Models\ManutencaoPneus;
use App\Models\ManutencaoPneusEntrada;
use App\Models\ManutencaoPneusEntradaItens;
use App\Models\NotaFiscalEntrada;
use App\Modules\Compras\Models\PedidoCompra;
use App\Modules\Compras\Models\PedidosOrdemAux;
use App\Models\Pneu;
use App\Modules\Manutencao\Models\Servico;
use App\Modules\Compras\Models\SolicitacaoCompra;
use App\Models\TipoBorrachaPneu;
use App\Models\TipoDesenhoPneu;
use App\Models\TipoReformaPneu;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HasPneusParadosTrait;

class ManutencaoPneusEntradaController extends Controller
{
    use SanitizesMonetaryValues;
    use HasPneusParadosTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $envios = ManutencaoPneus::query();
        $recebimento = ManutencaoPneusEntrada::query();


        $query = ManutencaoPneusEntrada::query()
            ->with(['filial', 'fornecedor'])
            ->orderBy('id_manutencao_entrada', 'desc');

        if ($request->filled('id_pneu')) {
            $query->where('id_manutencao_entrada', $request->id_pneu);
        }

        if ($request->filled(['data_inicial', 'data_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial,
                $request->data_final,
            ]);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        $manutencaoPneusEntrada = $query->latest('id_manutencao_entrada')->paginate(15);

        $filiais = Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray();

        $formOptions = [
            'pneus' => Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', '=', 'DIAGNOSTICO')
                ->orderBy('label')
                ->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoReforma' => TipoReformaPneu::select('descricao_tipo_reforma as label', 'id_tipo_reforma as value')->orderBy('label')->get()->toArray(),
            'desenhopneu' => TipoDesenhoPneu::select('descricao_desenho_pneu as label', 'id_desenho_pneu as value')->orderBy('label')->get()->toArray(),
            'tipoborracha' => TipoBorrachaPneu::select('descricao_tipo_borracha as label', 'id_tipo_borracha as value')->orderBy('label')->get()->toArray(),
            'servico' => Servico::select('descricao_servico as label', 'id_servico as value')->orderBy('label')->get()->toArray(),
        ];
        $manutencaoPneus = $envios->latest('id_manutencao_pneu')->paginate(15);

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        return view('admin.envioerecebimentopneus.index', compact(
            'manutencaoPneusEntrada',
            'formOptions',
            'fornecedoresFrequentes',
            'filiais',
            'envios',
            'manutencaoPneus',
            'recebimento'
        ));
    }

    public function create()
    {
        // Bloquear criação se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível criar entrada enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $formOptions = [
            'pneus' => Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', '=', 'DIAGNOSTICO')
                ->orderBy('label', 'desc')
                ->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoReforma' => TipoReformaPneu::select('descricao_tipo_reforma as label', 'id_tipo_reforma as value')->orderBy('label')->get()->toArray(),
            'desenhopneu' => TipoDesenhoPneu::select('descricao_desenho_pneu as label', 'id_desenho_pneu as value')->orderBy('label')->get()->toArray(),
            'tipoborracha' => TipoBorrachaPneu::select('descricao_tipo_borracha as label', 'id_tipo_borracha as value')->orderBy('label')->get()->toArray(),
            'servico' => Servico::select('descricao_servico as label', 'id_servico as value')
                ->where('id_grupo', '=', 600) // coloque aqui o id do grupo borracharia
                ->orderBy('label')
                ->limit(30)
                ->get()
                ->toArray(),
        ];

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        return view('admin.manutencaopneusentrada.create', compact('formOptions', 'fornecedoresFrequentes'));
    }

    public function store(Request $request)
    {
        // Bloquear armazenamento se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível cadastrar entrada enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $dados = $request->validate([
            'id_filial' => 'required|integer',
            'id_fornecedor' => 'required|integer',
            'chave_nf_entrada' => 'nullable|string|max:300',
            'numero_nf' => 'required|integer',
            'serie_nf' => 'required|string|max:300',
            'valor_total_nf' => 'required|string',
            'valor_total_desconto' => 'required|string',
            'data_recebimento' => 'required|date_format:Y-m-d',
            'valor_pneu' => 'nullable|string',
            'valor_pneu_total' => 'nullable|string',
            'id_servico'        => 'required|integer'

        ]);

        $pneus = json_decode($request->pneus);

        DB::beginTransaction();

        try {
            $valorPneu = $this->converterMoedaBrasileiraParaDecimal($dados['valor_pneu']);
            $valorTotalDesconto = $this->converterMoedaBrasileiraParaDecimal($dados['valor_total_desconto']);
            $valorTotalNf = $this->converterMoedaBrasileiraParaDecimal($dados['valor_total_nf']);
            $valorPneuTotal = $this->converterMoedaBrasileiraParaDecimal($dados['valor_pneu_total']);

            $manutencaoPneuEntrada = new ManutencaoPneusEntrada();

            $manutencaoPneuEntrada->data_inclusao            = now();
            $manutencaoPneuEntrada->id_fornecedor            = $dados['id_fornecedor'];
            $manutencaoPneuEntrada->id_filial                = $dados['id_filial'];
            $manutencaoPneuEntrada->numero_nf                = $dados['numero_nf'];
            $manutencaoPneuEntrada->valor_total_nf           = $valorTotalNf;
            $manutencaoPneuEntrada->serie_nf                 = $dados['serie_nf'];
            $manutencaoPneuEntrada->valor_total_desconto     = $valorTotalDesconto;
            $manutencaoPneuEntrada->data_recebimento         = $dados['data_recebimento'];
            $manutencaoPneuEntrada->id_manutencao            = $request->id_manutencao ?? null;
            $manutencaoPneuEntrada->situacao_entrada         = 'FINALIZADO';
            $manutencaoPneuEntrada->chave_nf_entrada         = $dados['chave_nf_entrada'];
            $manutencaoPneuEntrada->is_borracharia           = false;
            $manutencaoPneuEntrada->valor_pneu               = $valorPneu;
            $manutencaoPneuEntrada->valor_pneu_total         = $valorPneuTotal;
            $manutencaoPneuEntrada->id_servico               = $dados['id_servico'];


            $manutencaoPneuEntrada->save();

            foreach ($pneus as $pneu) {
                $manutencaoPneuEntradaItens = new ManutencaoPneusEntradaItens;

                $pneuModel = Pneu::find(is_array($pneu->id_pneu) ? $pneu->id_pneu[0] : $pneu->id_pneu);

                // Converte corretamente para boolean (aceita 0/1, "0"/"1", true/false)
                $houveDescarte = filter_var($pneu->descarte ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($houveDescarte) {
                    $pneuModel->update([
                        'status_pneu' => 'AGUARDANDO DESCARTE',
                        'data_alteracao' => now(),
                    ]);


                    $historicoPneu = [
                        'id_pneu' => $pneuModel->id_pneu,
                        'id_modelo' => $pneuModel->id_modelo_pneu,
                        'id_vida_pneu' => $pneuModel->id_controle_vida_pneu,
                        'status_movimentacao' => 'AGUARDANDO DESCARTE ',
                    ];

                    $historicoPneu['data_inclusao'] = now();
                    $historicoPneu['data_retirada'] = now();
                    HistoricoPneu::create($historicoPneu);
                }

                $manutencaoPneuEntradaItens->data_inclusao = now();
                $manutencaoPneuEntradaItens->id_pneu = is_array($pneu->id_pneu) ? $pneu->id_pneu[0] : $pneu->id_pneu;
                $manutencaoPneuEntradaItens->id_tipo_reforma = is_array($pneu->id_tipo_reforma) ? $pneu->id_tipo_reforma[0] : $pneu->id_tipo_reforma;
                $manutencaoPneuEntradaItens->id_desenho_pneu = is_array($pneu->id_desenho_pneu) ? $pneu->id_desenho_pneu[0] : $pneu->id_desenho_pneu;
                $manutencaoPneuEntradaItens->tipo_borracha = is_array($pneu->id_tipo_borracha) ? $pneu->id_tipo_borracha[0] : $pneu->id_tipo_borracha;
                $manutencaoPneuEntradaItens->laudo_descarte = $pneu->laudo_descarte ?? null;
                $manutencaoPneuEntradaItens->id_manutencao_pneu_entrada = $manutencaoPneuEntrada->id_manutencao_entrada;
                $manutencaoPneuEntradaItens->descarte = $houveDescarte;
                $manutencaoPneuEntradaItens->is_conferido = filter_var($pneu->is_conferido ?? false, FILTER_VALIDATE_BOOLEAN);
                $manutencaoPneuEntradaItens->is_feito = filter_var($pneu->is_feito ?? false, FILTER_VALIDATE_BOOLEAN);

                $manutencaoPneuEntradaItens->save();

                // Criar registro de descarte vinculado à manutenção, quando marcado para descarte
                if ($houveDescarte) {
                    DescartePneu::create([
                        'data_inclusao' => now(),
                        'id_pneu' => $pneuModel->id_pneu,
                        'observacao' => 'DESCARTE EFETUADO ATRAVÉS DA ENTRADA DE MANUTENÇÃO',
                        'origem' => 'manutencao',
                        'status_processo' => 'aguardando_inicio',
                        'id_manutencao_origem' => $manutencaoPneuEntradaItens->id_manutencao_pneu_entrada_itens,
                    ]);
                }

                // Se NÃO houve descarte, volta para estoque
                if (! $houveDescarte) {
                    $pneuModel->update([
                        'data_alteracao' => now(),
                        'status_pneu' => 'ESTOQUE',
                    ]);

                    $historicoPneu = [
                        'id_pneu' => $pneuModel->id_pneu,
                        'id_modelo' => $pneuModel->id_modelo_pneu,
                        'id_vida_pneu' => $pneuModel->id_controle_vida_pneu,
                        'status_movimentacao' => 'ESTOQUE ',
                    ];


                    $historicoPneu['data_inclusao'] = now();
                    $historicoPneu['data_retirada'] = now();
                    HistoricoPneu::create($historicoPneu);
                }

                $this->inserirPneuHistorico($manutencaoPneuEntrada->id_manutencao_entrada, $pneu->numero_fogo);
            }

            $solicCompra = SolicitacaoCompra::create([
                'id_filial'         => $manutencaoPneuEntrada->id_filial,
                'id_solicitante'    => \Illuminate\Support\Facades\Auth::id(),
                'id_comprador'      => \Illuminate\Support\Facades\Auth::id(),
                'id_aprovador'      => \Illuminate\Support\Facades\Auth::id(),
                'id_departamento'   => \Illuminate\Support\Facades\Auth::user()->departamento_id,
                'situacao_compra'   => 'FINALIZADO',
                'tipo_solicitacao'  => 2,
                'data_finalizada'   => now(),
                'aprovado_reprovado'    => true
            ]);

            $itemSolic = ItemSolicitacaoCompra::create([
                'id_solicitacao_compra' => $solicCompra->id_solicitacao_compra,
                'data_inclusao'         => now(),
                'valor_total_produto'   => $valorTotalDesconto, // ✅ já convertido
                'id_produto'            => 1
            ]);

            $pedidoCompra = PedidoCompra::create([
                'valor_total_desconto'     => $valorTotalDesconto,
                'id_fornecedor'            => $manutencaoPneuEntrada->id_fornecedor,
                'id_comprador'             => \Illuminate\Support\Facades\Auth::id(),
                'id_filial'                => $manutencaoPneuEntrada->id_filial,
                'id_aprovador_pedido'      => \Illuminate\Support\Facades\Auth::id(),
                'id_solicitacoes_compras'  => $itemSolic->id_itens_solicitacoes, // FK correta
                'situacao_pedido'          => 2,
                'situacao'                 => 'APROVADO',
                'is_liberado'              => true,
                'tipo_pedido'              => 2, // Serviço (REFORMA PNEU),
                'valor_total_sem_percentual' => $valorTotalDesconto
            ]);

            // agora relaciona com o pedido

            $pedidoOrdem = PedidosOrdemAux::create([
                //'id_ordem_servico',
                'id_pedido_compras'     => $pedidoCompra->id_pedido_compras,
                //'id_nf_compra_servico',
                //'id_pedido_geral'
            ]);



            DB::commit();

            return redirect()
                ->route('admin.compras.lancamento-notas.index')
                ->withNotification([
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Entrada de Manutenção de Pneus cadastrado com sucesso!',
                ]);
        } catch (\Exception $e) {
            // dd('' . $e->getMessage());
            DB::rollBack();

            Log::error('Erro na criação de Entrada de Manutenção de Pneus:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->withNotification([
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Não foi possível cadastrar a Entrada de Manutenção de Pneus.',
                ]);
        }
    }

    public function edit($id)
    {
        // Bloquear edição se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível editar enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $manutencaoPneusEntrada = ManutencaoPneusEntrada::find($id);

        $pneus = ManutencaoPneusEntradaItens::where('id_manutencao_pneu_entrada', $id)
            ->with(['tipo_reforma', 'desenho_pneu', 'tipo_borracha', 'pneu'])
            ->get();

        $formOptions = [
            'pneus' => Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', '=', 'DIAGNOSTICO')
                ->orderBy('label')
                ->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoReforma' => TipoReformaPneu::select('descricao_tipo_reforma as label', 'id_tipo_reforma as value')->orderBy('label')->get()->toArray(),
            'desenhopneu' => TipoDesenhoPneu::select('descricao_desenho_pneu as label', 'id_desenho_pneu as value')->orderBy('label')->get()->toArray(),
            'tipoborracha' => TipoBorrachaPneu::select('descricao_tipo_borracha as label', 'id_tipo_borracha as value')->orderBy('label')->get()->toArray(),
            'servico' => Servico::select('descricao_servico as label', 'id_servico as value')->orderBy('label')->get()->toArray(),
        ];

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        return view('admin.manutencaopneusentrada.edit', compact('formOptions', 'manutencaoPneusEntrada', 'fornecedoresFrequentes', 'pneus'));
    }

    public function update(Request $request, $id)
    {
        // Bloquear atualização se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível atualizar entrada enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        dd($request->all());
        $dados = $request->validate([
            'id_filial' => 'required|integer',
            'id_fornecedor' => 'required|integer',
            'chave_nf_entrada' => 'required|string|max:300',
            'numero_nf' => 'required|integer',
            'serie_nf' => 'required|string|max:300',
            'valor_total_nf' => 'required|string',
            'valor_total_desconto' => 'required|string',
            'data_recebimento' => 'required|date_format:Y-m-d',
        ]);

        $pneus = json_decode($request->pneus);

        DB::beginTransaction();

        try {

            $manutencaoPneuEntrada = ManutencaoPneusEntrada::findorFail($id);

            $manutencaoPneuEntrada->data_inclusao = now();
            $manutencaoPneuEntrada->id_fornecedor = $dados['id_fornecedor'];
            $manutencaoPneuEntrada->id_filial = $dados['id_filial'];
            $manutencaoPneuEntrada->numero_nf = $dados['numero_nf'];
            $manutencaoPneuEntrada->valor_total_nf = $dados['valor_total_nf'];
            $manutencaoPneuEntrada->serie_nf = $dados['serie_nf'];
            $manutencaoPneuEntrada->valor_total_desconto = $dados['valor_total_desconto'];
            $manutencaoPneuEntrada->data_recebimento = $dados['data_recebimento'];
            $manutencaoPneuEntrada->id_manutencao = $request->id_manutencao ?? null;
            $manutencaoPneuEntrada->chave_nf_entrada = $dados['chave_nf_entrada'];
            $manutencaoPneuEntrada->is_borracharia = false;

            $manutencaoPneuEntrada->save();

            // IDs dos pneus que estão vindo na requisição atual
            $pneusAtuais = collect($pneus)->pluck('numero_fogo')->toArray();

            // Remove os pneus que NÃO estão na lista atual (foram deletados pelo usuário)
            ManutencaoPneusEntradaItens::where('id_manutencao_pneu_entrada', $manutencaoPneuEntrada->id_manutencao_entrada)
                ->whereNotIn('id_pneu', $pneusAtuais)
                ->delete();

            if (empty($pneus)) {
                // Remove todos os itens se não há pneus
                ManutencaoPneusEntradaItens::where('id_manutencao_pneu_entrada', $manutencaoPneuEntrada->id_manutencao_entrada)
                    ->delete();
            } else {
                foreach ($pneus as $index => $pneu) {
                    // Upload específico para cada pneu (se aplicável)
                    $anexoPath = null;
                    $fileKey = "laudo_descarte_{$index}"; // ou usar o numero_fogo
                    if ($request->hasFile($fileKey) && $request->file($fileKey)->isValid()) {
                        $anexoPath = $request->file($fileKey)->store('laudos/descarte', 'public');
                    }

                    $manutencaoPneuEntradaItens = ManutencaoPneusEntradaItens::where('id_manutencao_pneu_entrada', $manutencaoPneuEntrada->id_manutencao_entrada)
                        ->where('id_pneu', $pneu->numero_fogo)
                        ->first();

                    if ($manutencaoPneuEntradaItens) {
                        // Update
                        $manutencaoPneuEntradaItens->update([
                            'data_alteracao' => now(),
                            'id_pneu' => $pneu->numero_fogo,
                            'id_tipo_reforma' => $pneu->id_tipo_reforma,
                            'id_desenho_pneu' => $pneu->id_desenho_pneu,
                            'tipo_borracha' => $pneu->id_tipo_borracha,
                            'laudo_descarte' => $anexoPath ?: $manutencaoPneuEntradaItens->laudo_descarte,
                            'id_manutencao_pneu_entrada' => $manutencaoPneuEntrada->id_manutencao_entrada,
                            'descarte' => $pneu->descarte ?? false,
                            'is_conferido' => $pneu->is_conferido ?? false,
                            'is_feito' => $pneu->is_feito ?? false,
                        ]);
                    } else {
                        // Create
                        ManutencaoPneusEntradaItens::create([
                            'data_inclusao' => now(),
                            'id_pneu' => $pneu->numero_fogo,
                            'id_tipo_reforma' => $pneu->id_tipo_reforma,
                            'id_desenho_pneu' => $pneu->id_desenho_pneu,
                            'tipo_borracha' => $pneu->id_tipo_borracha,
                            'laudo_descarte' => $anexoPath,
                            'id_manutencao_pneu_entrada' => $manutencaoPneuEntrada->id_manutencao_entrada,
                            'descarte' => $pneu->descarte ?? false,
                            'is_conferido' => $pneu->is_conferido ?? false,
                            'is_feito' => $pneu->is_feito ?? false,
                        ]);

                        $this->inserirPneuHistorico($manutencaoPneuEntrada->id_manutencao_entrada, $pneu->numero_fogo);
                    }
                }
            }

            $this->onSalvar($manutencaoPneuEntrada->id_manutencao_entrada);

            DB::commit();

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->withNotification([
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Entrada de Manutenção de Pneus atualizado com sucesso!',
                ]);
        } catch (\Exception $e) {
            dd('' . $e->getMessage());
            DB::rollBack();

            Log::error('Erro na atualização de Entrada de Manutenção de Pneus:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->withNotification([
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Não foi possível atualizar a Entrada de Manutenção de Pneus.',
                ]);
        }
    }

    public function destroy($id)
    {
        try {

            DB::beginTransaction();
            // Bloquear exclusão se existirem pneus no depósito parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return response()->json([
                    'notification' => [
                        'title' => 'Operação bloqueada',
                        'type' => 'error',
                        'message' => 'Não é possível excluir entrada enquanto houver pneus no depósito parados por mais de 24 horas.',
                    ],
                ], 423);
            }


            $manutencao = ManutencaoPneusEntrada::find($id);
            ManutencaoPneusEntradaItens::where('id_manutencao_pneu_entrada_itens', $manutencao->id_manutencao_entrada)->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Entrada Manutenção de Pneus excluído!',
                    'type' => 'success',
                    'message' => 'Entrada Manutenção de Pneus excluída com sucesso',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir Entrada Manutenção de Pneus: ' . $e->getMessage());

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => 'Não foi possível excluir Entrada Manutenção de Pneus: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function atualizarSituacaoEntrada($id_pneu_entrada)
    {
        // Contagem de itens
        $qtd_itens = ManutencaoPneusEntradaItens::where('id_manutencao_pneu_entrada', $id_pneu_entrada)
            ->count();

        // Contagem de itens atualizados
        $qtd_itens_atualizados = ManutencaoPneusEntradaItens::where('id_manutencao_pneu_entrada', $id_pneu_entrada)
            ->whereNotNull('id_tipo_reforma')
            ->whereNotNull('id_desenho_pneu')
            ->whereNotNull('tipo_borracha')
            ->where('descarte', false)
            ->count();

        // Lógica de atualização
        if ($qtd_itens > $qtd_itens_atualizados) {
            ManutencaoPneusEntrada::where('id_manutencao_entrada', $id_pneu_entrada)
                ->update([
                    'situacao_entrada' => 'PARCIAL',
                    'data_alteracao' => now(),
                ]);
        } else {
            ManutencaoPneusEntrada::where('id_manutencao_entrada', $id_pneu_entrada)
                ->update([
                    'situacao_entrada' => 'FINALIZADO',
                    'data_alteracao' => now(),
                ]);
        }
    }

    public function getFornecedoresFrequentes()
    {
        $fornecedores = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                ->orderBy('nome_fornecedor')
                ->limit(20)
                ->get();
        });

        return $fornecedores;
    }

    public function onObterManutencaoPneu($id)
    {
        try {

            $jaExiste = ManutencaoPneusEntrada::where('id_manutencao', $id)->exists(); // verifica a existencia

            if ($jaExiste) {
                return response()->json([
                    'error' => true,
                    'message' => '⚠️ Manutenção de pneu já lançada!'
                ], 422);
            }

            $manutencao = ManutencaoPneus::where('id_manutencao_pneu', $id)
                ->with(['manutencaopneusitens.tiporeforma', 'manutencaopneusitens.pneu.modeloPneu.desenho_pneu'])
                ->first();

            if (!$manutencao) {
                return response()->json([
                    'error' => true,
                    'message' => '❌ Manutenção não encontrada!'
                ], 404);
            }

            return response()->json($manutencao);
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao buscar requisição de manutenção de pneu: ' . $e->getMessage());
        }
    }

    public function onGetDesenhoPneu($id)
    {
        try {

            $desenhoPneu = DesenhoPneu::where('id_desenho_pneu', $id)->first();
            LOG::DEBUG($desenhoPneu);

            return response()->json($desenhoPneu);
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao buscar requisição de manutenção de pneu: ' . $e->getMessage());
        }
    }

    public function onSalvar($id_pneu_entrada)
    {
        $query = "select
                    (
                    select
                        count(mpei1.id_pneu)
                    from
                        manutencao_pneu_entrada_itens mpei1
                    where
                        mpei1.id_manutencao_pneu_entrada = $id_pneu_entrada) as qtd_itens,
                    count(mpei.id_pneu) as qtd_itens_atualizados
                from
                    manutencao_pneu_entrada_itens mpei
                where
                    mpei.id_tipo_reforma is not null
                    and mpei.id_desenho_pneu is not null
                    and mpei.tipo_borracha is not null
                    and mpei.descarte is false
                    and mpei.id_manutencao_pneu_entrada = $id_pneu_entrada";

        $objects = DB::connection('pgsql')->select($query);

        if ($objects[0]->qtd_itens > $objects[0]->qtd_itens_atualizados) {
            ManutencaoPneusEntrada::where('id_manutencao_entrada', $id_pneu_entrada)
                ->update([
                    'situacao_entrada' => 'PARCIAL',
                    'data_alteracao' => now(),
                ]);
        } else {
            ManutencaoPneusEntrada::where('id_manutencao_entrada', $id_pneu_entrada)
                ->update([
                    'situacao_entrada' => 'FINALIZADO',
                    'data_alteracao' => now(),
                ]);
        }
    }

    public static function inserirPneuHistorico($idmanutecao, $idpneu)
    {
        try {

            $idpneus = is_array($idpneu) ? $idpneu : [$idpneu];

            foreach ($idpneus as $pneu) {
                $query = "select * from fc_atualizar_entrada_pneu($pneu, $idmanutecao)";
                DB::connection('pgsql')->select($query);
            }
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao inserir pneu no historico: ' . $e->getMessage());
        }
    }

    public function checklist($id, $nf_entrada)
    {

        $nota = [
            ['value' => '10', 'label' => 'Conforme'],
            ['value' => '5', 'label' => 'Parcialmente Conforme'],
            ['value' => '0', 'label' => 'Não Conforme'],
            ['value' => 'N.A', 'label' => 'Não Aplicavel'],
        ];

        return view('admin.manutencaopneusentrada.checklist', compact('nota', 'id', 'nf_entrada'));
    }

    public function checklist_store(Request $request)
    {
        // Bloquear checklist_store se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível salvar checklist enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $dadosChecklist = $request->validate([
            'checklist_fornecedor_prazo' => 'required',
            'checklist_fornecedor_pontualidade' => 'required',
            'checklist_fornecedor_quantidade_conforme' => 'required',
            'checklist_fornecedor_integridade_embalagens' => 'required',
        ], [
            'checklist_fornecedor_prazo.required' => 'O campo prazo de entrega é obrigatório',
            'checklist_fornecedor_pontualidade.required' => 'O campo pontualidade é obrigatório',
            'checklist_fornecedor_quantidade_conforme.required' => 'O campo quantidade conforme é obrigatório',
            'checklist_fornecedor_integridade_embalagens.required' => 'O campo integridade embalagens é obrigatório',
        ]);

        try {
            DB::beginTransaction();

            $checkList = new CheckListRecebimentoFornecedor;
            $checkList->data_inclusao = now();
            $checkList->checklist_fornecedor_prazo = $dadosChecklist['checklist_fornecedor_prazo'];
            $checkList->checklist_fornecedor_pontualidade = $dadosChecklist['checklist_fornecedor_pontualidade'];
            $checkList->checklist_fornecedor_quantidade_conforme = $dadosChecklist['checklist_fornecedor_quantidade_conforme'];
            $checkList->checklist_fornecedor_integridade_embalagens = $dadosChecklist['checklist_fornecedor_integridade_embalagens'];

            $checkList->checklist_observacao_prazo = $request->checklist_observacao_prazo ?? null;
            $checkList->checklist_observacao_pontualidade = $request->checklist_observacao_pontualidade ?? null;
            $checkList->checklist_observacao_quantidade_conforme = $request->checklist_observacao_quantidade_conforme ?? null;
            $checkList->checklist_observacao_integridade_embalagens = $request->checklist_observacao_integridade_embalagens ?? null;

            // ID da entrada de manutenção
            $checkList->id_entrada_manutencao_pneu = $request->id;

            // Buscar ID interno da nota fiscal com base na chave ou número
            $notaFiscal = NotaFiscalEntrada::where('chave_nf_entrada', $request->nf_entrada)->first();

            if (! $notaFiscal) {
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Erro!',
                    'message' => 'Nota Fiscal não encontrada.',
                    'duration' => 5000,
                ]);
            }

            $checkList->id_nota_fiscal_entrada = $notaFiscal->id; // ID interno, não a chave

            $checkList->save();

            DB::commit();

            return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                'type' => 'success',
                'title' => 'Requisição de Manutenção de Pneus',
                'message' => 'A requisição foi criada com sucesso.',
                'duration' => 3000,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gravar check list de recebimento de fornecedor: ' . $e->getMessage());

            return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                'type' => 'error',
                'title' => 'Check List de Recebimento de Fornecedor',
                'message' => $e->getMessage(),
                'duration' => 7000,
            ]);
        }
    }

    /**
     * API: Busca pneus em DIAGNOSTICO por número de fogo (id_pneu)
     */
    public function searchPneusDiagnostico(Request $request)
    {
        try {
            $term = trim((string) $request->input('term', ''));

            $query = Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', 'DIAGNOSTICO');

            if ($term !== '') {
                // Busca por parte do número (cast para texto por ser Postgres)
                $query->whereRaw("CAST(id_pneu AS TEXT) ILIKE ?", ['%' . $term . '%']);
            }

            $result = $query->orderBy('label', 'desc')
                ->limit(20)
                ->get();

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Erro na busca de pneus em DIAGNOSTICO', [
                'erro' => $e->getMessage(),
            ]);
            return response()->json([], 500);
        }
    }

    public function enviarPedidoCompras(Request $request, $id) {}

    private function converterMoedaBrasileiraParaDecimal($valor)
    {
        // Remove todos os caracteres que não são dígitos, vírgula ou ponto
        $valor = preg_replace('/[^0-9.,]/', '', $valor);

        // Se estiver vazio, retorna 0
        if (empty($valor)) {
            return 0;
        }

        // Se contém vírgula e ponto, assume que a vírgula é o separador decimal
        if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
            // Remove pontos (separadores de milhares) e substitui vírgula por ponto
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (strpos($valor, ',') !== false) {
            // Apenas vírgula presente - assumir como separador decimal
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }
}
