<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VManutencaoVencida extends Model
{
    protected $table = 'v_manutencao_vencidas';

    protected $primaryKey = 'id_veiculo'; // ajuste conforme sua view

    public $timestamps = false;

    protected $fillable = []; // view é somente leitura

}