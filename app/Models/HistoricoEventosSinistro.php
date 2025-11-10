<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class HistoricoEventosSinistro extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'historico_eventos_sinistro';
    protected $primaryKey = 'id_historico_eventos_sinistro';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'id_sinistro', 'data_evento', 'id_usuario', 'descricao_situacao', 'observacao'];

    public function sinistro()
    {
        return $this->belongsTo(Sinistro::class, 'id_sinistro');
    }
}
