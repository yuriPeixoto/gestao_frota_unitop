<?php

namespace App\Modules\Certificados\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class SmartecIpva extends Model
{
    use LogsActivity;

    protected $table = 'smartec_ipva';
    protected $primaryKey = 'id_smartec_ipva';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'placa',
        'renavam',
        'valor',
        'servico'
    ];
}
