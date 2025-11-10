<?php

namespace App\View\Components\Tables;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class TabelaItens extends Component
{
    /**
     * Array ou Collection de itens a serem exibidos na tabela
     *
     * @var Collection|array
     */
    protected $itens;

    /**
     * Configuração das colunas da tabela
     *
     * @var array
     */
    protected $colunas;

    /**
     * Flag para permitir adição de novos itens
     *
     * @var bool
     */
    protected $permiteAdicionar;

    /**
     * Flag para permitir edição de itens
     *
     * @var bool
     */
    protected $permiteEditar;

    /**
     * Flag para permitir exclusão de itens
     *
     * @var bool
     */
    protected $permiteExcluir;

    /**
     * URL para busca de produtos/serviços
     *
     * @var string
     */
    protected $urlBusca;

    /**
     * URL para salvar os itens via AJAX
     *
     * @var string
     */
    protected $urlSalvar;

    /**
     * Tipo de item (produto, serviço, ambos)
     *
     * @var string
     */
    protected $tipoItem;

    /**
     * ID da entidade pai (solicitação, pedido, etc.)
     *
     * @var int|null
     */
    protected $entidadeId;

    /**
     * Tipo da entidade pai (solicitacao, pedido, etc.)
     *
     * @var string
     */
    protected $entidadeTipo;

    /**
     * Adicional classes CSS para a tabela
     *
     * @var string
     */
    protected $classesCss;

    /**
     * Flag que indica se a tabela está em modo somente leitura
     *
     * @var bool
     */
    protected $somenteVisualizacao;

    /**
     * Flag que indica se o total deve ser exibido
     *
     * @var bool
     */
    protected $exibirTotal;

    /**
     * Colunas que serão utilizadas para calcular o total
     *
     * @var array
     */
    protected $colunasTotal;

    /**
     * ID do componente para uso em JavaScript
     *
     * @var string
     */
    protected $componenteId;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $itens = [],
        $colunas = [],
        $permiteAdicionar = true,
        $permiteEditar = true,
        $permiteExcluir = true,
        $urlBusca = null,
        $urlSalvar = null,
        $tipoItem = 'ambos',
        $entidadeId = null,
        $entidadeTipo = null,
        $classesCss = '',
        $somenteVisualizacao = false,
        $exibirTotal = false,
        $colunasTotal = ['valor_total'],
        $componenteId = null
    ) {
        $this->itens = $itens instanceof Collection ? $itens : collect($itens);
        $this->colunas = $this->configurarColunas($colunas);
        $this->permiteAdicionar = $permiteAdicionar && !$somenteVisualizacao;
        $this->permiteEditar = $permiteEditar && !$somenteVisualizacao;
        $this->permiteExcluir = $permiteExcluir && !$somenteVisualizacao;
        $this->urlBusca = $urlBusca;
        $this->urlSalvar = $urlSalvar;
        $this->tipoItem = $tipoItem;
        $this->entidadeId = $entidadeId;
        $this->entidadeTipo = $entidadeTipo;
        $this->classesCss = $classesCss;
        $this->somenteVisualizacao = $somenteVisualizacao;
        $this->exibirTotal = $exibirTotal;
        $this->colunasTotal = $colunasTotal;

        // Usar o ID passado ou gerar um automaticamente
        $this->componenteId = $componenteId ?: $this->gerarComponenteId();
    }

    /**
     * Configure as colunas padrão se não forem fornecidas
     *
     * @param array $colunas
     * @return array
     */
    private function configurarColunas($colunas)
    {
        if (!empty($colunas)) {
            return $colunas;
        }

        // Colunas padrão para diferentes tipos de entidades
        return match ($this->entidadeTipo) {
            'solicitacao' => [
                ['campo' => 'codigo', 'titulo' => 'Código', 'largura' => '10%'],
                ['campo' => 'descricao', 'titulo' => 'Descrição', 'largura' => '40%'],
                ['campo' => 'unidade', 'titulo' => 'Unid.', 'largura' => '10%'],
                ['campo' => 'quantidade', 'titulo' => 'Qtde', 'largura' => '10%', 'tipo' => 'numero'],
                ['campo' => 'valor_unitario', 'titulo' => 'Valor Unit.', 'largura' => '15%', 'tipo' => 'moeda'],
                ['campo' => 'valor_total', 'titulo' => 'Total', 'largura' => '15%', 'tipo' => 'moeda', 'calculado' => true],
            ],
            'pedido' => [
                ['campo' => 'codigo', 'titulo' => 'Código', 'largura' => '10%'],
                ['campo' => 'descricao', 'titulo' => 'Descrição', 'largura' => '35%'],
                ['campo' => 'unidade', 'titulo' => 'Unid.', 'largura' => '10%'],
                ['campo' => 'quantidade', 'titulo' => 'Qtde', 'largura' => '10%', 'tipo' => 'numero'],
                ['campo' => 'valor_unitario', 'titulo' => 'Valor Unit.', 'largura' => '15%', 'tipo' => 'moeda'],
                ['campo' => 'valor_total', 'titulo' => 'Total', 'largura' => '15%', 'tipo' => 'moeda', 'calculado' => true],
                ['campo' => 'desconto', 'titulo' => 'Desconto', 'largura' => '10%', 'tipo' => 'moeda'],
            ],
            'orcamento' => [
                ['campo' => 'codigo', 'titulo' => 'Código', 'largura' => '10%'],
                ['campo' => 'descricao', 'titulo' => 'Descrição', 'largura' => '30%'],
                ['campo' => 'unidade', 'titulo' => 'Unid.', 'largura' => '10%'],
                ['campo' => 'quantidade', 'titulo' => 'Qtde', 'largura' => '10%', 'tipo' => 'numero'],
                ['campo' => 'valor_unitario', 'titulo' => 'Valor Unit.', 'largura' => '15%', 'tipo' => 'moeda'],
                ['campo' => 'valor_total', 'titulo' => 'Total', 'largura' => '15%', 'tipo' => 'moeda', 'calculado' => true],
                ['campo' => 'prazo_entrega', 'titulo' => 'Prazo', 'largura' => '10%'],
            ],
            'nota_fiscal' => [
                ['campo' => 'codigo', 'titulo' => 'Código', 'largura' => '10%'],
                ['campo' => 'descricao', 'titulo' => 'Descrição', 'largura' => '40%'],
                ['campo' => 'unidade', 'titulo' => 'Unid.', 'largura' => '10%'],
                ['campo' => 'quantidade', 'titulo' => 'Qtde', 'largura' => '10%', 'tipo' => 'numero'],
                ['campo' => 'valor_unitario', 'titulo' => 'Valor Unit.', 'largura' => '15%', 'tipo' => 'moeda'],
                ['campo' => 'valor_total', 'titulo' => 'Total', 'largura' => '15%', 'tipo' => 'moeda', 'calculado' => true],
            ],
            default => [
                ['campo' => 'codigo', 'titulo' => 'Código', 'largura' => '15%'],
                ['campo' => 'descricao', 'titulo' => 'Descrição', 'largura' => '55%'],
                ['campo' => 'quantidade', 'titulo' => 'Qtde', 'largura' => '15%', 'tipo' => 'numero'],
                ['campo' => 'valor_unitario', 'titulo' => 'Valor', 'largura' => '15%', 'tipo' => 'moeda'],
            ],
        };
    }

    /**
     * Gerar ID do componente para uso em JavaScript
     *
     * @return string
     */
    private function gerarComponenteId()
    {
        return 'tabela-itens-' . ($this->entidadeTipo ?? 'generico') . '-' . ($this->entidadeId ?? uniqid());
    }

    /**
     * Método acessório para obter o componenteId na view
     *
     * @return string
     */
    public function componenteId()
    {
        return $this->componenteId;
    }

    /**
     * Método acessório para obter as classes CSS na view
     *
     * @return string
     */
    public function classesCss()
    {
        return $this->classesCss;
    }

    /**
     * Método acessório para obter o valor de somenteVisualizacao na view
     *
     * @return bool
     */
    public function somenteVisualizacao()
    {
        return $this->somenteVisualizacao;
    }

    /**
     * Método acessório para obter o valor de exibirTotal na view
     *
     * @return bool
     */
    public function exibirTotal()
    {
        return $this->exibirTotal;
    }

    /**
     * Método acessório para obter as colunasTotal na view
     *
     * @return array
     */
    public function colunasTotal()
    {
        return $this->colunasTotal;
    }

    /**
     * Método acessório para obter os itens na view
     *
     * @return Collection
     */
    public function itens()
    {
        return $this->itens;
    }

    /**
     * Método acessório para obter as colunas na view
     *
     * @return array
     */
    public function colunas()
    {
        return $this->colunas;
    }

    /**
     * Método acessório para obter o valor de permiteAdicionar na view
     *
     * @return bool
     */
    public function permiteAdicionar()
    {
        return $this->permiteAdicionar;
    }

    /**
     * Método acessório para obter o valor de permiteEditar na view
     *
     * @return bool
     */
    public function permiteEditar()
    {
        return $this->permiteEditar;
    }

    /**
     * Método acessório para obter o valor de permiteExcluir na view
     *
     * @return bool
     */
    public function permiteExcluir()
    {
        return $this->permiteExcluir;
    }

    /**
     * Método acessório para obter a urlBusca na view
     *
     * @return string|null
     */
    public function urlBusca()
    {
        return $this->urlBusca;
    }

    /**
     * Método acessório para obter a urlSalvar na view
     *
     * @return string|null
     */
    public function urlSalvar()
    {
        return $this->urlSalvar;
    }

    /**
     * Método acessório para obter o tipoItem na view
     *
     * @return string
     */
    public function tipoItem()
    {
        return $this->tipoItem;
    }

    /**
     * Método acessório para obter o entidadeId na view
     *
     * @return int|null
     */
    public function entidadeId()
    {
        return $this->entidadeId;
    }

    /**
     * Método acessório para obter o entidadeTipo na view
     *
     * @return string
     */
    public function entidadeTipo()
    {
        return $this->entidadeTipo;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.tables.tabela-itens');
    }
}
