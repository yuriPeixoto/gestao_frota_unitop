<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anexo extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'anexos';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'entidade_tipo',
        'entidade_id',
        'arquivo_nome',
        'arquivo_path',
        'arquivo_tipo',
        'tamanho',
        'usuario_id',
        'descricao',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'entidade_id' => 'integer',
        'tamanho' => 'integer',
        'usuario_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Obtém o usuário que fez o upload do anexo.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relacionamento polimórfico com a entidade associada.
     * Isso é um método genérico que pode ser usado para relacionar com qualquer modelo.
     *
     * O tipo específico é determinado pelo campo entidade_tipo.
     */
    public function entidade()
    {
        // Exemplos de mapeamento de tipos de entidade para classes de modelo
        $modelMap = [
            'solicitacao' => SolicitacaoCompra::class,
            'pedido' => PedidoCompra::class,
            'contrato' => Contrato::class,
            // Adicionar outros mapeamentos conforme necessário
        ];

        $entidadeType = $this->entidade_tipo;
        $model = $modelMap[$entidadeType] ?? null;

        if (!$model) {
            return null;
        }

        return $this->morphTo('entidade', 'entidade_tipo', 'entidade_id');
    }

    /**
     * Verifica se o anexo é uma imagem.
     *
     * @return bool
     */
    public function isImagem()
    {
        $tiposImagem = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        return in_array(strtolower($this->arquivo_tipo), $tiposImagem);
    }

    /**
     * Verifica se o anexo é um PDF.
     *
     * @return bool
     */
    public function isPdf()
    {
        return strtolower($this->arquivo_tipo) === 'pdf';
    }

    /**
     * Formata o tamanho do arquivo para exibição amigável.
     *
     * @return string
     */
    public function formatarTamanho()
    {
        $bytes = $this->tamanho;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
