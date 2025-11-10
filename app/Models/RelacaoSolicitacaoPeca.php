<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelacaoSolicitacaoPeca extends Model
{
    use LogsActivity;

    protected $table = 'relacaosolicitacoespecas';
    protected $primaryKey = 'id_solicitacao_pecas';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_departamento',
        'id_usuario_abertura',
        'id_filial',
        'id_veiculo',
        'id_orderm_servico',
        'situacao',
        'id_usuario_estoque',
        'aprovacao_gestor',
        'transferencia_entre_filiais',
        'observacao',
        'justificativa_de_finalizacao',
        'id_terceiro',
        'requisicao_pneu',
        'id_filial_manutencao',
        'observacao_cancelamento',
        'is_separado',
        'id_user_aprovador',
        'data_aprovacao',
        'requisicao_ti',
        'is_cancelado',
        'observacao_cancelado',
        'is_requisicao_os_imobilizado',
        'anexo_imagem',
        'id_transferencia',
        'is_transferencia',
        'numero_nota',
        'chave_nota',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServicoPecas::class, 'id_orderm_servico');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function departamentoPecas()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function filialManutencao()
    {
        return $this->belongsTo(VFilial::class, 'id_filial_manutencao', 'id');
    }

    public function pessoalEstoque()
    {
        return $this->belongsTo(User::class, 'id_usuario_estoque', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario_abertura', 'id');
    }

    public function usuarioAprovador()
    {
        return $this->belongsTo(User::class, 'id_user_aprovador', 'id');
    }

    public function pessoalAbertura()
    {
        return $this->belongsTo(User::class, 'id_usuario_abertura', 'id');
    }

    public function transferenciaEstoqueAux(): HasMany
    {
        return $this->hasMany(TransferenciaEstoqueAux::class, 'id_relacao_solicitacoes_novo', 'id_solicitacao_pecas');
    }

    public function devolucoes(): HasMany
    {
        return $this->hasMany(DevolucaoTransferenciaEstoqueRequisicao::class, 'id_relacao_solicitacoes', 'id_solicitacao_pecas');
    }

    public function produtosSolicitacoes(): HasMany
    {
        return $this->hasMany(ProdutosSolicitacoes::class, 'id_relacao_solicitacoes', 'id_solicitacao_pecas');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_terceiro', 'id_fornecedor');
    }

    public function podeSerEnviadaParaAprovacao()
    {
        return $this->situacao === null || $this->situacao == 'REVISAR REQUISIÇÃO';
    }

    public function podeSerEditado()
    {
        return $this->situacao === null && !$this->is_cancelado && !$this->aprovacao_gestor ||
            $this->situacao === 'REVISAR REQUISIÇÃO' ||
            $this->situacao === 'AGUARDANDO APROVAÇÃO';
    }

    public function podeAprovar()
    {
        return $this->situacao === 'AGUARDANDO APROVAÇÃO';
    }
}
