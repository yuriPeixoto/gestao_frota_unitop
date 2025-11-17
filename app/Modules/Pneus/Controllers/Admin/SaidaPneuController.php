<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\ModeloPneu;
use App\Models\Pneu;
use App\Models\RequisicaoPneu;
use App\Models\RequisicaoPneuItens;
use App\Models\RequisicaoPneuModelos;
use App\Modules\Configuracoes\Models\User;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\VFilial;
use App\Models\ProdutosPorFilial;
use App\Models\TransferenciaPneus;
use App\Models\TransferenciaPneusModelos;
use App\Traits\ExportableTrait;
use App\Traits\HasPneusParadosTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaidaPneuController extends Controller
{
    use ExportableTrait;
    use HasPneusParadosTrait;

    public function index(Request $request)
    {
        // Complex business rule from legacy: Branch-based filtering
        $userFilialId = GetterFilial();

        $query = RequisicaoPneu::query()
            ->with([
                'filial',
                'usuarioSolicitante',
                'usuarioEstoque',
                'requisicaoPneuModelos',
            ]);

        // Apply branch-specific filtering logic from legacy
        if ($userFilialId == 1) {
            // Matriz - can see all requisitions
            $query->where('id_filial', '!=', 0);
        } else {
            // Filial - only requisitions created by them AND with stock
            $query->where('id_filial', $userFilialId);

            // Additional complex stock validation from legacy
            $modelosComEstoque = Pneu::query()
                ->select('id_modelo_pneu', DB::raw('count(requisicao_pneu_itens.id_pneu) as total'))
                ->join('requisicao_pneu_itens', 'pneu.id_pneu', '=', 'requisicao_pneu_itens.id_pneu')
                ->where('pneu.status_pneu', 'ESTOQUE')
                ->whereNotNull('pneu.id_modelo_pneu')
                ->where('pneu.id_filial', $userFilialId)
                ->groupBy('pneu.id_modelo_pneu')
                ->get();

            if (!empty($modelosComEstoque)) {
                $modeloIds = collect($modelosComEstoque)->pluck('ID_MODELO_PNEU')->toArray();
                $query->whereIn('id_requisicao_pneu', function ($subQuery) use ($modeloIds) {
                    $subQuery->select('id_requisicao_pneu')
                        ->from('requisicao_pneu_modelos')
                        ->whereIn('id_modelo_pneu', $modeloIds);
                });
            }
        }

        // Apply filters
        if ($request->filled('data_inicial')) {
            $query->where('data_inclusao', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->where('data_inclusao', '<=', $request->data_final);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_usuario_solicitante')) {
            $query->where('id_usuario_solicitante', $request->id_usuario_solicitante);
        }

        // Default ordering
        $requisicoes = $query->orderByDesc('id_requisicao_pneu')
            ->paginate(20)
            ->appends($request->query());

        // Prepare form data
        $situacoes = [
            'AGUARDANDO DOCUMENTO DE VENDA' => 'AGUARDANDO DOCUMENTO DE VENDA',
            'FINALIZADA' => 'BAIXADA',
            'INICIADA' => 'INICIADA',
            'APROVADO' => 'APROVADO',
            'BAIXADO PARCIAL' => 'BAIXADO PARCIAL',
        ];

        $filiais = Filial::select('id as value', 'name as label')->orderBy('name')->get();
        $usuarios = User::where('is_ativo', true)->orderBy('name')->get();

        return view('admin.saidaPneus.index', compact(
            'requisicoes',
            'situacoes',
            'filiais',
            'usuarios'
        ));
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

        $requisicao = RequisicaoPneu::with([
            'filial',
            'usuarioSolicitante',
            'usuarioEstoque',
            'terceiro',
            'requisicaoPneuModelos.modeloPneu',
            'requisicaoPneuModelos.requisicaoPneuItens.pneu',
        ])->findOrFail($id);

        // Check edit permissions (from legacy)
        if ($requisicao->situacao === 'FINALIZADA') {
            return redirect()->back()->with('error', 'Requisições finalizadas não podem ser editadas.');
        }

        // Get available models for dropdown
        $modelosPneu = ModeloPneu::with('dimensao_pneu')
            ->orderBy('descricao_modelo')
            ->get()
            ->mapWithKeys(function ($modelo) {
                return [
                    $modelo->id_modelo_pneu => $modelo->descricao_modelo . ' - ' . ($modelo->dimensao_pneu()->descricao_pneu ?? 'N/A'),
                ];
            });

        // Get terceiros/fornecedores
        $terceiros = Fornecedor::orderBy('nome_fornecedor')->get();

        // Get filiais para transfer�ncia entre filiais
        $filiais = VFilial::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.saidaPneus.edit', compact('requisicao', 'modelosPneu', 'terceiros', 'filiais'));
    }

    /**
     * Método para carregar pneus por modelo (AJAX) - Replica funcionalidade do legado
     */
    public function carregarPneus(Request $request)
    {
        try {
            $idModelo = $request->input('id_modelo_pneu');
            $idRequisicaoPneuModelos = $request->input('id_requisicao_pneu_modelos', 0);
            $idFilial = Auth::user()->filial_id ?? 1;
            Log::debug('AutoPermission Debug - Início', [
                'route_name' => $request->route() ? $request->route()->getName() : null,
                'route_uri' => $request->route() ? $request->route()->uri() : null,
                'user_id' => Auth::id(),
                'is_superuser' => Auth::user() ? Auth::user()->is_superuser : null,
            ]);

            if (empty($idModelo)) {
                return response()->json(['pneus' => []]);
            }

            // Query igual ao legado
            $pneus = DB::connection('pgsql')->select("
                SELECT DISTINCT ON (p.id_pneu)
                    p.id_pneu,
                    htp.id_modelo,
                    mdp.descricao_modelo,
                    cvp.descricao_vida_pneu,
                    p.status_pneu
                FROM pneu as p
                JOIN historicopneu as htp on htp.id_pneu = p.id_pneu
                JOIN modelopneu as mdp on mdp.id_modelo_pneu = htp.id_modelo
                JOIN controle_vida_pneu as cvp on cvp.id_controle_vida_pneu = htp.id_vida_pneu
                WHERE (p.status_pneu = 'ESTOQUE'
                OR p.id_pneu in (select rpi.id_pneu from requisicao_pneu_itens as rpi
                WHERE rpi.id_requisicao_pneu_modelos = ?))
                AND htp.id_modelo = ?
                AND p.id_filial = ?
                ORDER BY p.id_pneu, cvp.descricao_vida_pneu desc
            ", [$idRequisicaoPneuModelos, $idModelo, $idFilial]);

            $pneusFormatted = [];
            $pneusDesabilitados = [];
            $pneusSelecionados = [];
            $descricaoModelo = '';
            $idModelo = null;

            foreach ($pneus as $pneu) {
                $label = $pneu->id_pneu . ' - ' . $pneu->id_modelo . ' - ' .
                    $pneu->descricao_modelo . ' - Vida: ' . $pneu->descricao_vida_pneu;

                $pneusFormatted[] = [
                    'id' => $pneu->id_pneu,
                    'label' => $label,
                    'status' => $pneu->status_pneu
                ];

                // Capturar dados do modelo (será o mesmo para todos os pneus)
                if (empty($descricaoModelo)) {
                    $descricaoModelo = $pneu->descricao_modelo;
                    $idModelo = $pneu->id_modelo;
                }

                // Marcar como desabilitado/selecionado se APLICADO
                if ($pneu->status_pneu === 'APLICADO') {
                    $pneusDesabilitados[] = $pneu->id_pneu;
                    $pneusSelecionados[] = $pneu->id_pneu;
                }
            }

            // Buscar pneus já selecionados para o modelo específico em edição
            $pneusSelecionadosModelo = [];
            if ($idRequisicaoPneuModelos > 0) {
                $pneusSelecionadosModelo = RequisicaoPneuItens::where('id_requisicao_pneu_modelos', $idRequisicaoPneuModelos)
                    ->pluck('id_pneu')
                    ->toArray();

                Log::debug('Pneus selecionados para o modelo ' . $idRequisicaoPneuModelos . ': ' . implode(',', $pneusSelecionadosModelo));
            }

            return response()->json([
                'pneus' => $pneusFormatted,
                'desabilitados' => $pneusDesabilitados,
                'selecionados' => $pneusSelecionados,
                'selecionados_modelo' => $pneusSelecionadosModelo,
                'sem_estoque' => empty($pneus),
                'descricao_modelo' => $descricaoModelo,
                'id_modelo' => $idModelo
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Adicionar item ao detalhe (replica onAddDetailRequisicaoPneuModelosRequisicaoPneu)
     */
    public function adicionarItemDetalhe(Request $request)
    {
        // Bloquear adição de itens se houver pneus parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return response()->json([
                'error' => 'Operação bloqueada: existem pneus no depósito parados por mais de 24 horas.'
            ], 423);
        }

        try {
            $idRequisicao = $request->input('id_requisicao_pneu');
            $idOrdemServico = RequisicaoPneu::find($idRequisicao)->id_ordem_servico ?? RequisicaoPneu::find($idRequisicao)->id_solicitacao_pecas;
            $texto = RequisicaoPneu::find($idRequisicao)->id_ordem_servico ? 'Ordem de Serviço' : 'Requisição de Material';
            $idModelo = $request->input('id_modelo_pneu');
            $quantidade = $request->input('quantidade');
            $pneusSelecionados = $request->input('pneus_selecionados', []);
            $quantidadeBaixa = count($pneusSelecionados);
            $idRequisicaoModelo = $request->input('id_requisicao_pneu_modelos');
            $isEditMode = $request->input('is_edit_mode', false);

            // Validações do legado
            if (empty($idModelo)) {
                return response()->json(['error' => 'Selecione um modelo para continuar.'], 400);
            }

            if ($quantidadeBaixa == 0) {
                return response()->json(['error' => 'Selecione ao menos um número de fogo para continuar.'], 400);
            }

            if ($quantidadeBaixa > $quantidade) {
                return response()->json(['error' => 'A quantidade selecionada não pode ser maior que a solicitada'], 400);
            }

            DB::beginTransaction();

            // Se estiver em modo de edição, buscar o modelo específico pelo ID
            if ($isEditMode && $idRequisicaoModelo) {
                $requisicaoModelo = RequisicaoPneuModelos::findOrFail($idRequisicaoModelo);

                // Remover pneus antigos da tabela requisicao_pneu_itens
                $pneusAntigos = RequisicaoPneuItens::where('id_requisicao_pneu_modelos', $idRequisicaoModelo)
                    ->pluck('id_pneu')
                    ->toArray();

                RequisicaoPneuItens::where('id_requisicao_pneu_modelos', $idRequisicaoModelo)->delete();

                // Remover entradas do histórico dos pneus antigos
                foreach ($pneusAntigos as $idPneuAntigo) {
                    DB::connection('pgsql')->table('historicopneu')
                        ->where('id_pneu', $idPneuAntigo)
                        ->where('status_movimentacao', 'REQUISIÇÃO PNEU')
                        ->where('origem_operacao', 'SAIDA')
                        ->delete();
                }
            } else {
                // Modo criação: buscar ou criar o modelo
                $requisicaoModelo = RequisicaoPneuModelos::where([
                    'id_requisicao_pneu' => $idRequisicao,
                    'id_modelo_pneu' => $idModelo,
                ])->first();

                // Se não existe, criar novo
                if (!$requisicaoModelo) {
                    $requisicaoModelo = new RequisicaoPneuModelos();
                    $requisicaoModelo->id_requisicao_pneu = $idRequisicao;
                    $requisicaoModelo->id_modelo_pneu = $idModelo;
                    $requisicaoModelo->quantidade = $quantidade;
                    $requisicaoModelo->data_inclusao = now();

                    // Tentar buscar id_produto baseado no modelo de pneu se disponível
                    try {
                        $modeloPneu = ModeloPneu::find($idModelo);
                        if ($modeloPneu && isset($modeloPneu->id_produto)) {
                            $requisicaoModelo->id_produto = $modeloPneu->id_produto;
                            Log::debug("Definido id_produto $modeloPneu->id_produto para modelo $idModelo");
                        }
                    } catch (\Exception $e) {
                        Log::debug('Não foi possível definir id_produto automaticamente: ' . $e->getMessage());
                    }
                }
            }

            // Atualizar dados do modelo
            $requisicaoModelo->quantidade_baixa = $quantidadeBaixa;
            $requisicaoModelo->data_baixa = now()->format('Y-m-d');
            $requisicaoModelo->data_alteracao = now();
            $requisicaoModelo->save();

            // Adicionar novos itens
            foreach ($pneusSelecionados as $idPneu) {
                RequisicaoPneuItens::create([
                    'id_requisicao_pneu_modelos' => $requisicaoModelo->id_requisicao_pneu_modelos,
                    'id_pneu' => $idPneu,
                    'data_inclusao' => now(),
                ]);

                $idControleVidaPneu = Pneu::find($idPneu)->id_controle_vida_pneu;

                // Create history record
                DB::connection('pgsql')->table('historicopneu')->insert([
                    'data_inclusao' => now(),
                    'id_pneu' => $idPneu,
                    'id_usuario' => Auth::id(),
                    'id_modelo' => $idModelo,
                    'id_vida_pneu' => $idControleVidaPneu,
                    'status_movimentacao' => 'REQUISIÇÃO PNEU',
                    'origem_operacao' => 'SAIDA',
                    'observacoes_operacao' => 'Requisição de pneu para atender a ' . $texto .  '#' . $idOrdemServico,
                ]);
            }

            //Atualiza quantidade em estoque produtos_por_filial
            try {
                if ($isEditMode && $idRequisicaoModelo) {
                    // Para edição: calcular a diferença entre quantidade antiga e nova
                    $quantidadeAnterior = count($pneusAntigos);
                    $quantidadeNova = $quantidadeBaixa;
                    $diferenca = $quantidadeNova - $quantidadeAnterior;

                    Log::debug('Ajuste de estoque - Edição', [
                        'quantidade_anterior' => $quantidadeAnterior,
                        'quantidade_nova' => $quantidadeNova,
                        'diferenca' => $diferenca
                    ]);

                    if ($diferenca != 0) {
                        $this->atualizarQuantidadeEstoqueComDiferenca($idRequisicaoModelo, $diferenca);
                    }
                } else {
                    // Para criação: decrementar normalmente
                    $this->atualizarQuantidadeEstoque($idRequisicao);
                }
            } catch (\Exception $e) {
                Log::warning('Erro na atualização de estoque - continuando operação', [
                    'error' => $e->getMessage(),
                    'is_edit_mode' => $isEditMode,
                    'id_requisicao_modelo' => $idRequisicaoModelo
                ]);
                // Continua a execução mesmo com erro na atualização do estoque
            }

            // Atualizar situação da requisição
            $requisicaoPneu = RequisicaoPneu::find($idRequisicao);
            $requisicaoPneu->data_alteracao = now();
            $requisicaoPneu->situacao = 'BAIXADO PARCIAL';
            $requisicaoPneu->save();

            DB::commit();

            $message = $isEditMode ? 'Item editado com sucesso' : 'Item adicionado com sucesso';

            return response()->json([
                'success' => true,
                'message' => $message,
                'item' => $requisicaoModelo->load('modeloPneu'),
                'is_edit_mode' => $isEditMode,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Estornar requisição modelo (replica onEstorno)
     */
    public function estornarModelo($idRequisicaoModelo)
    {
        try {
            DB::beginTransaction();

            $modelo = RequisicaoPneuModelos::with('requisicaoPneuItens.pneu')->findOrFail($idRequisicaoModelo);

            // Verificar se há itens aplicados
            $temAplicados = $modelo->requisicaoPneuItens->some(function ($item) {
                return $item->pneu && $item->pneu->status_pneu === 'APLICADO';
            });

            if ($temAplicados) {
                return response()->json([
                    'error' => 'A requisição não pode ser estornada, pois contem itens selecionados ou aplicados.',
                ], 400);
            }

            // Ajusta o Estoque do modelo - devolver a quantidade baixada ao estoque
            $quantidadeBaixada = $modelo->quantidade_baixa ?? 0;
            $idRequisicao = $modelo->id_requisicao_pneu;

            if ($quantidadeBaixada > 0) {
                // Para estorno: usar diferença negativa (incrementar estoque)
                $this->atualizarQuantidadeEstoqueComDiferenca($idRequisicaoModelo, -$quantidadeBaixada);
                Log::debug("Estorno: devolvendo $quantidadeBaixada unidades ao estoque para o modelo $idRequisicaoModelo");
            }

            $modelo->quantidade_baixa = null;
            $modelo->data_baixa = null;
            $modelo->data_alteracao = now();

            $modelo->save();

            //deleta os itens relacionados
            $modelo->requisicaoPneuItens()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'A requisição foi estornada, com sucesso.',
                'redirect' => route('admin.saidaPneus.edit', $idRequisicao),
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Editar detalhe modelo (replica onEditDetailRequisicaoPneuModelos)
     */
    public function editarDetalheModelo(Request $request)
    {
        try {
            $idRequisicaoModelo = $request->input('id_requisicao_modelo');

            $modelo = RequisicaoPneuModelos::with([
                'modeloPneu',
                'requisicaoPneuItens',
            ])->findOrFail($idRequisicaoModelo);

            // Verificar situação da requisição
            $requisicao = RequisicaoPneu::find($modelo->id_requisicao_pneu);

            // Validação: só permite edição se quantidade baixa = 0 ou situação = BAIXADO PARCIAL
            if ($modelo->quantidade_baixa > 0 && $requisicao->situacao !== 'BAIXADO PARCIAL') {
                return response()->json([
                    'error' => 'Este item não pode ser editado pois já possui itens baixados.',
                ], 400);
            }

            // Obter IDs dos pneus já selecionados
            $pneusSelecionados = $modelo->requisicaoPneuItens->pluck('id_pneu')->toArray();
            Log::debug('Pneus Selecionados: ' . implode(',', $pneusSelecionados));

            // Retornar dados para popular o formulário
            return response()->json([
                'success' => true,
                'data' => [
                    'id_requisicao_pneu_modelos' => $modelo->id_requisicao_pneu_modelos,
                    'id_modelo_pneu' => $modelo->id_modelo_pneu,
                    'quantidade' => $modelo->quantidade,
                    'quantidade_baixa' => $modelo->quantidade_baixa ?? 0,
                    'data_baixa' => $modelo->data_baixa,
                    'pneus_selecionados' => $pneusSelecionados,
                    'modelo_descricao' => $modelo->modeloPneu->descricao_modelo ?? 'N/A',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validar requisição terceiro (replica onRequisicaoTerceiro)
     */
    public function validarRequisicaoTerceiro(Request $request)
    {
        try {
            $idTerceiro = $request->input('id_terceiro');

            $response = ['disable_transferencia' => false];

            if (! empty($idTerceiro)) {
                // Se há terceiro selecionado, desabilitar transferência entre filiais
                $response['disable_transferencia'] = true;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validar se baixa de pneu foi iniciada
     */
    private function validarBaixaPneuIniciada($idRequisicao)
    {
        $result = DB::connection('pgsql')->select('
            SELECT
            CASE
            WHEN (SELECT rp.id_usuario_estoque FROM requisicao_pneu AS rp WHERE rp.id_requisicao_pneu = ?) IS NOT NULL
            THEN TRUE
            ELSE FALSE
            END as baixa_iniciada
        ', [$idRequisicao]);

        return $result[0]->baixa_iniciada ?? false;
    }

    /**
     * Método para finalizar saída (replica onFinalizarSaida)
     */
    public function finalizarSaida($id)
    {
        try {
            Log::debug("Iniciando finalização da saída para requisição $id");
            $requisicao = RequisicaoPneu::with('requisicaoPneuModelos')->findOrFail($id);

            // Validações do legado
            $totalRequisitado = $requisicao->requisicaoPneuModelos->sum('quantidade');
            $totalBaixado = $requisicao->requisicaoPneuModelos->sum('quantidade_baixa');
            Log::debug("Total Requisitado: $totalRequisitado, Total Baixado: $totalBaixado");
            if ($totalRequisitado != $totalBaixado) {
                return redirect()->back()->with('warning', 'A quantidade selecionada não atende a quantidade requisitada!');
            }

            DB::beginTransaction();

            // Verificar se é transferência entre filiais
            if ($requisicao->transferencia_entre_filiais) {
                Log::debug('Iniciando transferência entre filiais');
                $this->baixarPneusTransferencia($id);
            }

            // Finalizar requisição
            $requisicao->update([
                'situacao' => 'FINALIZADA',
                'data_alteracao' => now(),
            ]);

            $this->atualizaStatusPneus($requisicao);

            //Atualizar o status da ordem servico para retirada da peça
            if (isset($requisicao->id_ordem_servico)) {
                DB::connection('pgsql')->table('ordem_servico')
                    ->where('id_ordem_servico', $requisicao->id_ordem_servico)
                    ->update(['id_status_ordem_servico' => 2]);
            }

            DB::commit();

            return redirect()->route('admin.saidaPneus.index')
                ->with('success', 'Saída de Pneus Finalizada com Sucesso!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Erro ao finalizar saída: ' . $e->getMessage());
        }
    }

    /**
     * Baixar pneus para transferência (replica baixarpneustransferencia)
     */
    private function baixarPneusTransferencia($idRequisicaoPneu)
    {
        $userId = Auth::id();
        $requisicao = RequisicaoPneu::find($idRequisicaoPneu);

        try {
            db::beginTransaction();

            // Inserir na tabela transferencia_pneus
            $transferencia = new TransferenciaPneus();

            $transferencia->data_inclusao    = now();
            $transferencia->id_filial        = $requisicao->id_filial_destino;
            $transferencia->aprovado         = 1;
            $transferencia->situacao         = 'AGUARDANDO RECEBIMENTO';
            $transferencia->id_usuario       = $userId;
            $transferencia->observacao_saida = 'TRANSFERENCIA DE PNEU POR REQUISIÇÃO';
            $transferencia->recebido         = 0;
            $transferencia->id_saida_pneu    = $idRequisicaoPneu;

            $transferencia->save();

            // Processar modelos
            foreach ($requisicao->requisicaoPneuModelos as $modelo) {
                $pneuIds = $modelo->requisicaoPneuItens->pluck('id_pneu')->implode(',');

                $modeloTransferencia = new TransferenciaPneusModelos();

                $modeloTransferencia->data_inclusao = now();
                $modeloTransferencia->id_modelos_requisitados = $modelo->id_modelo_pneu;
                // armazenar o id inteiro da transferência, não o objeto
                $modeloTransferencia->id_transferencia_pneu = $transferencia->id_transferencia_pneus;
                $modeloTransferencia->quantidade = $modelo->quantidade;

                $modeloTransferencia->save();

                // Inserir itens individuais
                foreach ($modelo->requisicaoPneuItens as $item) {
                    DB::connection('pgsql')->table('transferencia_pneu_itens')->insert([
                        'data_inclusao' => now(),
                        // usar o id inteiro do modelo de transferência
                        'id_transferencia_modelo' => $modeloTransferencia->id_transferencia_pneus_modelos,
                        'id_pneu' => $item->id_pneu,
                        'recebido' => false,
                    ]);

                    $idControleVidaPneu = Pneu::find($item->id_pneu)->id_controle_vida_pneu;
                    $idModelo = Pneu::find($item->id_pneu)->id_modelo_pneu;

                    // Create history record
                    DB::connection('pgsql')->table('historicopneu')->insert([
                        'data_inclusao' => now(),
                        'id_pneu' => $item->id_pneu,
                        'id_usuario' => Auth::id(),
                        'id_modelo' => $idModelo,
                        'id_vida_pneu' => $idControleVidaPneu,
                        'status_movimentacao' => 'TRANSFERENCIA PNEU',
                        'origem_operacao' => 'SAIDA',
                        'observacoes_operacao' => 'Transferência de pneu para a O.S.#' . $requisicao->id_requisicao_pneu,
                    ]);
                }
            }

            db::commit();
        } catch (\Exception $e) {
            log::error('Erro ao criar transferência de pneus: ' . $e->getMessage());
            db::rollback();
            throw new \Exception('Erro ao criar transferência: ' . $e->getMessage());
        }
    }

    public function assumirBaixa($id)
    {
        // Bloquear assumir baixa se houver pneus parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível assumir baixa enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        try {
            $requisicao = RequisicaoPneu::findOrFail($id);

            // Validate if already assumed
            // Bloquear finalização se houver pneus no depósito parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Não é possível finalizar saídas enquanto houver pneus no depósito parados por mais de 24 horas.',
                    'duration' => 8000,
                ]);
            }

            if ($requisicao->id_usuario_estoque) {
                return redirect()->back()->with('notification', [
                    'type' => 'warning',
                    'title' => 'Atenção',
                    'message' => 'Esta Saída de Pneus já foi iniciada.',
                    'duration' => 5000, // opcional (padrão: 5000ms)
                ]);
            }

            // Check status permissions
            if ($requisicao->situacao !== 'APROVADO') {
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Atenção',
                    'message' => 'Apenas requisições aprovadas podem ter baixa assumida.',
                    'duration' => 5000, // opcional (padrão: 5000ms)
                ]);
            }

            DB::beginTransaction();

            // Update requisition status
            $requisicao->update([
                'id_usuario_estoque' => Auth::id(),
                'situacao' => 'INICIADA',
                'data_alteracao' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.saidaPneus.edit', $id)
                ->with('success', 'Baixa de pneus assumida com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Erro ao assumir baixa: ' . $e->getMessage());
        }
    }

    public function visualizar($id)
    {
        $requisicao = RequisicaoPneu::with([
            'filial',
            'usuarioSolicitante',
            'usuarioEstoque',
            'requisicaoPneuModelos.modeloPneu',
            'requisicaoPneuModelos.requisicaoPneuItens.pneu',
        ])->findOrFail($id);

        return view('admin.saidaPneus.visualizar', compact('requisicao'));
    }

    /**
     * Lista de filtros válidos para exportação
     *
     * @return array
     */
    protected function getValidExportFilters()
    {
        return [
            'data_inicial',
            'data_final',
            'situacao',
            'id_filial',
            'id_usuario_solicitante',
        ];
    }

    /**
     * Exportar para PDF
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildExportQuery($request);

        return $this->exportToPdf(
            $request,
            $query,
            'admin.saidaPneus.pdf',
            'saida_pneus',
            $this->getValidExportFilters()
        );
    }

    /**
     * Exportar para CSV
     *
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_requisicao_pneu' => 'Cód',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'usuarioSolicitante.name' => 'Usuário Solicitante',
            'situacao' => 'Situação',
            'usuarioEstoque.name' => 'Usuário Estoque',
            'filial.name' => 'Filial',
        ];

        return $this->exportToCsv($request, $query, $columns, 'saida_pneus', $this->getValidExportFilters());
    }

    /**
     * Exportar para Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_requisicao_pneu' => 'Cód',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'usuarioSolicitante.name' => 'Usuário Solicitante',
            'situacao' => 'Situação',
            'usuarioEstoque.name' => 'Usuário Estoque',
            'filial.name' => 'Filial',
        ];

        return $this->exportToExcel($request, $query, $columns, 'saida_pneus', $this->getValidExportFilters());
    }

    /**
     * Exportar para XML
     *
     * @return \Illuminate\Http\Response
     */
    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_requisicao_pneu',
            'data_inclusao' => 'data_inclusao',
            'data_alteracao' => 'data_alteracao',
            'usuario_solicitante' => 'usuarioSolicitante.name',
            'situacao' => 'situacao',
            'usuario_estoque' => 'usuarioEstoque.name',
            'filial' => 'filial.name',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'saida_pneus',
            'saida_pneu',
            'saida_pneus',
            $this->getValidExportFilters()
        );
    }

    private function buildExportQuery(Request $request)
    {
        $userFilialId = Auth::user()->filial_id ?? 1;

        $query = RequisicaoPneu::query()
            ->with([
                'filial',
                'usuarioSolicitante',
                'usuarioEstoque',
            ]);

        // Apply same filtering logic as index
        if ($userFilialId == 1) {
            $query->where('id_filial', '!=', 0);
        } else {
            $query->where('id_filial', $userFilialId);
        }

        // Apply filters from session or request
        if ($request->filled('data_inicial')) {
            $query->where('data_inclusao', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->where('data_inclusao', '<=', $request->data_final);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_usuario_solicitante')) {
            $query->where('id_usuario_solicitante', $request->id_usuario_solicitante);
        }

        return $query->orderByDesc('id_requisicao_pneu');
    }

    // Check if user can edit (legacy condition)
    public function canEdit($requisicao)
    {
        return $requisicao->situacao !== 'FINALIZADA';
    }

    // Check if user can baixar itens (legacy condition)
    public function canBaixarItens($requisicao)
    {
        return $requisicao->situacao === 'APROVADO';
    }

    /**
     * Processar baixa individual de pneus
     */
    public function baixarPneus(Request $request, $id)
    {
        try {
            // Bloquear baixa de pneus se houver pneus no depósito parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Não é possível baixar pneus enquanto houver pneus no depósito parados por mais de 24 horas.',
                    'duration' => 8000,
                ]);
            }

            $requisicao = RequisicaoPneu::findOrFail($id);

            // Validate if requisition can be processed
            if ($requisicao->situacao !== 'INICIADA') {
                return redirect()->back()->with('error', 'Apenas requisições iniciadas podem ter pneus baixados.');
            }

            $pneusIds = $request->input('pneus_selecionados', []);

            if (empty($pneusIds)) {
                return redirect()->back()->with('error', 'Selecione pelo menos um pneu para baixar.');
            }

            DB::beginTransaction();

            // Update selected pneus status and create history records
            $pneus = Pneu::whereIn('id_pneu', $pneusIds)->get();

            foreach ($pneus as $pneu) {
                // Update pneu status
                $pneu->update([
                    'status_pneu' => 'TRANSFERENCIA',
                    'data_alteracao' => now(),
                ]);

                // Create history record
                DB::connection('pgsql')->table('historicopneu')->insert([
                    'data_inclusao' => now(),
                    'id_pneu' => $pneu->id_pneu,
                    'id_usuario' => Auth::user()->id,
                    'operacao' => 'SAIDA_REQUISICAO',
                    'observacao' => 'Baixa por requisição #' . $requisicao->id_requisicao_pneu,
                ]);
            }

            // Update quantities in requisicao_pneu_modelos
            foreach ($requisicao->requisicaoPneuModelos as $modelo) {
                $pneusBaixados = $modelo->requisicaoPneuItens->whereIn('id_pneu', $pneusIds)->count();
                if ($pneusBaixados > 0) {
                    $modelo->update([
                        'quantidade_baixa' => ($modelo->quantidade_baixa ?? 0) + $pneusBaixados,
                    ]);
                }
            }

            // Check if all items have been processed
            $totalRequisitado = $requisicao->requisicaoPneuModelos->sum('quantidade');
            $totalBaixado = $requisicao->requisicaoPneuModelos->sum('quantidade_baixa');

            if ($totalBaixado >= $totalRequisitado) {
                $requisicao->update([
                    'situacao' => 'FINALIZADA',
                    'data_alteracao' => now(),
                ]);
            } elseif ($totalBaixado > 0) {
                $requisicao->update([
                    'situacao' => 'BAIXADO PARCIAL',
                    'data_alteracao' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Pneus baixados com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Erro ao baixar pneus: ' . $e->getMessage());
        }
    }

    /**

     * Update requisition data
     */
    public function update(Request $request, $id)
    {
        try {
            // Bloquear atualização se houver pneus parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Não é possível atualizar requisições enquanto houver pneus no depósito parados por mais de 24 horas.',
                    'duration' => 8000,
                ]);
            }

            $requisicao = RequisicaoPneu::findOrFail($id);

            // Validate edit permissions
            if ($requisicao->situacao === 'FINALIZADA') {
                return redirect()->back()->with('error', 'Requisições finalizadas não podem ser editadas.');
            }

            DB::beginTransaction();

            // Update editable fields
            $requisicao->update([
                'observacao' => $request->input('observacao'),
                'justificativa_de_finalizacao' => $request->input('justificativa_de_finalizacao'),
                'data_alteracao' => now(),
                'id_usuario_estoque' => Auth::id(),
                'transferencia_entre_filiais' => $request->input('transferencia_entre_filiais', false),
                'id_filial_destino' => $request->input('id_filial_destino'),
            ]);

            // Handle file upload if exists
            if ($request->hasFile('documento_autorizacao')) {
                $file = $request->file('documento_autorizacao');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('documentos/requisicoes', $filename, 'public');
                $requisicao->documento_autorizacao = $path;
                $requisicao->save();
            }

            // Update dates for pneus baixados if provided
            if ($request->has('data_baixa')) {
                foreach ($request->input('data_baixa') as $idPneu => $dataBaixa) {
                    if (! empty($dataBaixa)) {
                        // Update in requisicao_pneu_itens
                        DB::connection('pgsql')
                            ->table('requisicao_pneu_itens')
                            ->where('id_pneu', $idPneu)
                            ->update(['data_baixa' => $dataBaixa]);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Requisição atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Erro ao atualizar requisição: ' . $e->getMessage());
        }
    }

    /**
     * Estornar requisição
     */
    public function estornar($id)
    {
        Log::debug('Estornar');
        // try {
        //     $requisicao = RequisicaoPneu::findOrFail($id);

        //     if ($requisicao->situacao === 'FINALIZADA') {
        //         return redirect()->back()->with('error', 'Requisições finalizadas não podem ser estornadas.');
        //     }

        //     DB::beginTransaction();

        //     // Revert all pneus back to ESTOQUE status and restore produtos_por_filial
        //     foreach ($requisicao->requisicaoPneuModelos as $modelo) {
        //         // guardar quantidade baixada antes de resetar
        //         $quantidadeBaixa = (int) ($modelo->quantidade_baixa ?? 0);
        //         $idProdutoModelo = $modelo->id_produto ?? null;

        //         foreach ($modelo->requisicaoPneuItens as $item) {
        //             if ($item->pneu->status_pneu !== 'ESTOQUE') {
        //                 $item->pneu->update([
        //                     'status_pneu' => 'ESTOQUE',
        //                     'data_alteracao' => now(),
        //                 ]);

        //                 $idControleVidaPneu = Pneu::find($item->id_pneu)->id_controle_vida_pneu;
        //                 $idModelo = Pneu::find($item->id_pneu)->id_modelo_pneu;

        //                 // Create history record
        //                 DB::connection('pgsql')->table('historicopneu')->insert([
        //                     'data_inclusao' => now(),
        //                     'id_pneu' => $item->id_pneu,
        //                     'id_usuario' => Auth::user()->id,
        //                     'operacao' => 'ESTORNO_REQUISICAO',
        //                     'observacao' => 'Estorno da requisição #' . $requisicao->id_requisicao_pneu,

        //                 ]);
        //             }
        //         }

        //         // Se havia quantidade baixada, restaurar no estoque por filial
        //         if ($idProdutoModelo && $quantidadeBaixa > 0) {
        //             $idFilialReq = $requisicao->id_filial;

        //             if (! $idFilialReq) {
        //                 throw new \Exception(sprintf(
        //                     'Filial inválida para requisição %s. Estorno não realizado.',
        //                     $requisicao->id_requisicao_pneu
        //                 ));
        //             }

        //             $exists = DB::connection('pgsql')->table('produtos_por_filial')
        //                 ->where('id_produto', $idProdutoModelo)
        //                 ->where('id_filial', $idFilialReq)
        //                 ->exists();

        //             if ($exists) {
        //                 DB::connection('pgsql')->table('produtos_por_filial')
        //                     ->where('id_produto', $idProdutoModelo)
        //                     ->where('id_filial', $idFilialReq)
        //                     ->increment('quantidade_estoque', $quantidadeBaixa);
        //             } else {
        //                 // Não inserir novo registro — tratar como erro para evitar inconsistência
        //                 throw new \Exception(sprintf(
        //                     'Registro produtos_por_filial não encontrado (id_produto=%s, id_filial=%s). Estorno não realizado.',
        //                     $idProdutoModelo,
        //                     $idFilialReq
        //                 ));
        //             }
        //         }

        //         // Reset quantidade_baixa após restaurar estoque
        //         $modelo->update(['quantidade_baixa' => 0]);
        //     }

        //     // Update requisition status
        //     $requisicao->update([
        //         'situacao' => 'APROVADO',
        //         'data_alteracao' => now(),
        //     ]);

        //     DB::commit();

        //     return redirect()->route('admin.saidaPneus.index')
        //         ->with('success', 'Requisição estornada com sucesso!');
        // } catch (\Exception $e) {
        //     DB::rollback();

        //     return redirect()->back()->with('error', 'Erro ao estornar requisição: ' . $e->getMessage());
        // }
    }

    /**
     * Finalizar saída de pneu
     */
    public function finalizar($id)
    {
        try {
            // Bloquear finalização se houver pneus parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Não é possível finalizar requisições enquanto houver pneus no depósito parados por mais de 24 horas.',
                    'duration' => 8000,
                ]);
            }

            $requisicao = RequisicaoPneu::findOrFail($id);

            if ($requisicao->situacao === 'FINALIZADA') {
                return redirect()->back()->with('error', 'Esta requisição já foi finalizada.');
            }

            // Check if there are any pneus to finalize
            $totalRequisitado = $requisicao->requisicaoPneuModelos->sum('quantidade');
            $totalBaixado = $requisicao->requisicaoPneuModelos->sum('quantidade_baixa');

            if ($totalBaixado == 0) {
                return redirect()->back()->with('error', 'Não é possível finalizar sem pneus baixados.');
            }

            DB::beginTransaction();

            // Finalize the requisition
            $requisicao->update([
                'situacao' => 'FINALIZADA',
                'data_alteracao' => now(),
            ]);

            // Create history records for finalization
            foreach ($requisicao->requisicaoPneuModelos as $modelo) {
                foreach ($modelo->requisicaoPneuItens as $item) {
                    if ($item->pneu->status_pneu === 'TRANSFERENCIA') {
                        // Update final status based on requisition type
                        $finalStatus = $requisicao->venda ? 'VENDA' : 'APLICADO';

                        $item->pneu->update([
                            'status_pneu' => $finalStatus,
                            'data_alteracao' => now(),
                        ]);

                        // Create history record
                        DB::connection('pgsql')->table('historicopneu')->insert([
                            'data_inclusao' => now(),
                            'id_pneu' => $item->id_pneu,
                            'id_usuario' => Auth::user()->id,
                            'operacao' => 'FINALIZACAO_REQUISICAO',
                            'observacao' => 'Finalização da requisição # ' . $requisicao->id_requisicao_pneu,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.saidaPneus.index')
                ->with('success', 'Saída de pneu finalizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'Erro ao finalizar saída: ' . $e->getMessage());
        }
    }

    /**
     * Imprimir documento de venda
     */
    public function imprimir($id)
    {
        try {
            $requisicao = RequisicaoPneu::with([
                'filial',
                'usuarioSolicitante',
                'usuarioEstoque',
                'terceiro',
                'requisicaoPneuModelos.modeloPneu',
                'requisicaoPneuModelos.requisicaoPneuItens.pneu',
            ])->findOrFail($id);

            // Calculate totals
            $totalQuantidade = $requisicao->requisicaoPneuModelos->sum('quantidade');
            $totalQuantidadeBaixa = $requisicao->requisicaoPneuModelos->sum('quantidade_baixa');
            $valorTotalGeral = $requisicao->requisicaoPneuModelos->sum('valor_total');

            return view('admin.saidaPneus.imprimir', compact(
                'requisicao',
                'totalQuantidade',
                'totalQuantidadeBaixa',
                'valorTotalGeral'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao gerar documento: ' . $e->getMessage());
        }
    }

    /**
     * Limpar formulário (replica onClear)
     */
    public function limparFormulario(Request $request)
    {
        try {
            // Retornar dados limpos para o formulário
            return response()->json([
                'success' => true,
                'clear_data' => [
                    'requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu' => '',
                    'requisicao_pneu_modelos_requisicao_pneu_id_requisicao_pneu_modelos' => '',
                    'requisicao_pneu_modelos_requisicao_pneu_quantidade' => '',
                    'id_pneus' => [],
                    'requisicao_pneu_modelos_requisicao_pneu_quantidade_baixa' => '',
                    'requisicao_pneu_modelos_requisicao_pneu_data_baixa' => '',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obter dados para edição de modelo específico
     */
    public function obterDadosEdicao($idRequisicao, $idModelo)
    {
        try {
            // Buscar modelo existente na requisição
            $modelo = RequisicaoPneuModelos::where('id_requisicao_pneu', $idRequisicao)
                ->where('id_modelo_pneu', $idModelo)
                ->with(['modeloPneu', 'requisicaoPneuItens'])
                ->first();

            if ($modelo) {
                // Carregar pneus para este modelo
                $pneusResponse = $this->carregarPneus(new Request([
                    'id_modelo_pneu' => $idModelo,
                    'id_requisicao_pneu_modelos' => $modelo->id_requisicao_pneu_modelos,
                ]));

                $pneusData = json_decode($pneusResponse->getContent(), true);
                $pneusSelecionados = $modelo->requisicaoPneuItens->pluck('id_pneu')->toArray();

                return response()->json([
                    'success' => true,
                    'modelo' => [
                        'id_requisicao_pneu_modelos' => $modelo->id_requisicao_pneu_modelos,
                        'quantidade' => $modelo->quantidade,
                        'quantidade_baixa' => $modelo->quantidade_baixa ?? 0,
                        'data_baixa' => $modelo->data_baixa,
                    ],
                    'pneus' => $pneusData['pneus'] ?? [],
                    'pneus_selecionados' => $pneusSelecionados,
                    'pneus_desabilitados' => $pneusData['desabilitados'] ?? [],
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Modelo não encontrado']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validar se baixa de pneu foi iniciada (método público)
     */
    public function validarBaixaIniciada($idRequisicao)
    {
        try {
            $baixaIniciada = $this->validarBaixaPneuIniciada($idRequisicao);

            return response()->json([
                'baixa_iniciada' => $baixaIniciada,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verificar permissões de edição baseado no legado
     */
    public function verificarPermissoesEdicao($requisicao)
    {
        $userId = Auth::id();
        $usuariosPermitidos = [423]; // IDs dos usuários com permissão especial
        $situacao = $requisicao->situacao;

        // Validação: pode editar se situação != FINALIZADA
        if ($situacao !== 'FINALIZADA') {
            return true;
        }

        return false;
    }

    /**
     * Verificar se pode baixar itens
     */
    public function verificarPermissaoBaixa($requisicao)
    {
        return $requisicao->situacao === 'APROVADO';
    }

    /**
     * Método para obter nome do form (compatibility)
     */
    public static function getFormName()
    {
        return 'form_RequisicaoPneuForm';
    }

    /**
     * Método para replicar funcionalidade onShow do legado
     */
    public function onShow($param = null)
    {
        // Método mantido para compatibilidade, mas funcionalidade movida para index
        return $this->index(request());
    }

    /**
     * Método para gerenciar row da datagrid (compatibility com legado)
     */
    public static function manageRow($id, $param = [])
    {
        // Método mantido para compatibilidade, mas não usado no Laravel
        return response()->json(['success' => true]);
    }

    /**
     * Atualizar estoque baseado na diferença de quantidades (para edições)
     */
    public function atualizarQuantidadeEstoqueComDiferenca($idRequisicaoModelo, $diferenca)
    {
        if ($diferenca == 0) {
            return; // Não há diferença, não faz nada
        }

        try {
            $modelo = RequisicaoPneuModelos::with('requisicao')->findOrFail($idRequisicaoModelo);


            $idProduto = $modelo->id_produto;
            $idFilial = $modelo->requisicao ? $modelo->requisicao->id_filial : null;

            // Se não tiver id_produto ou id_filial, pular a atualização de estoque
            if (!$idProduto) {
                Log::warning("Modelo $idRequisicaoModelo não possui id_produto definido - pulando atualização de estoque");
                return;
            }

            if (!$idFilial) {
                Log::warning("Modelo $idRequisicaoModelo não possui id_filial definido - pulando atualização de estoque");
                return;
            }

            // Verificar se existe registro na tabela produtos_por_filial
            $exists = DB::connection('pgsql')
                ->table('produtos_por_filial')
                ->where('id_produto_unitop', $idProduto)
                ->where('id_filial', $idFilial)
                ->exists();

            if (!$exists) {
                Log::warning("Registro produtos_por_filial não encontrado (id_produto=$idProduto, id_filial=$idFilial) - pulando atualização");
                return;
            }

            // Aplicar a diferença no estoque
            $query = DB::connection('pgsql')->table('produtos_por_filial')
                ->where('id_produto_unitop', $idProduto)
                ->where('id_filial', $idFilial);

            if ($diferenca > 0) {
                // Quantidade aumentou: decrementar estoque
                $query->decrement('quantidade_produto', abs($diferenca));
                Log::info("Decrementando estoque em " . abs($diferenca) . " unidades (produto: $idProduto, filial: $idFilial)");
            } else {
                // Quantidade diminuiu: incrementar estoque
                $query->increment('quantidade_produto', abs($diferenca));
                Log::info("Incrementando estoque em " . abs($diferenca) . " unidades (produto: $idProduto, filial: $idFilial)");
            }

            $query->update(['data_alteracao' => now()]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar estoque com diferença', [
                'id_requisicao_modelos' => $idRequisicaoModelo,
                'diferenca' => $diferenca,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Não lançar exceção para não quebrar o fluxo principal
            // apenas logar o erro
        }
    }

    public function atualizarQuantidadeEstoque($requisicao, $incrementar = false)
    {
        // aceita tanto o objeto RequisicaoPneu quanto o id da requisição
        $idRequisicao = is_int($requisicao) ? $requisicao : ($requisicao->id_requisicao_pneu ?? null);
        $filialId = is_object($requisicao) ? ($requisicao->id_filial ?? null) : null;

        if (! $idRequisicao) {
            return;
        }

        $modelos = RequisicaoPneuModelos::where('id_requisicao_pneu', $idRequisicao)->get();

        try {
            foreach ($modelos as $modelo) {
                $idProduto = $modelo->id_produto ?? null;
                $quantidade = (int) ($modelo->quantidade_baixa ?? $modelo->quantidade ?? 0);

                if (! $idProduto || $quantidade <= 0) {
                    throw new \Exception("Produto inválido ou quantidade insuficiente (id_produto=$idProduto, quantidade=$quantidade)");
                }

                $idFilial = $filialId ?? DB::connection('pgsql')
                    ->table('requisicao_pneu')
                    ->where('id_requisicao_pneu', $idRequisicao)
                    ->value('id_filial');

                if (! $idFilial) {
                    continue;
                }

                $exists = ProdutosPorFilial::where('id_produto_unitop', $idProduto)
                    ->where('id_filial', $idFilial)
                    ->exists();

                if ($exists) {
                    $query = DB::connection('pgsql')->table('produtos_por_filial')
                        ->where('id_produto_unitop', $idProduto)
                        ->where('id_filial', $idFilial);

                    if ($incrementar) {
                        $query->increment('quantidade_produto', $quantidade);
                    } else {
                        $query->decrement('quantidade_produto', $quantidade);
                    }

                    $query->update(['data_alteracao' => now()]);
                } else {
                    Log::debug("Registro não encontrado para produto $idProduto na filial $idFilial");
                    throw new \Exception(sprintf(
                        'Registro produtos_por_filial não encontrado (id_produto=%s, id_filial=%s). Atualização de estoque não realizada.',
                        $idProduto,
                        $idFilial
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception('Erro ao atualizar quantidade em estoque: ' . $e->getMessage());
        }
    }

    public function atualizaStatusPneus($requisicao)
    {
        // Atualiza o status dos pneus de 'ESTOQUE' PARA 'DEPOSITO' caso haja ordem de serviço
        if (isset($requisicao->id_ordem_servico)) {
            $novoStatus = 'DEPOSITO';
        } else {
            $novoStatus = 'ESTOQUE';
        }

        if ($requisicao->transferencia_entre_filiais) {
            $novoStatus = 'TRANSFERENCIA';
        }

        foreach ($requisicao->requisicaoPneuModelos as $modelo) {
            foreach ($modelo->requisicaoPneuItens as $item) {
                $item->pneu->update([
                    'status_pneu' => $novoStatus,
                    'data_alteracao' => now(),
                ]);
            }
        }
    }
}
