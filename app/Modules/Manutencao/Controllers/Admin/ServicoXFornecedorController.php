<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\Servico;
use App\Models\ServicoXFornecedor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ServicoXFornecedorController extends Controller
{
    public function index(Request $request)
    {
        $query = ServicoXFornecedor::query();

        if ($request->filled('id')) {
            $query->where('id_precoservicoxfornecedor', $request->id);
        }

        if ($request->filled('data_inclusao_inicio')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicio);
        }

        if ($request->filled('data_inclusao_fim')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_fim);
        }

        if ($request->filled('data_alteracao_inicio')) {
            $query->whereDate('data_alteracao', '>=', $request->data_alteracao_inicio);
        }

        if ($request->filled('data_alteracao_fim')) {
            $query->whereDate('data_alteracao', '<=', $request->data_alteracao_fim);
        }

        if ($request->filled('id_servico')) {
            $query->where('id_servico', $request->id_servico);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        $servicoFornecedor = $query->latest('id_precoservicoxfornecedor')
            ->with('servico', 'fornecedor')
            ->paginate(10);

        $referenceDatas = $this->getReferenceDatas();

        // dd($referenceDatas);
        return view('admin.servicofornecedor.index', array_merge(
            [
                'servicoFornecedor'      => $servicoFornecedor,
                'referenceDatas'         => $referenceDatas,
            ]
        ));
    }

    public function getReferenceDatas()
    {
        return Cache::remember('config_servico_fornecedor', now()->addHours(12), function () {
            return [
                'servico' => Servico::where('ativo_servico', true)
                    ->orderBy('id_servico')
                    ->limit(100)
                    ->get(['id_servico as value', 'descricao_servico as label']),

                'fornecedor' => Fornecedor::orderBy('id_fornecedor')
                    ->limit(100)
                    ->get(['id_fornecedor as value', 'nome_fornecedor as label']),
            ];
        });
    }

    public function edit($id)
    {
        $manutencaoConfig = ServicoXFornecedor::where('id_precoservicoxfornecedor', $id)->first();

        $servicosFrequentes = Cache::remember('servicos_frequentes', now()->addHours(12), function () {
            return Servico::where('ativo_servico', true)
                ->limit(20)
                ->orderBy('descricao_servico')
                ->get(['id_servico as value', 'descricao_servico as label']);
        });

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        return view('admin.servicofornecedor.edit', compact(
            'servicosFrequentes',
            'fornecedoresFrequentes',
            'manutencaoConfig'
        ));
    }

    public function create()
    {
        $servicosFrequentes = Cache::remember('servicos_frequentes', now()->addHours(12), function () {
            return Servico::where('ativo_servico', true)
                ->limit(20)
                ->orderBy('descricao_servico')
                ->get(['id_servico as value', 'descricao_servico as label']);
        });

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::where('is_ativo', true)
                ->limit(20)
                ->orderBy('nome_fornecedor')
                ->get(['id_fornecedor as value', 'nome_fornecedor as label']);
        });

        return view('admin.servicofornecedor.create', compact(
            'servicosFrequentes',
            'fornecedoresFrequentes',
        ));
    }

    public function store(Request $request)
    {
        $servicoFornecedor = $request->validate([
            'id_servico'                => 'required|string',
            'id_fornecedor'             => 'required|string',
            'valor_servico_fornecedor'  => 'required|string',
        ]);

        $servicoFornecedor['data_inclusao'] = now();
        $servicoFornecedor['valor_servico_fornecedor'] = sanitizeToDouble($servicoFornecedor['valor_servico_fornecedor']);

        try {
            DB::beginTransaction();

            ServicoXFornecedor::create($servicoFornecedor);

            DB::commit();

            return redirect()
                ->route('admin.servicofornecedor.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Serviço X Fornecedor cadastrado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação do Serviço X Fornecedor:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.servicofornecedor.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o Serviço X Fornecedor."
                ]);
        }
    }

    public function update(Request $request, $id)
    {
        $servicoFornecedor = $request->validate([
            'id_servico'                => 'required|string',
            'id_fornecedor'             => 'required|string',
            'valor_servico_fornecedor'  => 'required|string',
        ]);

        $servicoFornecedor['data_alteracao'] = now();
        $servicoFornecedor['valor_servico_fornecedor'] = sanitizeToDouble($servicoFornecedor['valor_servico_fornecedor']);

        try {
            DB::beginTransaction();

            $buscaRegistro = ServicoXFornecedor::findOrFail($id);
            $buscaRegistro->update($servicoFornecedor);

            DB::commit();

            return redirect()
                ->route('admin.servicofornecedor.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Serviço X Fornecedor editado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro na edição do Serviço X Fornecedor:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.servicofornecedor.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar o Serviço X Fornecedor."
                ]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $buscaRegistro = ServicoXFornecedor::where('id_precoservicoxfornecedor', $id)->first();
            $buscaRegistro->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    function sanitizeToDouble($valor)
    {
        // Remove espaços e tudo que não seja número, vírgula ou ponto
        $valor = trim($valor);

        // Remove pontos de milhar
        $valor = str_replace('.', '', $valor);

        // Substitui vírgula por ponto (decimal)
        $valor = str_replace(',', '.', $valor);

        // Converte para float
        return (float) $valor;
    }
}
