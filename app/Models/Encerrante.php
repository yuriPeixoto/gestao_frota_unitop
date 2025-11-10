<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Encerrante extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'encerrante';
    protected $primaryKey = 'id_encerrante';
    public const CREATED_AT = 'data_inclusao';
    public const UPDATED_AT = 'data_alteracao';

    protected $fillable = [
        'id_bomba',
        'id_tanque',
        'data_hora_abertura',
        'data_hora_encerramento',
        'encerrante_abertura',
        'encerrante_fechamento',
        'id_filial',
        'usuario'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_hora_abertura' => 'datetime',
        'data_hora_encerramento' => 'datetime',
        'encerrante_abertura' => 'integer',
        'encerrante_fechamento' => 'integer'
    ];

    public function bomba()
    {
        return $this->belongsTo(Bomba::class, 'id_bomba');
    }

    public function tanque()
    {
        return $this->belongsTo(Tanque::class, 'id_tanque');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario');
    }

    public function conferente()
    {
        return $this->belongsTo(VUsuario::class, 'usuario', 'id');
    }
}
