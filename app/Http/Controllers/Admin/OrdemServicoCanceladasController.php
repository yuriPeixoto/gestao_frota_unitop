<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\OrdemServico;
use App\Models\TipoOrdemServico;
use App\Models\Veiculo;
use App\Models\VFilial;
use App\Models\StatusOrdemServico;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\ExportableTrait;

class OrdemServicoCanceladasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = OrdemServico::query();

        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->id_ordem_servico);
        }

        if ($request->filled('data_abertura')) {
            $query->whereDate('data_abertura', $request->data_abertura);
        }

        if ($request->filled('id_tipo_ordem_servico')) {
            $query->where('id_tipo_ordem_servico', $request->id_tipo_ordem_servico);
        }

        if ($request->filled('id_status_ordem_servico')) {
            $query->where('id_status_ordem_servico', $request->id_status_ordem_servico);
        }

        if ($request->filled('id_lancamento_os_auxiliar')) {
            $query->where('id_lancamento_os_auxiliar', '=', $request->id_lancamento_os_auxiliar);
        }

        if ($request->filled('recepcionista')) {
            $query->where('id_recepcionista', '=', $request->recepcionista);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', '=', $request->id_veiculo);
        }

        if ($request->filled('local_manutencao')) {
            $query->where('local_manutencao', '=', $request->local_manutencao);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        $ordemServicoCanceladas = $query->latest('id_ordem_servico')
            ->where('is_cancelada', true) //-> Regra de carregamento somente as não canceladas
            ->whereNotin('id_veiculo', [11231]) //-> Regra de carregamento para não mostrar o veiculo BOR0001
            ->paginate(40)
            ->appends($request->query());


        if ($request->header('HX-Request')) {
            return view('admin.ordemservicocanceladas._table', compact('ordemServicoCanceladas'));
        }

        // Carregamento direto dos dados para os selects sem formatação adicional
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('placa')
                ->limit(20)
                ->get(['id_veiculo as value', 'placa as label']);
        });

        $filiais = VFilial::orderBy('name')
            ->get()
            ->map(function ($filial) {
                return [
                    'label' => $filial->name,
                    'value' => $filial->id
                ];
            })
            ->values()
            ->toArray();

        $tipoOrdemServico = TipoOrdemServico::orderBy('descricao_tipo_ordem')
            ->get()
            ->map(function ($tipoOrdemServico) {
                return [
                    'label' => $tipoOrdemServico->descricao_tipo_ordem,
                    'value' => $tipoOrdemServico->id_tipo_ordem_servico
                ];
            })
            ->values()
            ->toArray();

        $situacaoOrdemServico = StatusOrdemServico::orderBy('situacao_ordem_servico')
            ->get()
            ->map(function ($situacaoOrdemServico) {
                return [
                    'label' => $situacaoOrdemServico->situacao_ordem_servico,
                    'value' => $situacaoOrdemServico->id_status_ordem_servico
                ];
            })
            ->values()
            ->toArray();

        $usuariosFrequentes = Cache::remember('usuarios_frequentes', now()->addHours(12), function () {
            return User::orderBy('name')
                ->limit(20)
                ->get(['id as value', 'name as label']);
        });

        return view('admin.ordemservicocanceladas.index', compact(
            'ordemServicoCanceladas',
            'veiculosFrequentes',
            'filiais',
            'tipoOrdemServico',
            'situacaoOrdemServico',
            'usuariosFrequentes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function onRetornarOSFinalizada(string $id)
    {
        try {
            DB::beginTransaction();

            OrdemServico::where('id_ordem_servico', $id)
                ->update([
                    'id_status_ordem_servico' => 2,
                    'is_cancelada' => false,
                    'data_alteracao' => now(),
                ]);

            DB::commit();
            return redirect()->route('admin.ordemservicocanceladas.index')->with('success', 'O.S Reaberta com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.ordemservicocanceladas.index')->with('error', $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        $osCancelada = OrdemServico::findorfail($request->id);

        // Configurar opções do PDF de forma mais simples
        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('a4', 'landscape');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.ordemservicocanceladas.pdf', compact('osCancelada'));

        return $pdf->download('osCanceladas_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $ordemServico = OrdemServico::find($id);
            $ordemServico->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('admin.ordemservicocanceladas.index')->with('success', 'O.S Cancelada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return redirect()->route('admin.ordemservicocanceladas.index')->with('error', $e->getMessage());
        }
    }
}
