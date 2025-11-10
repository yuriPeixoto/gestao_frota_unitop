<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Traits\ToggleIsActiveOnSoftDelete;
use App\Traits\LogsActivity;

class ProdutosPorFilial extends Model
{
    use SoftDeletes, LogsActivity, ToggleIsActiveOnSoftDelete;

    protected $table = 'produtos_por_filial';
    protected $primaryKey = 'id_produtos_por_filial';
    public $timestamps    = false;

    protected $fillable = [
        "id_produtos_por_filial",
        "data_inclusao",
        "data_alteracao",
        "id_filial",
        "id_produto_unitop",
        "quantidade_produto",
        "valor_medio",
        "localizacao",
        "id_estoque",
        "deleted_at",
        "is_ativo",
        "quantidade_transferencia"
    ];


    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto_unitop', 'id_produto');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }


    public function getValorMedioAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorMedioAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['valor_medio'] = 0;
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
            $this->attributes['valor_medio'] = $floatValue;
        } else {
            $this->attributes['valor_medio'] = 0;
        }
    }

    protected static function boot()
    {
        parent::boot();

        // Limpar cache quando um produto for modificado
        static::saved(function ($produto) {
            Cache::forget('produtos_por_filial_' . $produto->id_produtos_por_filial);

            // Limpar o cache dos pessoales frequentes
            Cache::forget('produtos_por_filial_frequentes');
        });

        static::deleted(function ($produto) {
            Cache::forget('produtos_por_filial_' . $produto->id_produtos_por_filial);
            Cache::forget('produtos_por_filial_frequentes');
        });
    }
}
