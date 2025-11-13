<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class TransferenciaEstoque extends Model
{
    use LogsActivity;

    protected $table = 'transferencia_estoque';
    protected $primaryKey = 'id_tranferencia';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'id_usuario',
        'id_departamento',
        'aprovado',
        'situacao',
        'usuario_baixa',
        'filial_baixa',
        'observacao_baixa',
        'observacao_aprovacao',
        'observacao_solicitacao',
        'recebido',
        'transferencia_direta_estoque_id',
        'observacao_inconsistencia'
    ];


    public function filial()
    {
        return $this->belongsTo(Vfilial::class, 'id_filial', 'id');
    }

    public function filialBaixa()
    {
        return $this->belongsTo(Vfilial::class, 'filial_baixa', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function itens()
    {
        return $this->hasMany(TransferenciaEstoqueItens::class, 'id_transferencia', 'id_tranferencia');
    }

    public function transferencia()
    {
        return $this->belongsTo(TransferenciaDiretaEstoque::class, 'transferencia_direta_estoque_id', 'id_transferencia_direta_estoque');
    }

    public function relacaoSolicitacaoPecas()
    {
        return $this->belongsTo(RelacaoSolicitacaoPeca::class, 'id_tranferencia', 'id_transferencia');
    }
}
