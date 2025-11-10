<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VPremioPagamento extends Model
{
    protected $table = 'v_premio_pagamento';
    public $timestamp = false;
    protected $fillable = [
        'placa',
        'distancia',
        'media',
        'subcategoria',
        'valor_premio',
        'tipo_calculo',
        'tipo_operacao',
        'step',
        'cod_rv',
        'nome',
        'cod_premio',
        'excedente',
    ];
}
