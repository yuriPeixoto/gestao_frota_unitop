<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class TimelineProcesso extends Component
{
    /**
     * Tipo do processo (solicitacao, pedido, etc)
     *
     * @var string
     */
    public $tipoProcesso;

    /**
     * Instância da entidade (Solicitação, Pedido, etc)
     *
     * @var mixed
     */
    public $entidade;

    /**
     * Status atual do processo
     *
     * @var string
     */
    public $statusAtual;

    /**
     * Lista de etapas do processo
     *
     * @var Collection|array
     */
    public $etapas;

    /**
     * Histórico de eventos do processo
     *
     * @var Collection|array
     */
    public $historico;

    /**
     * Flag que indica se deve mostrar apenas as etapas principais
     *
     * @var bool
     */
    public $etapasPrincipais;

    /**
     * Flag que indica se deve exibir timestamps detalhados
     *
     * @var bool
     */
    public $exibeTimestamps;

    /**
     * Flag que indica se deve exibir informações de usuários
     *
     * @var bool
     */
    public $exibeUsuarios;

    /**
     * Flag que indica se deve exibir observações
     *
     * @var bool
     */
    public $exibeObservacoes;

    /**
     * Flag que indica se deve expandir/recolher automaticamente
     *
     * @var bool
     */
    public $expandidoInicial;

    /**
     * Orientação da timeline (vertical, horizontal)
     *
     * @var string
     */
    public $orientacao;

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
        $tipoProcesso,
        $entidade = null,
        $statusAtual = null,
        $etapas = null,
        $historico = null,
        $etapasPrincipais = true,
        $exibeTimestamps = true,
        $exibeUsuarios = true,
        $exibeObservacoes = true,
        $expandidoInicial = false,
        $orientacao = 'vertical',
        $classesCss = ''
    ) {
        $this->tipoProcesso = $tipoProcesso;
        $this->entidade = $entidade;
        $this->statusAtual = $statusAtual ?? ($entidade ? $entidade->status : null);
        $this->etapas = $etapas ?? $this->determinarEtapas();
        $this->historico = $historico ?? $this->obterHistorico();
        $this->etapasPrincipais = $etapasPrincipais;
        $this->exibeTimestamps = $exibeTimestamps;
        $this->exibeUsuarios = $exibeUsuarios;
        $this->exibeObservacoes = $exibeObservacoes;
        $this->expandidoInicial = $expandidoInicial;
        $this->orientacao = $orientacao;
        $this->classesCss = $classesCss;
    }

    /**
     * Determinar as etapas do processo com base no tipo
     *
     * @return array
     */
    private function determinarEtapas()
    {
        if (!$this->tipoProcesso) {
            return [];
        }

        return match ($this->tipoProcesso) {
            'solicitacao' => $this->etapasSolicitacao(),
            'pedido' => $this->etapasPedido(),
            'orcamento' => $this->etapasOrcamento(),
            'contrato' => $this->etapasContrato(),
            'nota_fiscal' => $this->etapasNotaFiscal(),
            default => []
        };
    }

    /**
     * Etapas para o fluxo de solicitação de compra
     *
     * @return array
     */
    private function etapasSolicitacao()
    {
        return [
            [
                'valor' => 'nova',
                'label' => 'Nova Solicitação',
                'descricao' => 'Solicitação criada e aguardando envio para aprovação',
                'cor' => 'blue',
                'icone' => 'document-add',
                'principal' => true
            ],
            [
                'valor' => 'em_analise',
                'label' => 'Em Análise',
                'descricao' => 'Solicitação está sendo analisada pelo aprovador',
                'cor' => 'yellow',
                'icone' => 'document-search',
                'principal' => true
            ],
            [
                'valor' => 'aprovada',
                'label' => 'Aprovada',
                'descricao' => 'Solicitação aprovada e enviada para compras',
                'cor' => 'green',
                'icone' => 'check-circle',
                'principal' => true
            ],
            [
                'valor' => 'rejeitada',
                'label' => 'Rejeitada',
                'descricao' => 'Solicitação rejeitada pelo aprovador',
                'cor' => 'red',
                'icone' => 'x-circle',
                'principal' => true
            ],
            [
                'valor' => 'cancelada',
                'label' => 'Cancelada',
                'descricao' => 'Solicitação cancelada pelo solicitante',
                'cor' => 'gray',
                'icone' => 'trash',
                'principal' => true
            ],
            [
                'valor' => 'finalizada',
                'label' => 'Finalizada',
                'descricao' => 'Solicitação atendida e finalizada',
                'cor' => 'indigo',
                'icone' => 'check-badge',
                'principal' => true
            ]
        ];
    }

    /**
     * Etapas para o fluxo de pedido de compra
     *
     * @return array
     */
    private function etapasPedido()
    {
        return [
            [
                'valor' => 'rascunho',
                'label' => 'Rascunho',
                'descricao' => 'Pedido criado como rascunho',
                'cor' => 'blue',
                'icone' => 'document',
                'principal' => true
            ],
            [
                'valor' => 'aguardando_aprovacao',
                'label' => 'Aguardando Aprovação',
                'descricao' => 'Pedido aguardando aprovação conforme alçada',
                'cor' => 'yellow',
                'icone' => 'clock',
                'principal' => true
            ],
            [
                'valor' => 'aprovado',
                'label' => 'Aprovado',
                'descricao' => 'Pedido aprovado e pronto para envio',
                'cor' => 'green',
                'icone' => 'check-circle',
                'principal' => true
            ],
            [
                'valor' => 'enviado',
                'label' => 'Enviado ao Fornecedor',
                'descricao' => 'Pedido enviado ao fornecedor',
                'cor' => 'purple',
                'icone' => 'paper-airplane',
                'principal' => true
            ],
            [
                'valor' => 'confirmado',
                'label' => 'Confirmado pelo Fornecedor',
                'descricao' => 'Pedido confirmado pelo fornecedor',
                'cor' => 'indigo',
                'icone' => 'thumb-up',
                'principal' => false
            ],
            [
                'valor' => 'parcial',
                'label' => 'Em Entrega Parcial',
                'descricao' => 'Pedido com entregas parciais em andamento',
                'cor' => 'teal',
                'icone' => 'truck',
                'principal' => false
            ],
            [
                'valor' => 'finalizado',
                'label' => 'Finalizado',
                'descricao' => 'Pedido entregue e finalizado',
                'cor' => 'green',
                'icone' => 'check-badge',
                'principal' => true
            ],
            [
                'valor' => 'rejeitado',
                'label' => 'Rejeitado',
                'descricao' => 'Pedido rejeitado pela aprovação',
                'cor' => 'red',
                'icone' => 'x-circle',
                'principal' => true
            ],
            [
                'valor' => 'cancelado',
                'label' => 'Cancelado',
                'descricao' => 'Pedido cancelado',
                'cor' => 'gray',
                'icone' => 'trash',
                'principal' => true
            ]
        ];
    }

    /**
     * Etapas para o fluxo de orçamento
     *
     * @return array
     */
    private function etapasOrcamento()
    {
        return [
            [
                'valor' => 'recebido',
                'label' => 'Recebido',
                'descricao' => 'Orçamento recebido do fornecedor',
                'cor' => 'blue',
                'icone' => 'document-text',
                'principal' => true
            ],
            [
                'valor' => 'em_analise',
                'label' => 'Em Análise',
                'descricao' => 'Orçamento em análise pela equipe de compras',
                'cor' => 'yellow',
                'icone' => 'document-search',
                'principal' => true
            ],
            [
                'valor' => 'aprovado',
                'label' => 'Aprovado',
                'descricao' => 'Orçamento aprovado',
                'cor' => 'green',
                'icone' => 'check-circle',
                'principal' => true
            ],
            [
                'valor' => 'rejeitado',
                'label' => 'Rejeitado',
                'descricao' => 'Orçamento rejeitado',
                'cor' => 'red',
                'icone' => 'x-circle',
                'principal' => true
            ],
            [
                'valor' => 'selecionado',
                'label' => 'Selecionado',
                'descricao' => 'Orçamento selecionado para pedido',
                'cor' => 'indigo',
                'icone' => 'check-badge',
                'principal' => true
            ]
        ];
    }

    /**
     * Etapas para o fluxo de contrato
     *
     * @return array
     */
    private function etapasContrato()
    {
        return [
            [
                'valor' => 'rascunho',
                'label' => 'Rascunho',
                'descricao' => 'Contrato em elaboração',
                'cor' => 'blue',
                'icone' => 'document',
                'principal' => true
            ],
            [
                'valor' => 'revisao_juridica',
                'label' => 'Revisão Jurídica',
                'descricao' => 'Contrato em revisão pelo jurídico',
                'cor' => 'yellow',
                'icone' => 'document-search',
                'principal' => true
            ],
            [
                'valor' => 'aprovacao_interna',
                'label' => 'Aprovação Interna',
                'descricao' => 'Contrato em aprovação interna',
                'cor' => 'orange',
                'icone' => 'clipboard-check',
                'principal' => true
            ],
            [
                'valor' => 'em_assinatura',
                'label' => 'Em Assinatura',
                'descricao' => 'Contrato em processo de assinatura',
                'cor' => 'purple',
                'icone' => 'pencil',
                'principal' => true
            ],
            [
                'valor' => 'vigente',
                'label' => 'Vigente',
                'descricao' => 'Contrato assinado e em vigência',
                'cor' => 'green',
                'icone' => 'check-badge',
                'principal' => true
            ],
            [
                'valor' => 'encerrado',
                'label' => 'Encerrado',
                'descricao' => 'Contrato encerrado ou expirado',
                'cor' => 'gray',
                'icone' => 'archive',
                'principal' => true
            ],
            [
                'valor' => 'cancelado',
                'label' => 'Cancelado',
                'descricao' => 'Contrato cancelado',
                'cor' => 'red',
                'icone' => 'x-circle',
                'principal' => true
            ]
        ];
    }

    /**
     * Etapas para o fluxo de nota fiscal
     *
     * @return array
     */
    private function etapasNotaFiscal()
    {
        return [
            [
                'valor' => 'registrada',
                'label' => 'Registrada',
                'descricao' => 'Nota fiscal registrada no sistema',
                'cor' => 'blue',
                'icone' => 'document-text',
                'principal' => true
            ],
            [
                'valor' => 'conferida',
                'label' => 'Conferida',
                'descricao' => 'Nota fiscal conferida com o recebimento físico',
                'cor' => 'yellow',
                'icone' => 'clipboard-check',
                'principal' => true
            ],
            [
                'valor' => 'aprovada',
                'label' => 'Aprovada',
                'descricao' => 'Nota fiscal aprovada para pagamento',
                'cor' => 'green',
                'icone' => 'check-circle',
                'principal' => true
            ],
            [
                'valor' => 'paga',
                'label' => 'Paga',
                'descricao' => 'Nota fiscal paga',
                'cor' => 'indigo',
                'icone' => 'currency-dollar',
                'principal' => true
            ],
            [
                'valor' => 'cancelada',
                'label' => 'Cancelada',
                'descricao' => 'Nota fiscal cancelada',
                'cor' => 'red',
                'icone' => 'x-circle',
                'principal' => true
            ]
        ];
    }

    /**
     * Obter histórico de eventos do processo
     *
     * @return Collection|array
     */
    private function obterHistorico()
    {
        if (!$this->entidade) {
            return [];
        }

        // Verifica se a entidade tem relacionamento "historico" ou "eventos"
        if (method_exists($this->entidade, 'historico')) {
            return $this->entidade->historico;
        }

        if (method_exists($this->entidade, 'eventos')) {
            return $this->entidade->eventos;
        }

        if (method_exists($this->entidade, 'aprovacoes')) {
            return $this->entidade->aprovacoes;
        }

        // Para log de atividades do Spatie (se estiver usando)
        if (method_exists($this->entidade, 'activities')) {
            return $this->entidade->activities;
        }

        return [];
    }

    /**
     * Obter o índice atual da etapa no processo
     *
     * @return int
     */
    public function getEtapaAtualIndex()
    {
        if (!$this->statusAtual) {
            return -1;
        }

        $etapas = collect($this->etapas);

        return $etapas->search(function ($etapa) {
            return $etapa['valor'] === $this->statusAtual;
        });
    }

    /**
     * Verificar se uma etapa está concluída
     *
     * @param array $etapa
     * @return bool
     */
    public function etapaConcluida($etapa)
    {
        if (!$this->statusAtual) {
            return false;
        }

        $etapaAtualIndex = $this->getEtapaAtualIndex();

        if ($etapaAtualIndex === -1) {
            return false;
        }

        $etapas = collect($this->etapas);
        $etapaIndex = $etapas->search(function ($e) use ($etapa) {
            return $e['valor'] === $etapa['valor'];
        });

        // Se houver histórico, verificar se existe um evento para esta etapa
        if ($this->historico && count($this->historico) > 0) {
            $historico = collect($this->historico);
            $eventoEtapa = $historico->first(function ($evento) use ($etapa) {
                return ($evento['status'] ?? $evento['acao'] ?? '') === $etapa['valor'];
            });

            if ($eventoEtapa) {
                return true;
            }
        }

        // Se esta etapa vier antes da etapa atual, está concluída
        return $etapaIndex !== false && $etapaIndex < $etapaAtualIndex;
    }

    /**
     * Verificar se uma etapa é a atual
     *
     * @param array $etapa
     * @return bool
     */
    public function etapaAtual($etapa)
    {
        return $this->statusAtual && $etapa['valor'] === $this->statusAtual;
    }

    /**
     * Obter o evento do histórico relacionado a uma etapa
     *
     * @param array $etapa
     * @return mixed
     */
    public function eventoEtapa($etapa)
    {
        if (!$this->historico || count($this->historico) === 0) {
            return null;
        }

        $historico = collect($this->historico);

        return $historico->first(function ($evento) use ($etapa) {
            return ($evento['status'] ?? $evento['acao'] ?? '') === $etapa['valor'];
        });
    }

    /**
     * Obter o ID do componente para uso em JavaScript
     *
     * @return string
     */
    public function componenteId()
    {
        return 'timeline-' . $this->tipoProcesso . '-' . ($this->entidade->id ?? uniqid());
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.ui.timeline-processo');
    }
}
