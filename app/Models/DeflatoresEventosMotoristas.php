<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class DeflatoresEventosMotoristas extends Model
{
    use LogsActivity;

    protected $table = 'deflatores_motoristas_eventos';
    protected $primaryKey = 'id_deflatores_motoristas_eventos';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_deflatores',
        'id_motorista',
        'data_evento',
        'observacao',
        'arquivo',
        'user_lancamento',
        'filial_lancamento',
        'id_veiculo'
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_lancamento', 'id');
    }
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_lancamento', 'id');
    }
    public function motorista()
    {
        return $this->belongsTo(Pessoal::class, 'id_motorista');
    }
    public function deflatores()
    {
        return $this->belongsTo(Deflatores::class, 'id_deflatores', 'id_deflatores');
    }
}
