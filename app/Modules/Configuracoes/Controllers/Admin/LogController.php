<?php

namespace App\Modules\Configuracoes\Controllers\Admin;

use App\Models\ActivityLog;
use App\Modules\Configuracoes\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Validação dos filtros
        $request->validate([
            'criticality' => 'nullable|in:low,medium,high,critical',
            'category' => 'nullable|in:security,financial,operational,administrative',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'user_id' => 'nullable|exists:users,id',
            'model' => 'nullable|string',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        // Query otimizada com cache de usuários
        $query = ActivityLog::with(['user:id,name'])
            ->select([
                'id', 'user_id', 'action', 'model', 'model_id',
                'old_values', 'new_values', 'ip_address', 'user_agent',
                'summary', 'criticality', 'category', 'tags', 'created_at'
            ])
            // Filtros de permissão
            ->when(auth()->user()->cannot('ver_todas_atividades'), function (Builder $query) {
                $query->where('user_id', auth()->id());
            })
            // Filtro para excluir atualizações irrelevantes de User
            ->excludeLastLoginUpdates()
            // Novos filtros
            ->when($request->filled('criticality'), function (Builder $query) use ($request) {
                $query->where('criticality', $request->criticality);
            })
            ->when($request->filled('category'), function (Builder $query) use ($request) {
                $query->where('category', $request->category);
            })
            ->when($request->filled('date_from'), function (Builder $query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function (Builder $query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('user_id'), function (Builder $query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('model'), function (Builder $query) use ($request) {
                $query->where('model', 'LIKE', '%' . $request->model . '%');
            })
            ->latest('created_at');

        $activities = $query->paginate($request->per_page ?? 15);

        // Cache de usuários ativos
        $users = Cache::remember('activity_log_users_' . now()->format('Y-m-d-H'), 3600, function() {
            return User::select('id', 'name')
                ->whereIn('id', function($query) {
                    $query->select('user_id')
                          ->from('activity_logs')
                          ->where('created_at', '>=', now()->subWeek())
                          ->distinct();
                })
                ->get()
                ->keyBy('id');
        });

        // Estatísticas para o cabeçalho
        $stats = $this->getHeaderStats();

        return view('admin.log-atividades.index', [
            'activities' => $activities,
            'users' => $users,
            'filters' => $request->only(['criticality', 'category', 'date_from', 'date_to', 'user_id', 'model']),
            'stats' => $stats,
        ]);
    }

    public function dashboard()
    {
        // Verificar permissão
        if (auth()->user()->cannot('ver_dashboard_atividades')) {
            return redirect()->route('admin.log-atividades.index')
                           ->with('warning', 'Você não tem permissão para acessar o dashboard.');
        }

        $stats = ActivityLog::getDashboardStats();
        $criticalAlerts = ActivityLog::getCriticalAlerts();

        // Gráficos de atividade (últimos 7 dias)
        $activityChart = Cache::remember('activity_chart_7days', 1800, function() {
            return ActivityLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->date => $item->count];
                });
        });

        // Top usuários mais ativos
        $topUsers = Cache::remember('top_users_today', 1800, function() {
            return ActivityLog::with('user:id,name')
                ->select('user_id', DB::raw('COUNT(*) as activity_count'))
                ->whereDate('created_at', today())
                ->groupBy('user_id')
                ->orderByDesc('activity_count')
                ->limit(10)
                ->get();
        });

        return view('admin.log-atividades.dashboard', compact(
            'stats', 'criticalAlerts', 'activityChart', 'topUsers'
        ));
    }

    public function show(ActivityLog $log)
    {
        // Verificar permissões
        if (auth()->user()->cannot('ver_todas_atividades') && $log->user_id !== auth()->id()) {
            abort(403, 'Você não tem permissão para ver este log.');
        }

        $log->load(['user:id,name']);

        // DEBUG TEMPORÁRIO: Verificar dados brutos do banco
        logger()->info('ActivityLog show DEBUG', [
            'log_id' => $log->id,
            'old_values_from_db' => $log->getRawOriginal('old_values'),
            'new_values_from_db' => $log->getRawOriginal('new_values'),
            'old_values_cast' => $log->old_values,
            'new_values_cast' => $log->new_values,
            'old_values_type' => gettype($log->old_values),
            'new_values_type' => gettype($log->new_values),
        ]);

        // Logs relacionados (mesmo modelo e ID)
        $relatedLogs = ActivityLog::with('user:id,name')
            ->where('model', $log->model)
            ->where('model_id', $log->model_id)
            ->where('id', '!=', $log->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.log-atividades.show', compact('log', 'relatedLogs'));
    }

    public function export(Request $request)
    {
        // Validar se o usuário tem permissão para exportar dados completos
        if (auth()->user()->cannot('ver_auditoria_completa')) {
            abort(403, 'Você não tem permissão para exportar dados completos de auditoria.');
        }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'model' => 'nullable|string',
            'criticality' => 'nullable|in:low,medium,high,critical',
            'category' => 'nullable|in:security,financial,operational,administrative',
        ]);

        $activities = ActivityLog::with('user')
            ->when(auth()->user()->cannot('ver_todas_atividades'), function (Builder $query) {
                $query->where('user_id', auth()->id());
            })
            ->when($request->filled('start_date'), function (Builder $query) use ($request) {
                $query->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function (Builder $query) use ($request) {
                $query->whereDate('created_at', '<=', $request->end_date);
            })
            ->when($request->filled('user_id'), function (Builder $query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('model'), function (Builder $query) use ($request) {
                $query->where('model', $request->model);
            })
            ->when($request->filled('criticality'), function (Builder $query) use ($request) {
                $query->where('criticality', $request->criticality);
            })
            ->when($request->filled('category'), function (Builder $query) use ($request) {
                $query->where('category', $request->category);
            })
            ->latest()
            ->limit(5000) // Limitar para evitar timeout
            ->get();

        $content = $this->generateExportContent($activities, $request);

        $filename = 'log_atividades_completo_' . now()->format('Y-m-d_H-i-s') . '.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function cleanup()
    {
        // Verificar permissão de administrador
        if (auth()->user()->cannot('administrar_sistema')) {
            abort(403, 'Você não tem permissão para executar limpeza de logs.');
        }

        $deleted = ActivityLog::cleanup();

        return response()->json([
            'success' => true,
            'message' => "Limpeza concluída. {$deleted} logs antigos foram removidos.",
            'deleted_count' => $deleted
        ]);
    }

    public function getCriticalAlerts()
    {
        $alerts = ActivityLog::getCriticalAlerts();

        return response()->json([
            'alerts' => $alerts->map(function($alert) {
                return [
                    'id' => $alert->id,
                    'summary' => $alert->getFormattedSummary(),
                    'user' => $alert->user->name ?? 'Sistema',
                    'criticality' => $alert->criticality,
                    'category' => $alert->category,
                    'created_at' => $alert->created_at->diffForHumans(),
                    'icon' => $alert->getCriticalityIcon(),
                ];
            }),
            'count' => $alerts->count()
        ]);
    }

    private function getHeaderStats()
    {
        return Cache::remember('log_header_stats', 300, function() {
            $today = now()->startOfDay();

            return [
                'today_total' => ActivityLog::whereDate('created_at', $today)->count(),
                'today_critical' => ActivityLog::critical()->whereDate('created_at', $today)->count(),
                'today_high' => ActivityLog::high()->whereDate('created_at', $today)->count(),
                'active_users_today' => ActivityLog::whereDate('created_at', $today)->distinct('user_id')->count(),
                'last_activity' => ActivityLog::latest()->first()?->created_at?->diffForHumans(),
            ];
        });
    }

    private function generateExportContent($activities, $request)
    {
        $content = "RELATÓRIO COMPLETO DE LOG DE ATIVIDADES\n";
        $content .= "========================================\n\n";
        $content .= "Gerado em: " . now()->format('d/m/Y H:i:s') . "\n";
        $content .= "Usuário: " . auth()->user()->name . " (ID: " . auth()->id() . ")\n";
        $content .= "Total de registros: " . $activities->count() . "\n";

        // Incluir filtros aplicados
        if ($request->hasAny(['start_date', 'end_date', 'user_id', 'model', 'criticality', 'category'])) {
            $content .= "\nFiltros Aplicados:\n";
            if ($request->start_date) $content .= "- Data inicial: {$request->start_date}\n";
            if ($request->end_date) $content .= "- Data final: {$request->end_date}\n";
            if ($request->user_id) $content .= "- Usuário: ID {$request->user_id}\n";
            if ($request->model) $content .= "- Modelo: {$request->model}\n";
            if ($request->criticality) $content .= "- Criticidade: {$request->criticality}\n";
            if ($request->category) $content .= "- Categoria: {$request->category}\n";
        }

        $content .= "\n" . str_repeat("=", 80) . "\n\n";

        foreach ($activities as $activity) {
            $content .= "ID: {$activity->id}\n";
            $content .= "Data/Hora: {$activity->created_at->format('d/m/Y H:i:s')}\n";
            $content .= "Usuário: " . ($activity->user->name ?? 'Sistema') . " (ID: {$activity->user_id})\n";
            $content .= "Ação: " . formatActivityAction($activity->action) . "\n";
            $content .= "Modelo: " . formatActivityModel($activity->model) . "\n";
            $content .= "ID do Registro: {$activity->model_id}\n";

            // Novos campos
            if ($activity->summary) {
                $content .= "Resumo: {$activity->summary}\n";
            }

            if ($activity->criticality) {
                $content .= "Criticidade: {$activity->getCriticalityIcon()} {$activity->criticality}\n";
            }

            if ($activity->category) {
                $content .= "Categoria: {$activity->getCategoryIcon()} {$activity->category}\n";
            }

            if ($activity->tags) {
                $content .= "Tags: " . implode(', ', $activity->tags) . "\n";
            }

            if ($activity->ip_address) {
                $content .= "Endereço IP: {$activity->ip_address}\n";
            }

            if ($activity->user_agent) {
                $content .= "User Agent: {$activity->user_agent}\n";
            }

            if ($activity->old_values) {
                $content .= "\nValores Anteriores:\n";
                $content .= str_repeat("-", 20) . "\n";
                $content .= json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }

            if ($activity->new_values) {
                $content .= "\nNovos Valores:\n";
                $content .= str_repeat("-", 20) . "\n";
                $content .= json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }

            $content .= "\n" . str_repeat("=", 80) . "\n\n";
        }

        $content .= "FIM DO RELATÓRIO\n";
        $content .= "Relatório gerado pelo sistema de gestão de frota.\n";
        $content .= "Sistema com monitoramento inteligente de atividades.\n";

        return $content;
    }
}
