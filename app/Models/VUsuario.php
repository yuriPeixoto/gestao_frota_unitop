<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VUsuario extends Model
{
    /**
     * Conexão com banco de homologação (pgsql padrão)
     * Removida conexão específica 'pgsql'
     */
    protected $connection = 'pgsql';

    /**
     * Nome da tabela física (não mais view)
     */
    protected $table = 'usuarios_laravel';

    /**
     * Chave primária
     */
    protected $primaryKey = 'id';

    /**
     * Timestamps habilitados para controle
     */
    public $timestamps = true;

    /**
     * Chave primária não é auto-incrementing
     * (vem do sistema de origem)
     */
    public $incrementing = false;

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'int';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'id',
        'name',
        'login',
        'active',
        'system_unit_id',
        'email'
    ];

    /**
     * Cast de tipos para garantir consistência
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'login' => 'string',
        'active' => 'string',
        'system_unit_id' => 'integer',
        'email' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Escopo para filtrar apenas usuários ativos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtivo($query)
    {
        return $query->where('active', 'Y');
    }

    /**
     * Escopo para buscar por email
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Escopo para buscar por login
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $login
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorLogin($query, $login)
    {
        return $query->where('login', $login);
    }

    /**
     * Acessor para formatar o nome do usuário
     *
     * @param string|null $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return $value ? mb_strtoupper($value, 'UTF-8') : '';
    }

    /**
     * Acessor para verificar se o usuário está ativo
     *
     * @return bool
     */
    public function getIsAtivoAttribute()
    {
        return $this->active === 'Y';
    }

    /**
     * Acessor para email formatado (lowercase)
     *
     * @param string|null $value
     * @return string|null
     */
    public function getEmailAttribute($value)
    {
        return $value ? strtolower(trim($value)) : null;
    }

    /**
     * Relacionamento reverso - encerrantes criados por este usuário
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function encerrantes()
    {
        return $this->hasMany(Encerrante::class, 'usuario', 'id');
    }

    /**
     * Método estático para buscar usuário por ID com cache
     *
     * @param int $id
     * @return self|null
     */
    public static function buscarPorId($id)
    {
        return static::where('id', $id)->first();
    }

    /**
     * Método estático para listar usuários ativos
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function usuariosAtivos($limit = 100)
    {
        return static::ativo()
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * Método para sincronizar dados com o sistema origem
     * (para uso futuro em jobs/commands)
     *
     * @return bool
     */
    public static function sincronizarComOrigem()
    {
        try {
            // Este método pode ser implementado futuramente
            // para sincronizar dados da view v_usuarios
            // usando um job ou command artisan

            return true;
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar usuários: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Override do método save para atualizar timestamp
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        // Atualizar updated_at apenas se não for uma criação
        if ($this->exists) {
            $this->updated_at = Carbon::now();
        }

        return parent::save($options);
    }
}
