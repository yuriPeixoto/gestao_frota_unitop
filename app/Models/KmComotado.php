<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KmComotado extends Model
{
    use LogsActivity;

    protected $table = 'km_comodato';
    protected $primaryKey = 'id_km_comodato';
    public $timestamps = false;
    protected $fillable = ['km_realizacao', 'id_veiculo', 'horimetro', 'data_realizacao', 'data_inclusao', 'data_alteracao'];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }
}
