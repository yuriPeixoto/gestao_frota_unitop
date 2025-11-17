<?php

namespace App\Modules\Veiculos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DespesasVeiculos extends Model
{
    use LogsActivity;

    protected $table = 'despesas_veiculos';

    protected $primaryKey = 'id_despesas_veiculos';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'id_departamento',
        'data_despesa',
        'valor_despesa',
        'arquivo',
        'nome_arquivo',
        'id_tipo_despesas',
        'valor_frete',
        'valor_pago',
        'numero_nf',
        'id_fornecedor',
        'id_filial',
        'serie_nf',
        'observacao',
        'aplicar_rateio',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_despesa' => 'datetime',
        'valor_despesa' => 'float',
        'valor_frete' => 'float',
        'valor_pago' => 'float',
    ];

    /**
     * Método auxiliar para formatar valores monetários para exibição
     */
    private function formatMonetaryValue($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    /**
     * Método auxiliar para processar valores monetários antes de salvar
     */
    private function processMonetaryValue($value)
    {
        if (empty($value)) {
            return 0;
        }

        $value = (string)$value;
        $value = trim($value);

        // Remove o símbolo R$ se presente
        if (strpos($value, 'R$') !== false) {
            $value = str_replace('R$', '', $value);
            $value = trim($value);
            LOG::DEBUG('Valor sem R$: ' . $value);
            // Remove pontos (separadores de milhar)
            $value = str_replace('.', '', $value);
            LOG::DEBUG('Valor sem pontos: ' . $value);
            // Converte vírgula para ponto decimal
            $value = str_replace(',', '.', $value);
            LOG::DEBUG('Valor sem virgula: ' . $value);
        } else {
            // Converte vírgula para ponto decimal
            $value = str_replace(',', '.', $value);
            LOG::DEBUG('Valor sem R$ e sem virgula: ' . $value);
        }

        // Remove caracteres não numéricos exceto ponto decimal
        $value = preg_replace('/[^0-9.]/', '', $value);
        LOG::DEBUG('Valor sem caracteres nao numericos: ' . $value);

        if (is_numeric($value)) {
            LOG::DEBUG('Valor numerico: ' . $value);
            return (float)$value;
        }

        return 0;
    }

    // Getters para formatação de exibição
    public function getValorDespesaAttribute($value)
    {
        return $this->formatMonetaryValue($value);
    }

    public function getValorFreteAttribute($value)
    {
        return $this->formatMonetaryValue($value);
    }

    public function getValorPagoAttribute($value)
    {
        // Debug temporário - remover após resolver
        Log::info('setValorPagoAttribute chamado', [
            'value_original' => $value,
            'value_processed' => $this->processMonetaryValue($value)
        ]);
        return $this->formatMonetaryValue($value);
    }

    // Setters para processamento antes de salvar
    public function setValorDespesaAttribute($value)
    {
        $this->attributes['valor_despesa'] = $this->processMonetaryValue($value);
    }

    public function setValorFreteAttribute($value)
    {
        $this->attributes['valor_frete'] = $this->processMonetaryValue($value);
    }

    public function setValorPagoAttribute($value)
    {
        $this->attributes['valor_pago'] = $this->processMonetaryValue($value);
    }

    // Relacionamentos
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function tipoDespesas()
    {
        return $this->belongsTo(TipoDespesas::class, 'id_tipo_despesas', 'id_tipo_despesas');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
}
