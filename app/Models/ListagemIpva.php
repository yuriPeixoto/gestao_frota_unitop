<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ListagemIpva extends Model
{
    use LogsActivity;

    protected $table      = 'smartec_vencimentos_ipva';
    protected $primaryKey = 'id_smartec_vencimentos_ipva';

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'placa',
        'renavam',
        'proprietario',
        'tipo',
        'uf',
        'cota1',
        'vencimento_cota1',
        'cota2',
        'vencimento_cota2',
        'cota3',
        'vencimento_cota3',
        'cota4',
        'vencimento_cota4',
        'cota5',
        'vencimento_cota5',
        'cota6',
        'vencimento_cota6',
        'cota_unica_desconto1',
        'vencimento_cota_unica_desconto1',
        'cota_unica_sem_desconto',
        'vencimento_cota_unica_sem_desconto',
        'cota_unica_desconto2',
        'cota_unica_desconto',
        'vencimento_cota_unica_desconto',
        'url_cota_unica',
        'boleto_cota_unica_valor',
        'boleto_cota_unica_vencimento',
        'boleto_cota_unica_linha_digitavel',
        'boleto_cedente'
    ];
}
