<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class ModalConfirmacao extends Component
{
    /**
     * Título do modal
     *
     * @var string
     */
    public $titulo;

    /**
     * Mensagem de confirmação
     *
     * @var string
     */
    public $mensagem;

    /**
     * Texto do botão de confirmação
     *
     * @var string
     */
    public $textoBotaoConfirmar;

    /**
     * Texto do botão de cancelamento
     *
     * @var string
     */
    public $textoBotaoCancelar;

    /**
     * Tipo de confirmação (confirmar, perigo, info)
     *
     * @var string
     */
    public $tipo;

    /**
     * ID do formulário que deve ser enviado (se houver)
     *
     * @var string
     */
    public $formId;

    /**
     * Método do formulário (POST, PUT, DELETE, etc)
     *
     * @var string
     */
    public $metodo;

    /**
     * URL para envio da confirmação (se não usar formulário)
     *
     * @var string
     */
    public $urlConfirmacao;

    /**
     * Flag que indica se um campo de observação deve ser exibido
     *
     * @var bool
     */
    public $exibeObservacao;

    /**
     * Nome do campo de observação
     *
     * @var string
     */
    public $campoObservacao;

    /**
     * Flag que indica se a observação é obrigatória
     *
     * @var bool
     */
    public $observacaoObrigatoria;

    /**
     * Flag que indica se é necessário confirmar digitando um texto
     *
     * @var bool
     */
    public $confirmacaoDigitada;

    /**
     * Texto que deve ser digitado para confirmação
     *
     * @var string
     */
    public $textoConfirmacao;

    /**
     * Flag que indica se o modal está aberto
     *
     * @var bool
     */
    public $aberto;

    /**
     * Classes CSS adicionais
     *
     * @var string
     */
    public $classesCss;

    /**
     * ID único do componente
     *
     * @var string
     */
    public $componenteId;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $titulo = 'Confirmação',
        $mensagem = 'Tem certeza que deseja realizar esta ação?',
        $textoBotaoConfirmar = 'Confirmar',
        $textoBotaoCancelar = 'Cancelar',
        $tipo = 'confirmar',
        $formId = null,
        $metodo = 'POST',
        $urlConfirmacao = null,
        $exibeObservacao = false,
        $campoObservacao = 'observacao',
        $observacaoObrigatoria = false,
        $confirmacaoDigitada = false,
        $textoConfirmacao = 'confirmar',
        $aberto = false,
        $classesCss = '',
        $componenteId = null,
        $id = null // Captura o atributo id passado ao componente
    ) {
        $this->titulo = $titulo;
        $this->mensagem = $mensagem;
        $this->textoBotaoConfirmar = $textoBotaoConfirmar;
        $this->textoBotaoCancelar = $textoBotaoCancelar;
        $this->tipo = $tipo;
        $this->formId = $formId;
        $this->metodo = $metodo;
        $this->urlConfirmacao = $urlConfirmacao;
        $this->exibeObservacao = $exibeObservacao;
        $this->campoObservacao = $campoObservacao;
        $this->observacaoObrigatoria = $observacaoObrigatoria;
        $this->confirmacaoDigitada = $confirmacaoDigitada;
        $this->textoConfirmacao = $textoConfirmacao;
        $this->aberto = $aberto;
        $this->classesCss = $classesCss;

        // Prioriza o atributo id se fornecido, caso contrário usa componenteId ou gera um novo
        $this->componenteId = $id ?? $componenteId ?? 'modal-confirmacao-' . uniqid();
    }

    /**
     * Obter as classes CSS para o botão de confirmação com base no tipo
     *
     * @return string
     */
    public function classesBotaoConfirmar()
    {
        return match ($this->tipo) {
            'perigo', 'excluir', 'deletar' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
            'info', 'informacao' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
            'primario' => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
            'sucesso' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
            'alerta', 'aviso' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
            default => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        };
    }

    /**
     * Obter as classes CSS para o ícone com base no tipo
     *
     * @return string
     */
    public function classeIcone()
    {
        return match ($this->tipo) {
            'perigo', 'excluir', 'deletar' => 'text-red-600 bg-red-100',
            'info', 'informacao' => 'text-blue-600 bg-blue-100',
            'primario' => 'text-indigo-600 bg-indigo-100',
            'sucesso' => 'text-green-600 bg-green-100',
            'alerta', 'aviso' => 'text-yellow-600 bg-yellow-100',
            default => 'text-blue-600 bg-blue-100',
        };
    }

    /**
     * Obter o ícone apropriado com base no tipo
     *
     * @return string
     */
    public function icone()
    {
        return match ($this->tipo) {
            'perigo', 'excluir', 'deletar' => 'exclamation',
            'info', 'informacao' => 'information-circle',
            'sucesso' => 'check',
            'alerta', 'aviso' => 'exclamation-circle',
            default => 'question-mark-circle',
        };
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        // Passando explicitamente todas as variáveis para a view
        return view('components.ui.modal-confirmacao', [
            'componenteId' => $this->componenteId,
            'titulo' => $this->titulo,
            'mensagem' => $this->mensagem,
            'textoBotaoConfirmar' => $this->textoBotaoConfirmar,
            'textoBotaoCancelar' => $this->textoBotaoCancelar,
            'tipo' => $this->tipo,
            'formId' => $this->formId,
            'metodo' => $this->metodo,
            'urlConfirmacao' => $this->urlConfirmacao,
            'exibeObservacao' => $this->exibeObservacao,
            'campoObservacao' => $this->campoObservacao,
            'observacaoObrigatoria' => $this->observacaoObrigatoria,
            'confirmacaoDigitada' => $this->confirmacaoDigitada,
            'textoConfirmacao' => $this->textoConfirmacao,
            'aberto' => $this->aberto,
            'classesCss' => $this->classesCss,
            'classesBotaoConfirmar' => $this->classesBotaoConfirmar(),
            'classeIcone' => $this->classeIcone(),
            'icone' => $this->icone()
        ]);
    }
}
