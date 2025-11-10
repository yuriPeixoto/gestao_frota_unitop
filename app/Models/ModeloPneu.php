<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ModeloPneu extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'modelopneu';

    protected $primaryKey = 'id_modelo_pneu';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_modelo',
        'ativo',
        'quantidade_dias_calibragem_vencida',
        'id_desenho_pneu_m',
        'id_dimensao_pneu_m',
        'id_fornecedor',
        'cod_antigo',
        'id_modelo_pneu'
    ];

    public function desenho_pneu(): BelongsTo
    {
        return $this->belongsTo(DesenhoPneu::class, 'id_desenho_pneu_m');
    }

    public function dimensao_pneu(): BelongsTo
    {
        return $this->belongsTo(DimensaoPneu::class, 'id_dimensao_pneu_m');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function controleVida(): BelongsTo
    {
        return $this->belongsTo(ControleVidaPneus::class, 'id_modelo_pneu');
    }


    protected static function boot()
    {
        parent::boot();

        // Limpar cache quando um modelopneu for modificado
        static::saved(function ($modelopneu) {
            // Remove o cache específico deste modelopneu
            Cache::forget('modelopneu_' . $modelopneu->id_modelo_pneu);

            // Remove o cache de modelopneues frequentes
            Cache::forget('modelopneus_frequentes');

            // Uma abordagem para limpar caches de busca relacionados
            // (simplificada, mas funcional para a maioria dos casos)
            $cacheKeys = Cache::get('modelopneu_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::put('modelopneu_search_keys', [], now()->addDay());
        });

        static::deleted(function ($modelopneu) {
            Cache::forget('modelopneu_' . $modelopneu->id_modelo_pneu);
            Cache::forget('modelopneues_frequentes');

            // Mesma lógica para limpar caches de busca
            $cacheKeys = Cache::get('modelopneu_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        });
    }
}
