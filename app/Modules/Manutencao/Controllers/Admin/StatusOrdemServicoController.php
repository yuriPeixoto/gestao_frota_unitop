<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatusOrdemServico;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatusOrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = StatusOrdemServico::query();


        if ($request->filled('id_status_ordem_servico')) {
            $query->where('id_status_ordem_servico', $request->id_status_ordem_servico);
        }

        if ($request->filled('situacao_ordem_servico')) {
            $query->where('situacao_ordem_servico', $request->situacao_ordem_servico);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_alteracao_inicial')) {
            $query->whereDate('data_alteracao', '>=', $request->data_alteracao_inicial);
        }

        if ($request->filled('data_alteracao_final')) {
            $query->whereDate('data_alteracao', '<=', $request->data_alteracao_final);
        }


        $statusOrdemServico = $query->latest('id_status_ordem_servico')
            ->paginate(10)
            ->withQueryString();

        // dd($referenceDatas);
        return view('admin.statusordemservico.index', array_merge(
            [
                'statusOrdemServico'         => $statusOrdemServico,
            ]
        ));
    }

    public function create()
    {
        return view('admin.statusordemservico.create');
    }

    public function edit($id)
    {
        $statusOrdemServico = StatusOrdemServico::where('id_status_ordem_servico', $id)->first();

        return view('admin.statusordemservico.edit', compact(
            'statusOrdemServico',
        ));
    }

    public function store(Request $request)
    {


        $statusOs = $request->validate([
            'situacao_ordem_servico' => 'required|string',
        ]);
        $statusOs['data_inclusao'] = now();


        try {
            DB::beginTransaction();

            StatusOrdemServico::create($statusOs);

            DB::commit();

            return redirect()
                ->route('admin.statusordemservico.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Status Ordem Serviço cadastrado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            Log::error('Erro na criação do Status Ordem Serviço:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.statusordemservico.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o Status Ordem Serviço."
                ]);
        }
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());

        $statusOs = $request->validate([
            'situacao_ordem_servico'  => 'required|string',
        ]);
        $statusOs['data_alteracao'] = now();

        try {
            DB::beginTransaction();

            $ordemServicoStatus = StatusOrdemServico::where('id_status_ordem_servico', $id)->first();
            $ordemServicoStatus->update($statusOs);

            DB::commit();

            return redirect()
                ->route('admin.statusordemservico.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Status Ordem Seviço editado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            Log::error('Erro na criação do Status Ordem Seviço:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.statusordemservico.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar o Status Ordem Seviço."
                ]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            StatusOrdemServico::where('id_status_ordem_servico', $id)->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
