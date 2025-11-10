<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VRequisicaoProdutoOs extends Model
{
    protected $table = 'v_requisicao_produto_os';

    public $timestamps = false;

    protected $guarded = ['*'];



    public function departamentoPecas()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function filialManutencao()
    {
        return $this->belongsTo(Filial::class, 'id_filial_manutencao', 'id');
    }

    public function pessoalEstoque()
    {
        return $this->belongsTo(Pessoal::class, 'id_usuario_estoque', 'id_pessoal');
    }

    public function pessoalAbertura()
    {
        return $this->belongsTo(Pessoal::class, 'id_usuario_abertura', 'id_pessoal');
    }

    public function produtos()
    {
        return $this->belongsTo(RelacaoSolicitacaoPeca::class, 'id_filial_manutencao', 'id');
    }
}
