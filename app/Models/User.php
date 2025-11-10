<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property bool $is_superuser
 * @property int $id
 * @property int $filial_id
 * @property int $departamento_id
 * @property int $pessoal_id
 * @property int $address_id
 * @property int $matricula
 * @property string $rg
 * @property string $orgao_emissor
 * @property string $data_nascimento
 * @property string $data_admissao
 * @property int $cnh
 * @property string $validade_cnh
 * @property string $tipo_cnh
 * @property int $pis
 * @property string $imagem_pessoal
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasPermissions;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $connection = 'pgsql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'matricula',
        'rg',
        'orgao_emissor',
        'data_nascimento',
        'data_admissao',
        'cnh',
        'validade_cnh',
        'tipo_cnh',
        'pis',
        'imagem_pessoal',
        'filial_id',
        'departamento_id',
        'pessoal_id',
        'is_superuser',
        'avatar',
        'last_login_at',
        'last_login_ip',
        'has_password_updated',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
        'address_id',
        'is_ativo',
        'role_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password_updated_at' => 'datetime',
            'data_nascimento' => 'date',
            'data_admissao' => 'date',
            'validade_cnh' => 'date',
        ];
    }

    protected $appends = ['avatar_url'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAvatarUrlAttribute()
    {
        // Priorizar imagem_pessoal se existir, senão usar avatar
        if ($this->imagem_pessoal && Storage::disk('public')->exists($this->imagem_pessoal)) {
            return url(Storage::url($this->imagem_pessoal));
        }

        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return url(Storage::url($this->avatar));
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    /**
     * Clear the user's permissions cache.
     */
    public function clearPermissionCache(): void
    {
        Cache::forget('user_permissions_' . $this->id);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_superuser) {
            return true;
        }

        return $this->hasPermissionTo($permission);
    }

    /**
     * Check if user is a superuser.
     */
    public function isSuperuser(): bool
    {
        return $this->is_superuser;
    }

    /**
     * Check if user belongs to MATRIZ (filial_id = 1).
     * Users from MATRIZ can see data from ALL filiais.
     */
    public function isMatriz(): bool
    {
        return $this->filial_id === 1;
    }

    /**
     * Get all filial IDs that the user has access to.
     * - If user is from MATRIZ (filial_id = 1): returns ALL filial IDs
     * - Otherwise: returns primary filial + secondary filiais from user_filial pivot
     *
     * @param  bool  $useCache  Whether to use cache for the query
     * @return array Array of filial IDs
     */
    public function getAccessibleFilialIds(bool $useCache = true): array
    {
        $cacheKey = "user_accessible_filials_{$this->id}";

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $filialIds = [];

        if ($this->isMatriz()) {
            // Usuário da MATRIZ vê todas as filiais
            $filialIds = VFilial::pluck('id')->toArray();
        } else {
            // Usuário de outras filiais vê apenas:
            // 1. Sua filial principal
            // 2. Filiais secundárias (pivot user_filial)

            // Adicionar filial principal
            if ($this->filial_id) {
                $filialIds[] = $this->filial_id;
            }

            // Adicionar filiais secundárias
            $filiaisSecundarias = $this->filiais()->pluck('filiais.id')->toArray();
            $filialIds = array_merge($filialIds, $filiaisSecundarias);

            // Remover duplicatas
            $filialIds = array_unique($filialIds);
        }

        if ($useCache) {
            Cache::put($cacheKey, $filialIds, now()->addHours(12));
        }

        return $filialIds;
    }

    /**
     * Get accessible filiais as Eloquent Collection.
     * Useful for relationships and queries.
     *
     * @param  bool  $useCache  Whether to use cache for the query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAccessibleFiliais(bool $useCache = true)
    {
        $filialIds = $this->getAccessibleFilialIds($useCache);

        return VFilial::whereIn('id', $filialIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if user has access to a specific filial.
     */
    public function hasAccessToFilial(int $filialId): bool
    {
        return in_array($filialId, $this->getAccessibleFilialIds());
    }

    /**
     * Scope query to filter records by user's accessible filiais.
     * Usage: $query->whereUserHasAccess($user, 'filial_id_column')
     *
     * @param  Builder  $query
     * @param  string  $filialColumn  The column name that contains filial_id
     * @return Builder
     */
    public function scopeWhereUserHasAccess($query, string $filialColumn = 'id_filial')
    {
        $accessibleFilialIds = $this->getAccessibleFilialIds();

        return $query->whereIn($filialColumn, $accessibleFilialIds);
    }

    /**
     * Clear user's filial access cache.
     * Call this method when user's filial associations change.
     */
    public function clearFilialAccessCache(): void
    {
        Cache::forget("user_accessible_filials_{$this->id}");
    }

    /**
     * Format the user's name with proper capitalization.
     *
     * @param  string  $value
     */
    public function getNameAttribute($value): string
    {
        return ucwords(mb_strtolower($value, 'UTF-8'));
    }

    /**
     * Format the RG with proper mask.
     *
     * @param  string  $value
     */
    public function getRgAttribute($value): ?string
    {
        return $value ? strtoupper($value) : null;
    }

    /**
     * Format the orgao emissor with proper capitalization.
     *
     * @param  string  $value
     */
    public function getOrgaoEmissorAttribute($value): ?string
    {
        return $value ? strtoupper($value) : null;
    }

    /**
     * Format the tipo CNH with proper capitalization.
     *
     * @param  string  $value
     */
    public function getTipoCnhAttribute($value): ?string
    {
        return $value ? strtoupper($value) : null;
    }

    /**
     * Check if CNH is expired.
     */
    public function isCnhExpired(): bool
    {
        return $this->validade_cnh && $this->validade_cnh < now();
    }

    /**
     * Check if CNH expires within X days.
     */
    public function isCnhExpiringWithin(int $days = 30): bool
    {
        return $this->validade_cnh && $this->validade_cnh <= now()->addDays($days);
    }

    /**
     * Get the filial that the user belongs to.
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'filial_id', 'id');
    }

    public function filiais(): BelongsToMany
    {
        return $this->belongsToMany(VFilial::class, 'user_filial', 'user_id', 'filial_id')
            ->withTimestamps();
    }


    public function roles(): BelongsToMany
    {
        // Usando a tabela padrão do Spatie Permission
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->where('model_type', self::class)
            ->select('roles.id', 'roles.name');
    }

    public function pertenceAFilial($filialId): bool
    {
        return $this->filiais()->where('filiais.id', $filialId)->exists();
    }

    public function getFilialIdsArrayAttribute(): array
    {
        return $this->filiais()->pluck('filiais.id')->toArray();
    }

    public function getFilialNamesAttribute(): array
    {
        return $this->filiais()->pluck('filiais.name')->toArray();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get activity logs for this user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'model_id')->where('model', 'User');
    }

    /**
     * Get the departamento that the user belongs to.
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id_departamento');
    }
    /*
    relacionamento com mecanico

    */

    /**
     * Abastecimentos relacionados com o usuário.
     */
    public function valorCombustivelTerceiro(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'model_id')->where('model', 'User')->latest();
    }

    public function tipoPessoal()
    {
        // Make sure this matches your actual relationship
        return $this->belongsTo(TipoPessoal::class, 'pessoal_id', 'id_tipo_pessoal');
    }

    /**
     * Scope a query to users in a specific filial.
     *
     * @param  Builder  $query
     * @param  int  $filialId
     * @return Builder
     */
    public function scopeByFilial($query, $filialId)
    {
        return $query->where('filial_id', $filialId);
    }

    /**
     * Scope a query to users with a specific role/cargo.
     *
     * @param  Builder  $query
     * @param  int  $tipoPessoalId
     * @return Builder
     */
    public function scopeByTipoPessoal($query, $tipoPessoalId)
    {
        return $query->where('pessoal_id', $tipoPessoalId);
    }

    /**
    /**
     * Scope a query to users in a specific department.
     *
     * @param  Builder  $query
     * @param  int  $departamentoId
     * @return Builder
     */
    public function scopeByDepartamento($query, $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    /*
    |--------------------------------------------------------------------------
    | Relações do Módulo de Compras
    |--------------------------------------------------------------------------
    */

    /**
     * Solicitações de compra criadas por este usuário
     */
    public function solicitacoesCompra(): HasMany
    {
        return $this->hasMany(SolicitacaoCompra::class, 'id_solicitante', 'id');
    }

    /**
     * Solicitações de compra aprovadas por este usuário
     */
    public function solicitacoesCompraAprovadas(): HasMany
    {
        return $this->hasMany(SolicitacaoCompra::class, 'id_aprovador', 'id');
    }

    /**
     * Pedidos de compra criados/processados por este usuário (como comprador)
     */
    public function pedidosCompra(): HasMany
    {
        return $this->hasMany(PedidoCompra::class, 'id_comprador', 'id');
    }

    /**
     * Pedidos de compra aprovados por este usuário
     */
    public function pedidosCompraAprovados(): HasMany
    {
        return $this->hasMany(PedidoCompra::class, 'id_aprovador', 'id');
    }

    /**
     * Orçamentos criados por este usuário
     */
    public function orcamentos(): HasMany
    {
        return $this->hasMany(Orcamento::class, 'id_usuario', 'id');
    }

    /**
     * Notas fiscais registradas por este usuário
     */
    public function notasFiscais(): HasMany
    {
        return $this->hasMany(NotaFiscal::class, 'id_usuario', 'id');
    }

    /**
     * Contratos criados/administrados por este usuário
     */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'id_usuario', 'id');
    }

    // User.php
    public function calibragens()
    {
        return $this->hasMany(CalibragemPneus::class, 'id_user_calibragem');
    }

    /**
     * Solicitações de compra pendentes de aprovação deste usuário
     *
     * @return SolicitacaoCompra
     */
    public function solicitacoesCompraParaAprovar()
    {
        // Obtém solicitações que estão aguardando aprovação e onde o usuário
        // é o aprovador designado para o departamento da solicitação

        // Este escopo assume que existe uma tabela ou lógica que define
        // quais usuários são aprovadores de quais departamentos
        return SolicitacaoCompra::where('status', 'aguardando_aprovacao')
            ->whereHas('departamento', function ($query) {
                $query->whereHas('aprovadores', function ($query) {
                    $query->where('id_user', $this->id);
                });
            });
    }

    /**
     * Pedidos de compra pendentes de aprovação deste usuário
     *
     * @return PedidoCompra
     */
    public function pedidosCompraParaAprovar()
    {
        // Obtém pedidos que estão aguardando aprovação e onde o usuário
        // tem alçada para aprovar baseado no valor do pedido

        // Este escopo assume que existe uma tabela ou lógica de alçadas
        // que define quais usuários podem aprovar pedidos até determinado valor
        return PedidoCompra::where('status', 'aguardando_aprovacao')
            ->whereHas('alcadas', function ($query) {
                $query->where('id_user', $this->id)
                    ->whereRaw('pedidos_compra.valor_total <= alcadas.valor_limite');
            });
    }

    /**
     * Verificar se o usuário é um aprovador de compras
     */
    public function isAprovadorCompras(): bool
    {
        return $this->hasRole('Aprovador de Compra');
    }

    /**
     * Verificar se o usuário é um comprador
     */
    public function isComprador(): bool
    {
        return $this->hasRole('Comprador');
    }

    /**
     * Verificar se o usuário pode aprovar solicitações de um departamento específico
     */
    public function podeAprovarSolicitacoesDoDepartamento(int $departamentoId): bool
    {
        // Verificar se o usuário é aprovador do departamento
        return $this->usuarioDepartamento()
            ->where('id_departamento', $departamentoId)
            ->where('is_aprovador', true)
            ->exists();
    }

    /**
     * Verificar se o usuário pode aprovar pedidos até determinado valor
     */
    public function podeAprovarPedidosAteValor(float $valor): bool
    {
        // Verificar se o usuário tem alçada para aprovar o valor
        if ($this->is_superuser) {
            return true;
        }

        return $this->alcadas()
            ->where('valor_limite', '>=', $valor)
            ->exists();
    }

    public function ordemServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'id_recepcionista', 'id');
    }

    public function ordemServicoEncerramento(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'id_recepcionista_encerramento', 'id');
    }

    public function osAuxiliarServicos(): HasMany
    {
        return $this->hasMany(GerarOSServicosAuxiliar::class, 'id_mecanico', 'id');
    }

    /**
     * Telefones associados ao usuário
     */
    public function telefones(): HasMany
    {
        return $this->hasMany(Telefone::class, 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relações do Módulo de Notificações
    |--------------------------------------------------------------------------
    */

    /**
     * Configurações de notificação do usuário
     */
    public function notificationSettings()
    {
        return $this->hasOne(UserNotificationSetting::class, 'user_id', 'id');
    }

    /**
     * Obtém as configurações de notificação (cria se não existir)
     */
    public function getNotificationSettings(): UserNotificationSetting
    {
        return UserNotificationSetting::getOrCreateForUser($this->id);
    }

    /**
     * Notificações segmentadas não lidas do usuário
     */
    public function unreadNotificationTargets()
    {
        return NotificationTarget::readyToSend()
            ->forUser($this)
            ->unreadByUser($this->id)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Conta notificações não lidas
     */
    public function unreadNotificationsCount(): int
    {
        // Notificações diretas (padrão Laravel)
        $directCount = $this->unreadNotifications()->count();

        // Notificações segmentadas
        $targetedCount = $this->unreadNotificationTargets()->count();

        return $directCount + $targetedCount;
    }
}
