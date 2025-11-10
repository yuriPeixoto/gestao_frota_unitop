<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class FranquiaPremioRv extends Model
{
    use LogsActivity;

    protected $table = 'franquia_premio_rv';
    protected $primaryKey = 'id_franquia_premio_rv';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'id_operacao',
        'id_categoria',
        'id_tipoequipamento',
        'id_subcategoria',
        'step',
        'media',
        'valor',
        'id_filial',
        'usuario_inclusao',
        'pesobruto',
        'ativo',
        'clonado',
    ];
    protected $casts = [
        'ativo' => 'boolean',

    ];
    public function categoria()
    {
        return $this->belongsTo(CategoriaVeiculo::class, 'id_categoria', 'id_categoria');
    }
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
