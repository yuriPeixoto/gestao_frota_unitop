<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Estoque;

class v_Filial extends Model
{
    protected $table = "filiais";


    public function estoque()
    {
        return $this->hasMany(Estoque::class, 'id_filial');
    }
}
