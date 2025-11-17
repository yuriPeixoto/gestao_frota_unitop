<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdemServico extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'ordem_servico';

    protected $primaryKey = 'id_ordem_servico';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_tipo_ordem_servico',
        'id_status_ordem_servico',
        'id_veiculo',
        'id_departamento',
        'id_doca',
        'id_fornecedor',
        'id_mecanico',
        'observacao',
        'data_encerramento',
        'id_os_retorno',
        'situacao_tipo_os_corretiva',
        'km_atual',
        'data_inicio_ordem',
        'servico_garantia',
        'id_manutencao',
        'horas_manutencao_tk',
        'id_nf_ordem',
        'id_filial',
        'id_recepcionista',
        'km_encerramento',
        'data_abertura',
        'data_previsao_saida',
        'local_manutencao',
        'id_recepcionista_encerramento',
        'telefone_motorista',
        'id_motorista',
        'prioridade_os',
        'tipo_corretiva',
        'id_lancamento_os_auxiliar',
        'id_pre_os',
        'id_user_alteracao',
        'is_cancelada',
        'data_hora_cancelamento',
        'id_filial_manutencao',
        'id_recepcionista_finalixacao',
        'data_hora_finalizacao',
        'grupo_resolvedor',
        'telefone_cadastro_motorista',
        'relato_problema'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_encerramento' => 'datetime',
        'data_inicio_ordem' => 'datetime',
        'data_abertura' => 'datetime',
        'data_previsao_saida' => 'datetime',
        'data_hora_cancelamento' => 'datetime',
        'data_hora_finalizacao' => 'datetime',
        'horas_manutencao_tk' => 'float',
        'km_encerramento' => 'float',
        'is_cancelada' => 'boolean'
    ];

    // ===== SCOPES ESPECÍFICOS =====
    public function scopeBorracharia($query)
    {
        // Filtra OS apenas para veículos de borracharia pela placa: BOR001 e BOR0001 (conforme legado)
        return $query->whereHas('veiculo', function ($q) {
            $q->whereIn('placa', ['BOR001', 'BOR0001'])->orWhere('id_tipo_ordem_servico', 3);
        });
    }

    public function scopeAssumiveis($query)
    {
        // Critério mínimo: OS sem recepcionista vinculado e não cancelada
        return $query->whereNull('id_recepcionista')
            ->where(function ($q) {
                $q->whereNull('is_cancelada')->orWhere('is_cancelada', false);
            });
    }

    /**
     * Relacionamento com Departamento
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Relacionamento com Fornecedor
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Relacionamento com Motorista
     */
    public function motorista(): BelongsTo
    {
        return $this->belongsTo(Pessoal::class, 'id_motorista', 'id_pessoal');
    }

    /**
     * Relacionamento com Mecânico
     */
    public function mecanico(): BelongsTo
    {
        return $this->belongsTo(Pessoal::class, 'id_mecanico', 'id_pessoal');
    }

    /**
     * Relacionamento com Manutenção
     */
    public function manutencao(): BelongsTo
    {
        return $this->belongsTo(Manutencao::class, 'id_manutencao', 'id_manutencao');
    }

    /**
     * Relacionamento com Doca
     */
    public function doca(): BelongsTo
    {
        return $this->belongsTo(Doca::class, 'id_doca', 'id_docas');
    }

    /**
     * Relacionamento com Filial
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    /**
     * Relacionamento com Filial de Manutenção
     */
    public function filialManutencao(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial_manutencao', 'id');
    }

    public function tipoOrdemServico(): BelongsTo
    {
        return $this->belongsTo(TipoOrdemServico::class, 'id_tipo_ordem_servico');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function statusOrdemServico(): BelongsTo
    {
        return $this->belongsTo(StatusOrdemServico::class, 'id_status_ordem_servico');
    }

    public function recepcionista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_recepcionista', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_recepcionista', 'id');
    }

    public function recepcionistaEncerramento(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_recepcionista_encerramento', 'id');
    }

    public function usuarioEncerramento(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_recepcionista_encerramento', 'id');
    }

    public function usuarioAlteracao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_alteracao', 'id');
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(OrdemServicoServicos::class, 'id_ordem_servico', 'id_ordem_servico');
    }

    /**
     * Relacionamento com Notas Fiscais
     */
    public function notasFiscais()
    {
        return $this->hasMany(NfOrdemServico::class, 'id_nf_ordem', 'id_ordem_servico');
    }

    public function pecas(): HasMany
    {
        return $this->hasMany(OrdemServicoPecas::class, 'id_ordem_servico', 'id_ordem_servico');
    }

    public function grupoResolvedor(): BelongsTo
    {
        return $this->belongsTo(GrupoResolvedor::class, 'grupo_resolvedor', 'id_grupo_resolvedor');
    }

    public function preOrdemServico(): BelongsTo
    {
        return $this->belongsTo(PreOrdemServico::class, 'id_pre_os', 'id_ordem_servico');
    }
}
