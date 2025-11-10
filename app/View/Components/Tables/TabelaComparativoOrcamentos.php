<?php

namespace App\View\Components\Tables;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class TabelaComparativoOrcamentos extends Component
{
    /**
     * Pedido de compra relacionado aos orçamentos
     *
     * @var mixed
     */
    public $pedido;

    /**
     * Coleção de orçamentos a serem comparados
     *
     * @var Collection|array
     */
    public $orcamentos;

    /**
     * Flag que indica se o usuário pode selecionar um orçamento
     *
     * @var bool
     */
    public $podeSelecionarOrcamento;

    /**
     * URL para seleção de orçamento
     *
     * @var string
     */
    public $urlSelecionar;

    /**
     * URL para remover um orçamento
     *
     * @var string
     */
    public $urlRemover;

    /**
     * Lista de critérios de comparação
     *
     * @var array
     */
    public $criterios;

    /**
     * Flag que indica se exibe o valor total
     *
     * @var bool
     */
    public $exibeTotal;

    /**
     * Flag que indica se exibe valores unitários
     *
     * @var bool
     */
    public $exibeUnitarios;

    /**
     * Flag que indica se destaca automaticamente o melhor orçamento
     *
     * @var bool
     */
    public $destacaMelhorOrcamento;

    /**
     * Flag que indica se é permitido expandir/recolher detalhes
     *
     * @var bool
     */
    public $permiteExpandirDetalhes;

    /**
     * Classes CSS adicionais
     *
     * @var string
     */
    public $classesCss;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $pedido = null,
        $orcamentos = [],
        $podeSelecionarOrcamento = false,
        $urlSelecionar = null,
        $urlRemover = null,
        $criterios = null,
        $exibeTotal = true,
        $exibeUnitarios = true,
        $destacaMelhorOrcamento = true,
        $permiteExpandirDetalhes = true,
        $classesCss = ''
    ) {
        $this->pedido = $pedido;
        $this->orcamentos = $orcamentos instanceof Collection ? $orcamentos : collect($orcamentos);
        $this->podeSelecionarOrcamento = $podeSelecionarOrcamento;

        // Configurar URLs
        $this->urlSelecionar = $urlSelecionar ?? route('orcamentos.selecionar', ['_id_placeholder']);
        $this->urlRemover = $urlRemover ?? route('orcamentos.destroy', ['_id_placeholder']);

        // Configurar critérios de comparação
        $this->criterios = $criterios ?? $this->criteriosPadrao();

        $this->exibeTotal = $exibeTotal;
        $this->exibeUnitarios = $exibeUnitarios;
        $this->destacaMelhorOrcamento = $destacaMelhorOrcamento;
        $this->permiteExpandirDetalhes = $permiteExpandirDetalhes;
        $this->classesCss = $classesCss;
    }

    /**
     * Definir critérios de comparação padrão
     *
     * @return array
     */
    private function criteriosPadrao()
    {
        return [
            ['campo' => 'valor_total', 'titulo' => 'Valor Total', 'tipo' => 'moeda', 'melhor' => 'menor', 'peso' => 40],
            ['campo' => 'prazo_entrega', 'titulo' => 'Prazo de Entrega', 'tipo' => 'numero', 'melhor' => 'menor', 'peso' => 25],
            ['campo' => 'condicao_pagamento', 'titulo' => 'Condição de Pagamento', 'tipo' => 'texto', 'melhor' => null, 'peso' => 20],
            ['campo' => 'validade', 'titulo' => 'Validade', 'tipo' => 'numero', 'melhor' => 'maior', 'peso' => 15],
        ];
    }

    /**
     * Calcular a pontuação para cada orçamento com base nos critérios
     *
     * @return Collection
     */
    public function calcularPontuacao()
    {
        if ($this->orcamentos->isEmpty()) {
            return collect();
        }

        $pontuacoes = collect();

        // Para cada critério, calcular a pontuação relativa de cada orçamento
        foreach ($this->criterios as $criterio) {
            if (!isset($criterio['melhor']) || !$criterio['melhor']) {
                continue; // Pular critérios sem definição de "melhor"
            }

            $campo = $criterio['campo'];
            $melhor = $criterio['melhor'];
            $peso = $criterio['peso'] ?? 1;

            // Coletar valores para este critério de todos os orçamentos
            $valores = $this->orcamentos->map(function ($orcamento) use ($campo) {
                return $orcamento[$campo] ?? 0;
            })->filter();

            if ($valores->isEmpty()) {
                continue;
            }

            // Determinar o melhor valor
            $melhorValor = $melhor === 'menor' ? $valores->min() : $valores->max();

            // Calcular pontuação para cada orçamento
            foreach ($this->orcamentos as $orcamento) {
                $valor = $orcamento[$campo] ?? 0;
                if (!$valor) {
                    continue;
                }

                $orcamentoId = $orcamento['id'];

                if (!$pontuacoes->has($orcamentoId)) {
                    $pontuacoes[$orcamentoId] = 0;
                }

                // Calcular pontuação (valor relativo ao melhor valor)
                if ($melhor === 'menor') {
                    // Para critérios onde menor é melhor (ex: valor, prazo)
                    // A fórmula é: (melhor valor / valor atual) * peso
                    $pontuacoes[$orcamentoId] += ($melhorValor / $valor) * $peso;
                } else {
                    // Para critérios onde maior é melhor (ex: validade)
                    // A fórmula é: (valor atual / melhor valor) * peso
                    $pontuacoes[$orcamentoId] += ($valor / $melhorValor) * $peso;
                }
            }
        }

        return $pontuacoes;
    }

    /**
     * Identificar o melhor orçamento com base na pontuação
     *
     * @return mixed
     */
    public function melhorOrcamento()
    {
        if ($this->orcamentos->isEmpty() || !$this->destacaMelhorOrcamento) {
            return null;
        }

        $pontuacoes = $this->calcularPontuacao();

        if ($pontuacoes->isEmpty()) {
            return null;
        }

        // Encontrar o orçamento com maior pontuação
        $melhorOrcamentoId = $pontuacoes->sortDesc()->keys()->first();

        return $this->orcamentos->firstWhere('id', $melhorOrcamentoId);
    }

    /**
     * Verificar se um orçamento é o melhor com base na pontuação
     *
     * @param mixed $orcamento
     * @return bool
     */
    public function ehMelhorOrcamento($orcamento)
    {
        if (!$this->destacaMelhorOrcamento || !$orcamento) {
            return false;
        }

        $melhorOrcamento = $this->melhorOrcamento();

        return $melhorOrcamento && $melhorOrcamento['id'] === $orcamento['id'];
    }

    /**
     * Obter o ID do componente para uso em JavaScript
     *
     * @return string
     */
    public function componenteId()
    {
        return 'tabela-comparativo-orcamentos-' . ($this->pedido->id ?? uniqid());
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.tables.tabela-comparativo-orcamentos');
    }
}
