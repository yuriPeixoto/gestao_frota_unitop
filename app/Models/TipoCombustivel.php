<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCombustivel extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'tipocombustivel';
    protected $primaryKey = 'id_tipo_combustivel';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao', 'unidade_medida', 'ncm'];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function tanque()
    {
        return $this->hasMany(Tanque::class, 'combustivel');
    }
}
