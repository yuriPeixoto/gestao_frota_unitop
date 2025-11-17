<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotaFiscalServicoItens extends Model
{
    use LogsActivity;

    protected $table = 'nota_fiscal_servico_itens';
    protected $primaryKey = 'id_nota_fiscal_servico_itens';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'valor_produto',
        'quantidade',
        'total_produto',
        'tempogarantia_produto',
        'id_nota_fiscal_servico',
        'id_servico',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'valor_produto' => 'float',
        'total_produto' => 'float',
    ];


    public function getValorProdutoAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorProdutoAttribute($value)
    {

        if (empty($value)) {
            $this->attributes['valor_produto'] = 0;
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
            $this->attributes['valor_produto'] = $floatValue;
            Log::debug('Valor convertido com sucesso: ' . $floatValue);
        } else {
            Log::debug('Valor ainda não é numérico: "' . $value . '", definindo como 0');
            $this->attributes['valor_produto'] = 0;
        }
    }

    public function getTotalProdutoAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setTotalProdutoAttribute($value)
    {

        if (empty($value)) {
            $this->attributes['total_produto'] = 0;
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
            $this->attributes['total_produto'] = $floatValue;
            Log::debug('Valor convertido com sucesso: ' . $floatValue);
        } else {
            Log::debug('Valor ainda não é numérico: "' . $value . '", definindo como 0');
            $this->attributes['total_produto'] = 0;
        }
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'id_servico', 'id_servico');
    }
}
