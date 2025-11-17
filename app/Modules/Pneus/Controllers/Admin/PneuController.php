<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\NotaFiscalEntrada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Departamento;
use App\Models\Pneu;
use App\Models\ModeloPneu;
use App\Models\ControleVidaPneus;
use App\Models\HistoricoPneu;
use App\Models\NotaFiscalPneu;
use App\Models\VFilial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PneuApiResource;
use App\Http\Resources\PneuApiCollection;


class PneuController extends Controller
{
    public function index(Request $request)
    {
        $query = Pneu::query();

        if ($request->filled('id_pneu')) {
            $query->where('id_pneu', $request->id_pneu);
        }

        if ($request->filled('cod_antigo')) {
            $query->where('cod_antigo', $request->cod_antigo);
        }

        if ($request->filled(['data_inclusao_inicial', 'data_inclusao_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inclusao_inicial,
                $request->data_inclusao_final
            ]);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->id_departamento);
        }

        if ($request->filled('status_pneu')) {
            if ($request->status_pneu == 'VENDIDO') {
                $query->where('status_pneu', 'like', 'VENDIDO%');
            } else {
                $query->where('status_pneu', $request->status_pneu);
            }
        }

        $pneus = $query->latest('id_pneu')
            ->with('filialPneu', 'departamentoPneu')
            ->where('deleted_at', null)
            ->paginate(40)
            ->appends($request->query());

        $formOptions = [
            'filiais'          => VFilial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'departamentos'    => Departamento::select('descricao_departamento as label', 'id_departamento as value')->orderBy('label')->get()->toArray(),
            'statuses' => [
                ['label' => 'Selecione...',       'value' => ''],
                ['label' => 'AGUARDANDO LAUDO',   'value' => 'AGUARDANDO LAUDO'],
                ['label' => 'APLICADO',           'value' => 'APLICADO'],
                ['label' => 'DEPOSITO',           'value' => 'DEPOSITO'],
                ['label' => 'DESCARTE',           'value' => 'DESCARTE'],
                ['label' => 'DIAGNOSTICO',        'value' => 'DIAGNOSTICO'],
                ['label' => 'EM MANUTENÇÃO',      'value' => 'EM MANUTENÇÃO'],
                ['label' => 'ESTOQUE',            'value' => 'ESTOQUE'],
                ['label' => 'NÃO APLICADO',       'value' => 'NÃO APLICADO'],
                ['label' => 'TERCEIRO',           'value' => 'TERCEIRO'],
                ['label' => 'TRANSFERÊNCIA',      'value' => 'TRANSFERÊNCIA'],
                ['label' => 'VENDIDO',            'value' => 'VENDIDO'],
            ]
        ];

        if ($request->header('HX-Request')) {
            return view('admin.pneus._table', compact('pneus', 'formOptions'));
        }

        return view('admin.pneus.index', compact('pneus', 'formOptions'));
    }

    public function create()
    {
        $formOptions = [
            'filiais'          => VFilial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'departamentos'    => Departamento::select('descricao_departamento as label', 'id_departamento as value')->orderBy('label')->get()->toArray(),
            'status_pneu'      => Pneu::select('status_pneu as label', 'status_pneu as value')->distinct()->where('status_pneu', 'not like', 'VENDIDO%')->orderBy('label')->get()->toArray(),
            'modeloPneu'       => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->where('ativo', true)->orderBy('label')->get()->toArray(),
            'controleVidaPneu' => ControleVidaPneus::select('descricao_vida_pneu as label', 'descricao_vida_pneu as value')->where('descricao_vida_pneu', '!=', null)->orderBy('label')->distinct('label')->get()->toArray()
        ];

        $NFPneusFrequentes = Cache::remember('NFPneus_frequentes', now()->addHours(12), function () {
            return NotaFiscalPneu::whereDate('data_inclusao', '>=', '2024-01-01')
                ->limit(20)
                ->orderBy('data_inclusao', 'desc')
                ->get()
                ->map(function ($nf) {
                    return [
                        'label' => 'NF: ' . $nf->numero_nf . ' - Série: ' . $nf->serie,
                        'value' => $nf->id_nota_fiscal_pneu
                    ];
                })
                ->toArray();
        });

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        // Obter o próximo número de fogo
        $nextId = DB::connection('pgsql')->select("SELECT last_value + 1 AS next_id FROM pneu_id_pneu_seq")[0]->next_id;

        $formOptions['id_pneu'] = $nextId;

        return view('admin.pneus.create', compact('formOptions', 'NFPneusFrequentes', 'fornecedoresFrequentes'));
    }


    public function store(Request $request)
    {
        $pneusDados = $request->validate([
            'id_pneu' => 'required',
            'id_modelo_pneu' => 'required',
            'id_controle_vida_pneu' => 'required'
        ], [
            'id_pneu.required' => 'O campo Número de Fogo é obrigatorio',
            'id_modelo_pneu.required' => 'O campo Modelo é obrigatorio',
            'id_controle_vida_pneu.required' => 'O campo Controle de vida do pneu é obrigatorio',
        ]);

        try {
            DB::beginTransaction();

            $pneus = new Pneu();

            $pneus->data_inclusao   = now();
            $pneus->id_departamento = $request->id_departamento;
            $pneus->id_filial       = $request->id_filial;
            $pneus->status_pneu     = $request->status_pneu;

            $pneus->save();

            $historicoPneu = new HistoricoPneu();

            $historicoPneu->data_inclusao       = now();
            $historicoPneu->id_pneu             = $pneus->id_pneu;
            $historicoPneu->id_modelo           = $pneusDados['id_modelo_pneu'];
            $historicoPneu->id_vida_pneu        = $pneusDados['id_controle_vida_pneu'];
            $historicoPneu->status_movimentacao = 'ENTRADA';

            $historicoPneu->save();

            //----------------------------Gravação Nota fiscal------------------------------//

            $nfPneus = json_decode($request->input('notafiscal', '[]'), true);

            foreach ($nfPneus as $nfPneu_data) {
                $nfPneu_Update = NotaFiscalPneu::where('numero_nf', $nfPneu_data['numeroNF'])->where('id_pneu', $request->id_pneu)->first();

                if ($nfPneu_Update) {
                    $nfPneu_Update->data_alteracao = now();
                    $nfPneu_Update->numero_nf      = $nfPneu_data['numeroNF'];
                    $nfPneu_Update->serie          = $nfPneu_data['serie'];
                    $nfPneu_Update->valor_unitario = $nfPneu_data['valorUnitario'];
                    $nfPneu_Update->valor_total    = $nfPneu_data['valorTotal'];
                    $nfPneu_Update->data_nf        = $nfPneu_data['dataEmissao'];
                    $nfPneu_Update->id_fornecedor  = $nfPneu_data['fornecedorID'];

                    $nfPneu_Update->save();
                } else {
                    $nfPneu_Novo = new NotaFiscalPneu();

                    $nfPneu_Novo->data_inclusao  = now();
                    $nfPneu_Novo->numero_nf      = $nfPneu_data['numeroNF'];
                    $nfPneu_Novo->serie          = $nfPneu_data['serie'];
                    $nfPneu_Novo->valor_unitario = $nfPneu_data['valorUnitario'];
                    $nfPneu_Novo->valor_total    = $nfPneu_data['valorTotal'];
                    $nfPneu_Novo->data_nf        = $nfPneu_data['dataEmissao'];
                    $nfPneu_Novo->id_fornecedor  = $nfPneu_data['fornecedorID'];

                    $nfPneu_Novo->save();
                }
            }
            DB::commit();

            return redirect()->route('admin.pneus.index')->withErrors(['success', 'Pneu já se encontra aplicado e não pode ser atualizado']);
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::INFO('Erro ao cadastrar o pneu: ' . $e->getMessage());
            return redirect()->route('admin.pneus.index')->withErrors(['error', 'Erro ao cadastrar o pneu: ' . $e->getMessage()]);
        }
    }

    public function edit(Pneu $pneus)
    {
        $formOptions = [
            'filiais'          => VFilial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'departamentos'    => Departamento::select('descricao_departamento as label', 'id_departamento as value')->orderBy('label')->get()->toArray(),
            'modeloPneu'       => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray(),
            'controleVidaPneu' => ControleVidaPneus::select('descricao_vida_pneu as label', 'id_controle_vida_pneu as value')->distinct('label')->get()->toArray(),
            'status_pneu'      => Pneu::select('status_pneu as label', 'status_pneu as value')->distinct()->orderBy('label')->get()->toArray(),
            'notaFiscal'       => NotaFiscalEntrada::select('numero_nota_fiscal as label', 'id_nota_fiscal_entrada as value')->get()->toArray(),
            'fornecedor'       => Fornecedor::select('nome_fornecedor as label', 'id_fornecedor as value')->orderBy('label')->get()->toArray(),
        ];

        $NFEntradaFrequentes = Cache::remember('NFEntrada_frequentes', now()->addHours(12), function () {
            return NotaFiscalEntrada::whereDate('data_inclusao', '>=', '2024-01-01')
                ->limit(20)
                ->orderBy('data_inclusao', 'desc')
                ->get(['numero_nota_fiscal as label', 'id_nota_fiscal_entrada as value']);
        });

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        $pneus = Pneu::with(['notasFiscais.fornecedor'])
            ->join('historicopneu as h', 'h.id_pneu', '=', 'pneu.id_pneu')
            ->join('modelopneu as m', 'm.id_modelo_pneu', '=', 'h.id_modelo')
            ->select('pneu.*', 'm.id_modelo_pneu')
            ->where('pneu.id_pneu', $pneus->id_pneu)
            ->first();

        $historicoPneu = HistoricoPneu::where('id_pneu', $pneus->id_pneu)
            ->orderBy('data_inclusao', 'desc')
            ->with('veiculo', 'vidaPneu')
            ->get();

        return view('admin.pneus.edit', compact(
            'formOptions',
            'pneus',
            'historicoPneu',
            'NFEntradaFrequentes',
            'fornecedoresFrequentes'
        ));
    }


    public function update(Request $request, string $id)
    {
        $pneus = Pneu::findorFail($id);

        if ($pneus->status_pneu == 'APLICADO') {
            return redirect()->back()->withErrors(['Pneu já se encontra aplicado e não pode ser atualizado']);
        }

        $pneusDados = $request->validate([
            'id_pneu' => 'required',
            'id_modelo_pneu' => 'required',
            'id_controle_vida_pneu' => 'required'
        ], [
            'id_pneu.required' => 'O campo Número de Fogo é obrigatorio',
            'id_modelo_pneu.required' => 'O campo Modelo é obrigatorio',
            'id_controle_vida_pneu.required' => 'O campo Controle de vida do pneu é obrigatorio',
        ]);

        try {
            DB::beginTransaction();

            $pneus->data_inclusao   = now();
            $pneus->id_departamento = $request->id_departamento;
            $pneus->id_filial       = $request->id_filial;
            $pneus->status_pneu     = $request->status_pneu;

            $pneus->save();

            $historicoPneu = new HistoricoPneu();

            $historicoPneu->data_inclusao       = now();
            $historicoPneu->id_pneu             = $pneus->id_pneu;
            $historicoPneu->id_modelo           = $pneusDados['id_modelo_pneu'];
            $historicoPneu->id_vida_pneu        = $pneusDados['id_controle_vida_pneu'];
            $historicoPneu->status_movimentacao = 'ALTERAÇÃO CADASTRAL';

            $historicoPneu->save();

            //----------------------------Gravação Nota fiscal------------------------------//

            $nfPneus = json_decode($request->input('notafiscal', '[]'), true);

            foreach ($nfPneus as $nfPneu_data) {
                $nfPneu_Update = NotaFiscalPneu::where('numero_nf', $nfPneu_data['numeroNF'])->where('id_pneu', $request->id_pneu)->first();

                if ($nfPneu_Update) {
                    $nfPneu_Update->data_alteracao = now();
                    $nfPneu_Update->numero_nf      = $nfPneu_data['numeroNF'];
                    $nfPneu_Update->serie          = $nfPneu_data['serie'];
                    $nfPneu_Update->valor_unitario = $nfPneu_data['valorUnitario'];
                    $nfPneu_Update->valor_total    = $nfPneu_data['valorTotal'];
                    $nfPneu_Update->data_nf        = $nfPneu_data['dataEmissao'];
                    $nfPneu_Update->id_fornecedor  = $nfPneu_data['fornecedorID'];

                    $nfPneu_Update->save();
                } else {
                    $nfPneu_Novo = new NotaFiscalPneu();

                    $nfPneu_Novo->data_inclusao  = now();
                    $nfPneu_Novo->numero_nf      = $nfPneu_data['numeroNF'];
                    $nfPneu_Novo->serie          = $nfPneu_data['serie'];
                    $nfPneu_Novo->valor_unitario = $nfPneu_data['valorUnitario'];
                    $nfPneu_Novo->valor_total    = $nfPneu_data['valorTotal'];
                    $nfPneu_Novo->data_nf        = $nfPneu_data['dataEmissao'];
                    $nfPneu_Novo->id_fornecedor  = $nfPneu_data['fornecedorID'];

                    $nfPneu_Novo->save();
                }
            }
            DB::commit();

            return redirect()
                ->route('admin.pneus.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Pneu cadastrado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na criação de pneu:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.pneus.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o pneu."
                ]);
        }
    }

    public function destroy(Pneu $pneus)
    {
        try {
            DB::beginTransaction();

            //colocar historico pneu
            // ModeloPneu::create($dadosControle);
            // ControleVidaPneus::create($dadosHistoricoPneu);
            // NotaFiscalPneu::create($dadosNfPneu);

            $pneus->delete();
            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Pneu excluído!',
                    'type'    => 'success',
                    'message' => 'Pneu excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir pneu: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o pneu: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getInfoPneu(string $id)
    {
        try {
            $resultado = ModeloPneu::where('id_modelo_pneu', $id)
                ->with('desenho_pneu', 'dimensao_pneu', 'fornecedor')
                ->first();

            $resultado['vida_pneu'] = ControleVidaPneus::where('id_modelo', $id)
                ->where('id_desenho_pneu_m', $resultado->desenho_pneu->id_desenho_pneu)
                ->first();

            LOG::INFO($resultado);

            return response()->json($resultado);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar o pneu: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível buscar o pneu: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function search(Request $request)
    {
        LOG::DEBUG('PESQUISANDO');
        $term = $request->get('term');
        LOG::DEBUG('Termo de pesquisa: ' . $term);

        // Cache para melhorar performance
        $pneu = Pneu::select('id_pneu as value', 'id_pneu as label')
            ->where('id_pneu', 'LIKE', '%' . $term . '%')
            ->where('status_pneu', 'NOT LIKE', 'VENDIDO%')
            ->where('status_pneu', 'NOT LIKE', 'DESCARTE%')
            ->where('status_pneu', 'NOT LIKE', 'APLICADO%')
            ->orderBy('id_pneu')
            ->limit(30) // Limite razoável de resultados
            ->get();

        log::debug('Resultados encontrados: ' . $pneu);

        return response()->json($pneu);
    }

    /**
     * Retorna um fornecedor específico pelo ID
     * Usado para carregar o item selecionado inicialmente e para interatividade entre campos
     */
    public function getById($id)
    {
        // Cache para melhorar performance
        $pneu = Cache::remember('pneu_' . $id, now()->addHours(24), function () use ($id) {
            return Pneu::findOrFail($id);
        });

        return response()->json([
            'value' => $pneu->id_pneu,
            'label' => $pneu->id_pneu,
        ]);
    }

    /**
     * Retorna dados estruturados do pneu para API
     */
    public function apiPneu($id)
    {
        $ids = array_filter(explode(',', $id), fn($v) => is_numeric($v));

        if (empty($ids)) {
            return response()->json(['error' => 'ID inválido'], 400);
        }

        $query = Pneu::with([
            'modeloPneu.dimensao_pneu',
            // 'tipoDesenhoPneu'
        ]);

        if (count($ids) === 1) {
            $pneu = $query->findOrFail($ids[0]);
            return new PneuApiResource($pneu);
        }

        $pneus = $query->whereIn('id_pneu', $ids)->get();
        return PneuApiResource::collection($pneus);
    }


    /**
     * Endpoint específico para lista de pneus (API)
     */
    public function apiLista(Request $request)
    {
        $pneus = Pneu::with([
            'modeloPneu.dimensao_pneu',
            'controleVidaPneus',
            //'tipoDesenhoPneu'
        ]);

        // Filtros opcionais
        if ($request->has('status')) {
            $pneus->where('status_pneu', $request->status);
        }

        if ($request->has('filial_id')) {
            $pneus->where('id_filial', $request->filial_id);
        }

        // Busca por texto
        if ($request->has('search')) {
            $search = $request->search;
            $pneus->whereHas('modeloPneu', function ($query) use ($search) {
                $query->where('descricao_modelo', 'LIKE', "%{$search}%");
            });
        }

        // Opção 1: Com paginação (recomendado para listas grandes)
        if ($request->get('paginate', true)) {
            $resultado = $pneus->paginate($request->get('per_page', 15));
            return new PneuApiCollection($resultado);
        }

        // Opção 2: Sem paginação (todos os resultados)
        $resultado = $pneus->get();
        return new PneuApiCollection($resultado);
    }

    public function apiListaPorIds($ids)
    {
        $idsArray = explode(',', $ids);

        return PneuApiResource::collection(
            Pneu::with(['modeloPneu.dimensao_pneu'])
                ->whereIn('id_pneu', $idsArray)
                ->get()
        );
    }
}
