<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManutencaoPneusEntradaItens extends Model
{
    use LogsActivity;


    protected $table = 'manutencao_pneu_entrada_itens';

    protected $primaryKey = 'id_manutencao_pneu_entrada_itens';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_pneu',
        'id_tipo_reforma',
        'id_desenho_pneu',
        'tipo_borracha',
        'laudo_descarte',
        'situacao_pneu_interno',
        'id_manutencao_pneu_entrada',
        'descarte',
        'is_conferido',
        'is_feito',
    ];

    public function tipoReforma()
    {
        return $this->belongsTo(TipoReformaPneu::class, 'id_tipo_reforma');
    }

    public function tipo_reforma()
    {
        return $this->belongsTo(TipoReformaPneu::class, 'id_tipo_reforma');
    }

    public function desenho_pneu()
    {
        return $this->belongsTo(TipoDesenhoPneu::class, 'id_desenho_pneu');
    }

    public function tipo_borracha()
    {
        return $this->belongsTo(TipoBorrachaPneu::class, 'tipo_borracha');
    }

    public function pneu()
    {
        return $this->belongsTo(Pneu::class, 'id_pneu');
    }
}
