<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemSolicitacaoCompra extends Model
{
    use LogsActivity;

    protected $table      = 'itenssolicitacoescompras';
    protected $primaryKey = 'id_itens_solicitacoes';
    public $timestamps    = false;

    // Constantes de status do item
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_produto',
        'id_unidade',
        'valor_ultima_compra',
        'fornecedor_ultima_compra',
        'id_solicitacao_compra',
        'quantidade_solicitada',
        'imagem_produto',
        'observacao_item',
        'justificativa_iten_solicitacao',
        'pre_cadastro',
        'id_ordem_servico_pecas',
        'id_contrato',
        'valor_produto',
        'valor_total',
        'valor_total_desconto',
    ];

    protected $casts = [
        'quantidade_solicitada' => 'float',
    ];

    /**
     * Get the solicitação that owns the item
     */
    public function solicitacaoCompra()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacao_compra', 'id_solicitacoes_compras');
    }

    /**
     * Get the produto associated with the item
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    /**
     * Get the serviço associated with the item
     */
    public function servico()
    {
        return $this->belongsTo(Servico::class, 'id_produto', 'id_servico');
    }

    public function unidadeProduto()
    {
        return $this->belongsTo(UnidadeProduto::class, 'id_unidade', 'id_unidade_produto');
    }

    /**
     * Get the item (produto ou serviço) de forma inteligente
     */
    public function getItemAttribute()
    {
        // Primeiro tenta buscar como produto
        $produto = $this->produto;
        if ($produto) {
            return $produto;
        }

        // Se não encontrar como produto, tenta buscar como serviço
        $servico = $this->servico;
        if ($servico) {
            return $servico;
        }

        return null;
    }

    /**
     * Get produto ou serviço (fallback inteligente)
     */
    public function getProdutoOrServicoAttribute()
    {
        return $this->item;
    }

    /**
     * Get the itens de pedido relacionados a este item de solicitação
     */
    public function itensPedido()
    {
        return $this->hasMany(ItensPedidos::class, 'id_item_solicitacao', 'id_item_solicitacao');
    }

    /**
     * Verificar se este item já foi incluído em algum pedido
     */
    public function foiInclusoEmPedido()
    {
        return $this->itensPedido()->exists();
    }

    /**
     * Verificar se este item é um produto (de forma inteligente)
     */
    public function isProduto()
    {
        // Verifica se existe um produto com esse ID
        return $this->produto !== null;
    }

    /**
     * Verificar se este item é um serviço (de forma inteligente)
     */
    public function isServico()
    {
        // Verifica se existe um serviço com esse ID
        return $this->servico !== null;
    }

    /**
     * Verificar o tipo do item dinamicamente
     */
    public function getTipoItemAttribute()
    {
        if ($this->isProduto()) {
            return 'produto';
        }
        if ($this->isServico()) {
            return 'servico';
        }
        return 'indefinido';
    }

    /**
     * Obter o nome do item (produto ou serviço)
     */
    public function getNomeItemAttribute()
    {
        $item = $this->item;

        if ($item) {
            // Se for um produto
            if (isset($item->descricao_produto)) {
                return $item->descricao_produto;
            }
            // Se for um serviço
            if (isset($item->descricao_servico)) {
                return $item->descricao_servico;
            }
        }

        return $this->descricao ?? 'Item não encontrado';
    }

    /**
     * Obter a quantidade pendente (que ainda não foi atendida por pedidos de compra)
     */
    public function getQuantidadePendenteAttribute()
    {
        $atendido = $this->itensPedido->sum('quantidade');
        return max(0, $this->quantidade - $atendido);
    }

    /**
     * Obter o percentual já atendido deste item
     */
    public function getPercentualAtendidoAttribute()
    {
        if ($this->quantidade <= 0) {
            return 0;
        }

        $atendido = $this->itensPedido->sum('quantidade');
        return min(100, round(($atendido / $this->quantidade) * 100, 2));
    }

    /**
     * Verificar se este item pode ser adicionado a um pedido
     */
    public function podeSerAdicionadoAPedido()
    {
        $solicitacao = $this->solicitacaoCompra;

        // Se a solicitação não existir ou não estiver aprovada, não pode adicionar
        if (!$solicitacao || $solicitacao->aprovado_reprovado !== true) {
            return false;
        }

        // Se a solicitação estiver cancelada ou finalizada, não pode adicionar
        if ($solicitacao->is_cancelada || $solicitacao->data_finalizada) {
            return false;
        }

        // Se o item já foi totalmente atendido, não pode adicionar
        if ($this->isTotalmenteAtendido()) {
            return false;
        }

        return true;
    }

    /**
     * Obter a URL da imagem do produto
     */
    public function getImagemProdutoUrlAttribute()
    {
        if (!$this->imagem_produto) {
            return null;
        }

        // Se é uma URL completa, retorna como está
        if (filter_var($this->imagem_produto, FILTER_VALIDATE_URL)) {
            return $this->imagem_produto;
        }

        // Se é um caminho relativo, retorna a URL usando o storage
        // Verificar se o arquivo existe
        $fullPath = storage_path('app/public/' . $this->imagem_produto);
        if (file_exists($fullPath)) {
            return asset('storage/' . $this->imagem_produto);
        }

        // Se o arquivo não existir, retornar null
        return null;
    }

    /**
     * Verificar se tem imagem anexada
     */
    public function hasImagem()
    {
        if (empty($this->imagem_produto)) {
            return false;
        }

        // Se é uma URL completa, assumir que existe
        if (filter_var($this->imagem_produto, FILTER_VALIDATE_URL)) {
            return true;
        }

        // Verificar se o arquivo existe no storage
        $fullPath = storage_path('app/public/' . $this->imagem_produto);
        return file_exists($fullPath);
    }
}
