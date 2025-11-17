<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoServicos extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'ordem_servico_servicos';
    protected $primaryKey = 'id_ordem_servico_serv';

    public $timestamps = true;

    const CREATED_AT = 'data_inclusao';
    const UPDATED_AT = 'data_alteracao';

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_fornecedor',
        'id_ordem_servico',
        'quantidade_servico',
        'finalizado',
        'id_servicos',
        'valor_servico',
        'numero_nota_fiscal_servicos',
        'id_manutencao',
        'valor_descontoservico',
        'valor_total_com_desconto',
        'status_servico',
        'user_mec',
        'is_contrato',
        'is_solicitado',
        'data_solicitacao',
        'valor_total',
        'desconto',
        'id_contrato'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_solicitacao' => 'datetime',
        'finalizado' => 'boolean',
        'is_contrato' => 'boolean',
        'is_solicitado' => 'boolean',
        'valor_servico' => 'float',
        'valor_descontoservico' => 'float',
        'valor_total_com_desconto' => 'float',
        'valor_total' => 'float',
        'desconto' => 'float'
    ];

    public function setValorServicoAttribute($value)
    {
        $this->attributes['valor_servico'] = $this->cleanMoney($value);
    }

    public function setValorDescontoServicoAttribute($value)
    {
        $this->attributes['valor_descontoservico'] = $this->cleanMoney($value);
    }

    public function setValorTotalComDescontoAttribute($value)
    {
        $this->attributes['valor_total_com_desconto'] = $this->cleanMoney($value);
    }

    public function setValorDescontoAttribute($value)
    {
        $this->attributes['desconto'] = $this->cleanMoney($value);
    }

    private function cleanMoney($value)
    {
        if (is_null($value)) {
            return 0;
        }

        if (is_numeric($value)) {
            return $value;
        }

        $cleanValue = preg_replace('/[^\d,\.]/', '', $value);
        $cleanValue = str_replace(',', '.', $cleanValue);
        return (float) $cleanValue;
    }

    /**
     * Relacionamento com Fornecedor
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Relacionamento com Serviço
     */
    public function servicos()
    {
        return $this->belongsTo(Servico::class, 'id_servicos', 'id_servico');
    }

    /**
     * Relacionamento com Ordem de Serviço
     */
    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'id_ordem_servico', 'id_ordem_servico');
    }

    /**
     * Relacionamento com Manutenção
     */
    public function manutencao()
    {
        return $this->belongsTo(Manutencao::class, 'id_manutencao', 'id_manutencao');
    }

    /**
     * Calcula o valor total com desconto
     */
    public function calcularValorTotal()
    {
        if ($this->valor_servico && $this->quantidade_servico) {
            $valorTotal = $this->valor_servico * $this->quantidade_servico;
            $valorComDesconto = $valorTotal - ($this->valor_descontoservico ?? 0);

            $this->valor_total = $valorTotal;
            $this->valor_total_com_desconto = $valorComDesconto;
        }
    }
}
