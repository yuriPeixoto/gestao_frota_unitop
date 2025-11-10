<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoPecas extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'ordem_servico_pecas';

    protected $primaryKey = 'id_ordem_servico_pecas';

    public $timestamps = true;

    const CREATED_AT = 'data_inclusao';
    const UPDATED_AT = 'data_alteracao';

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_ordem_servico',
        'id_produto',
        'quantidade',
        'id_unidade',
        'aplicacao',
        'id_servico',
        'id_manutencao',
        'jasolicitada',
        'numero_nota_fiscal_pecas',
        'valor_pecas',
        'situacao_pecas',
        'id_fornecedor',
        'valor_desconto',
        'valor_total_com_desconto',
        'data_solicitacao',
        'data_recebimento',
        'is_contrato',
        'id_contrato',
        'pneus_aplicados',
        'is_finalizado',
    ];

    protected $casts = [
        'data_solicitacao' => 'datetime',
        'data_recebimento' => 'datetime',
        'valor_pecas' => 'float',
        'valor_desconto' => 'float',
        'valor_total_com_desconto' => 'float',
        'is_contrato' => 'boolean',
        'is_finalizado' => 'boolean',
    ];

    protected $appends = [
        'valor_pecas_formatado',
        'valor_desconto_formatado',
        'valor_total_com_desconto_formatado'
    ];

    // Mutators - Para salvar no banco
    public function setValorPecasAttribute($value)
    {
        $this->attributes['valor_pecas'] = $this->cleanMoney($value);
    }

    public function setValorDescontoAttribute($value)
    {
        $this->attributes['valor_desconto'] = $this->cleanMoney($value);
    }

    public function setValorTotalComDescontoAttribute($value)
    {
        $this->attributes['valor_total_com_desconto'] = $this->cleanMoney($value);
    }

    // Accessors - Para enviar ao frontend
    public function getValorPecasFormatadoAttribute()
    {
        return $this->formatMoney($this->valor_pecas);
    }

    public function getValorDescontoFormatadoAttribute()
    {
        return $this->formatMoney($this->valor_desconto);
    }

    public function getValorTotalComDescontoFormatadoAttribute()
    {
        return $this->formatMoney($this->valor_total_com_desconto);
    }

    private function cleanMoney($value)
    {

        if (is_null($value) || $value === '') {
            return 0;
        }

        // Se já é numérico, retorna como float
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove espaços em branco
        $cleanValue = trim($value);

        // Remove caracteres que não sejam dígitos, vírgula ou ponto
        $cleanValue = preg_replace('/[^\d,\.]/', '', $cleanValue);

        // Se não tem vírgula nem ponto, é um número inteiro
        if (strpos($cleanValue, ',') === false && strpos($cleanValue, '.') === false) {
            return (float) $cleanValue;
        }

        // Verifica se tem vírgula no final (formato brasileiro)
        if (substr($cleanValue, -3, 1) === ',') {
            // Formato brasileiro: 1.234,56 ou 234,56
            $cleanValue = str_replace('.', '', $cleanValue); // Remove pontos (separadores de milhares)
            $cleanValue = str_replace(',', '.', $cleanValue); // Troca vírgula por ponto
        } else {
            // Se tem ponto no final, assume formato americano: 1,234.56 ou 234.56
            $cleanValue = str_replace(',', '', $cleanValue); // Remove vírgulas (separadores de milhares)
        }

        $result = (float) $cleanValue;

        return $result;
    }

    private function formatMoney($value)
    {
        if (is_null($value)) {
            $value = 0;
        }

        return number_format($value, 2, ',', '.');
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'id_ordem_servico', 'id_ordem_servico');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    public function relacaoImobilizados(): BelongsTo
    {
        return $this->belongsTo(RelacaoImobilizados::class, 'id_ordem_servico', 'id_orderm_servico');
    }
}
