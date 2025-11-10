<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class DevolucaoMatriz extends Model
{
    use LogsActivity;

    protected $table = 'devolucao_matriz';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'id_transferencia_estoque',
        'id_user_solicitante',
        'id_user_aprovador',
        'aprovado',
        'liberado',
        'observaocao',
        'observacao_aprovador',
        'id_departamento',
        'id_nova_entrada',
        'transferencia_feita'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'aprovado' => 'boolean',
        'liberado' => 'boolean',
        'transferencia_feita' => 'boolean'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user_solicitante');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }
}