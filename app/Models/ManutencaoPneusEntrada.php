<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManutencaoPneusEntrada extends Model
{
    use LogsActivity;


    protected $table = 'manutencao_pneu_entrada';

    protected $primaryKey = 'id_manutencao_entrada';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'id_fornecedor',
        'numero_nf',
        'serie_nf',
        'valor_total_nf',
        'valor_total_desconto',
        'data_recebimento',
        'id_manutencao',
        'situacao_entrada',
        'chave_nf_entrada',
        'is_borracharia',
        'valor_pneu',
        'valor_pneu_total',
        'id_servico',

    ];

    public function getValorTotalDescontoAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorTotalDescontoAttribute($value)
    {

        if (empty($value)) {
            $this->attributes['valor_total_desconto'] = 0;
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
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $floatValue = (float)$value;
            $this->attributes['valor_total_desconto'] = $floatValue;
        } else {
            $this->attributes['valor_total_desconto'] = 0;
        }
    }

    public function getValorTotalNfAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorTotalNfAttribute($value)
    {

        if (empty($value)) {
            $this->attributes['valor_total_nf'] = 0;
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
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $floatValue = (float)$value;
            $this->attributes['valor_total_nf'] = $floatValue;
        } else {
            $this->attributes['valor_total_nf'] = 0;
        }
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function manutencaoPneusEntrada(): BelongsTo
    {
        return $this->belongsTo(ManutencaoPneusEntradaItens::class, 'id_manutencao_entrada');
    }

    public function manutencaoPneu(): BelongsTo
    {
        return $this->belongsTo(ManutencaoPneus::class, 'id_manutencao');
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }
}
