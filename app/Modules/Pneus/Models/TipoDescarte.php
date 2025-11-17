<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasPermissions;

class TipoDescarte extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $table = 'tipodescarte';

    protected $primaryKey = 'id_tipo_descarte';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_tipo_descarte',
    ];

    protected static function boot()
    {
        parent::boot();

        // Limpar cache quando um tipodescarte for modificado
        static::saved(function ($tipodescarte) {
            // Remove o cache específico deste tipodescarte
            Cache::forget('tipodescarte_' . $tipodescarte->id_tipo_descarte);

            // Remove o cache de tipodescartees frequentes
            Cache::forget('tipodescartees_frequentes');

            // Uma abordagem para limpar caches de busca relacionados
            // (simplificada, mas funcional para a maioria dos casos)
            $cacheKeys = Cache::get('tipodescarte_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::put('tipodescarte_search_keys', [], now()->addDay());
        });

        static::deleted(function ($tipodescarte) {
            Cache::forget('tipodescarte_' . $tipodescarte->id_tipo_descarte);
            Cache::forget('tipodescartees_frequentes');

            // Mesma lógica para limpar caches de busca
            $cacheKeys = Cache::get('tipodescarte_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        });
    }

    public function descartePneu(): HasMany
    {
        return $this->hasMany(DescartePneu::class, 'id_tipo_descarte');
    }
}
