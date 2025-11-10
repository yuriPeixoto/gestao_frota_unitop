<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaseVeiculo extends Model
{
    use LogsActivity;

    protected $table = 'base_veiculo';

    protected $primaryKey = 'id_base_veiculo';

    public $timestamps = false;

    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_base'];

    public function veiculo(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'id_base_veiculo');
    }
}
