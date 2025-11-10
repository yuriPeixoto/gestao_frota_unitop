<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class JornadaFeriado extends Model
{
    use LogsActivity;

    protected $table = 'jornada_feriados';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'tipo', 'descricao'];
}
