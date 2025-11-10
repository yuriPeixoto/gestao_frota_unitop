<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Bomba extends Model
{
    use LogsActivity;
    use SoftDeletes;
    use ToggleIsActiveOnSoftDelete;

    protected $connection = 'pgsql';
    protected $table = 'bomba';
    protected $primaryKey = 'id_bomba';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_bomba',
        'id_fornecedor',
        'id_filial',
        'bomba_ctf',
        'boma_ctf_2_bico',
        'tamanho_maximo_encerrante',
        'descricao_bomba',
        'id_tanque',
        'nome_bomba_integracao',
        'is_ativo'
    ];

    // Define qual campo é usado para controlar se o registro está ativo
    protected $activeField = 'is_ativo';

    // Define campos de data para SoftDeletes
    protected $dates = ['deleted_at'];

    // Escopos para filtrar por status

    /**
     * Escopo para filtrar apenas bombas ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Escopo para filtrar apenas bombas inativas
     */
    public function scopeInativas($query)
    {
        return $query->where('is_ativo', false);
    }

    /**
     * Retorna os dados formatados para uso em select de busca
     */
    public static function getFormatadoParaSelect($apenasAtivas = true)
    {
        $query = self::orderBy('descricao_bomba');

        if ($apenasAtivas) {
            $query->where('is_ativo', true);
        }

        return $query->get(['id_bomba as value', 'descricao_bomba as label']);
    }

    /**
     * Retorna apenas as bombas associadas aos tanques internos (Carvalima)
     *
     * @param bool $apenasAtivas Se deve filtrar apenas bombas ativas
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function bombasInternas($apenasAtivas = true)
    {
        // Obter a query para tanques internos
        $tanquesInternosQuery = Tanque::tanquesInternos();

        // Obter IDs dos tanques internos (execute a query)
        $tanquesInternosIds = $tanquesInternosQuery->pluck('id_tanque')->toArray();

        // Filtrar bombas pelos IDs dos tanques internos
        $query = self::whereHas('tanque', function ($query) use ($tanquesInternosIds) {
            $query->whereIn('id_tanque', $tanquesInternosIds);
        });

        if ($apenasAtivas) {
            $query->where('is_ativo', true);
        }

        return $query;
    }

    /**
     * Retorna os dados de bombas internas formatados para uso em select de busca
     *
     * @param bool $apenasAtivas Se deve filtrar apenas bombas ativas
     * @param bool $cache Se deve utilizar cache na consulta
     * @return array Array de bombas no formato ['value' => id, 'label' => nome]
     */
    public static function bombasInternasParaSelect($apenasAtivas = true, $cache = true)
    {
        $cacheKey = 'bombas_internas_select' . ($apenasAtivas ? '_ativas' : '');

        if ($cache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $bombas = self::bombasInternas($apenasAtivas)
            ->orderBy('descricao_bomba')
            ->get(['id_bomba as value', 'descricao_bomba as label'])
            ->toArray();

        if ($cache) {
            Cache::put($cacheKey, $bombas, now()->addHours(12));
        }

        return $bombas;
    }

    /**
     * Toggle o status da bomba entre ativo/inativo
     */
    public function toggleStatus()
    {
        $this->is_ativo = !$this->is_ativo;
        $this->data_alteracao = now();
        return $this->save();
    }

    /**
     * Retorna string representando o status da bomba
     */
    public function getStatusFormatadoAttribute()
    {
        return $this->is_ativo ? 'Ativo' : 'Inativo';
    }

    // Acessores e mutadores para ambas as versões do nome

    // 1. Para o nome correto do banco (boma_ctf_2_bico)
    public function getBomaCtf2BicoAttribute()
    {
        Log::debug("Acessando propriedade boma_ctf_2_bico");
        return $this->attributes['boma_ctf_2_bico'] ?? null;
    }

    public function setBomaCtf2BicoAttribute($value)
    {
        Log::debug("Definindo propriedade boma_ctf_2_bico: " . $value);
        $this->attributes['boma_ctf_2_bico'] = $value;
    }

    // 2. Para o nome usado no formulário (bomba_ctf_2_bico)
    public function getBombaCtf2BicoAttribute()
    {
        return $this->attributes['boma_ctf_2_bico'] ?? null;
    }

    public function setBombaCtf2BicoAttribute($value)
    {
        Log::debug("Definindo propriedade bomba_ctf_2_bico: " . $value);
        $this->attributes['boma_ctf_2_bico'] = $value;
    }

    // Garante que o método mágico __set intercepte qualquer tentativa de definir a propriedade
    public function __set($key, $value)
    {
        Log::debug("__set chamado para $key com valor: " . $value);

        // Se for bomba_ctf_2_bico, redirecione para boma_ctf_2_bico
        if ($key === 'bomba_ctf_2_bico') {
            $this->attributes['boma_ctf_2_bico'] = $value;
            return;
        }

        // Caso contrário, use o comportamento padrão
        $this->setAttribute($key, $value);
    }

    public function getBombaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function getDescricaoAtsAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function tanque()
    {
        return $this->belongsTo(Tanque::class, 'id_tanque');
    }

    public function abastecimentos()
    {
        return $this->hasMany(AbastecimentoIntegracao::class, 'descricao_bomba', 'descricao_bomba');
    }
}
