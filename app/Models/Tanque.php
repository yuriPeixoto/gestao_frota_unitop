<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Tanque extends Model
{
    use LogsActivity;
    use SoftDeletes;
    use ToggleIsActiveOnSoftDelete;

    protected $connection = 'pgsql';
    protected $table = 'tanque';
    protected $primaryKey = 'id_tanque';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'tanque',
        'capacidade',
        'id_fornecedor',
        'combustivel',
        'estoque_minimo',
        'estoque_maximo',
        'id_filial',
        'descricao_ats',
        'is_ativo',
        'deleted_at'
    ];

    protected $casts = [
        'is_ativo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Campo a ser alterado quando o registro for excluído via soft delete
     */
    protected $activeField = 'is_ativo';

    /**
     * IDs dos tanques considerados internos no sistema
     */
    protected static $tanquesInternosIds = [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        15,
        26,
        36,
        47,
        109,
        131,
        152,
        165,
        166,
        167,
        168
    ];

    /**
     * Escopo para filtrar apenas tanques ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Escopo para filtrar apenas tanques inativos
     */
    public function scopeInativo($query)
    {
        return $query->where('is_ativo', false);
    }

    /**
     * Retorna uma query com apenas os tanques internos
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function tanquesInternos()
    {
        return self::whereIn('id_tanque', self::$tanquesInternosIds);
    }

    /**
     * Retorna um array formatado de tanques internos para uso em selects
     *
     * @param bool $cache Se deve utilizar cache na consulta
     * @return array Array de tanques no formato ['value' => id, 'label' => nome]
     */
    public static function tanquesInternosParaSelect($cache = true)
    {
        $cacheKey = 'tanques_internos_select';

        if ($cache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $tanques = self::tanquesInternos()
            ->ativo()
            ->select('id_tanque as value', 'tanque as label')
            ->orderBy('tanque')
            ->get()
            ->toArray();

        if ($cache) {
            Cache::put($cacheKey, $tanques, now()->addMinutes(15));
        }

        return $tanques;
    }

    public static function tanquesPorFilial($cache = true, $filialId = null)
    {
        $cacheKey = 'tanques_por_filial';

        if ($cache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $tanques = self::tanquesInternos()
            ->where('id_filial', $filialId)
            ->ativo()
            ->select('id_tanque as value', 'tanque as label')
            ->orderBy('tanque')
            ->get()
            ->toArray();

        if ($cache) {
            Cache::put($cacheKey, $tanques, now()->addMinutes(15));
        }

        return $tanques;
    }

    // Toggles the active status of the tank
    public function toggleActive()
    {
        $this->is_ativo = !$this->is_ativo;
        $this->data_alteracao = now();
        return $this->save();
    }

    public function getTanqueAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function getDescricaoAtsAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function tipoCombustivel()
    {
        return $this->belongsTo(TipoCombustivel::class, 'combustivel', 'id_tipo_combustivel');
    }

    public function bombas()
    {
        return $this->hasMany(Bomba::class, 'id_tanque');
    }

    public function estoqueCombustivel()
    {
        return $this->hasMany(EstoqueCombustivel::class, 'id_tanque');
    }

    // Accessors para formatar com separador de milhar
    public function getCapacidadeAttribute($value)
    {
        return number_format($value, 0, ',', '.');
    }

    public function getEstoqueMinimoAttribute($value)
    {
        return number_format($value, 0, ',', '.');
    }

    public function getEstoqueMaximoAttribute($value)
    {
        return number_format($value, 0, ',', '.');
    }

    // Mutators para remover formatação antes de salvar
    public function setCapacidadeAttribute($value)
    {
        $this->attributes['capacidade'] = str_replace(['.', ','], '', $value);
    }

    public function setEstoqueMinimoAttribute($value)
    {
        $this->attributes['estoque_minimo'] = str_replace(['.', ','], '', $value);
    }

    public function setEstoqueMaximoAttribute($value)
    {
        $this->attributes['estoque_maximo'] = str_replace(['.', ','], '', $value);
    }

    public function combustivel()
    {
        return $this->belongsTo(TipoCombustivel::class, 'id_combustivel');
    }
}
