<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ActivityLog extends Model
{
    // Constantes para criticidade
    const CRITICALITY_LOW = 'low';
    const CRITICALITY_MEDIUM = 'medium';
    const CRITICALITY_HIGH = 'high';
    const CRITICALITY_CRITICAL = 'critical';

    // Constantes para categorias
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_OPERATIONAL = 'operational';
    const CATEGORY_ADMINISTRATIVE = 'administrative';

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        // Campos extras comentados até migração ser executada:
        // 'criticality',
        // 'category',
        // 'summary',
        // 'tags',
        // 'retention_days',
        // 'affected_users',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'affected_users' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'criticality' => self::CRITICALITY_MEDIUM,
        'retention_days' => 90,
    ];

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo('subject', 'model', 'model_id');
    }

    // Scopes existentes (mantidos)
    public function scopeExcludeLastLoginUpdates(Builder $query): void
    {
        $query->where(function ($query) {
            $query->where('model', '!=', 'User')
                ->orWhere(function ($q) {
                    $q->where('model', 'User')
                        ->where(function ($subQuery) {
                            // Compara os JSONs excluindo o campo last_login_at
                            $subQuery->whereRaw("
                                jsonb_delete_path(old_values::jsonb, '{last_login_at}')::text !=
                                jsonb_delete_path(new_values::jsonb, '{last_login_at}')::text
                            ");
                        });
                });
        });
    }

    public function scopeExcludeFieldsUpdates(Builder $query, array $fields): void
    {
        foreach ($fields as $field) {
            $query->where(function ($query) use ($field) {
                $query->where('model', '!=', 'User')
                    ->orWhere(function ($q) use ($field) {
                        $q->where('model', 'User')
                            ->where(function ($subQuery) use ($field) {
                                $subQuery->whereRaw("
                                    CAST(old_values->>'$field' AS timestamp) !=
                                    CAST(new_values->>'$field' AS timestamp)
                                ");
                            });
                    });
            });
        }
    }

    // Novos scopes para consultas otimizadas
    public function scopeCritical($query)
    {
        return $query->where('criticality', self::CRITICALITY_CRITICAL);
    }

    public function scopeHigh($query)
    {
        return $query->where('criticality', self::CRITICALITY_HIGH);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()]);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Métodos existentes (mantidos)
    public function getTranslatedActionAttribute()
    {
        $translations = [
            'created' => 'Criado',
            'updated' => 'Atualizado',
            'deleted' => 'Deletado'
        ];

        return $translations[$this->action] ?? $this->action;
    }

    // Métodos estáticos para estatísticas
    public static function getDashboardStats()
    {
        return Cache::remember('activity_dashboard_stats', 1800, function () {
            return [
                'today_count' => self::today()->count(),
                'week_count' => self::thisWeek()->count(),
                'critical_24h' => self::critical()->where('created_at', '>=', now()->subDay())->count(),
                'high_24h' => self::high()->where('created_at', '>=', now()->subDay())->count(),
                'active_users_today' => self::today()->distinct('user_id')->count(),
                'top_actions_today' => self::today()
                    ->selectRaw('action, COUNT(*) as count')
                    ->groupBy('action')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->pluck('count', 'action'),
                'top_models_today' => self::today()
                    ->selectRaw('model, COUNT(*) as count')
                    ->groupBy('model')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->pluck('count', 'model'),
                'categories_today' => self::today()
                    ->whereNotNull('category')
                    ->selectRaw('category, COUNT(*) as count')
                    ->groupBy('category')
                    ->pluck('count', 'category'),
            ];
        });
    }

    public static function getCriticalAlerts()
    {
        return self::critical()
            ->with('user:id,name')
            ->where('created_at', '>=', now()->subHours(2))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    // Métodos de instância
    public function isCritical()
    {
        return $this->criticality === self::CRITICALITY_CRITICAL;
    }

    public function isHigh()
    {
        return $this->criticality === self::CRITICALITY_HIGH;
    }

    public function getFormattedSummary()
    {
        if ($this->summary) {
            return $this->summary;
        }

        // Fallback: gerar resumo automaticamente
        return $this->generateDefaultSummary();
    }

    public function generateDefaultSummary()
    {
        $action = formatActivityAction($this->action);
        $model = formatActivityModel($this->model);

        return "{$action} {$model} #{$this->model_id}";
    }

    public function getCriticalityColor()
    {
        return match ($this->criticality) {
            self::CRITICALITY_CRITICAL => 'red',
            self::CRITICALITY_HIGH => 'orange',
            self::CRITICALITY_MEDIUM => 'yellow',
            self::CRITICALITY_LOW => 'green',
            default => 'gray'
        };
    }

    public function getCriticalityIcon()
    {
        return match ($this->criticality) {
            self::CRITICALITY_CRITICAL => '🔴',
            self::CRITICALITY_HIGH => '🟠',
            self::CRITICALITY_MEDIUM => '🟡',
            self::CRITICALITY_LOW => '🟢',
            default => '🔵' // Para logs antigos sem criticidade definida
        };
    }

    public function getCategoryIcon()
    {
        return match ($this->category) {
            self::CATEGORY_SECURITY => '🔒',
            self::CATEGORY_FINANCIAL => '💰',
            self::CATEGORY_OPERATIONAL => '⚙️',
            self::CATEGORY_ADMINISTRATIVE => '📋',
            default => '📄'
        };
    }

    public function hasTag($tag)
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function getRelevantChanges()
    {
        return getRelevantChanges($this->new_values ?? []);
    }

    public function getChangedFieldsCount()
    {
        $changes = $this->getRelevantChanges();
        return count($changes);
    }

    // Boot method para eventos
    protected static function boot()
    {
        parent::boot();

        static::created(function ($log) {
            // Limpar cache quando um novo log é criado
            Cache::forget('activity_dashboard_stats');
            Cache::tags(['activity_logs'])->flush();

            // Verificar se é crítico para alertas
            if ($log->isCritical()) {
                // Aqui poderia disparar um evento para alertas
                // event(new CriticalActivityLogged($log));
            }
        });
    }

    // Método para limpeza de logs antigos
    public static function cleanup()
    {
        $deleted = self::where('created_at', '<', now()->subDays(365))
            ->where('criticality', self::CRITICALITY_LOW)
            ->delete();

        // Log da limpeza
        if ($deleted > 0) {
            self::create([
                'user_id' => 1, // Sistema
                'action' => 'cleanup',
                'model' => 'ActivityLog',
                'model_id' => 0,
                'summary' => "Limpeza automática removeu {$deleted} logs antigos",
                'criticality' => self::CRITICALITY_LOW,
                'category' => self::CATEGORY_ADMINISTRATIVE,
            ]);
        }

        return $deleted;
    }
}
