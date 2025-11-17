<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manutencao;
use App\Models\PlanejamentoManutencao;
use App\Models\Servico;
use App\Models\ServicoPlanejamentoManutencao;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManutencaoXServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = PlanejamentoManutencao::where('status_planejamento', true);

        if ($request->filled('id')) {
            $query->where('id_planejamento_manutencao', $request->id);
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

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('id_servico')) {
            $query->where('id_servico', $request->id_servico);
        }

        // dd($query);
        $servico = $query->latest('id_planejamento_manutencao')
            ->paginate(10)
            ->withQueryString();

        $referenceDatas = $this->getReferenceDatas();

        return view('admin.manutencaoservico.index', array_merge(
            [
                'servico'      => $servico,
                'referenceDatas' => $referenceDatas,
            ]
        ));
    }

    public function getReferenceDatas()
    {
        return Cache::remember('config_mutencao_servico', now()->addHours(12), function () {
            return [
                'categoria' => Servico::where('ativo_servico', true)
                    ->orderBy('id_servico')
                    ->get(['id_servico as value', 'descricao_servico as label']),

                'manutencao' => Manutencao::orderBy('id_manutencao')
                    ->get(['id_manutencao as value', 'descricao_manutencao as label']),
            ];
        });
    }

    public function edit($id)
    {
        // dd($id);
        $manutencaoConfig = PlanejamentoManutencao::where('id_planejamento_manutencao', $id)->first();

        $servicos = Servico::where('ativo_servico', true)
            ->limit(30)
            ->orderBy('id_servico')
            ->get()
            ->map(function($servico) {
                return [
                    'value' => $servico->id_servico,
                    'label' => $servico->id_servico . ' - ' . $servico->descricao_servico
                ];
            });

        $planejamentos = Manutencao::orderBy('id_manutencao')->get()
            ->map(function($manutencao) {
                return [
                    'value' => $manutencao->id_manutencao,
                    'label' => $manutencao->id_manutencao . ' - ' . $manutencao->descricao_manutencao
                ];
            });

        return view('admin.manutencaoservico.edit', compact(
            'servicos',
            'planejamentos',
            'manutencaoConfig',
        ));
    }

    public function create()
    {
        $servicos = Servico::where('ativo_servico', true)
            ->orderBy('id_servico')
            ->get();

        $planejamentos = Manutencao::orderBy('id_manutencao')->get();

        return view('admin.manutencaoservico.create', compact(
            'servicos',
            'planejamentos',
        ));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // Validação dos campos principais
        $planejamento = $request->validate([
            'id_manutencao'         => 'required|string',
            'status_planejamento'   => 'required|in:true,false',
        ]);
        $planejamento['data_inclusao'] = now();
        $planejamento['status_planejamento'] = $request->status_planejamento === 'true';
        // Validação do campo de serviços (JSON)
        $request->validate([
            'servicos' => 'required|json',
        ]);

        // Decodifica o JSON dos serviços
        $servicos = json_decode($request->input('servicos'), true);

        try {
            DB::beginTransaction();

            // Cria o planejamento de manutenção
            $servicoManutencao = PlanejamentoManutencao::create($planejamento);

            // Itera sobre os serviços e os associa ao planejamento
            foreach ($servicos as $servico) {
                ServicoPlanejamentoManutencao::create([
                    'id_manutencao' => $servicoManutencao->id_planejamento_manutencao,
                    'id_servico' => $servico['id_servico'],
                    'data_inclusao' => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.manutencaoservico.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Planejamento por serviço cadastrado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            Log::error('Erro na criação do Planejamento por serviço:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.manutencaoservico.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o Planejamento por serviço."
                ]);
        }
    }


    public function update(Request $request, $id)
    {
        // Log inicial
        Log::info('Iniciando atualização do planejamento', [
            'id' => $id,
            'planejamento' => $request->only(['id_manutencao', 'status_planejamento', 'servicos'])
        ]);

        // Validação dos campos principais
        $planejamento = $request->validate([
            'id_manutencao'       => 'required|string',
            'status_planejamento' => 'required|in:true,false',
        ]);

        // Ajusta os valores
        $planejamento['data_alteracao'] = now();
        $planejamento['status_planejamento'] = $request->status_planejamento === 'true';

        // Validação do campo de serviços (JSON)
        $request->validate([
            'servicos' => 'required|json',
        ]);

        // Decodifica o JSON dos serviços
        $servicos = json_decode($request->input('servicos'), true);

        try {
            DB::beginTransaction();

            // Atualiza o planejamento de manutenção
            $planejamentoModel = PlanejamentoManutencao::findOrFail($id);
            $planejamentoModel->update($planejamento);
            Log::info('Planejamento atualizado', ['planejamento' => $planejamentoModel]);

            // Remove todos os serviços antigos
            ServicoPlanejamentoManutencao::where('id_manutencao', $planejamentoModel->id_planejamento_manutencao)->delete();
            Log::info('Serviços antigos removidos');

            // Itera sobre os serviços e os associa ao planejamento
            foreach ($servicos as $servico) {
                ServicoPlanejamentoManutencao::create([
                    'id_manutencao' => $planejamentoModel->id_planejamento_manutencao,
                    'id_servico'    => $servico['id_servico'],
                    'data_inclusao' => now(),
                ]);
            }
            Log::info('Serviços novos adicionados', ['servicos' => $servicos]);

            DB::commit();

            return redirect()
                ->route('admin.manutencaoservico.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Planejamento por serviço atualizado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro na atualização do Planejamento por serviço', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.manutencaoservico.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível atualizar o Planejamento por serviço."
                ]);
        }
    }



    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Busca o planejamento de manutenção
            $planejamento = PlanejamentoManutencao::where('id_planejamento_manutencao', $id)->first();

            if (!$planejamento) {
                return response()->json(['success' => false, 'message' => 'Planejamento não encontrado.'], 404);
            }

            // Desativa todos os serviços associados ao planejamento
            ServicoPlanejamentoManutencao::where('id_manutencao', $planejamento->id_planejamento_manutencao)
                ->update([
                    'ativo' => false,
                    'data_alteracao' => now(),
                ]);

            // Desativa o planejamento de manutenção
            $planejamento->update([
                'status_planejamento' => false,
                'data_alteracao' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao desativar o Planejamento por serviço:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
