<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Deflatores extends Model
{
    use LogsActivity;

    protected $table = 'deflatores_carvalima';
    protected $primaryKey = 'id_deflatores';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao_evento',
        'valor',
        'data_evento',
        'user_id',
        'unidade_id',
        'ativo',

    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'unidade_id', 'id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
