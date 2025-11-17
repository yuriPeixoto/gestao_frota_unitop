<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContratoFornecedor;
use App\Models\ContratoModelo;
use App\Modules\Veiculos\Models\ModeloVeiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContratoModeloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContratoModelo::query();

        // Filtros
        if ($request->filled('id_contrato_modelo')) {
            $query->where('id_contrato_modelo', $request->id_contrato_modelo);
        }

        if ($request->filled('id_contrato')) {
            $query->where('id_contrato', $request->id_contrato);
        }

        if ($request->filled('id_modelo')) {
            $query->where('id_modelo', $request->id_modelo);
        }

        if ($request->filled('ativo')) {
            $ativo = $request->ativo === 'true' || $request->ativo === '1';
            $query->where('ativo', $ativo);
        }

        // Se estiver filtrando por fornecedor
        if ($request->filled('id_fornecedor')) {
            $query->whereHas('contrato', function ($q) use ($request) {
                $q->where('id_fornecedor', $request->id_fornecedor);
            });
        }

        // Carregar relacionamentos
        $query->with(['contrato', 'modelo', 'contrato.fornecedor']);

        $contratosModelo = $query->orderBy('id_contrato_modelo', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Se for uma requisição HTMX, retorne apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.contratosmodelo._table', compact('contratosModelo'));
        }

        // Obter dados para os filtros
        $contratos = ContratoFornecedor::where('is_valido', true)
            ->with('fornecedor')
            ->get()
            ->map(function ($contrato) {
                return [
                    'value' => $contrato->id_contrato_forn,
                    'label' => "#{$contrato->id_contrato_forn} - {$contrato->fornecedor->nome_fornecedor}"
                ];
            });

        $modelos = ModeloVeiculo::orderBy('descricao_modelo_veiculo')
            ->get(['id_modelo_veiculo as value', 'descricao_modelo_veiculo as label']);

        return view('admin.contratosmodelo.index', compact('contratosModelo', 'contratos', 'modelos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Verificar se um contrato foi especificado na requisição
        $contratoId = $request->query('contrato_id');
        $fornecedorId = $request->query('fornecedor_id');

        $contrato = null;
        $contratos = [];

        // Se um ID de contrato foi fornecido, buscar o contrato
        if ($contratoId) {
            $contrato = ContratoFornecedor::with('fornecedor')->find($contratoId);

            if ($contrato) {
                $contratos = [
                    [
                        'value' => $contrato->id_contrato_forn,
                        'label' => "#{$contrato->id_contrato_forn} - {$contrato->fornecedor->nome_fornecedor}"
                    ]
                ];
            }
        } elseif ($fornecedorId) {
            $contratos = ContratoFornecedor::where('id_fornecedor', $fornecedorId)
                ->where('is_valido', true)
                ->with('fornecedor')
                ->get()
                ->map(function ($contrato) {
                    return [
                        'value' => $contrato->id_contrato_forn,
                        'label' => "#{$contrato->id_contrato_forn} - {$contrato->fornecedor->nome_fornecedor}"
                    ];
                })
                ->toArray();
        } else {
            $contratos = ContratoFornecedor::where('is_valido', true)
                ->with('fornecedor')
                ->get()
                ->map(function ($contrato) {
                    return [
                        'value' => $contrato->id_contrato_forn,
                        'label' => "#{$contrato->id_contrato_forn} - {$contrato->fornecedor->nome_fornecedor}"
                    ];
                })
                ->toArray();
        }

        // Obter todos os modelos de veículos
        $modelos = ModeloVeiculo::orderBy('descricao_modelo_veiculo')
            ->get(['id_modelo_veiculo as value', 'descricao_modelo_veiculo as label']);

        return view('admin.contratosmodelo.create', compact('contratos', 'modelos', 'contrato'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'id_contrato' => 'required|exists:contrato_fornecedor,id_contrato_forn',
                'id_modelo' => 'required|exists:modelo_veiculo,id_modelo_veiculo',
                'ativo' => 'boolean',
            ]);

            DB::beginTransaction();

            // Verificar se já existe um vínculo igual
            $existingVinculo = ContratoModelo::where('id_contrato', $validated['id_contrato'])
                ->where('id_modelo', $validated['id_modelo'])
                ->where('ativo', true)
                ->first();

            if ($existingVinculo) {
                throw new \Exception('Já existe um vínculo ativo entre este contrato e este modelo de veículo.');
            }

            // Criar o vínculo contrato-modelo
            $contratoModelo = new ContratoModelo();
            $contratoModelo->id_contrato = $validated['id_contrato'];
            $contratoModelo->id_modelo = $validated['id_modelo'];
            $contratoModelo->ativo = $validated['ativo'] ?? true;
            $contratoModelo->id_user = Auth::id();
            $contratoModelo->save();

            DB::commit();

            // Redirecionar com base na origem da requisição
            $redirectRoute = $request->input('redirect_to_contrato')
                ? route('admin.contratos.show', $contratoModelo->id_contrato)
                : route('admin.contratosmodelo.index');

            return redirect($redirectRoute)
                ->with('success', 'Vínculo entre contrato e modelo criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao vincular contrato e modelo: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao vincular contrato e modelo: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contratoModelo = ContratoModelo::with([
            'contrato',
            'contrato.fornecedor',
            'modelo',
            'servicosFornecedor',
            'pecasFornecedor'
        ])->findOrFail($id);

        return view('admin.contratosmodelo.show', compact('contratoModelo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contratoModelo = ContratoModelo::with(['contrato', 'contrato.fornecedor', 'modelo'])->findOrFail($id);

        // Preparar dados dos contratos (apenas o atual para evitar mudanças acidentais)
        $contratos = [
            [
                'value' => $contratoModelo->contrato->id_contrato_forn,
                'label' => "#{$contratoModelo->contrato->id_contrato_forn} - {$contratoModelo->contrato->fornecedor->nome_fornecedor}"
            ]
        ];

        // Preparar dados dos modelos (apenas o atual para evitar mudanças acidentais)
        $modelos = [
            [
                'value' => $contratoModelo->modelo->id_modelo_veiculo,
                'label' => $contratoModelo->modelo->descricao_modelo_veiculo
            ]
        ];

        return view('admin.contratosmodelo.edit', compact('contratoModelo', 'contratos', 'modelos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'ativo' => 'boolean',
            ]);

            DB::beginTransaction();

            // Buscar o registro
            $contratoModelo = ContratoModelo::findOrFail($id);

            // Atualizar apenas o status ativo
            $contratoModelo->ativo = $validated['ativo'] ?? $contratoModelo->ativo;
            $contratoModelo->save();

            DB::commit();

            // Redirecionar com base na origem da requisição
            $redirectRoute = $request->input('redirect_to_contrato')
                ? route('admin.contratos.show', $contratoModelo->id_contrato)
                : route('admin.contratosmodelo.index');

            return redirect($redirectRoute)
                ->with('success', 'Vínculo entre contrato e modelo atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar vínculo entre contrato e modelo: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar vínculo entre contrato e modelo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $contratoModelo = ContratoModelo::findOrFail($id);

            // Verificar se existem relações que impediriam a exclusão
            $hasServicos = $contratoModelo->servicosFornecedor()->count() > 0;
            $hasPecas = $contratoModelo->pecasFornecedor()->count() > 0;

            if ($hasServicos || $hasPecas) {
                throw new \Exception('Não é possível excluir este vínculo pois existem serviços ou peças associados.');
            }

            // Excluir o vínculo
            $contratoModelo->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir vínculo entre contrato e modelo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Listar vínculos contrato-modelo por contrato
     */
    public function listarPorContrato(Request $request, $contratoId)
    {
        $contratosModelo = ContratoModelo::where('id_contrato', $contratoId)
            ->with(['modelo'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contratosModelo
        ]);
    }

    /**
     * Listar vínculos contrato-modelo por fornecedor
     */
    public function listarPorFornecedor(Request $request, $fornecedorId)
    {
        $contratosModelo = ContratoModelo::whereHas('contrato', function ($q) use ($fornecedorId) {
            $q->where('id_fornecedor', $fornecedorId)
                ->where('is_valido', true);
        })
            ->with(['modelo', 'contrato'])
            ->where('ativo', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contratosModelo
        ]);
    }

    /**
     * Clonar um vínculo entre contrato e modelo
     */
    public function clonar(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Buscar o vínculo a ser clonado
            $contratoModeloOriginal = ContratoModelo::findOrFail($id);

            // Verificar se já existe um vínculo igual
            $existingVinculo = ContratoModelo::where('id_contrato', $contratoModeloOriginal->id_contrato)
                ->where('id_modelo', $contratoModeloOriginal->id_modelo)
                ->where('ativo', true)
                ->where('id_contrato_modelo', '!=', $id)
                ->first();

            if ($existingVinculo) {
                throw new \Exception('Já existe um vínculo ativo entre este contrato e este modelo de veículo.');
            }

            // Criar um novo vínculo com os mesmos dados
            $novoContratoModelo = $contratoModeloOriginal->replicate();
            $novoContratoModelo->id_user = Auth::id();
            $novoContratoModelo->data_inclusao = now();
            $novoContratoModelo->data_alteracao = null;
            $novoContratoModelo->ativo = true;

            // Salvar o novo vínculo
            $novoContratoModelo->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vínculo entre contrato e modelo clonado com sucesso!',
                'id' => $novoContratoModelo->id_contrato_modelo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar vínculo entre contrato e modelo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao clonar vínculo entre contrato e modelo: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Buscar modelos disponíveis para um determinado contrato
     */
    public function buscarModelosDisponiveis(Request $request, $contratoId)
    {
        // Buscar modelos já vinculados a este contrato
        $modelosVinculados = ContratoModelo::where('id_contrato', $contratoId)
            ->where('ativo', true)
            ->pluck('id_modelo')
            ->toArray();

        // Buscar todos os modelos exceto os já vinculados
        $modelosDisponiveis = ModeloVeiculo::whereNotIn('id_modelo_veiculo', $modelosVinculados)
            ->orderBy('descricao_modelo_veiculo')
            ->get(['id_modelo_veiculo as value', 'descricao_modelo_veiculo as label']);

        return response()->json([
            'success' => true,
            'data' => $modelosDisponiveis
        ]);
    }
}
