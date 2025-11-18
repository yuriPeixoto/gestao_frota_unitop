<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Support\Facades\Auth;

class CartaoAprovacao extends Component
{
    /**
     * Tipo da entidade (solicitacao, pedido, etc)
     *
     * @var string
     */
    public $entidadeTipo;

    /**
     * Instância da entidade (Solicitação, Pedido, etc)
     *
     * @var mixed
     */
    public $entidade;

    /**
     * Status atual da entidade
     *
     * @var string
     */
    public $status;

    /**
     * Etapas de aprovação
     *
     * @var Collection|array
     */
    public $etapas;

    /**
     * URL para aprovar
     *
     * @var string
     */
    public $urlAprovar;

    /**
     * URL para rejeitar
     *
     * @var string
     */
    public $urlRejeitar;

    /**
     * URL para registrar observação
     *
     * @var string
     */
    public $urlObservacao;

    /**
     * Flag que indica se requer justificativa para rejeição
     *
     * @var bool
     */
    public $requerJustificativa;

    /**
     * Flag que indica se exibe histórico de aprovações
     *
     * @var bool
     */
    public $exibeHistorico;

    /**
     * Flag que indica se o usuário atual pode aprovar/rejeitar
     *
     * @var bool
     */
    public $podeAprovar;

    /**
     * Flag que indica se o usuário atual pode adicionar observações
     *
     * @var bool
     */
    public $podeAdicionarObservacao;

    /**
     * Valor da entidade (para verificar alçadas de aprovação)
     *
     * @var float
     */
    public $valor;

    /**
     * CSS classes adicionais
     *
     * @var string
     */
    public $classesCss;

    /**
     * Histórico de aprovações
     *
     * @var Collection|array
     */
    public $historico;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $entidadeTipo,
        $entidade,
        $status = null,
        $etapas = null,
        $urlAprovar = null,
        $urlRejeitar = null,
        $urlObservacao = null,
        $requerJustificativa = true,
        $exibeHistorico = true,
        $podeAprovar = null,
        $podeAdicionarObservacao = null,
        $valor = null,
        $classesCss = '',
        $historico = null
    ) {
        $this->entidadeTipo = $entidadeTipo;
        $this->entidade = $entidade;
        $this->status = $status ?? $this->determinarStatus();
        $this->etapas = $etapas ?? $this->determinarEtapas();

        // Determinar URLs padrão se não forem fornecidas
        $this->urlAprovar = $urlAprovar ?? $this->gerarUrlAprovar();
        $this->urlRejeitar = $urlRejeitar ?? $this->gerarUrlRejeitar();
        $this->urlObservacao = $urlObservacao ?? $this->gerarUrlObservacao();

        $this->requerJustificativa = $requerJustificativa;
        $this->exibeHistorico = $exibeHistorico;

        // Determinar permissões do usuário atual
        $this->podeAprovar = $podeAprovar ?? $this->verificarPodeAprovar();
        $this->podeAdicionarObservacao = $podeAdicionarObservacao ?? $this->verificarPodeAdicionarObservacao();

        $this->valor = $valor ?? $this->determinarValor();
        $this->classesCss = $classesCss;

        // Obter histórico de aprovações
        $this->historico = $historico ?? $this->obterHistorico();
    }

    /**
     * Determinar o status atual da entidade
     *
     * @return string
     */
    private function determinarStatus()
    {
        if ($this->entidade) {
            return $this->entidade->status ?? '';
        }

        return '';
    }

    /**
     * Determinar as etapas de aprovação com base no tipo da entidade
     *
     * @return array
     */
    private function determinarEtapas()
    {
        return match ($this->entidadeTipo) {
            'solicitacao' => [
                ['valor' => 'nova', 'label' => 'Nova', 'cor' => 'blue'],
                ['valor' => 'em_analise', 'label' => 'Em Análise', 'cor' => 'yellow'],
                ['valor' => 'aprovada', 'label' => 'Aprovada', 'cor' => 'green'],
                ['valor' => 'rejeitada', 'label' => 'Rejeitada', 'cor' => 'red'],
                ['valor' => 'cancelada', 'label' => 'Cancelada', 'cor' => 'gray'],
            ],
            'pedido' => [
                ['valor' => 'rascunho', 'label' => 'Rascunho', 'cor' => 'blue'],
                ['valor' => 'aguardando_aprovacao', 'label' => 'Aguardando Aprovação', 'cor' => 'yellow'],
                ['valor' => 'aprovado', 'label' => 'Aprovado', 'cor' => 'green'],
                ['valor' => 'rejeitado', 'label' => 'Rejeitado', 'cor' => 'red'],
                ['valor' => 'enviado', 'label' => 'Enviado ao Fornecedor', 'cor' => 'indigo'],
                ['valor' => 'parcial', 'label' => 'Entrega Parcial', 'cor' => 'purple'],
                ['valor' => 'finalizado', 'label' => 'Finalizado', 'cor' => 'green'],
                ['valor' => 'cancelado', 'label' => 'Cancelado', 'cor' => 'gray'],
            ],
            'orcamento' => [
                ['valor' => 'recebido', 'label' => 'Recebido', 'cor' => 'blue'],
                ['valor' => 'em_analise', 'label' => 'Em Análise', 'cor' => 'yellow'],
                ['valor' => 'aprovado', 'label' => 'Aprovado', 'cor' => 'green'],
                ['valor' => 'rejeitado', 'label' => 'Rejeitado', 'cor' => 'red'],
            ],
            'nota_fiscal' => [
                ['valor' => 'registrada', 'label' => 'Registrada', 'cor' => 'blue'],
                ['valor' => 'conferida', 'label' => 'Conferida', 'cor' => 'yellow'],
                ['valor' => 'aprovada', 'label' => 'Aprovada', 'cor' => 'green'],
                ['valor' => 'paga', 'label' => 'Paga', 'cor' => 'indigo'],
                ['valor' => 'cancelada', 'label' => 'Cancelada', 'cor' => 'red'],
            ],
            default => [
                ['valor' => 'pendente', 'label' => 'Pendente', 'cor' => 'yellow'],
                ['valor' => 'aprovado', 'label' => 'Aprovado', 'cor' => 'green'],
                ['valor' => 'rejeitado', 'label' => 'Rejeitado', 'cor' => 'red'],
            ],
        };
    }

    /**
     * Gerar URL para aprovar com base no tipo da entidade
     *
     * @return string
     */
    private function gerarUrlAprovar()
    {
        if (!$this->entidade || !$this->entidade->id) {
            return '#';
        }

        return match ($this->entidadeTipo) {
            'solicitacao' => route('solicitacoes.aprovar', ['id' => $this->entidade->id]),
            'pedido' => route('pedidos.aprovar', ['id' => $this->entidade->id]),
            'orcamento' => route('orcamentos.aprovar', ['id' => $this->entidade->id]),
            'nota_fiscal' => route('notas.aprovar', ['id' => $this->entidade->id]),
            default => '#',
        };
    }

    /**
     * Gerar URL para rejeitar com base no tipo da entidade
     *
     * @return string
     */
    private function gerarUrlRejeitar()
    {
        if (!$this->entidade || !$this->entidade->id) {
            return '#';
        }

        return match ($this->entidadeTipo) {
            'solicitacao' => route('solicitacoes.rejeitar', ['id' => $this->entidade->id]),
            'pedido' => route('pedidos.rejeitar', ['id' => $this->entidade->id]),
            'orcamento' => route('orcamentos.rejeitar', ['id' => $this->entidade->id]),
            'nota_fiscal' => route('notas.rejeitar', ['id' => $this->entidade->id]),
            default => '#',
        };
    }

    /**
     * Gerar URL para adicionar observação com base no tipo da entidade
     *
     * @return string
     */
    private function gerarUrlObservacao()
    {
        if (!$this->entidade || !$this->entidade->id) {
            return '#';
        }

        return match ($this->entidadeTipo) {
            'solicitacao' => route('solicitacoes.observacao', ['id' => $this->entidade->id]),
            'pedido' => route('pedidos.observacao', ['id' => $this->entidade->id]),
            'orcamento' => route('orcamentos.observacao', ['id' => $this->entidade->id]),
            'nota_fiscal' => route('notas.observacao', ['id' => $this->entidade->id]),
            default => '#',
        };
    }

    /**
     * Verificar se o usuário atual pode aprovar/rejeitar a entidade
     *
     * @return bool
     */
    private function verificarPodeAprovar()
    {
        if (!Auth::check() || !$this->entidade) {
            return false;
        }

        $user = Auth::user();

        return match ($this->entidadeTipo) {
            'solicitacao' => $user->can('aprovar_solicitacao_compra') &&
                in_array($this->status, ['nova', 'em_analise']),
            'pedido' => $user->can('aprovar_pedido_compra') &&
                in_array($this->status, ['aguardando_aprovacao']),
            'orcamento' => $user->can('aprovar_orcamento') &&
                in_array($this->status, ['recebido', 'em_analise']),
            'nota_fiscal' => $user->can('aprovar_nota_fiscal') &&
                in_array($this->status, ['registrada', 'conferida']),
            default => false,
        };
    }

    /**
     * Verificar se o usuário atual pode adicionar observações
     *
     * @return bool
     */
    private function verificarPodeAdicionarObservacao()
    {
        if (!Auth::check() || !$this->entidade) {
            return false;
        }

        $user = Auth::user();

        return match ($this->entidadeTipo) {
            'solicitacao' => $user->can('visualizar_solicitacao_compra'),
            'pedido' => $user->can('visualizar_pedido_compra'),
            'orcamento' => $user->can('visualizar_orcamento'),
            'nota_fiscal' => $user->can('visualizar_nota_fiscal'),
            default => false,
        };
    }

    /**
     * Determinar o valor da entidade para verificação de alçadas
     *
     * @return float
     */
    private function determinarValor()
    {
        if (!$this->entidade) {
            return 0;
        }

        return match ($this->entidadeTipo) {
            'solicitacao' => $this->entidade->valor_total ??
                ($this->entidade->itens ? $this->entidade->itens->sum(fn($item) =>
                $item->quantidade * $item->valor_unitario) : 0),
            'pedido' => $this->entidade->valor_total ?? 0,
            'orcamento' => $this->entidade->valor_total ?? 0,
            'nota_fiscal' => $this->entidade->valor_total ?? 0,
            default => 0,
        };
    }

    /**
     * Obter o histórico de aprovações da entidade
     *
     * @return Collection|array
     */
    private function obterHistorico()
    {
        if (!$this->entidade || !$this->exibeHistorico) {
            return [];
        }

        // Verifica se a entidade tem relacionamento "aprovacoes"
        if (method_exists($this->entidade, 'aprovacoes')) {
            return $this->entidade->aprovacoes;
        }

        // Verificar se a entidade tem relacionamento "historico"
        if (method_exists($this->entidade, 'historico')) {
            return $this->entidade->historico;
        }

        return [];
    }

    /**
     * Obter a classe CSS para o status atual
     *
     * @return string
     */
    public function classeStatus()
    {
        $etapa = collect($this->etapas)->firstWhere('valor', $this->status);

        if (!$etapa) {
            return 'bg-gray-100 text-gray-800';
        }

        return match ($etapa['cor']) {
            'blue' => 'bg-blue-100 text-blue-800',
            'green' => 'bg-green-100 text-green-800',
            'red' => 'bg-red-100 text-red-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'indigo' => 'bg-indigo-100 text-indigo-800',
            'purple' => 'bg-purple-100 text-purple-800',
            'gray' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obter a label do status atual
     *
     * @return string
     */
    public function labelStatus()
    {
        $etapa = collect($this->etapas)->firstWhere('valor', $this->status);

        return $etapa ? $etapa['label'] : $this->status;
    }

    /**
     * Obter o ID do componente para uso em JavaScript
     *
     * @return string
     */
    public function componenteId()
    {
        return 'cartao-aprovacao-' . $this->entidadeTipo . '-' . ($this->entidade->id ?? uniqid());
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.ui.cartao-aprovacao');
    }
}
