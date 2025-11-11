<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Certificados\Models\Licenciamentos;
use App\Models\SmartecVeiculo;
use App\Models\VSmartecMultasSneDetran;
use App\Models\SmartecNotificacoesSneDetran;
use App\Models\VSmartecNotificacoesSneDetran;
use App\Models\ListagemAntt;
use App\Models\SmartecIpva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardMultasController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');

        // Indicadores de Veículos
        $indicadores = [
            'veiculos' => SmartecVeiculo::count(),
            'licenciados' => SmartecVeiculo::where('licenciamento_vigente', 'ilike', '%LICENCIAMENTO EM DIA%')->count(),
            'nao_licenciados' => SmartecVeiculo::where('licenciamento_vigente', 'not ilike', 'LICENCIAMENTO EM DIA')->count(),
            'restricoes' => SmartecVeiculo::where('restricoes', '!=', 'NADA CONSTA')->count(),
            'ipva_total' => SmartecIpva::sum(DB::raw('CAST(valor AS NUMERIC)')),
            'licenciamento_valor' => Licenciamentos::sum('valor'),

            // Indicadores de Notificações
            'total_notificacoes' => SmartecNotificacoesSneDetran::count(),
            'valor_notificacoes' => SmartecNotificacoesSneDetran::sum('valor_a_pagar'),

            // Indicadores de Multas
            'multas_total' => VSmartecMultasSneDetran::count(),
            'valor_multas' => VSmartecMultasSneDetran::sum(DB::raw('CAST(valor_a_pagar AS NUMERIC)')),

            // Indicadores ANTT
            'multa_antt' => ListagemAntt::count(),
            'vlr_antt' => ListagemAntt::sum('valor'),

            // Multas por vencimento
            'valor_vencidas' => VSmartecMultasSneDetran::whereDate('vencimento_infracao', '<=', $today)
                ->sum(DB::raw('CAST(valor_a_pagar AS NUMERIC)')),
            'desconto_perdido' => VSmartecMultasSneDetran::whereDate('vencimento_infracao', '<=', $today)
                ->sum(DB::raw('CAST(valor_desconto AS NUMERIC)')),
            'multa_avencer' => VSmartecMultasSneDetran::whereDate('vencimento_infracao', '>=', $today)
                ->sum(DB::raw('CAST(valor_a_pagar AS NUMERIC)')),
            'multa_desconto_a_vencer' => VSmartecMultasSneDetran::whereDate('vencimento_infracao', '>=', $today)
                ->sum(DB::raw('CAST(valor_com_desconto AS NUMERIC)'))
        ];

        // Dados para gráficos
        $graficos = [
            'multas_por_placa' => VSmartecMultasSneDetran::select('placa')
                ->selectRaw('COALESCE(SUM(CAST(valor_a_pagar AS NUMERIC)), 0) as total')
                ->whereNotNull('placa')
                ->where('placa', '<>', '')
                ->groupBy('placa')
                ->havingRaw('COALESCE(SUM(CAST(valor_a_pagar AS NUMERIC)), 0) > 0')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'placa' => $item->placa ?: 'Sem placa',
                        'total' => (float)$item->total
                    ];
                }),


            'notificacoes_por_orgao' => SmartecNotificacoesSneDetran::select('orgao_autuador')
                ->selectRaw('COUNT(*) as total')
                ->whereNotNull('orgao_autuador')
                ->where('orgao_autuador', '!=', '')
                ->where('orgao_autuador', '!=', ' ')
                ->groupBy('orgao_autuador')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),

            'notificacoes_por_gravidade' => VSmartecNotificacoesSneDetran::select('gravidade')
                ->selectRaw('COUNT(*) as total')
                ->whereNotNull('gravidade')
                ->where('gravidade', '!=', '')
                ->where('gravidade', '!=', ' ')
                ->groupBy('gravidade')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),

            'multas_por_veiculo' => VSmartecMultasSneDetran::select('placa')
                ->selectRaw('COUNT(*) as total')
                ->whereNotNull('placa')
                ->where('placa', '<>', '')
                ->groupBy('placa')
                ->havingRaw('COUNT(*) > 0')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'placa' => $item->placa ?: 'Sem placa',
                        'total' => (int)$item->total
                    ];
                })
        ];

        //dd($graficos);

        return view('admin.dashboard-multas.index', compact('indicadores', 'graficos'));
    }

    private function formatCurrency($value)
    {
        return 'R$ ' . number_format($value ?? 0, 2, ',', '.');
    }
}
