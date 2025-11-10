<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ToggleIsActiveOnSoftDelete;
use App\Traits\LogsActivity;

class CadastroImobilizado extends Model
{
    use LogsActivity, SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $connection = 'pgsql';
    protected $table = 'cadastro_imobilizado';
    protected $primaryKey = 'id_cadastro_imobilizado';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_tipo_solicitacao',
        'id_tipo_imobilizado',
        'id_fornecedor',
        'id_filial',
        'id_veiculo',
        'cod_produto',
        'usuario',
        'status_cadastro_imobilizado',
        'is_ativo',
        'observacao',
        'chave_nf',
        'numero_nota_fiscal',
        'deleted_at'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario', 'id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'cod_produto', 'id_produto');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function status()
    {
        return $this->belongsTo(StatusCadastroImobilizado::class, 'status_cadastro_imobilizado', 'id');
    }

    public function tipoImobilizado()
    {
        return $this->belongsTo(TipoImobilizado::class, 'id_tipo_imobilizado', 'id_tipo_imobilizados');
    }
}
