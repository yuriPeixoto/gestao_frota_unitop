<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class FranquiaPremioMensal extends Model
{
    use LogsActivity;

    protected $table = 'franquia_premio_mensal';
    protected $primaryKey = 'id_franquia_premio_mensal';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'id_operacao',
        'id_tipoequipamento',
        'id_subcategoria',
        'step',
        'media',
        'id_filial',
        'usuario_inclusao',
        'ativo',
        'clonado',
        '_1000',
        '_2000',
        '_3000',
        '_4000',
        '_5000',
        '_6000',
        '_7000',
        '_8000',
        '_9000',
        '_10000',
        '_11000',
        '_12000',
        '_13000',
        '_14000',
        '_15000',
        '_16000',
        '_17000',
        '_18000',
        '_19000',
        '_20000',
        '_0_1000',
    ];

    public function subcategoria()
    {
        return $this->belongsTo(SubCategoriaVeiculo::class, 'id_subcategoria', 'id_subcategoria');
    }
    public function equipamento()
    {
        return $this->belongsTo(TipoEquipamento::class, 'id_tipoequipamento', 'id_tipo_equipamento');
    }
    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }
    public function operacao()
    {
        return $this->belongsTo(TipoOperacao::class, 'id_operacao', 'id_tipo_operacao');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'usuario_inclusao', 'id');
    }
}
