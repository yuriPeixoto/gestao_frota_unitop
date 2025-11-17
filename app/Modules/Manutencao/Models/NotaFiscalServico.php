<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotaFiscalServico extends Model
{
    use LogsActivity;

    protected $table = 'nota_fiscal_servico';
    protected $primaryKey = 'id_nota_fiscal_servico';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_fornecedor',
        'data_servico',
        'valor_total_servico',
        'numero_serie',
        'numero_nota_fiscal',
        'id_ordem_servico',
        'tempogarantiaservico',
        'rateio_nf',
    ];

    protected $casts = [
        'data_servico' => 'date',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'valor_total_servico' => 'float'
    ];

    public function getValorTotalServicoAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorTotalServicoAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['valor_total_servico'] = 0;
            return;
        }

        $value = (string)$value;
        $value = trim($value);

        // Remove o símbolo R$ se presente
        if (strpos($value, 'R$') !== false) {
            $value = str_replace('R$', '', $value);
            $value = trim($value);
        }

        // Remove todos os caracteres que não são dígitos, vírgula ou ponto
        $value = preg_replace('/[^0-9.,]/', '', $value);

        // Trata o formato brasileiro (ex: 1.111,11 ou 1111,11)
        if (preg_match('/^[\d.]*,\d{2}$/', $value)) {
            // Formato brasileiro: remove pontos (separadores de milhar) e substitui vírgula por ponto
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        // Trata o formato americano (ex: 1,111.11 ou 1111.11)
        elseif (preg_match('/^[\d,]*\.\d{2}$/', $value)) {
            // Formato americano: remove vírgulas (separadores de milhar)
            $value = str_replace(',', '', $value);
        }
        // Se tem apenas números e um ponto ou vírgula, decide com base na posição
        elseif (preg_match('/^\d+[.,]\d+$/', $value)) {
            // Se a vírgula/ponto está nas últimas 3 posições, é separador decimal
            if (preg_match('/[.,]\d{1,2}$/', $value)) {
                $value = str_replace(',', '.', $value);
            }
        }
        // Se contém apenas números, mantém como está
        elseif (preg_match('/^\d+$/', $value)) {
            Log::debug('Apenas números: "' . $value . '"');
        }
        // Caso não se encaixe em nenhum padrão conhecido
        else {
            // Remove tudo exceto números e o último ponto/vírgula
            $value = preg_replace('/[^0-9]/', '', $value);
            if (empty($value)) {
                $value = '0';
            }
        }

        if (is_numeric($value)) {
            $floatValue = (float)$value;
            $this->attributes['valor_total_servico'] = $floatValue;
            Log::debug('Valor convertido com sucesso: ' . $floatValue);
        } else {
            Log::debug('Valor ainda não é numérico: "' . $value . '", definindo como 0');
            $this->attributes['valor_total_servico'] = 0;
        }
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    public function servicos()
    {
        return $this->hasMany(NotaFiscalServicoItens::class, 'id_nota_fiscal_servico', 'id_nota_fiscal_servico');
    }
}
