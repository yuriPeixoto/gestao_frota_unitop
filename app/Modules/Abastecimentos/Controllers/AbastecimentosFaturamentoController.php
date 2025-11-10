<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Abastecimentos\Models\AbastecimentosFaturamento;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AbastecimentosFaturamentoController extends Controller
{
    /**
     * Atualização do método index para incluir os dados de referência
     */
    public function index(Request $request)
    {
        $query = AbastecimentosFaturamento::query();

        if ($request->filled('cod_transacao')) {
            // Verificar se há vírgulas para tratar como múltiplos códigos
            if (strpos($request->cod_transacao, ',') !== false) {
                // Dividir a string por vírgulas e limpar espaços extras
                $codigos = array_map('trim', explode(',', $request->cod_transacao));
                // Filtrar para manter apenas valores não vazios
                $codigos = array_filter($codigos, fn($value) => !empty($value));
                // Aplicar a consulta usando whereIn
                if (!empty($codigos)) {
                    $query->whereIn('cod_transacao', $codigos);
                }
            } else {
                // Caso de busca por um único código
                $query->where('cod_transacao', $request->cod_transacao);
            }
        }

        if ($request->filled('chave_nf')) {
            $query->where('chave_nf', 'like', '%' . $request->chave_nf . '%');
        }

        if ($request->filled('numero_nf')) {
            $query->where('numero_nf', $request->numero_nf);
        }

        if ($request->filled('data_vencimento_nf')) {
            $query->whereDate('data_vencimento_nf', Carbon::createFromFormat('Y-m-d', $request->data_vencimento_nf));
        }

        if ($request->filled('valor_nf')) {
            $query->where('valor_nf', $request->valor_nf);
        }

        if ($request->filled('posto_abastecimento')) {
            $query->where('posto_abastecimento', $request->posto_abastecimento);
        }

        if ($request->filled('cnpj')) {
            $query->where('cnpj', $request->cnpj);
        }

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('tipo_combustivel')) {
            $query->where('tipo_combustivel', $request->tipo_combustivel);
        }

        if ($request->filled('data_abastecimento')) {
            $query->whereDate('data_abastecimento', Carbon::createFromFormat('Y-m-d', $request->data_abastecimento));
        }

        $abastecimentos = $query->latest('cod_transacao')
            ->paginate(50);

        $totalGeral = $abastecimentos->sum(function ($abastecimento) {
            return (float) $abastecimento->valor_nf;
        });

        // Obter dados de referência para os selects
        $referenceDatas = $this->getReferenceDatas();

        if ($request->header('HX-Request')) {
            return view('admin.abastecimentosfaturamento._table', [
                'abastecimentos' => $abastecimentos,
                'totalGeral' => $totalGeral
            ]);
        }

        // Mesclar os dados do abastecimento com os dados de referência
        return view('admin.abastecimentosfaturamento.index', array_merge([
            'abastecimentos' => $abastecimentos,
            'totalGeral' => $totalGeral
        ], $referenceDatas));
    }

    /**
     * Processa a leitura da chave NF através de código de barras
     * e busca o código de transação correspondente.
     */
    public function processarChaveNf(Request $request)
    {
        try {
            // Validar entrada
            $request->validate([
                'chave_nf' => 'required|string'
            ]);

            $chaveNf = $request->chave_nf;

            // Processamento similar ao sistema legado
            $numeroSemZeros = ltrim($chaveNf, '0');
            $numeroSemZeros = substr($numeroSemZeros, 0, -1);

            if (empty($numeroSemZeros)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave NF inválida.'
                ]);
            }

            // Buscar o código de transação associado à chave NF
            $codTransacao = $this->buscarCodTransacaoPorChaveNf($numeroSemZeros);

            if (!$codTransacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum abastecimento encontrado para esta chave NF.'
                ]);
            }

            // Recuperar array de seleções existente da sessão ou inicializar novo
            $selecoes = $request->session()->get('id_solicitacao_array', []);

            // Adicionar novo código encontrado se ainda não estiver no array
            if (!in_array($codTransacao, $selecoes)) {
                $selecoes[] = $codTransacao;
                $request->session()->put('id_solicitacao_array', $selecoes);
            }

            return response()->json([
                'success' => true,
                'message' => 'Abastecimento encontrado e adicionado.',
                'cod_transacao' => $codTransacao,
                'selecoes' => $selecoes
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao processar chave NF: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar a chave NF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca o código de transação associado a uma chave NF
     * Similar à função traz_cod do sistema legado
     */
    private function buscarCodTransacaoPorChaveNf($chaveNf)
    {
        try {
            $resultado = DB::connection('pgsql')->table('v_listar_abastecimentos_para_faturamento')
                ->where('chave_nf', $chaveNf)
                ->value('cod_transacao');

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar código de transação: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Limpa as seleções armazenadas na sessão
     */
    public function limparSelecoes(Request $request)
    {
        $request->session()->forget('id_solicitacao_array');

        return response()->json([
            'success' => true,
            'message' => 'Seleções limpas com sucesso.'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Obter IDs dos parâmetros da URL ou da sessão
        $idsParam = $request->query('ids', '');
        $idsSession = $request->session()->get('id_solicitacao_array', []);

        // Priorizar IDs da URL, mas usar da sessão se URL estiver vazia
        $idsStr = !empty($idsParam) ? $idsParam : implode(',', $idsSession);
        $ids = array_filter(explode(',', $idsStr), function ($id) {
            return !empty($id) && is_numeric($id);
        });

        if (empty($ids)) {
            return redirect()->route('admin.abastecimentosfaturamento.index')
                ->with('error', 'Nenhum abastecimento selecionado para faturamento.');
        }

        // Recupera todos os abastecimentos selecionados
        $abastecimentosSelecionados = AbastecimentosFaturamento::whereIn('cod_transacao', $ids)->get();

        if ($abastecimentosSelecionados->isEmpty()) {
            return redirect()->route('admin.abastecimentosfaturamento.index')
                ->with('error', 'Nenhum dos abastecimentos selecionados foi encontrado.');
        }

        return view('admin.abastecimentosfaturamento.create', [
            'abastecimentosSelecionados' => $abastecimentosSelecionados,
            'action' => route('admin.abastecimentosfaturamento.store'),
            'method' => 'POST',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids)) {
            return redirect()->route('admin.abastecimentosfaturamento.index')
                ->with('error', 'Nenhum abastecimento selecionado para faturamento.');
        }

        $usuario = auth()->user();

        try {
            // Log para verificação (número de itens a faturar)
            Log::info('Iniciando faturamento de ' . count($ids) . ' abastecimentos pelo usuário ' . $usuario->name);

            // Variáveis para registro do status do processamento
            $sucessos = 0;
            $falhas = 0;
            $erros = [];

            foreach ($ids as $id) {
                try {
                    DB::connection('pgsql')->select('SELECT fc_faturamento_abastecimento(?, ?)', [$id, $usuario->id]);
                    $sucessos++;
                } catch (\Exception $e) {
                    $falhas++;
                    $erros[] = "Erro ao faturar abastecimento #$id: " . $e->getMessage();
                    Log::error("Erro ao faturar abastecimento #$id: " . $e->getMessage());
                }
            }

            // Limpar seleções da sessão após processamento
            $request->session()->forget('id_solicitacao_array');

            // Determina a mensagem baseada no resultado do processamento
            if ($falhas == 0) {
                $mensagem = "Todos os $sucessos abastecimentos foram faturados com sucesso.";
                $tipo = 'success';
            } elseif ($sucessos > 0) {
                $mensagem = "$sucessos abastecimentos faturados com sucesso. $falhas falharam.";
                $tipo = 'warning';
            } else {
                $mensagem = "Falha ao faturar abastecimentos. Nenhum foi processado com sucesso.";
                $tipo = 'error';
            }

            return redirect()->route('admin.abastecimentosfaturamento.index')
                ->with([
                    'limparSelecao' => true,
                    'notification' => [
                        'title'   => $tipo == 'success' ? 'Sucesso' : 'Atenção',
                        'type'    => $tipo,
                        'message' => $mensagem,
                    ],
                ]);
        } catch (\Exception $e) {
            Log::error('Erro crítico ao faturar abastecimentos: ' . $e->getMessage());

            return redirect()->route('admin.abastecimentosfaturamento.index')
                ->with([
                    'notification' => [
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => 'Erro ao processar faturamento: ' . $e->getMessage(),
                    ],
                ]);
        }
    }

    /**
     * Obtém os dados de referência para os selects do formulário
     *
     * @return array
     */
    public function getReferenceDatas()
    {
        return Cache::remember('abastecimentos_faturamento_reference_datas', now()->addHours(12), function () {
            return [
                'postosFrequentes' => DB::connection('pgsql')
                    ->table('v_listar_abastecimentos_para_faturamento')
                    ->select(DB::connection('pgsql')
                        ->raw('DISTINCT posto_abastecimento as value, posto_abastecimento as label'))
                    ->whereNotNull('posto_abastecimento')
                    ->orderBy('posto_abastecimento')
                    ->limit(20)
                    ->get(),

                'placasFrequentes' => DB::connection('pgsql')
                    ->table('v_listar_abastecimentos_para_faturamento')
                    ->select(DB::connection('pgsql')->raw('DISTINCT placa as value, placa as label'))
                    ->whereNotNull('placa')
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(),

                'tiposCombustivel' => DB::connection('pgsql')
                    ->table('v_listar_abastecimentos_para_faturamento')
                    ->select(DB::connection('pgsql')->raw('DISTINCT tipo_combustivel as value, tipo_combustivel as label'))
                    ->whereNotNull('tipo_combustivel')
                    ->orderBy('tipo_combustivel')
                    ->get(),
            ];
        });
    }

    /**
     * API para buscar postos de abastecimento por nome (autocomplete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPostos(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        try {
            $postos = DB::connection('pgsql')
                ->table('v_listar_abastecimentos_para_faturamento')
                ->select(DB::connection('pgsql')->raw('DISTINCT posto_abastecimento as value, posto_abastecimento as label'))
                ->where('posto_abastecimento', 'ilike', "%{$term}%")
                ->whereNotNull('posto_abastecimento')
                ->orderBy('posto_abastecimento')
                ->limit(15)
                ->get();

            return response()->json($postos);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar postos: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar postos'], 500);
        }
    }

    /**
     * API para buscar placas de veículos por nome (autocomplete)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPlacas(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        try {
            $placas = DB::connection('pgsql')
                ->table('v_listar_abastecimentos_para_faturamento')
                ->select(DB::connection('pgsql')
                    ->raw('DISTINCT placa as value, placa as label'))
                ->where('placa', 'ilike', "%{$term}%")
                ->whereNotNull('placa')
                ->orderBy('placa')
                ->limit(15)
                ->get();

            return response()->json($placas);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar placas: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar placas'], 500);
        }
    }

    /**
     * API para obter tipos de combustível disponíveis
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTiposCombustivel()
    {
        try {
            $tipos = DB::connection('pgsql')
                ->table('v_listar_abastecimentos_para_faturamento')
                ->select(DB::connection('pgsql')->raw('DISTINCT tipo_combustivel as value, tipo_combustivel as label'))
                ->whereNotNull('tipo_combustivel')
                ->orderBy('tipo_combustivel')
                ->get();

            return response()->json($tipos);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar tipos de combustível: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar tipos de combustível'], 500);
        }
    }

    /**
     * Busca abastecimentos por códigos de transação específicos
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarPorCodigos(Request $request)
    {
        try {
            $codigos = $request->input('codigos', []);

            // Se a entrada for uma string, tenta converter para array
            if (is_string($codigos)) {
                $codigos = array_map('trim', explode(',', $codigos));
            }

            // Filtrar valores vazios
            $codigos = array_filter($codigos, fn($value) => !empty($value));

            if (empty($codigos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum código de transação fornecido'
                ], 400);
            }

            // Buscar os abastecimentos pelos códigos
            $abastecimentos = AbastecimentosFaturamento::whereIn('cod_transacao', $codigos)
                ->get();

            // Verificar se encontrou algum abastecimento
            if ($abastecimentos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum abastecimento encontrado para os códigos fornecidos',
                    'codigos_fornecidos' => $codigos
                ]);
            }

            // Retornar dados encontrados
            return response()->json([
                'success' => true,
                'message' => count($abastecimentos) . ' abastecimento(s) encontrado(s)',
                'data' => $abastecimentos,
                'codigos_encontrados' => $abastecimentos->pluck('cod_transacao')->toArray(),
                'codigos_nao_encontrados' => array_diff($codigos, $abastecimentos->pluck('cod_transacao')->toArray())
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar abastecimentos por códigos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar a busca: ' . $e->getMessage(),
                'codigos_fornecidos' => $codigos ?? []
            ], 500);
        }
    }
}
