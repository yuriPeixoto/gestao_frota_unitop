<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VControleManutencaoFrota extends Model 
{
    use LogsActivity;

    protected $table = 'v_controle_manutencao_frota';

    public $timestamps = false;

    protected $guarded = ['*'];

}