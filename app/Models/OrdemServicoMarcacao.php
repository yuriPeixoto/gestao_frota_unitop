<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class OrdemServicoMarcacao extends Model
{
    use LogsActivity;

    protected $table = 'ordem_servico_marcacao';

    protected $primaryKey = 'id_marcacao';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_ordem_servico',
        'id_pneu',
        'is_marcado',
        'id_nota_fiscal_entrada',
        'is_conferido',
        'marcado_por',
        'conferido_por',
    ];

    protected $casts = [
        'is_marcado' => 'boolean',
        'is_conferido' => 'boolean',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function pneus()
    {
        return $this->belongsTo(Pneu::class, 'id_pneu', 'id_pneu');
    }

    public function usuarioMarcadoPor()
    {
        return $this->belongsTo(User::class, 'marcado_por', 'id');
    }

    public function usuarioConferidoPor()
    {
        return $this->belongsTo(User::class, 'conferido_por', 'id');
    }
}
