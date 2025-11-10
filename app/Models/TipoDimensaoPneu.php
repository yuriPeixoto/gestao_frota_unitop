<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TipoDimensaoPneu extends Model
{
    use LogsActivity;

    protected $table = 'dimensaopneu';
    protected $primaryKey = 'id_dimensao_pneu';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'descricao_pneu'];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];


    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
