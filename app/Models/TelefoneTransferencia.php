<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class TelefoneTransferencia extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'telefone_transferencia';
    protected $primaryKey = 'id_telefone_transferencia';
    public $timestamps = false;
    protected $fillable = [
        'telefone',
        'nome',
        'departamento',
        'data_inclusao',
        'data_alteracao',
        'email'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function departamentoTransferencia()
    {
        return $this->belongsTo(DepartamentoTransferencia::class, 'departamento', 'id_departamento_transferencia');
    }

    public function getTelefoneFormatadoAttribute()
    {
        $numero = $this->telefone;

        if (preg_match('/^(\d{2})(\d{1})(\d{4})(\d{4})$/', $numero, $matches)) {
            return "($matches[1]) $matches[2] $matches[3]-$matches[4]";
        }

        return $numero;
    }
}
