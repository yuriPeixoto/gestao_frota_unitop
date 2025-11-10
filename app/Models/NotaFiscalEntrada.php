<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotaFiscalEntrada extends Model
{
    use LogsActivity;

    protected $table = 'nota_fiscal_entrada';
    protected $primaryKey = 'id_nota_fiscal_entrada';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'cnpj',
        'nome_empresa',
        'numero',
        'endereco',
        'bairro',
        'ibge_municipio',
        'uf',
        'cep',
        'cod_nota_fiscal',
        'natureza_operacao',
        'numero_nota_fiscal',
        'data_emissao',
        'data_saida',
        'valor_nota_fiscal',
        'valor_frete',
        'valor_desconto',
        'processada',
        'id_fornecedor',
        'id_filial',
        'id_uf',
        'apuracao_saldo',
        'aplica_rateio',
        'id_pedido_compras',
        'chave_nf_entrada',
        'tipo_nota',
        'id_nfe_core',
        'caminho_xml',
        'id_user',
    ];

    /**
     * Formatar valor monetário para formato brasileiro
     * 
     * @param float|string|null $valor
     * @return string
     */
    public function formatarValorMonetario($valor): string
    {
        if (is_null($valor) || $valor === '') {
            return 'R$ 0,00';
        }

        // Converte para float caso seja string
        $valor = (float) $valor;

        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Accessor para valor da nota fiscal formatado
     */
    public function getValorNotaFiscalFormatadoAttribute(): string
    {
        return $this->formatarValorMonetario($this->valor_nota_fiscal);
    }

    /**
     * Accessor para valor do frete formatado
     */
    public function getValorFreteFormatadoAttribute(): string
    {
        return $this->formatarValorMonetario($this->valor_frete);
    }

    /**
     * Accessor para valor do desconto formatado
     */
    public function getValorDescontoFormatadoAttribute(): string
    {
        return $this->formatarValorMonetario($this->valor_desconto);
    }

    /**
     * Validar se a nota fiscal pode ser devolvida
     * Verifica se não há produtos com quantidade insuficiente no estoque
     */
    public function validarDevolucao()
    {
        if (empty($this->id_nota_fiscal_entrada)) {
            return false;
        }

        $query = "
            SELECT 
                nfe.id_nota_fiscal_entrada
            FROM nota_fiscal_entrada nfe
                INNER JOIN nota_fiscal_produtos nfp ON nfp.id_nota_fiscal_entrada = nfe.id_nota_fiscal_entrada
                INNER JOIN produtos_por_filial ppf ON ppf.id_produto_unitop = nfp.cod_produto AND ppf.id_filial = nfe.id_filial
            WHERE nfe.id_nota_fiscal_entrada = :idNota
            AND ppf.quantidade_produto < nfp.quantidade_produtos
            LIMIT 1
        ";

        $result = DB::select($query, ['idNota' => $this->id_nota_fiscal_entrada]);
        return empty($result);
    }

    /**
     * Validar devolução parcial
     * Verifica se não há produtos já devolvidos parcialmente
     */
    public function validarDevolucaoParcial()
    {
        if (empty($this->id_nota_fiscal_entrada)) {
            return false;
        }

        $produto = DB::table('nota_fiscal_produtos')
            ->where('id_nota_fiscal_entrada', $this->id_nota_fiscal_entrada)
            ->whereNotNull('qtde_devolucao')
            ->first();

        return empty($produto);
    }

    /**
     * Relacionamento com NotaFiscalProdutos
     */
    public function produtos()
    {
        return $this->hasMany(\App\Models\NotaFiscalProdutos::class, 'id_nota_fiscal_entrada', 'id_nota_fiscal_entrada');
    }

    // relacionamento com pedido de compras
    public function pedidoCompra()
    {
        return $this->belongsTo(
            \App\Models\PedidoCompra::class,
            'id_pedido_compras',
            'id_pedido_compras'
        );
    }

    // accessor para saber se valores são diferentes
    public function getValoresDiferentesAttribute(): bool
    {
        // se não tiver pedido, considere como iguais
        if (!$this->pedidoCompra) {
            return false;
        }

        // comparo como string numérica para não ter problema de precisão
        return bccomp(
            $this->valor_nota_fiscal,
            $this->pedidoCompra->valor_total_desconto,
            2 // casas decimais
        ) !== 0;
    }
}