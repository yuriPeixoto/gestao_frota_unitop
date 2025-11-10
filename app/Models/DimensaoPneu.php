<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DimensaoPneu extends Model
{
    use LogsActivity;


    protected $table = 'dimensaopneu';

    protected $primaryKey = 'id_dimensao_pneu';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_pneu',
    ];
}