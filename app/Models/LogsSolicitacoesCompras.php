<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class LogsSolicitacoesCompras extends Model
{
    use LogsActivity;

    // protected $connection = 'pgsql'; // Usar a conexão padrão
    protected $table = 'logs_solicitacoes_compras';
    protected $primaryKey = 'id_logs_solicitacoes_compras';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'situacao_compra',
        'user_id',
        'observacao',
        'id_solicitacoes_compras'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->data_inclusao = now();
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }

    public function solicitacaoCompra()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacoes_compras', 'id_solicitacoes_compras');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
