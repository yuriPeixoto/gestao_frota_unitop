<?php

namespace App\View\Components\Forms;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class UploadAnexos extends Component
{
    /**
     * Lista de anexos existentes
     *
     * @var Collection|array
     */
    public $anexos;

    /**
     * Tipo da entidade relacionada (solicitacao, pedido, etc.)
     *
     * @var string
     */
    public $entidadeTipo;

    /**
     * ID da entidade relacionada
     *
     * @var int|null
     */
    public $entidadeId;

    /**
     * URL para upload dos anexos
     *
     * @var string
     */
    public $urlUpload;

    /**
     * URL para exclusão dos anexos
     *
     * @var string
     */
    public $urlExcluir;

    /**
     * Tipos de arquivos permitidos
     *
     * @var string
     */
    public $tiposPermitidos;

    /**
     * Tamanho máximo do arquivo em MB
     *
     * @var int
     */
    public $tamanhoMaximo;

    /**
     * Flag que indica se o componente está em modo somente visualização
     *
     * @var bool
     */
    public $somenteVisualizacao;

    /**
     * Flag que indica se o upload de múltiplos arquivos é permitido
     *
     * @var bool
     */
    public $multiplosArquivos;

    /**
     * Texto explicativo para o usuário
     *
     * @var string
     */
    public $textoAjuda;

    /**
     * Classes CSS adicionais
     *
     * @var string
     */
    public $classesCss;

    /**
     * ID do componente para uso no DOM
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
        $anexos = [],
        $entidadeTipo = null,
        $entidadeId = null,
        $urlUpload = null,
        $urlExcluir = null,
        $tiposPermitidos = '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv',
        $tamanhoMaximo = 10,
        $somenteVisualizacao = false,
        $multiplosArquivos = true,
        $textoAjuda = 'Arraste e solte arquivos aqui ou clique para selecionar',
        $classesCss = '',
        $componenteId = null
    ) {
        $this->anexos = $anexos instanceof Collection ? $anexos : collect($anexos);
        $this->entidadeTipo = $entidadeTipo;
        $this->entidadeId = $entidadeId;

        // Configurar URLs padrão se não forem fornecidas
        $this->urlUpload = $urlUpload ?? route('admin.anexos.upload', ['entidade_tipo' => $entidadeTipo, 'entidade_id' => $entidadeId]);
        $this->urlExcluir = $urlExcluir ?? route('admin.anexos.destroy', ['_id_placeholder']);

        $this->tiposPermitidos = $tiposPermitidos;
        $this->tamanhoMaximo = $tamanhoMaximo;
        $this->somenteVisualizacao = $somenteVisualizacao;
        $this->multiplosArquivos = $multiplosArquivos;
        $this->textoAjuda = $textoAjuda;
        $this->classesCss = $classesCss;
        $this->componenteId = $componenteId ?? $this->gerarComponenteId();
    }

    /**
     * Gera o ID do componente para uso no DOM
     *
     * @return string
     */
    private function gerarComponenteId()
    {
        return 'upload-anexos-' . ($this->entidadeTipo ?? 'generico') . '-' . ($this->entidadeId ?? uniqid());
    }

    /**
     * Obter a lista de extensões permitidas em formato para o Filepond
     *
     * @return string
     */
    public function extensoesPermitidas()
    {
        return str_replace('.', '', $this->tiposPermitidos);
    }

    /**
     * Obter o texto descritivo dos tipos de arquivos permitidos
     *
     * @return string
     */
    public function descricaoTiposPermitidos()
    {
        $tipos = explode(',', $this->tiposPermitidos);
        $tipos = array_map(fn($tipo) => strtoupper(str_replace('.', '', $tipo)), $tipos);

        if (count($tipos) > 5) {
            $primeiros = array_slice($tipos, 0, 5);
            return implode(', ', $primeiros) . ' e outros';
        }

        return implode(', ', $tipos);
    }

    /**
     * Obter o limite de tamanho em bytes para o Filepond
     *
     * @return int
     */
    public function tamanhoMaximoBytes()
    {
        return $this->tamanhoMaximo * 1024 * 1024; // Converter MB para bytes
    }

    /**
     * Determinar o ícone baseado na extensão do arquivo
     *
     * @param string $nomeArquivo
     * @return string
     */
    public function iconeArquivo($nomeArquivo)
    {
        $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

        return match ($extensao) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx', 'csv' => 'fa-file-excel',
            'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'fa-file-image',
            'zip', 'rar', '7z' => 'fa-file-archive',
            'txt' => 'fa-file-alt',
            default => 'fa-file',
        };
    }

    /**
     * Verificar se o arquivo é uma imagem
     *
     * @param string $nomeArquivo
     * @return bool
     */
    public function ehImagem($nomeArquivo)
    {
        $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
        return in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        // Passando explicitamente todas as variáveis necessárias para a view
        return view('components.forms.upload-anexos', [
            'componenteId' => $this->componenteId,
            'anexos' => $this->anexos,
            'urlUpload' => $this->urlUpload,
            'urlExcluir' => $this->urlExcluir,
            'entidadeTipo' => $this->entidadeTipo,
            'entidadeId' => $this->entidadeId,
            'tiposPermitidos' => $this->tiposPermitidos,
            'tamanhoMaximo' => $this->tamanhoMaximo,
            'tamanhoMaximoBytes' => $this->tamanhoMaximoBytes(),
            'somenteVisualizacao' => $this->somenteVisualizacao,
            'multiplosArquivos' => $this->multiplosArquivos,
            'textoAjuda' => $this->textoAjuda,
            'classesCss' => $this->classesCss,
            'descricaoTiposPermitidos' => $this->descricaoTiposPermitidos()
        ]);
    }
}
