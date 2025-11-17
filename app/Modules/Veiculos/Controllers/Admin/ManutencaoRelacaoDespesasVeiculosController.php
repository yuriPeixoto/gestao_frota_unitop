<?php

namespace App\Modules\Veiculos\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\DespesasVeiculos;
use App\Models\Fornecedor;
use App\Modules\Manutencao\Models\Servico;
use App\Models\TipoDespesas;
use App\Models\Veiculo;
use App\Models\Filial;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManutencaoRelacaoDespesasVeiculosController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = DespesasVeiculos::query();

        if ($request->filled('id_despesas_veiculos')) {
            $query->where('id_despesas_veiculos', $request->id_despesas_veiculos);
        }

        if ($request->filled('numero_nf')) {
            $query->where('numero_nf', $request->numero_nf);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }


        $relacaoDepesas = $query->latest('id_despesas_veiculos')
            ->paginate(10);

        $referenceDatas = $this->getReferenceDatas();

        // dd($referenceDatas);
        return view('admin.relacaodespesasveiculos.index', array_merge(
            [
                'relacaoDepesas'         => $relacaoDepesas,
                'referenceDatas'         => $referenceDatas,
            ]
        ));
    }

    public function getReferenceDatas()
    {
        return Cache::remember('config_despesas_veiculos', now()->addHours(12), function () {
            return [
                'veiculosFrequentes' => Veiculo::where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(['id_veiculo as value', 'placa as label']),

                'fornecedoresFrequentes' => Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                    ->orderBy('nome_fornecedor')
                    ->limit(20)
                    ->get(),

                'filiais' => Filial::orderBy('name')
                    ->get(['id as value', 'name as label']),

                'departamentos' => Departamento::orderBy('descricao_departamento')
                    ->get(['descricao_departamento as label', 'id_departamento as value']),

                'tipoDespesas' => TipoDespesas::orderBy('id_tipo_despesas')
                    ->get(['id_tipo_despesas as value', 'descricao_despesas as label'])
            ];
        });
    }

    public function create()
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->limit(20)
                ->orderBy('id_veiculo')
                ->get(['id_veiculo as value', 'placa as label']);
        });

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        $filiais          = Filial::orderBy('name')->get();
        $departamentos    = Departamento::select('id_departamento as value', 'descricao_departamento as label')->get();
        $tipoDespesas     = TipoDespesas::orderBy('id_tipo_despesas')->get();

        return view('admin.relacaodespesasveiculos.create', compact(
            'veiculosFrequentes',
            'fornecedoresFrequentes',
            'filiais',
            'departamentos',
            'tipoDespesas',
        ));
    }

    public function edit($id)
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->limit(20)
                ->orderBy('id_veiculo')
                ->get(['id_veiculo as value', 'placa as label']);
        });

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        $filiais          = Filial::orderBy('name')->get();
        $departamentos    = Departamento::select('id_departamento as value', 'descricao_departamento as label')->get();
        $tipoDespesas     = TipoDespesas::orderBy('id_tipo_despesas')->get();

        // Use find() em vez de where()->first() para melhor tratamento
        $manutencaoConfig = DespesasVeiculos::find($id);

        // Debug: verifique se está encontrando o registro
        if (!$manutencaoConfig) {
            abort(404, 'Registro não encontrado');
        }

        return view('admin.relacaodespesasveiculos.edit', compact(
            'veiculosFrequentes',
            'fornecedoresFrequentes',
            'filiais',
            'departamentos',
            'tipoDespesas',
            'manutencaoConfig',
        ));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $relacaoDespesas = $request->validate([
            'id_veiculo'        => 'required|string',
            'id_departamento'   => 'required|string',
            'id_filial'         => 'required|string',
            'valor_despesa'     => 'nullable|string',
            'valor_frete'       => 'nullable|string',
            'valor_pago'        => 'nullable|string',
            'numero_nf'         => 'nullable|string',
            'serie_nf'          => 'nullable|string',
            'id_fornecedor'     => 'required|string',
            'id_tipo_despesas'  => 'nullable|string',
            'aplicar_rateio'    => 'required|string',
            'observacao'        => 'nullable|string',
        ]);

        $relacaoDespesas['data_inclusao'] = now();

        try {
            DB::beginTransaction();

            DespesasVeiculos::create($relacaoDespesas);

            DB::commit();

            return redirect()->route('admin.relacaodespesasveiculos.index')->with('notification', [
                'type' => 'success',
                'title' => 'Operação concluída',
                'message' => 'Os dados foram salvos com sucesso.',
                'duration' => 3000, // opcional (padrão: 5000ms)
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            log::error('Error ao gravar a despesa: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação não concluída',
                'message' => 'error: ' . $e->getMessage(),
                'duration' => 3000, // opcional (padrão: 5000ms)
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Dados recebidos no update', $request->all());

        $relacaoDespesas = $request->validate([
            'id_veiculo'        => 'required|string',
            'id_departamento'   => 'required|string',
            'id_filial'         => 'required|string',
            'valor_despesa'     => 'nullable|string',
            'valor_frete'       => 'nullable|string',
            'valor_pago'        => 'nullable|string',
            'numero_nf'         => 'nullable|string',
            'serie_nf'          => 'nullable|string',
            'id_fornecedor'     => 'required|string',
            'id_tipo_despesas'  => 'nullable|string',
            'aplicar_rateio'    => 'required|string',
            'observacao'        => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $manutencaoConfig = DespesasVeiculos::findOrFail($id);

            // adiciona a data de alteração
            $relacaoDespesas['data_alteracao'] = now();

            // aplica os dados validados e salva
            $manutencaoConfig->update($relacaoDespesas);

            Log::info('itens salvos ->', $manutencaoConfig->toArray());
            DB::commit();

            return redirect()
                ->route('admin.relacaodespesasveiculos.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Relação de despesas editada com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gravar a despesa: ' . $e->getMessage());

            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'title'   => 'Operação não concluída',
                'message' => 'error: ' . $e->getMessage(),
                'duration' => 3000,
            ]);
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $manutencaoConfig = DespesasVeiculos::where('id_despesas_veiculos', $id)->first();
            $manutencaoConfig->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    function sanitizeToDouble($valor)
    {
        // Remove R$, espaços e pontos de milhar
        $valor = str_replace(['R$', ' ', '.'], '', $valor);

        // Substitui vírgula por ponto
        $valor = str_replace(',', '.', $valor);

        // Converte para float
        return (float) $valor;
    }
}
