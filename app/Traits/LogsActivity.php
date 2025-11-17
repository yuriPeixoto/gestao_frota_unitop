<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

trait LogsActivity
{
    // Campos que NUNCA devem ser logados (sensíveis/irrelevantes)
    protected $excludeFromActivityLog = [
        'password',
        'remember_token',
        'api_token',
        'email_verified_at', // Auto-atualizado
        'last_login_at',     // Auto-atualizado constantemente
        'password_reset_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    // Campos críticos que sempre devem ser monitorados
    protected $criticalFields = [
        'email',
        'is_active',
        'is_superuser',
        'is_admin',
        'role_id',
        'permissions',
        'status',
        'situacao_compra',
        'aprovado',
        'rejeitado',
        'cancelado',
    ];

    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            self::logActivity('created', $model);
        });

        static::updated(function ($model) {
            // IMPORTANTE: Sempre logar CRUDs, mas de forma inteligente
            if (self::shouldLogUpdate($model)) {
                self::logActivity('updated', $model);
            }
        });

        static::deleted(function ($model) {
            self::logActivity('deleted', $model);
        });
    }

    protected static function shouldLogUpdate($model)
    {
        // SEMPRE logar atualizações - conforme requisito
        // Mas filtrar campos irrelevantes no processamento

        // Se não houve mudanças reais, não logar
        if (!$model->isDirty()) {
            return false;
        }

        // Se apenas campos excluídos mudaram, não logar
        $instance = new static();
        $excludeFields = $instance->excludeFromActivityLog ?? [];

        $relevantChanges = collect($model->getDirty())
            ->except($excludeFields)
            ->toArray();

        return !empty($relevantChanges);
    }

    protected static function logActivity($action, $model)
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $instance = new static();

        // Obter valores filtrados
        $oldValues = $action !== 'created' ? self::filterLogData($model->getOriginal(), $instance) : null;
        $newValues = self::filterLogData($model->getAttributes(), $instance);

        // DEBUG TEMPORÁRIO: Adicionar logs para verificar valores
        logger()->info('LogsActivity DEBUG', [
            'action' => $action,
            'model' => class_basename($model),
            'model_id' => $model->getKey(),
            'user_id' => $user->id,
            'raw_original_count' => count($model->getOriginal()),
            'raw_attributes_count' => count($model->getAttributes()),
            'filtered_old_count' => $oldValues ? count($oldValues) : 0,
            'filtered_new_count' => $newValues ? count($newValues) : 0,
            'old_values_sample' => $oldValues ? array_slice($oldValues, 0, 3, true) : null,
            'new_values_sample' => $newValues ? array_slice($newValues, 0, 3, true) : null,
        ]);


        try {
            // ✅ IMPLEMENTAÇÃO ROBUSTA: Funciona com ou sem as colunas extras
            $baseData = [
                'user_id'    => $user->id,
                'action'     => $action,
                'model'      => class_basename($model),
                'model_id'   => $model->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            // 🔧 Verificar se colunas extras existem e adicionar se disponíveis
            try {
                if (Schema::hasColumn('activity_logs', 'criticality')) {
                    $criticality = self::determineCriticality($model, $action, $newValues, $oldValues);
                    $category = self::determineCategory($model);
                    $summary = self::generateSummary($model, $action, $newValues, $oldValues);
                    $affectedUsers = self::getAffectedUsers($model, $action);
                    $retentionDays = self::getRetentionDays($model, $criticality);

                    $baseData = array_merge($baseData, [
                        'criticality'    => $criticality,
                        'category'       => $category,
                        'summary'        => $summary,
                        'affected_users' => $affectedUsers,
                        'retention_days' => $retentionDays,
                        'tags'           => self::generateTags($model, $action),
                    ]);
                }
            } catch (\Exception $schemaException) {
                // Colunas extras não existem, continuar apenas com campos básicos
                logger()->info('ActivityLog usando apenas campos básicos', [
                    'reason' => 'Colunas extras não encontradas',
                    'error' => $schemaException->getMessage()
                ]);
            }

            $activityLog = ActivityLog::create($baseData);

            // DEBUG: Verificar se foi salvo corretamente
            logger()->info('ActivityLog criado', [
                'activity_log_id' => $activityLog->id,
                'old_values_saved' => $activityLog->old_values ? 'TEM_DADOS' : 'NULL_OU_VAZIO',
                'new_values_saved' => $activityLog->new_values ? 'TEM_DADOS' : 'NULL_OU_VAZIO',
                'old_values_count' => $activityLog->old_values ? count($activityLog->old_values) : 0,
                'new_values_count' => $activityLog->new_values ? count($activityLog->new_values) : 0,
            ]);
        } catch (\Exception $e) {
            logger()->error('Erro ao criar ActivityLog', [
                'error' => $e->getMessage(),
                'model' => class_basename($model),
                'model_id' => $model->getKey(),
                'user_id' => $user->id,
            ]);
        }
    }

    protected static function filterLogData($data, $instance)
    {
        if (!$data) {
            return null;
        }

        $excludeFields = $instance->excludeFromActivityLog ?? [];

        $filtered = collect($data)
            ->except($excludeFields)
            ->toArray();

        // Formatar datas
        foreach ($filtered as $key => $value) {
            if ($value instanceof Carbon) {
                $filtered[$key] = $value->format('Y-m-d H:i:s');
            }
        }

        return $filtered;
    }

    protected static function determineCriticality($model, $action, $newValues, $oldValues)
    {
        $modelName = class_basename($model);
        $instance = new static();
        $criticalFields = $instance->criticalFields ?? [];

        // Ações críticas por modelo
        if ($action === 'deleted') {
            return match ($modelName) {
                'User', 'Role', 'Permission' => ActivityLog::CRITICALITY_HIGH,
                'OrdemServico', 'Fornecedor', 'Veiculo' => ActivityLog::CRITICALITY_MEDIUM,
                default => ActivityLog::CRITICALITY_LOW
            };
        }

        if ($action === 'created') {
            return match ($modelName) {
                'User', 'Role', 'Permission' => ActivityLog::CRITICALITY_HIGH,
                'OrdemServico', 'LogsSolicitacoesCompras' => ActivityLog::CRITICALITY_MEDIUM,
                default => ActivityLog::CRITICALITY_LOW
            };
        }

        if ($action === 'updated') {
            // Verificar se campos críticos foram alterados
            $changedCriticalFields = collect($newValues)
                ->intersectByKeys(array_flip($criticalFields))
                ->count();

            if ($changedCriticalFields > 0) {
                return match ($modelName) {
                    'User', 'Role', 'Permission' => ActivityLog::CRITICALITY_CRITICAL,
                    'OrdemServico', 'LogsSolicitacoesCompras' => ActivityLog::CRITICALITY_HIGH,
                    default => ActivityLog::CRITICALITY_MEDIUM
                };
            }

            return ActivityLog::CRITICALITY_LOW;
        }

        return ActivityLog::CRITICALITY_MEDIUM;
    }

    protected static function determineCategory($model)
    {
        $modelName = class_basename($model);

        return match ($modelName) {
            'User', 'Role', 'Permission', 'PermissionGroup' => ActivityLog::CATEGORY_SECURITY,
            'OrdemServico', 'SolicitacaoCompra', 'LogsSolicitacoesCompras', 'PedidoCompra', 'NotaFiscal' => ActivityLog::CATEGORY_FINANCIAL,
            'Veiculo', 'Pneu', 'Manutencao', 'Abastecimento', 'Motorista' => ActivityLog::CATEGORY_OPERATIONAL,
            default => ActivityLog::CATEGORY_ADMINISTRATIVE
        };
    }

    protected static function generateSummary($model, $action, $newValues, $oldValues)
    {
        $modelName = class_basename($model);
        $instance = new static();

        // Identificar campo principal para identificação
        $identifierField = self::getModelIdentifier($model);
        $identifier = $newValues[$identifierField] ?? $oldValues[$identifierField] ?? "#{$model->getKey()}";

        $actionText = match ($action) {
            'created' => 'criou',
            'updated' => 'atualizou',
            'deleted' => 'excluiu',
            default => $action
        };

        $modelText = formatActivityModel($modelName);

        // Resumo básico
        $summary = "{$actionText} {$modelText} {$identifier}";

        // Adicionar detalhes específicos para atualizações
        if ($action === 'updated' && $oldValues && $newValues) {
            $changes = self::getSignificantChanges($newValues, $oldValues, $instance);
            if (count($changes) <= 3) {
                $changedFields = array_keys($changes);
                $translatedFields = array_map(function ($field) {
                    return formatActivityAttribute($field);
                }, $changedFields);

                $summary .= " (" . implode(', ', $translatedFields) . ")";
            } else {
                $summary .= " (" . count($changes) . " campos alterados)";
            }
        }

        return $summary;
    }

    protected static function getModelIdentifier($model)
    {
        $modelName = class_basename($model);

        // Campos identificadores por modelo
        return match ($modelName) {
            'User' => 'name',
            'Veiculo' => 'placa',
            'Fornecedor' => 'name',
            'Produto' => 'name',
            'OrdemServico' => 'numero_os',
            'LogsSolicitacoesCompras' => 'id_solicitacoes_compras',
            default => 'name'
        };
    }

    protected static function getSignificantChanges($newValues, $oldValues, $instance)
    {
        $excludeFields = array_merge(
            $instance->excludeFromActivityLog ?? [],
            ['updated_at', 'created_at'] // Sempre excluir timestamps
        );

        $changes = [];
        foreach ($newValues as $key => $newValue) {
            if (in_array($key, $excludeFields)) {
                continue;
            }

            $oldValue = $oldValues[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }

    protected static function getAffectedUsers($model, $action)
    {
        $modelName = class_basename($model);

        // Determinar usuários afetados baseado no modelo
        $affectedUsers = [];

        if (isset($model->user_id)) {
            $affectedUsers[] = $model->user_id;
        }

        // Modelos específicos que afetam múltiplos usuários
        if ($modelName === 'OrdemServico' && isset($model->motorista_id)) {
            $affectedUsers[] = $model->motorista_id;
        }

        return array_unique($affectedUsers);
    }

    protected static function getRetentionDays($model, $criticality)
    {
        $modelName = class_basename($model);

        // Retenção baseada na criticidade e modelo
        $baseDays = match ($criticality) {
            ActivityLog::CRITICALITY_CRITICAL => 2555, // 7 anos
            ActivityLog::CRITICALITY_HIGH => 1825,     // 5 anos
            ActivityLog::CRITICALITY_MEDIUM => 1095,   // 3 anos
            ActivityLog::CRITICALITY_LOW => 365,       // 1 ano
            default => 90
        };

        // Ajustar baseado no modelo
        if (in_array($modelName, ['User', 'Role', 'Permission'])) {
            $baseDays = max($baseDays, 1825); // Mínimo 5 anos para segurança
        }

        return $baseDays;
    }

    protected static function generateTags($model, $action)
    {
        $modelName = class_basename($model);
        $tags = [$action, strtolower($modelName)];

        // Tags específicas por modelo
        if ($modelName === 'User') {
            $tags[] = 'user_management';
        }

        if (in_array($modelName, ['OrdemServico', 'SolicitacaoCompra', 'LogsSolicitacoesCompras'])) {
            $tags[] = 'financial_operation';
        }

        if (in_array($modelName, ['Veiculo', 'Pneu', 'Manutencao'])) {
            $tags[] = 'fleet_management';
        }

        return $tags;
    }
}
