<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PremioSuperacao extends Model
{
    use LogsActivity;

    protected $table = 'premio_superacao_vf';
    protected $primaryKey = 'id_premio_superacao';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_user',
        'situacao',
        'data_inicial',
        'motivo_cancelamento',
        'id_user_cancelamento',
        'data_cancelamento',
        'pagamento_realizado'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    public function userCancelamento()
    {
        return $this->belongsTo(User::class, 'id_user_cancelamento', 'id');
    }
}
