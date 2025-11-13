<?php

namespace App\Modules\Estoque\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estoque extends Model
{
    use LogsActivity;

    protected $table = 'estoque';
    protected $primaryKey = 'id_estoque';
    public $timestamps = false;

    protected $fillable = [
        'id_filial',
        'descricao_estoque',
        'data_inclusao',
        'data_alteracao'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    /**
     * Relacionamento com a filial a que pertence o estoque
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    /**
     * Relacionamento com os itens deste estoque
     */
    public function itens(): HasMany
    {
        return $this->hasMany(EstoqueItem::class, 'id_estoque', 'id_estoque');
    }

    /**
     * Relacionamento com os produtos deste estoque
     */
    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'id_estoque_produto', 'id_estoque');
    }

    /**
     * Verifica se o estoque contém um determinado produto
     */
    public function temProduto($idProduto): bool
    {
        return $this->itens()->where('id_produto', $idProduto)->exists();
    }

    /**
     * Obtém a quantidade disponível de um produto neste estoque
     */
    public function quantidadeProduto($idProduto): float
    {
        $item = $this->itens()->where('id_produto', $idProduto)->first();
        return $item ? $item->quantidade_atual : 0;
    }

    /**
     * Adiciona um produto ao estoque ou atualiza se já existir
     */
    public function adicionarProduto($idProduto, $quantidadeMinima = 0, $quantidadeMaxima = null, $localizacao = null): EstoqueItem
    {
        $item = $this->itens()->firstOrNew([
            'id_produto' => $idProduto
        ]);

        if (!$item->exists) {
            $item->quantidade_atual = 0;
            $item->quantidade_minima = $quantidadeMinima;
            $item->quantidade_maxima = $quantidadeMaxima;
            $item->localizacao = $localizacao;
            $item->data_inclusao = now();
            $item->ativo = true;
            $item->save();
        }

        return $item;
    }

    /**
     * Lista os produtos com estoque baixo
     */
    public function produtosEmEstoqueBaixo()
    {
        return $this->itens()
            ->whereRaw('quantidade_atual <= quantidade_minima')
            ->where('ativo', true)
            ->get();
    }

    /**
     * Verifica se tem produtos em estoque baixo
     */
    public function temProdutosEmEstoqueBaixo(): bool
    {
        return $this->itens()
            ->whereRaw('quantidade_atual <= quantidade_minima')
            ->where('ativo', true)
            ->exists();
    }

    /**
     * Realiza uma entrada de produto no estoque
     */
    public function entradaProduto($idProduto, $quantidade, $origem = 'compra', $idReferencia = null, $observacao = null): bool
    {
        if ($quantidade <= 0) {
            return false;
        }

        $item = $this->adicionarProduto($idProduto);
        return $item->registrarEntrada($quantidade, $origem, $idReferencia, $observacao);
    }

    /**
     * Realiza uma saída de produto do estoque
     */
    public function saidaProduto($idProduto, $quantidade, $destino = 'requisicao', $idReferencia = null, $observacao = null): bool
    {
        if ($quantidade <= 0) {
            return false;
        }

        $item = $this->itens()->where('id_produto', $idProduto)->first();

        if (!$item || $item->quantidade_atual < $quantidade) {
            return false;
        }

        return $item->registrarSaida($quantidade, $destino, $idReferencia, $observacao);
    }

    /**
     * Realiza uma transferência de produto entre estoques
     */
    public function transferirProduto($idProduto, $quantidade, $idEstoqueDestino, $observacao = null): bool
    {
        if ($quantidade <= 0 || $this->id_estoque == $idEstoqueDestino) {
            return false;
        }

        $itemOrigem = $this->itens()->where('id_produto', $idProduto)->first();

        if (!$itemOrigem || $itemOrigem->quantidade_atual < $quantidade) {
            return false;
        }

        $estoqueDestino = Estoque::find($idEstoqueDestino);

        if (!$estoqueDestino) {
            return false;
        }

        $itemDestino = $estoqueDestino->adicionarProduto($idProduto);

        // Iniciar a transferência
        try {
            // Registrar a saída do estoque de origem
            $resultado = $itemOrigem->registrarSaida($quantidade, 'transferencia', $itemDestino->id_estoque_item, $observacao);

            if (!$resultado) {
                return false;
            }

            // Registrar a entrada no estoque de destino
            $resultado = $itemDestino->registrarEntrada($quantidade, 'transferencia', $itemOrigem->id_estoque_item, $observacao);

            if (!$resultado) {
                // Em caso de falha na entrada, tenta reverter a saída
                $itemOrigem->registrarEntrada($quantidade, 'estorno_transferencia', $itemDestino->id_estoque_item, 'Estorno automático - falha na transferência');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro na transferência de produtos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Escopo para estoques ativos
     */
    public function scopeAtivo($query)
    {
        // Como a tabela original não tem campo de ativo, consideramos todos ativos
        return $query;
    }

    /**
     * Escopo para estoques por filial
     */
    public function scopePorFilial($query, $idFilial)
    {
        return $query->where('id_filial', $idFilial);
    }

    /**
     * Boot do modelo para adicionar hooks
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->data_inclusao = $model->data_inclusao ?? now();
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }
}
