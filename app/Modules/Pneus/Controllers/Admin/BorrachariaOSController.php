<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\StatusOrdemServico;
use App\Models\TipoOrdemServico;
use App\Models\User;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrachariaOSController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemServico::query()
            ->borracharia()
            ->with([
                'veiculo',
                'tipoOrdemServico',
                'statusOrdemServico',
                'recepcionista',
                'recepcionistaEncerramento',
                'filial',
            ])
            ->where(function ($q) {
                $q->whereNull('is_cancelada')->orWhere('is_cancelada', false);
            });

        // Filtro: Código da Ordem Serviço
        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->input('id_ordem_servico'));
        }

        // Filtro: Data abertura
        if ($request->filled('data_abertura')) {
            $query->whereDate('data_abertura', $request->input('data_abertura'));
        }

        // Filtro: Tipo Ordem Serviço (dropdown)
        if ($request->filled('id_tipo_ordem_servico')) {
            $query->where('id_tipo_ordem_servico', $request->input('id_tipo_ordem_servico'));
        }

        // Filtro: Situação Ordem de Serviço (dropdown)
        if ($request->filled('id_status_ordem_servico')) {
            $query->where('id_status_ordem_servico', $request->input('id_status_ordem_servico'));
        }

        // Filtro: Código Lançamento OS Auxiliar
        if ($request->filled('id_lancamento_os_auxiliar')) {
            $query->where('id_lancamento_os_auxiliar', $request->input('id_lancamento_os_auxiliar'));
        }

        // Filtro: Placa (via relacionamento com veículo)
        if ($request->filled('placa')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('placa', 'ilike', '%'.$request->input('placa').'%');
            });
        }

        // Filtro: Recepcionista
        if ($request->filled('id_recepcionista')) {
            $query->where('id_recepcionista', $request->input('id_recepcionista'));
        }

        // Filtro: Local Manutenção
        if ($request->filled('local_manutencao')) {
            $query->where('local_manutencao', $request->input('local_manutencao'));
        }

        // Filtro: Filial
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        // Ordenação padrão como no legado
        $ordens = $query->orderByDesc('id_ordem_servico')
            ->paginate(40) // Mesmo limite do legado
            ->appends($request->query());

        // Carregar dados para os dropdowns dos filtros
        $tiposOrdem = TipoOrdemServico::orderBy('descricao_tipo_ordem')->get();
        $statusOrdem = StatusOrdemServico::orderBy('situacao_ordem_servico')->get();
        $recepcionistas = User::where('is_ativo', true)->orderBy('name')->get();
        $filiais = VFilial::orderBy('name')->get();

        $locaisManutencao = [
            'INTERNO' => 'INTERNO',
            'EXTERNO' => 'EXTERNO',
        ];

        return view('pneus.borracharia_os.index', compact(
            'ordens',
            'tiposOrdem',
            'statusOrdem',
            'recepcionistas',
            'filiais',
            'locaisManutencao'
        ));
    }

    public function assume($id)
    {
        $os = OrdemServico::borracharia()->findOrFail($id);

        if ($os->id_recepcionista) {
            return back()->with('error', 'Esta OS já foi assumida por outro recepcionista.');
        }

        // Validar se OS está em status que permite assumir (5 = ABERTA, 1 = EM ANDAMENTO)
        if (! in_array($os->id_status_ordem_servico, [1, 5])) {
            return back()->with('error', 'OS não está em situação que permite ser assumida.');
        }

        $os->id_recepcionista = Auth::id();
        $os->id_status_ordem_servico = 7; // EM ANDAMENTO como no legado
        $os->data_inicio_ordem = now();
        $os->save();

        return back()->with('success', 'OS assumida com sucesso.');
    }

    public function reopen($id)
    {
        $os = OrdemServico::borracharia()->findOrFail($id);

        if ($os->id_status_ordem_servico != 4) { // Só pode reabrir se estiver FINALIZADA
            return back()->with('error', 'Apenas OS finalizadas podem ser reabertas.');
        }

        // Limpar histórico de manutenção como no legado
        DB::table('historico_manutencao')
            ->where('id_ordem_servico', $id)
            ->delete();

        // Reabrir OS
        $os->id_status_ordem_servico = 2; // Status REABERTA
        $os->data_encerramento = null;
        $os->data_hora_finalizacao = null;
        $os->id_recepcionista_encerramento = null;
        $os->save();

        return back()->with('success', 'OS reaberta com sucesso!');
    }

    public function cancel($id)
    {
        $os = OrdemServico::borracharia()->findOrFail($id);
        $os->is_cancelada = true;
        $os->data_hora_cancelamento = now();
        $os->save();

        return back()->with('success', 'OS cancelada com sucesso.');
    }

    public function print($id)
    {
        // Usar a rota de impressão existente do controller principal
        return redirect()->route('admin.ordemservicos.imprimir', $id);
    }

    public function delete($id)
    {
        $os = OrdemServico::borracharia()->findOrFail($id);
        $os->delete();

        return back()->with('success', 'OS excluída com sucesso.');
    }
}
