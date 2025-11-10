<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

class Pessoal extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';

    protected $table = 'pessoal';

    protected $primaryKey = 'id_pessoal';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'nome',
        'rg',
        'cpf',
        'data_nascimento',
        'cnh',
        'validade_cnh',
        'tipo_cnh',
        'id_tipo_pessoal',
        'email',
        'orgao_emissor',
        'data_admissao',
        'id_departamento',
        'ativo',
        'id_sascar',
        'pis',
        'matricula',
        'rotas',
        'id_rota',
        'id_filial',
        'imagem_pessoal',
    ];

    // Conversão de tipos para datas
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_nascimento' => 'date',
        'validade_cnh' => 'date',
        'data_admissao' => 'date',
        'ativo' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($pessoal) {
            if (! $pessoal->data_inclusao) {
                $pessoal->data_inclusao = now();
            }
        });

        static::updating(function ($pessoal) {
            $pessoal->data_alteracao = now();
        });

        static::saved(function ($pessoal) {
            Cache::forget('pessoas_' . $pessoal->id_pessoal);
            Cache::forget('pessoas_frequentes');
        });

        static::deleted(function ($pessoal) {
            Cache::forget('pessoas_' . $pessoal->id_pessoal);
            Cache::forget('pessoas_frequentes');
        });
    }

    /**
     * Endereço associado ao pessoal
     */
    public function endereco(): HasMany
    {
        return $this->hasMany(Endereco::class, 'id_pessoal_endereco');
    }

    /**
     * Telefones associados ao pessoal
     */
    public function telefone(): HasMany
    {
        return $this->hasMany(Telefone::class, 'id_pessoal');
    }

    /**
     * Filial associada ao pessoal
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }

    /**
     * Tipo de pessoal associado
     */
    public function tipoPessoal(): BelongsTo
    {
        return $this->belongsTo(TipoPessoal::class, 'id_tipo_pessoal', 'id_tipo_pessoal');
    }

    /**
     * Formata a descrição tipo em maiúsculo
     */
    public function getDescricaoTipoAttribute($value): array|false|string|null
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Usuário associado ao pessoal
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'pessoal_id', 'id_pessoal');
    }

    /**
     * Departamento associado ao pessoal
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Sinistros relacionados ao pessoal (como motorista)
     */
    public function sinistro(): HasMany
    {
        return $this->hasMany(Sinistro::class, 'id_motorista', 'id_pessoal');
    }

    /**
     * Motorista associado ao pessoal (pelo id_sascar)
     */
    public function motorista(): BelongsTo
    {
        return $this->belongsTo(Motorista::class, 'id_sascar', 'idmotorista');
    }

    public function calibragens(): HasMany|Pessoal
    {
        return $this->hasMany(CalibragemPneus::class, 'id_user_calibragem');
    }

    public function rota(): BelongsTo
    {
        return $this->belongsTo(Rotas::class, 'id_rota', 'id_rotas');
    }
}
