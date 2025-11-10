<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Produto extends Model
{
    use LogsActivity, SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $connection = 'pgsql';

    // necessário para o soft delete identificar o campo
    protected $activeField = 'is_ativo';

    protected $table = 'produto';

    protected $primaryKey = 'id_produto';

    public $timestamps = false;

    protected $fillable = [
        'id_filial',
        'data_inclusao',
        'data_alteracao',
        'descricao_produto',
        'is_original',
        'curva_abc',
        'tempo_garantia',
        'id_unidade_produto',
        'ncm',
        'estoque_minimo',
        'estoque_maximo',
        'localizacao_produto',
        'quantidade_atual_produto',
        'imagem_produto',
        'id_estoque_produto',
        'id_grupo_servico',
        'id_produto_subgrupo',
        'valor_medio',
        'nome_imagem',
        'codigo_produto',
        'cod_fabricante_',
        'cod_alternativo_1_',
        'cod_alternativo_2_',
        'cod_alternativo_3_',
        'id_modelo_pneu',
        'is_ativo',
        'descricao_min',
        'is_imobilizado',
        'id_tipo_imobilizados',
        'marca',
        'modelo',
        'pre_cadastro',
        'id_user_cadastro',
        'id_user_edicao',
        'is_fracionado',
        'deleted_at',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'is_original' => 'boolean',
        'is_ativo' => 'boolean',
        'is_imobilizado' => 'boolean',
        'pre_cadastro' => 'boolean',
        'is_fracionado' => 'boolean',
        'estoque_minimo' => 'integer',
        'estoque_maximo' => 'integer',
        'quantidade_atual_produto' => 'float',
        'valor_medio' => 'float',
    ];

    public function getValorMedioAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float) $value, 2, ',', '.');
        }

        return $value;
    }

    public function setValorMedioAttribute($value)
    {

        if (empty($value)) {
            $this->attributes['valor_medio'] = 0;

            return;
        }

        $value = (string) $value;

        $value = trim($value);

        if (strpos($value, 'R$') !== false) {
            $value = str_replace('R$', '', $value);
            $value = trim($value);

            $value = str_replace('.', '', $value);

            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $floatValue = (float) $value;
            $this->attributes['valor_medio'] = $floatValue;
        } else {
            $this->attributes['valor_medio'] = 0;
        }
    }

    /**
     * Relacionamento com Usuário que editou
     */
    public function usuarioEdicao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_edicao', 'id');
    }

    /**
     * Relacionamento com Usuário que cadastrou
     */
    public function usuarioCadastro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_cadastro', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        // Limpar cache quando um produto for modificado
        static::saved(function ($produto) {
            Cache::forget('produtos_' . $produto->id_produto);

            // Limpar o cache dos pessoales frequentes
            Cache::forget('produtos_frequentes');
        });

        static::deleted(function ($produto) {
            Cache::forget('produtos_' . $produto->id_produto);
            Cache::forget('produtos_frequentes');
        });
    }

    public function produtosImobilizados()
    {
        return $this->hasMany(ProdutosImobilizados::class, 'id_produto');
    }

    /**
     * Relacionamento com Estoque
     */
    public function estoque(): BelongsTo
    {
        return $this->belongsTo(Estoque::class, 'id_estoque_produto', 'id_estoque');
    }

    /**
     * Relacionamento com Grupo de Serviço
     */
    public function grupoServico(): BelongsTo
    {
        return $this->belongsTo(GrupoServico::class, 'id_grupo_servico', 'id_grupo');
    }

    /**
     * Relacionamento com Subgrupo de Serviço
     */
    public function subgrupoServico(): BelongsTo
    {
        return $this->belongsTo(SubgrupoServico::class, 'id_produto_subgrupo', 'id_subgrupo');
    }

    /**
     * Relacionamento com Unidade de Produto
     */
    public function unidadeProduto(): BelongsTo
    {
        return $this->belongsTo(UnidadeProduto::class, 'id_unidade_produto', 'id_unidade_produto');
    }

    /**
     * Relacionamento com Modelo de Pneu
     */
    public function modeloPneu(): BelongsTo
    {
        return $this->belongsTo(ModeloPneu::class, 'id_modelo_pneu', 'id_modelo_pneu');
    }

    /**
     * Relacionamento com Filial
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    // Relacionamentos com o módulo de Compras

    /**
     * Relacionamento com Itens de Solicitação de Compra
     */
    public function itensSolicitacaoCompra(): HasMany
    {
        return $this->hasMany(ItemSolicitacaoCompra::class, 'id_produto', 'id_produto');
    }

    /**
     * Relacionamento com Itens de Pedido de Compra
     */
    public function itensPedidoCompra(): HasMany
    {
        return $this->hasMany(ItemPedidoCompra::class, 'id_produto', 'id_produto');
    }

    /**
     * Relacionamento com Itens de Contrato
     */
    public function itensContrato(): HasMany
    {
        return $this->hasMany(ItemContrato::class, 'id_produto', 'id_produto');
    }

    /**
     * Relacionamento com Itens de Nota Fiscal
     */
    public function itensNotaFiscal(): HasMany
    {
        return $this->hasMany(ItemNotaFiscal::class, 'id_produto', 'id_produto');
    }

    /**
     * Relacionamento com Itens de Orçamento
     */
    public function itensOrcamento(): HasMany
    {
        return $this->hasMany(ItemOrcamento::class, 'id_produto', 'id_produto')
            ->whereHas('itemPedidoCompra', function ($query) {
                $query->where('tipo', 'produto');
            });
    }

    // Relacionamento com peças da ordem de serviço
    public function ordemServicoPecas()
    {
        return $this->hasMany(OrdemServicoPecas::class, 'id_produto', 'id_produto');
    }

    /**
     * Permite buscar produtos ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Permite buscar produtos pelo grupo
     */
    public function scopePorGrupo($query, $idGrupo)
    {
        return $query->where('id_grupo_servico', $idGrupo);
    }

    /**
     * Permite buscar produtos pelo subgrupo
     */
    public function scopePorSubgrupo($query, $idSubgrupo)
    {
        return $query->where('id_produto_subgrupo', $idSubgrupo);
    }

    /**
     * Permite buscar produtos com estoque abaixo do mínimo
     */
    public function scopeEstoqueAbaixoMinimo($query)
    {
        return $query->whereRaw('quantidade_atual_produto < estoque_minimo');
    }

    /**
     * Scope para buscar produtos com estoque baixo na filial do usuário
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComEstoqueBaixoNaFilial($query)
    {
        $filialUser = GetterFilial();

        return $query->whereHas('produtoPorFilial', function ($subQuery) use ($filialUser) {
            $subQuery->where('id_filial', $filialUser)
                ->where('quantidade_produto', '>', 0)
                ->whereRaw('quantidade_produto <= (SELECT estoque_minimo FROM produto WHERE produto.id_produto = produtos_por_filial.id_produto_unitop)');
        })->where('is_ativo', true);
    }

    /**
     * Busca produtos da filial do usuário com paginação e filtros
     * Retorna uma query paginada pronta para uso no controller
     *
     * @param int $filialId ID da filial (opcional, usa GetterFilial() se não informado)
     * @param int $perPage Quantidade de itens por página (padrão: 15)
     * @param array $filtros Filtros para aplicar na busca
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function buscarProdutosDaFilialPaginado($filialId = null, $perPage = 15, $filtros = [])
    {
        $filialUser = $filialId ?? GetterFilial();

        // Buscar todos os produtos da filial
        $query = static::whereHas('produtoPorFilial', function ($subQuery) use ($filialUser) {
            $subQuery->where('id_filial', $filialUser);
        })->where('is_ativo', true)
            ->with(['produtoPorFilial' => function ($q) use ($filialUser) {
                $q->where('id_filial', $filialUser);
            }, 'estoque']);

        // Aplicar filtros
        if (!empty($filtros['id_estoque'])) {
            $query->where('id_estoque_produto', $filtros['id_estoque']);
        }

        if (!empty($filtros['id_produto'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('id_produto', 'like', '%' . $filtros['id_produto'] . '%')
                    ->orWhere('codigo_produto', 'like', '%' . $filtros['id_produto'] . '%');
            });
        }

        // Aplicar ordenação diretamente na query pelo nome do produto
        $ordem = $filtros['ordem'] ?? 'desc';
        if ($ordem === 'desc') {
            $query->orderBy('descricao_produto', 'desc');
        } else {
            $query->orderBy('descricao_produto', 'asc');
        }

        $produtos = $query->paginate($perPage);

        // Adicionar informações de estoque atual para cada produto
        foreach ($produtos as $produto) {
            $produtoFilial = $produto->produtoPorFilial->first();
            if ($produtoFilial) {
                $produto->estoque_atual = $produtoFilial->quantidade_produto;
                $produto->filial_id = $produtoFilial->id_filial;
                $produto->diferenca_estoque = max(0, $produto->estoque_minimo - $produtoFilial->quantidade_produto);
            } else {
                $produto->estoque_atual = 0;
                $produto->filial_id = $filialUser;
                $produto->diferenca_estoque = $produto->estoque_minimo ?? 0;
            }
        }

        return $produtos;
    }

    /**
     * Busca produtos com estoque baixo na filial do usuário com paginação
     * Retorna uma query paginada pronta para uso no controller
     *
     * @param int $filialId ID da filial (opcional, usa GetterFilial() se não informado)
     * @param int $perPage Quantidade de itens por página (padrão: 15)
     * @param array $filtros Filtros para aplicar na busca
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function buscarEstoqueBaixoPaginado($filialId = null, $perPage = 15, $filtros = [])
    {
        $filialUser = $filialId ?? GetterFilial();

        // Buscar produtos com estoque baixo
        $query = static::comEstoqueBaixoNaFilial()
            ->with(['produtoPorFilial' => function ($q) use ($filialUser) {
                $q->where('id_filial', $filialUser);
            }]);

        // Aplicar filtros
        if (!empty($filtros['id_estoque'])) {
            $query->where('id_estoque_produto', $filtros['id_estoque']);
        }

        if (!empty($filtros['id_produto'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('id_produto', 'like', '%' . $filtros['id_produto'] . '%')
                    ->orWhere('codigo_produto', 'like', '%' . $filtros['id_produto'] . '%');
            });
        }

        $itensBaixoEstoque = $query->paginate($perPage);

        // Adicionar informações de estoque atual para cada produto
        foreach ($itensBaixoEstoque as $produto) {
            $produtoFilial = $produto->produtoPorFilial->first();
            if ($produtoFilial) {
                $produto->estoque_atual = $produtoFilial->quantidade_produto;
                $produto->filial_id = $produtoFilial->id_filial;
                $produto->diferenca_estoque = $produto->estoque_minimo - $produtoFilial->quantidade_produto;
            }
        }

        // Aplicar ordenação após carregar os dados
        if (!empty($filtros['ordenar_por'])) {
            $ordenarPor = $filtros['ordenar_por'];
            $ordem = $filtros['ordem'] ?? 'desc';

            $collection = collect($itensBaixoEstoque->items());

            if ($ordem === 'desc') {
                $itensOrdenados = $collection->sortByDesc(function ($item) use ($ordenarPor) {
                    switch ($ordenarPor) {
                        case 'estoque_atual':
                            return $item->estoque_atual ?? 0;
                        case 'estoque_minimo':
                            return $item->estoque_minimo ?? 0;
                        case 'estoque_maximo':
                            return $item->estoque_maximo ?? 0;
                        case 'diferenca':
                            return $item->diferenca_estoque ?? 0;
                        default:
                            return $item->estoque_atual ?? 0;
                    }
                });
            } else {
                $itensOrdenados = $collection->sortBy(function ($item) use ($ordenarPor) {
                    switch ($ordenarPor) {
                        case 'estoque_atual':
                            return $item->estoque_atual ?? 0;
                        case 'estoque_minimo':
                            return $item->estoque_minimo ?? 0;
                        case 'estoque_maximo':
                            return $item->estoque_maximo ?? 0;
                        case 'diferenca':
                            return $item->diferenca_estoque ?? 0;
                        default:
                            return $item->estoque_atual ?? 0;
                    }
                });
            }

            // Criar novo paginador com os itens ordenados
            $currentPage = $itensBaixoEstoque->currentPage();
            $total = $itensBaixoEstoque->total();
            $path = request()->url();
            $options = [
                'path' => $path,
                'pageName' => 'page',
            ];

            $itensBaixoEstoque = new \Illuminate\Pagination\LengthAwarePaginator(
                $itensOrdenados->values(),
                $total,
                $perPage,
                $currentPage,
                $options
            );

            // Preservar parâmetros da query string
            $itensBaixoEstoque->appends(request()->query());
        }

        return $itensBaixoEstoque;
    }

    /**
     * Conta quantos produtos estão com estoque baixo na filial do usuário
     *
     * @param int $filialId ID da filial (opcional, usa GetterFilial() se não informado)
     * @return int
     */
    public static function contarEstoqueBaixo($filialId = null)
    {
        $filialUser = $filialId ?? GetterFilial();

        return static::whereHas('produtoPorFilial', function ($subQuery) use ($filialUser) {
            $subQuery->where('id_filial', $filialUser)
                ->where('quantidade_produto', '>', 0)
                ->whereRaw('quantidade_produto <= (SELECT estoque_minimo FROM produto WHERE produto.id_produto = produtos_por_filial.id_produto_unitop)');
        })->where('is_ativo', true)->count();
    }

    /**
     * Formata a descrição do produto para exibição
     */
    public function getDescricaoCompletaAttribute()
    {
        $codigo = $this->codigo_produto ? "[$this->codigo_produto] " : '';

        return $codigo . $this->descricao_produto;
    }

    public function produtoPorFilial()
    {
        return $this->hasMany(ProdutosPorFilial::class, 'id_produto_unitop', 'id_produto');
    }

    /**
     * Verifica se o produto está com estoque abaixo do mínimo na filial do usuário
     *
     * @return self|null Retorna o próprio produto se estoque estiver baixo, null caso contrário
     */
    public function estoqueBaixo()
    {
        $filialUser = GetterFilial();

        // Busca o produto na filial do usuário
        $produtoPorFilial = ProdutosPorFilial::where('id_produto_unitop', $this->id_produto)
            ->where('id_filial', $filialUser)
            ->first();

        // Verifica se o produto existe na filial
        if (!$produtoPorFilial) {
            return null;
        }

        $estoqueAtual = $produtoPorFilial->quantidade_produto ?? 0;

        // Verifica se há estoque e se está abaixo do mínimo
        if ($estoqueAtual > 0 && $estoqueAtual <= $this->estoque_minimo) {
            return $this;
        }

        return null;
    }
}
