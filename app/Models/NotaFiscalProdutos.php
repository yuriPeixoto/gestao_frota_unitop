<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaFiscalProdutos extends Model
{
    use LogsActivity;

    protected $table = 'nota_fiscal_produtos';
    protected $primaryKey = 'id_nota_fiscal_produtos';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_nota_fiscal_entrada',
        'numero_item',
        'cod_produto',
        'nome_produto',
        'ncm',
        'unidade',
        'quantidade_produtos',
        'valor_unitario',
        'valor_total',
        'valor_desconto',
        'inconformidade',
        'qtde_devolucao',
        'observacao',
    ];

    // ADICIONE ESTA PROPRIEDADE
    protected $appends = [
        'valor_unitario_formatado',
        'valor_total_formatado',
        'valor_unitario_desconto_formatado'
    ];

    public function notaFiscalEntrada()
    {
        return $this->belongsTo(NotaFiscalEntrada::class, 'id_nota_fiscal_entrada', 'id_nota_fiscal_entrada');
    }

    /**
     * Formatar valor monetário para formato brasileiro
     */
    public function formatarValorMonetario($valor): string
    {
        if (is_null($valor) || $valor === '') {
            return 'R$ 0,00';
        }

        $valor = (float) $valor;
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Accessor para valor unitário formatado
     */
    public function getValorUnitarioFormatadoAttribute(): string
    {
        return $this->formatarValorMonetario($this->valor_unitario);
    }

    /**
     * Accessor para valor total formatado
     */
    public function getValorTotalFormatadoAttribute(): string
    {
        return $this->formatarValorMonetario($this->valor_total);
    }

    /**
     * Accessor para valor do desconto formatado
     */
    public function getValorUnitarioDescontoFormatadoAttribute(): string
    {
        return $this->formatarValorMonetario($this->valor_desconto);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'cod_produto', 'id_produto');
    }

    public function nfPneus(): HasMany
    {
        return $this->hasMany(NotaFiscalPneu::class, 'id_nota_fiscal_produtos', 'id_nota_fiscal_produtos');
    }
}