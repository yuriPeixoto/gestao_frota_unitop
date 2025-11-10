<?php

namespace App\View\Components\Forms;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class SeletorProdutoServico extends Component
{
    /**
     * Tipo de item a ser selecionado (produto, servico, ambos)
     *
     * @var string
     */
    public $tipo;

    /**
     * Item selecionado (produto ou serviço)
     *
     * @var mixed
     */
    public $itemSelecionado;

    /**
     * URL para busca de itens
     *
     * @var string
     */
    public $urlBusca;

    /**
     * ID do elemento de input
     *
     * @var string
     */
    public $inputId;

    /**
     * Nome do elemento de input
     *
     * @var string
     */
    public $inputName;

    /**
     * Placeholder para o campo de busca
     *
     * @var string
     */
    public $placeholder;

    /**
     * Flag que indica se o campo é obrigatório
     *
     * @var bool
     */
    public $required;

    /**
     * Flag que indica se o componente está desabilitado
     *
     * @var bool
     */
    public $disabled;

    /**
     * Flag que indica se permite seleção de múltiplos itens
     *
     * @var bool
     */
    public $multiplo;

    /**
     * Flag que indica se deve exibir detalhes adicionais do item
     *
     * @var bool
     */
    public $exibeDetalhes;

    /**
     * Campos adicionais a serem exibidos na lista de resultados
     *
     * @var array
     */
    public $camposAdicionais;

    /**
     * Classes CSS adicionais
     *
     * @var string
     */
    public $classesCss;

    /**
     * Mensagem de erro
     *
     * @var string
     */
    public $mensagemErro;

    /**
     * Função JavaScript a ser chamada quando um item for selecionado
     *
     * @var string
     */
    public $onSelect;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $tipo = 'ambos',
        $itemSelecionado = null,
        $urlBusca = null,
        $inputId = null,
        $inputName = null,
        $placeholder = 'Buscar produto ou serviço...',
        $required = false,
        $disabled = false,
        $multiplo = false,
        $exibeDetalhes = true,
        $camposAdicionais = [],
        $classesCss = '',
        $mensagemErro = null,
        $onSelect = null
    ) {
        $this->tipo = $tipo;
        $this->itemSelecionado = $itemSelecionado;

        // Configurar URL de busca padrão se não for fornecida
        $this->urlBusca = $urlBusca ?? route('api.buscar-itens', ['tipo' => $tipo]);

        // Gerar IDs e nomes automáticos se não forem fornecidos
        $this->inputId = $inputId ?? 'seletor-' . $tipo . '-' . uniqid();
        $this->inputName = $inputName ?? $tipo . '_id';

        // Configurar placeholder específico com base no tipo
        $this->placeholder = $placeholder ?? match ($tipo) {
            'produto' => 'Buscar produto...',
            'servico' => 'Buscar serviço...',
            default => 'Buscar produto ou serviço...',
        };

        $this->required = $required;
        $this->disabled = $disabled;
        $this->multiplo = $multiplo;
        $this->exibeDetalhes = $exibeDetalhes;
        $this->camposAdicionais = $camposAdicionais;
        $this->classesCss = $classesCss;
        $this->mensagemErro = $mensagemErro;
        $this->onSelect = $onSelect;
    }

    /**
     * Obter o ID do componente para uso em JavaScript
     *
     * @return string
     */
    public function componenteId()
    {
        return 'seletor-' . $this->tipo . '-' . substr(md5($this->inputId), 0, 8);
    }

    /**
     * Obter o texto para exibição do tipo de seleção
     *
     * @return string
     */
    public function labelTipo()
    {
        return match ($this->tipo) {
            'produto' => 'Produto',
            'servico' => 'Serviço',
            default => 'Produto/Serviço',
        };
    }

    /**
     * Obter a lista de valores para itens selecionados (para múltipla seleção)
     *
     * @return array
     */
    public function valoresSelecionados()
    {
        if (!$this->itemSelecionado) {
            return [];
        }

        if ($this->multiplo && is_array($this->itemSelecionado)) {
            return collect($this->itemSelecionado)->pluck('id')->toArray();
        }

        if ($this->multiplo && $this->itemSelecionado instanceof Collection) {
            return $this->itemSelecionado->pluck('id')->toArray();
        }

        if (is_array($this->itemSelecionado) || $this->itemSelecionado instanceof Collection) {
            // Se não for múltiplo mas recebeu uma coleção, pega apenas o primeiro
            $item = is_array($this->itemSelecionado)
                ? (count($this->itemSelecionado) > 0 ? $this->itemSelecionado[0] : null)
                : $this->itemSelecionado->first();

            return $item ? [$item['id']] : [];
        }

        // Se for um objeto ou um valor escalar, tenta obter o ID
        return $this->itemSelecionado ? [$this->itemSelecionado['id'] ?? $this->itemSelecionado] : [];
    }

    /**
     * Obter os textos a serem exibidos para os itens selecionados
     *
     * @return array
     */
    public function textosSelecionados()
    {
        if (!$this->itemSelecionado) {
            return [];
        }

        if ($this->multiplo && (is_array($this->itemSelecionado) || $this->itemSelecionado instanceof Collection)) {
            $itens = $this->itemSelecionado instanceof Collection
                ? $this->itemSelecionado->toArray()
                : $this->itemSelecionado;

            return collect($itens)->map(function ($item) {
                return $this->formatarTextoItem($item);
            })->toArray();
        }

        if (is_array($this->itemSelecionado) || $this->itemSelecionado instanceof Collection) {
            // Se não for múltiplo mas recebeu uma coleção, pega apenas o primeiro
            $item = is_array($this->itemSelecionado)
                ? (count($this->itemSelecionado) > 0 ? $this->itemSelecionado[0] : null)
                : $this->itemSelecionado->first();

            return $item ? [$this->formatarTextoItem($item)] : [];
        }

        // Se for um objeto ou um valor escalar
        return $this->itemSelecionado ? [$this->formatarTextoItem($this->itemSelecionado)] : [];
    }

    /**
     * Formatar o texto para exibição do item
     *
     * @param mixed $item
     * @return string
     */
    private function formatarTextoItem($item)
    {
        if (is_scalar($item)) {
            return (string) $item;
        }

        // Se for um objeto ou array, tenta extrair codigo e descricao
        $codigo = $item['codigo'] ?? ($item['produto_codigo'] ?? ($item['servico_codigo'] ?? ''));
        $descricao = $item['descricao'] ?? ($item['produto_descricao'] ?? ($item['servico_descricao'] ?? ''));

        if ($codigo && $descricao) {
            return "{$codigo} - {$descricao}";
        }

        if ($descricao) {
            return $descricao;
        }

        if ($codigo) {
            return $codigo;
        }

        return $item['id'] ?? 'Item selecionado';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.forms.seletor-produto-servico');
    }
}
