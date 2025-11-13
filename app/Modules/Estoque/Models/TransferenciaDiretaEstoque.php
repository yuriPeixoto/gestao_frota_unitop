<?php

namespace App\Modules\Estoque\Models;

use Illuminate\Database\Eloquent\Model;

class TransferenciaDiretaEstoque extends Model
{
    protected $table = 'transferencia_direta_estoque';
    protected $primaryKey = 'id_transferencia_direta_estoque';
    //protected $connection = 'pgsql';
    public $timestamps = false;

    protected $fillable =  [
        'id_usuario',
        'id_departamento',
        'observacao',
        'filial_solicita',
        'data_inclusao',
        'data_alteracao',
        'status',
        'filial',
        'transferencia_feita',
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial', 'id');
    }

    public function filial_solicita_()
    {
        return $this->belongsTo(VFilial::class, 'filial_solicita', 'id');
    }
    public function filial_relacao()
    {
        return $this->belongsTo(Filial::class, 'filial', 'id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function devolucoes()
    {
        return $this->hasMany(DevolucaoTransferenciaEstoque::class, 'id_transferencia_direta_estoque');
    }

    public function itens()
    {
        return $this->hasMany(TransferenciaDiretaEstoqueItens::class, 'id_transferencia_direta_estoque', 'id_transferencia_direta_estoque');
    }

    public function matriz()
    {
        return $this->hasOneThrough(
            \App\Models\EstoqueItem::class, // tabela intermediária
            \App\Models\Estoque::class,     // tabela pai
            'id_filial',                    // chave em Estoque para o where
            'id_estoque',                   // chave em EstoqueItem que referencia Estoque
            'filial',                       // campo em TransferenciaDiretaEstoque que referencia Estoque
            'id_estoque'                    // chave primária de Estoque
        )->where('id_filial', 1); // ID da matriz (ajuste se necessário)
    }
}
