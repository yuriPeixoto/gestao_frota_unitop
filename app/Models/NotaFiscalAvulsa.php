<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotaFiscalAvulsa extends Model
{
    use HasFactory;
    //use SoftDeletes;
    use LogsActivity;

    /**
     * Nome da tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'nf_avulsa';

    /**
     * Chave primária da tabela.
     *
     * @var string
     */
    protected $primaryKey = 'id_nf_avulsa';

    /**
     * Desativa os timestamps padrão do Laravel.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'chave_nf',
        'id_fornecedor',
        'numero_nf',
        'serie_nf',
        'data_emissao',
        'valor_pecas',
        'valor_total_nf',
        'numero_do_pedido',
        'id_user_lancamento',
    ];

    /**
     * Atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_emissao' => 'date',
        'valor_pecas' => 'float',
        'valor_total_nf' => 'float',
    ];

    /**
     * Atributos de datas.
     *
     * @var array
     */
    protected $dates = [
        'data_inclusao',
        'data_alteracao',
        'data_emissao',
    ];

    /**
     * Relação com o fornecedor.
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Relação com o pedido de compra.
     */
    public function pedidoCompra()
    {
        return $this->belongsTo(PedidoCompra::class, 'numero_do_pedido', 'id_pedido_compras');
    }

    /**
     * Relação com o usuário que lançou a nota.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user_lancamento', 'id');
    }

    /**
     * Configura o valor de data_inclusao automaticamente ao criar um registro.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->data_inclusao)) {
                $model->data_inclusao = now();
            }
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }

    /**
     * Escopo para filtrar notas fiscais por período de emissão.
     */
    public function scopePorPeriodoEmissao($query, $dataInicio, $dataFim)
    {
        if ($dataInicio && $dataFim) {
            return $query->whereBetween('data_emissao', [$dataInicio, $dataFim]);
        }
        return $query;
    }

    /**
     * Escopo para filtrar notas fiscais por fornecedor.
     */
    public function scopeDoFornecedor($query, $idFornecedor)
    {
        if ($idFornecedor) {
            return $query->where('id_fornecedor', $idFornecedor);
        }
        return $query;
    }

    /**
     * Escopo para filtrar notas fiscais por pedido.
     */
    public function scopeDoPedido($query, $numeroPedido)
    {
        if ($numeroPedido) {
            return $query->where('numero_do_pedido', $numeroPedido);
        }
        return $query;
    }

    /**
     * Escopo para filtrar por número de nota fiscal.
     */
    public function scopePorNumeroNf($query, $numeroNf)
    {
        if (is_array($numeroNf)) {
            return $query->whereIn('numero_nf', $numeroNf);
        }
        return $query->where('numero_nf', 'like', "%$numeroNf%");
    }


    /**
     * Escopo para filtrar por chave NF.
     */
    public function scopePorChaveNf($query, $chaveNf)
    {
        if ($chaveNf) {
            return $query->where('chave_nf', 'like', "%{$chaveNf}%");
        }
        return $query;
    }
}
