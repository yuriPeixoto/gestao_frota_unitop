<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcertoEstoque extends Model
{
    protected $table = 'acerto_estoque';
    protected $primaryKey = 'id_acerto_estoque';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'id_estoque',
        'id_produto',
        'id_tipo_acerto',
        'quantidade_acerto',
        'preco_medio',
        'data_acerto',
        'quantidade_atual',
        'id_usuario_acerto'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_acerto' => 'date',
        'preco_medio' => 'float'
    ];

    public function getPrecoMedioAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setPrecoMedioAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['preco_medio'] = 0;
            return;
        }

        $value = (string)$value;
        $value = trim($value);

        if (strpos($value, 'R$') !== false) {
            $value = str_replace('R$', '', $value);
            $value = trim($value);
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // CORREÇÃO: Tratar o ponto como separador de milhares
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $floatValue = (float)$value;
            $this->attributes['preco_medio'] = $floatValue;
        } else {
            $this->attributes['preco_medio'] = 0;
        }
    }


    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }

    public function estoque()
    {
        return $this->belongsTo(Estoque::class, 'id_estoque');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto');
    }

    public function tipo_acerto()
    {
        return $this->belongsTo(TipoAcertoEstoque::class, 'id_tipo_acerto');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario_acerto');
    }
}
