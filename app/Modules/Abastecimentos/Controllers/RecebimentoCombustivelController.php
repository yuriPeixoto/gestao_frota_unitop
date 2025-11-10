<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Abastecimentos\Models\RecebimentoCombustivel;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\ItensPedidos;
use App\Models\PedidoCompra;
use App\Modules\Abastecimentos\Models\Tanque;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\IntegracaoWhatssappCarvalimaService;

class RecebimentoCombustivelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RecebimentoCombustivel::with([
            'filial',
            'tanque',
            'tanque.tipoCombustivel',
            'fornecedor'
        ]);

        // Lista de filtros diretos (que existem na tabela recebimento_combustivel)
        $filters = [
            'id_recebimento_combustivel',
            'numeronotafiscal',
            'numero_nf2',
            'numero_nf3',
            'numero_nf4',
            'id_tanque',
            'id_fornecedor',
            'id_filial',
            'quantidade',
        ];

        foreach ($request->only($filters) as $campo => $valor) {
            if (!empty($valor)) {
                $query->where($campo, $valor);
            }
        }

        // Filtro por situação
        if ($request->filled('situacao_nf')) {
            if ($request->situacao_nf === 'null') {
                $query->whereNull('situacao_nf');
            } else {
                $query->where('situacao_nf', $request->situacao_nf);
            }
        }

        // Filtro pelo Tipo de Combustível (Relacionamento Indireto)
        if ($request->filled('id_tipo_combustivel')) {
            $query->whereHas('tanque.tipoCombustivel', function ($q) use ($request) {
                $q->where('id_tipo_combustivel', $request->id_tipo_combustivel);
            });
        }

        // Filtros para datas
        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', $request->data_inclusao);
        }

        if ($request->filled('data_alteracao')) {
            $query->whereDate('data_alteracao', $request->data_alteracao);
        }

        if ($request->filled('data_entrada')) {
            $query->whereDate('data_entrada', $request->data_entrada);
        }

        // Paginação ordenada pelo ID mais recente
        $recebimentoCombustiveis = $query->latest('id_recebimento_combustivel')->paginate(10);

        // Adiciona informações para a visualização
        foreach ($recebimentoCombustiveis as $recebimento) {
            // Adiciona nome do tanque para facilitar a exibição na tabela
            $recebimento->tanque_nome = optional($recebimento->tanque)->tanque;

            // Adiciona nome do fornecedor para facilitar a exibição na tabela
            $recebimento->nome_fornecedor = optional($recebimento->fornecedor)->nome_fornecedor;

            // Adiciona nome da filial para facilitar a exibição na tabela
            $recebimento->filial_nome = optional($recebimento->filial)->name;

            // Adiciona nome do tipo de combustível para facilitar a exibição na tabela
            $recebimento->tipo_combustivel_nome = optional(optional($recebimento->tanque)->tipoCombustivel)->descricao;
        }

        // Se for requisição HTMX, retorna apenas a tabela parcial
        if ($request->header('HX-Request')) {
            return view('admin.recebimentocombustiveis._table', compact('recebimentoCombustiveis'));
        }

        // Obter dados de referência em cache
        $referenceDatas = $this->getReferenceDatas();

        return view('admin.recebimentocombustiveis.index', array_merge(
            compact('recebimentoCombustiveis'),
            $referenceDatas
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $filiais = Cache::remember('filiais', now()->addMinutes(15), function () {
            return Filial::select('id as value', 'name as label')->get();
        });

        // Uso do novo método para fornecedores ativos
        $fornecedores = Fornecedor::buscarFornecedoresAtivos($term = "", $limit = 20);

        // Uso do método para tanques internos
        $tanques = Tanque::tanquesPorFilial(true, GetterFilial());

        return view('admin.recebimentocombustiveis.create', compact('filiais', 'fornecedores', 'tanques'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $idtanque      = $request->id_tanque;
            $filial        = $request->id_filial;
            $valortotal    = (float) str_replace(',', '.', str_replace('.', '', $request->preco_total_item ?? '0'));
            $id_fornecedor = $request->id_fornecedor;
            $id_nf         = $request->numeronotafiscal;
            $idpedido      = $request->id_pedido;
            $quantidade    = 0;
            $estoqueatual  = null;
            $situacao      = null;

            if (isset($id_nf)) {
                $checkNF = RecebimentoCombustivel::where('numeronotafiscal', $id_nf)->first();
                $situacao = $checkNF ? $checkNF->situacao_nf : null;
            }

            if ($situacao == 'FINALIZADO') {
                return redirect()
                    ->back()
                    ->with('error', 'Esta NF já está finalizada no sistema.');
            }

            if ($situacao != 'AGUARDANDO LANÇAMENTO DE NF') {
                if (isset($idtanque)) {
                    $tanque = Tanque::findOrFail($idtanque);

                    if (!empty($request->volume_convertido)) {
                        if (in_array($tanque->combustivel, [4, 5])) {
                            $quantidade = (float) $request->volume_convertido;
                        } else {
                            $quantidade = (float) str_replace(',', '.', str_replace('.', '', $request->volume_convertido));
                        }

                        if (empty($quantidade)) {
                            return redirect()
                                ->back()
                                ->with('error', 'Erro ao tentar salvar o recebimento de combustível: quantidade inválida');
                        }
                    } else {
                        // Se não tiver volume_convertido, tenta usar o campo quantidade
                        $quantidade = (float) str_replace(',', '.', str_replace('.', '', $request->quantidade ?? '0'));
                    }
                }
            }

            // Verificar estoque se não estiver aguardando lançamento
            if ($situacao != 'AGUARDANDO LANÇAMENTO DE NF') {
                if (!$this->VerificarEstoqueEQuantidade($quantidade, $filial, $idtanque)) {
                    $result = DB::connection('pgsql')->select("
                                SELECT quantidade_em_estoque FROM estoque_combustivel
                                WHERE id_tanque = ? AND data_encerramento IS NULL LIMIT 1", [$idtanque]);


                    if ($result) {
                        $estoqueatual = $result[0]->quantidade_em_estoque;
                    }

                    $estoqueFormatado = number_format($estoqueatual ?? 0, 2, ',', '.');
                    return redirect()
                        ->back()
                        ->with('error', 'Atenção: a quantidade informada está superior à capacidade do tanque. Estoque atual de: ' . $estoqueFormatado);
                }
            }

            // Obter objeto antigo para comparação de valor unitário
            $old_object = RecebimentoCombustivel::where('id_tanque', $idtanque)
                ->orderBy('data_inclusao', 'desc')
                ->first();

            // Criar novo registro
            $recebimentoCombustiveis = new RecebimentoCombustivel();
            $recebimentoCombustiveis->data_inclusao = now();
            $recebimentoCombustiveis->id_filial               = $request->id_filial;
            $recebimentoCombustiveis->id_tanque               = $request->id_tanque;
            $recebimentoCombustiveis->id_fornecedor           = $request->id_fornecedor;
            $recebimentoCombustiveis->data_entrada            = $request->data_entrada;
            $recebimentoCombustiveis->quantidade              = $this->formatarNumeroParaBanco($request->quantidade);
            $recebimentoCombustiveis->preco_total_item        = $this->formatarNumeroParaBanco($request->preco_total_item);
            $recebimentoCombustiveis->valor_frete             = $this->formatarNumeroParaBanco($request->valor_frete);
            $recebimentoCombustiveis->despesa_acessoria       = $this->formatarNumeroParaBanco($request->despesa_acessoria);
            $recebimentoCombustiveis->numeronotafiscal        = $request->numeronotafiscal;
            $recebimentoCombustiveis->valor_unitario          = $this->formatarNumeroParaBanco($request->valor_unitario);
            $recebimentoCombustiveis->chave_nf                = $request->chave_nf;
            $recebimentoCombustiveis->temperatura_combustivel = $this->formatarNumeroParaBanco($request->temperatura_combustivel);
            $recebimentoCombustiveis->densidade_combustivel   = $this->formatarNumeroParaBanco($request->densidade_combustivel);
            $recebimentoCombustiveis->volume_convertido       = $this->formatarNumeroParaBanco($request->volume_convertido);
            $recebimentoCombustiveis->id_pedido               = $request->id_pedido;
            $recebimentoCombustiveis->numero_nf2              = $request->numero_nf2;
            $recebimentoCombustiveis->numero_nf3              = $request->numero_nf3;
            $recebimentoCombustiveis->numero_nf4              = $request->numero_nf4;
            $recebimentoCombustiveis->chave_nf2               = $request->chave_nf2;
            $recebimentoCombustiveis->chave_nf3               = $request->chave_nf3;
            $recebimentoCombustiveis->chave_nf4               = $request->chave_nf4;
            $recebimentoCombustiveis->id_user                 = Auth::user()->id;
            $recebimentoCombustiveis->situacao_nf             = 'FINALIZADO';
            $recebimentoCombustiveis->save();

            // Atualizar estoque se não estiver aguardando lançamento
            if ($situacao != 'AGUARDANDO LANÇAMENTO DE NF' && $quantidade > 0) {
                $this->atualizarEstoque($quantidade, $idtanque, $filial, $valortotal);
            }

            if ($recebimentoCombustiveis->id_recebimento_combustivel) {
                // Verificar se houve alteração no valor unitário e enviar notificações, se necessário
                if ($recebimentoCombustiveis->valor_unitario && $recebimentoCombustiveis->valor_unitario > 0) {
                    $valorAtual = $recebimentoCombustiveis->valor_unitario;
                    $valorAntigo = $old_object ? $old_object->valor_unitario : 0;

                    // Comparar valores formatados ou sempre executar se não há valor anterior
                    if (!$old_object || abs($valorAtual - $valorAntigo) > 0.001) {
                        $this->enviarNotificacoesAlteracaoValor($recebimentoCombustiveis, $old_object);
                        $this->atualizarValorCombustivel($idtanque, $valorAtual);
                        Log::info('Valor unitário alterado para o tanque ' . $idtanque . '. Novo valor: ' . $valorAtual);
                    }
                }
            }

            return redirect()
                ->route('admin.recebimentocombustiveis.index')
                ->with('success', 'Recebimento de combustível cadastrado com sucesso');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar recebimento de combustível: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao tentar salvar o recebimento de combustível: ' . $e->getMessage());
        }
    }

    protected function enviarNotificacoesAlteracaoValor($recebimento, $old_object)
    {
        try {
            $idTanque = Tanque::find($recebimento->id_tanque);
            if (!$idTanque) {
                return;
            }

            $idCombus = TipoCombustivel::find($idTanque->combustivel);
            if (!$idCombus) {
                return;
            }

            $NomeForn = Fornecedor::find($recebimento->id_fornecedor);
            if (!$NomeForn) {
                return;
            }

            $results = DB::connection('pgsql')->select("
                        SELECT vf.name
                        FROM filiais AS vf
                        WHERE vf.id = ?", [$idTanque->id_filial]);

            $name = $results ? $results[0]->name : 'Desconhecido';

            $valorunitario = str_replace('.', ',', $recebimento->valor_unitario);
            $old_object_valor = str_replace('.', ',', $old_object->valor_unitario);

            $telefones = [
                '65998027432' => 'Renato',
                '65999295814' => 'Victor',
                '65981108787' => 'Otavio',
                '65999721294' => 'Qualidade',
                '65996620322' => 'Rafael'
            ];

            // $telefones = ['65992830745' => 'Marcelo'];

            foreach ($telefones as $telefone => $nome) {
                if (!empty($telefone)) {
                    $texto = "*Atenção:* $nome. \n"
                        . "O valor de compra por Litro sofreu alteração.\n"
                        . "Fornecedor: {$NomeForn->nome_fornecedor}.\n"
                        . "Local: {$name}. \n"
                        . "Combustível: {$idCombus->descricao}.\n"
                        . "Valor anterior: R$ {$old_object_valor}.\n"
                        . "Novo valor: R$ {$valorunitario}.\n";

                    IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, "$nome", "$telefone");
                    Log::info("Notificação enviada para $nome no telefone $telefone: $texto");
                }
                Log::info("pode ser que entrou ou pode ser que não entrou");
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificações: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $recebimentoCombustiveis = RecebimentoCombustivel::findOrFail($id);

        $filiais = Filial::select('id as value', 'name as label')->get();

        // Uso do novo método para fornecedores ativos
        $fornecedores = Fornecedor::fornecedoresAtivosParaSelect();

        // Uso do método para tanques internos
        $tanques = Tanque::tanquesInternosParaSelect();

        return view('admin.recebimentocombustiveis.edit', compact('filiais', 'fornecedores', 'tanques', 'recebimentoCombustiveis'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $recebimentocombustiveis_update = $request->validate([
                'id_pedido'               => 'required', // Adicionado campo id_pedido
                'id_filial'               => 'nullable',
                'numeronotafiscal'        => 'nullable',
                'chave_nf'                => 'nullable',
                'numero_nf2'              => 'nullable',
                'chave_nf2'               => 'nullable',
                'numero_nf3'              => 'nullable',
                'chave_nf3'               => 'nullable',
                'numero_nf4'              => 'nullable',
                'chave_nf4'               => 'nullable',
                'id_fornecedor'           => 'nullable',
                'id_tanque'               => 'nullable',
                'data_entrada'            => 'nullable',
                'preco_total_item'        => 'nullable',
                'valor_unitario'          => 'nullable',
                'quantidade'              => 'nullable',
                'temperatura_combustivel' => 'nullable',
                'densidade_combustivel'   => 'nullable',
                'volume_convertido'       => 'nullable',
            ]);

            $recebimentoCombustiveis = RecebimentoCombustivel::findOrFail($id);

            // Guardar valores antigos para comparação
            $old_valor_unitario = $recebimentoCombustiveis->valor_unitario;
            $old_quantidade = $recebimentoCombustiveis->quantidade;
            $old_id_tanque = $recebimentoCombustiveis->id_tanque;

            // Aplicar formatação nos campos numéricos antes de atualizar
            if (isset($recebimentocombustiveis_update['quantidade'])) {
                $recebimentocombustiveis_update['quantidade'] = $this->formatarNumeroParaBanco($recebimentocombustiveis_update['quantidade']);
            }
            if (isset($recebimentocombustiveis_update['preco_total_item'])) {
                $recebimentocombustiveis_update['preco_total_item'] = $this->formatarNumeroParaBanco($recebimentocombustiveis_update['preco_total_item']);
            }
            if (isset($recebimentocombustiveis_update['valor_unitario'])) {
                $recebimentocombustiveis_update['valor_unitario'] = $this->formatarNumeroParaBanco($recebimentocombustiveis_update['valor_unitario']);
            }
            if (isset($recebimentocombustiveis_update['temperatura_combustivel'])) {
                $recebimentocombustiveis_update['temperatura_combustivel'] = $this->formatarNumeroParaBanco($recebimentocombustiveis_update['temperatura_combustivel']);
            }
            if (isset($recebimentocombustiveis_update['densidade_combustivel'])) {
                $recebimentocombustiveis_update['densidade_combustivel'] = $this->formatarNumeroParaBanco($recebimentocombustiveis_update['densidade_combustivel']);
            }
            if (isset($recebimentocombustiveis_update['volume_convertido'])) {
                $recebimentocombustiveis_update['volume_convertido'] = $this->formatarNumeroParaBanco($recebimentocombustiveis_update['volume_convertido']);
            }

            // Atualizar o registro
            $recebimentoCombustiveis->data_alteracao = now();
            $recebimentoCombustiveis->situacao_nf = 'FINALIZADO';
            $recebimentoCombustiveis->fill($recebimentocombustiveis_update);
            $recebimentoCombustiveis->update();

            // Verificar se houve alteração no valor unitário
            if ($old_valor_unitario != $recebimentoCombustiveis->valor_unitario) {
                $old_object = (object)[
                    'valor_unitario' => $old_valor_unitario
                ];
                $this->enviarNotificacoesAlteracaoValor($recebimentoCombustiveis, $old_object);
            }

            return redirect()
                ->route('admin.recebimentocombustiveis.index')
                ->with('success', 'Recebimento de combustível atualizado com sucesso');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar recebimento de combustível: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao tentar atualizar o recebimento de combustível: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $recebimento = RecebimentoCombustivel::findOrFail($id);
            $recebimento->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir recebimento de combustível: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Busca dados do pedido
     *
     * Método corrigido para getPedido (nome do método na rota)
     */
    public function getPedido(Request $request)
    {
        Log::debug('Requisição getPedido recebida', ['id_pedido' => $request->pedidoId]);
        try {
            $resultado = PedidoCompra::where('id_pedido_compras', intval($request->pedidoId))
                ->with('fornecedor')
                ->select('id_fornecedor', 'valor_total', 'id_filial')
                ->first();

            $itens = ItensPedidos::where('id_pedido_compras', intval($request->pedidoId))
                ->first();

            if ($resultado && $itens) {
                Log::debug('Dados do pedido encontrados', [
                    'id_pedido' => $resultado->id_pedido_compras,
                    'id_fornecedor' => $resultado->id_fornecedor,
                    'id_filial' => $resultado->id_filial,
                    'id_fornecedor' => $resultado->id_fornecedor,
                    'nome_fornecedor' => $resultado->fornecedor->nome_fornecedor,
                    'valor_total' => $itens->valor_total,
                    'quantidade_produtos' => $itens ? $itens->quantidade_produtos : 0
                ]);
                return response()->json([
                    'id_pedido' => $request->id_pedido_compras,
                    'id_fornecedor' => $resultado->id_fornecedor,
                    'id_filial' => $resultado->id_filial,
                    'id_fornecedor' => $resultado->id_fornecedor,
                    'nome_fornecedor' => $resultado->fornecedor->nome_fornecedor,
                    'valor_total' => $itens->valor_total,
                    'quantidade_produtos' => $itens ? $itens->quantidade_produtos : 0
                ], 200);
            }

            return response()->json(['error' => 'Nenhum resultado encontrado para o pedido.'], 404);
        } catch (\Exception $e) {
            // Log do erro para depuração
            Log::error('Erro ao buscar dados: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do pedido: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verifica se o pedido já foi baixado
     *
     * Método corrigido para pedidoJaBaixado (nome do método na rota)
     */
    public function pedidoJaBaixado(Request $request)
    {
        try {
            $idPedido = $request->idPedido;

            if (!$idPedido) {
                return response()->json(['error' => 'ID do pedido não fornecido'], 400);
            }

            $results = DB::connection('pgsql')->select("
            SELECT p.situacao FROM pedido_compras AS p
            WHERE p.id_pedido_compras = ?
        ", [$idPedido]);

            if (empty($results)) {
                return response()->json(['result' => false, 'message' => 'Pedido não encontrado']);
            }

            $retorno = $results[0]->situacao;
            $result = false;

            if ($retorno != null && !empty($retorno)) {
                $result = ($retorno == 'FINALIZADO');
            }

            return response()->json(['result' => $result]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do pedido: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao verificar status do pedido: ' . $e->getMessage()], 500);
        }
    }

    public function verificarEstoqueEQuantidade($quantidade, $idfilial, $idtanque)
    {
        if ($quantidade == null || $idfilial == null || $idtanque == null) {
            return false;
        }

        try {
            $capacidadeTanque = 0;
            $estoqueatual = 0;

            $results = DB::connection('pgsql')->select("
                SELECT capacidade
                FROM tanque AS t
                WHERE id_tanque = ?", [$idtanque]);

            if ($results) {
                $capacidadeTanque = $results[0]->capacidade;
            }

            $results = DB::connection('pgsql')->select("
                SELECT quantidade_em_estoque
                FROM estoque_combustivel
                WHERE id_tanque = ?
                AND data_encerramento IS NULL LIMIT 1", [$idtanque]);

            if ($results) {
                $estoqueatual = $results[0]->quantidade_em_estoque;
            }

            Log::info('Verificando estoque e quantidade', [
                'capacidadeTanque' => $capacidadeTanque,
                'estoqueatual' => $estoqueatual,
                'quantidade' => $quantidade
            ]);

            if (floatval($estoqueatual) + floatval($quantidade) > floatval($capacidadeTanque)) {
                return false;
            } elseif (floatval($quantidade) > floatval($capacidadeTanque)) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao verificar estoque: ' . $e->getMessage());
            return false;
        }
    }

    public function atualizarEstoque($quantidade, $idtanque, $filial, $valortotal)
    {
        try {
            $saldo = 0;
            $id_estoque_combustivel = 0;

            $results = DB::connection('pgsql')->select(
                "
                SELECT quantidade_em_estoque, id_estoque_combustivel
                FROM estoque_combustivel
                WHERE id_tanque = ? AND id_filial = ? AND data_encerramento IS NULL",
                [$idtanque, $filial]
            );

            if ($results) {
                $saldo = $results[0]->quantidade_em_estoque;
                $id_estoque_combustivel = $results[0]->id_estoque_combustivel;
            }

            if ($saldo >= 0 && $id_estoque_combustivel != 0) {
                $valormedio = (floatval($valortotal) / floatval($quantidade));
                $quantidadeAtualizar = floatval($saldo) + floatval($quantidade);

                DB::connection('pgsql')->update(
                    "
                    UPDATE estoque_combustivel
                    SET quantidade_em_estoque = ?,
                        valor_unitario = ?,
                        quantidade_anterior = ?,
                        data_alteracao = current_timestamp
                    WHERE id_estoque_combustivel = ?",
                    [$quantidadeAtualizar, $valormedio, $saldo, $id_estoque_combustivel]
                );

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar estoque: ' . $e->getMessage());
            return false;
        }
    }

    protected function getReferenceDatas()
    {
        return Cache::remember('recebimento_combustivel_reference_datas', now()->addHours(12), function () {
            return [
                'filiais' => Filial::select('id as value', 'name as label')
                    ->orderBy('name')
                    ->get(),

                'tiposCombustivel' => TipoCombustivel::select('id_tipo_combustivel as value', 'descricao as label')
                    ->orderBy('descricao')
                    ->get(),

                'fornecedoresFrequentes' => Fornecedor::fornecedoresAtivosParaSelect(true, 20),

                'tanques' => Tanque::tanquesInternosParaSelect(),
            ];
        });
    }

    public function getTankData(Request $request)
    {
        try {
            // Buscar todos os tanques do modelo selecionado
            $resultado = Tanque::select('id_tanque as value', 'tanque as label')
                ->orderBy('tanque')
                ->get(); // Adicionar get() para executar a query

            if (GetterFilial() != 1) {
                $resultado = $resultado->where('id_filial', GetterFilial());
            }

            $tanques = $resultado->values(); // Reindexar a coleção

            Log::info('Tanques encontrados', [
                'fornecedor' => $request->fornecedor,
                'tanques_count' => $tanques->count()
            ]);

            return response()->json([
                'tanques' => $tanques,
                'total' => $tanques->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do tanque: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do tanque'], 500);
        }
    }


    public function atualizarValorCombustivel($id_tanque, $valor_unitario)
    {
        try {
            // Formatar com 4 casas decimais usando number_format para garantir a precisão
            $valor_terceiro = number_format(floatval($valor_unitario) + 0.15, 4, '.', '');
            $valor_interno = number_format(floatval($valor_unitario), 4, '.', '');
            $data_atualizacao = now()->format('d/m/Y H:i');

            // Buscar tipo de combustível e filial do tanque
            $tanque = DB::connection('pgsql')->table('tanque')->where('id_tanque', $id_tanque)->first();
            $tanque = DB::connection('pgsql')->table('tanque')->where('id_tanque', $id_tanque)->first();
            if (!$tanque) return false;

            $tipo_combustivel = $tanque->combustivel;
            $id_filial = $tanque->id_filial;

            $descricao_filial = DB::connection('pgsql')->table('v_filiais_carvalima')->where('id', $id_filial)->value('name');
            $descricao_combustivel = DB::connection('pgsql')->table('tipocombustivel')->where('id_tipo_combustivel', $tipo_combustivel)->value('descricao');
            $descricao_filial = DB::connection('pgsql')->table('v_filiais_carvalima')->where('id', $id_filial)->value('name');
            $descricao_combustivel = DB::connection('pgsql')->table('tipocombustivel')->where('id_tipo_combustivel', $tipo_combustivel)->value('descricao');

            // Buscar bombas do tanque
            $bombas = DB::connection('pgsql')->table('bomba')->where('id_tanque', $id_tanque)->pluck('id_bomba');

            foreach ($bombas as $bomba) {
                DB::connection('pgsql')->table('valor_combustivel_terceiro')->insert([
                    'data_inclusao'      => now(),
                    'valor_diesel'       => $valor_unitario,
                    'valor_acrescimo'    => $valor_interno,
                    'id_tipo_combustivel' => $tipo_combustivel,
                    'data_inicio'        => now()->toDateString(),
                    'boma_combustivel'   => $bomba,
                    'id_usuario'         => 1, // ajuste conforme necessário
                    'id_filial'          => $id_filial,
                    'valor_terceiro'     => $valor_terceiro,
                ]);
            }

            // Telefones para notificação
            $telefones = [
                '65998027432' => 'Renato',
                '65999295814' => 'Victor',
                '65981108787' => 'Otavio',
                '65999721294' => 'Qualidade',
                '65996620322' => 'Rafael',
                '65992322756' => 'Marcos',
                '65996137300' => 'Matheus'
            ];

            // $telefones = ['65992830745' => 'Marcelo'];

            foreach ($telefones as $telefone => $nome) {
                if (!empty($telefone)) {
                    $texto = "*Atenção:* $nome. \n"
                        . "Atualizado Valor Combustível para Terceiro.\n"
                        . "Data: {$data_atualizacao}.\n"
                        . "Filial: {$descricao_filial}.\n"
                        . "Combustível: {$descricao_combustivel}.\n"
                        . "Novo valor: R$ " . number_format($valor_terceiro, 4, ',', '.') . ".\n";

                    IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, "$nome", "$telefone");
                    Log::info("Notificação enviada para $nome no telefone $telefone: $texto");
                }
                Log::info("pode ser que entrou ou pode ser que não entrou");
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar valor do combustível para terceiro: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Converte valores numéricos do formato brasileiro (vírgula) para formato de banco (ponto)
     *
     * @param string|float|null $valor
     * @return float|null
     */
    private function formatarNumeroParaBanco($valor)
    {
        if ($valor === null || $valor === '' || $valor === 0) {
            return null;
        }

        // Se já for um número, retorna como float
        if (is_numeric($valor)) {
            return floatval($valor);
        }

        // Converter string para float no formato correto para PostgreSQL
        $valorString = trim(strval($valor));

        // Log para depuração
        Log::info('Formatando valor para banco', [
            'valor_original' => $valor,
            'valor_string' => $valorString
        ]);

        // Se contém vírgula, assumir formato brasileiro (1.234,56)
        if (strpos($valorString, ',') !== false) {
            // Remove pontos (separadores de milhar) e troca vírgula por ponto (separador decimal)
            $valorLimpo = str_replace(',', '.', str_replace('.', '', $valorString));
        } else {
            // Se não tem vírgula, pode ser formato americano ou número simples
            $valorLimpo = $valorString;
        }

        // Verifica se o resultado é um número válido
        if (!is_numeric($valorLimpo)) {
            Log::warning('Valor não numérico após formatação', [
                'valor_original' => $valor,
                'valor_limpo' => $valorLimpo
            ]);
            return null;
        }

        $resultado = floatval($valorLimpo);

        Log::info('Valor formatado com sucesso', [
            'valor_original' => $valor,
            'resultado' => $resultado
        ]);

        return $resultado;
    }

    public function getFornecedores(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $fornecedores = Cache::remember('fornecedor_search_' . $term, now()->addMinutes(15), function () use ($term) {
            return Fornecedor::select('id_fornecedor', 'nome_fornecedor', 'cpf_fornecedor', 'cnpj_fornecedor')
                ->where('is_ativo', true)
                ->whereRaw('LOWER(nome_fornecedor) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('LOWER(apelido_fornecedor) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('cnpj_fornecedor LIKE ?', ["%" . $term . "%"])
                ->orderBy('nome_fornecedor')
                ->limit(30)
                ->get()
                ->map(function ($f) {
                    $cnpj = $f->cnpj_fornecedor ?? '';
                    $label = trim($cnpj) !== '' ? ($cnpj . ' - ' . $f->nome_fornecedor) : $f->nome_fornecedor;
                    Log::debug('Fornecedor encontrado: ' . $label);
                    return ['label' => $label, 'value' => $f->id_fornecedor];
                })->toArray();
        });

        return response()->json($fornecedores);
    }
}