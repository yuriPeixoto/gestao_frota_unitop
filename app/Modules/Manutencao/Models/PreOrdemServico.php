<?php

namespace App\Modules\Manutencao\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PreOrdemServico extends Model
{
    use LogsActivity;

    protected $table = 'pre_os';
    protected $primaryKey = 'id_pre_os';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'id_motorista',
        'km_realizacao',
        'horimetro_tk',
        'local_execucao',
        'descricao_reclamacao',
        'id_usuario',
        'id_status',
        'observacoes',
        'id_recepcionista',
        'id_filial',
        'id_departamento',
        'id_grupo_resolvedor',
        'telefone_motorista',
        'cidade',
        'rua',
        'situacao_pre_os',
        'id_user_create',
        'id_pacoteposicaorangjson',
        'tipo',
    ];

    protected $casts = [
        'data_inclusao'      => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
    public function recepcionista()
    {
        return $this->belongsTo(User::class, 'id_recepcionista');
    }


    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function tipoStatusPreOs()
    {
        return $this->belongsTo(TipoStatusPreOs::class, 'id_status', 'id_tipostatus_pre_os');
    }

    public function pessoal()
    {
        return $this->belongsTo(Pessoal::class, 'id_motorista', 'id_pessoal');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function grupoResolvedor()
    {
        return $this->belongsTo(GrupoResolvedor::class, 'id_grupo_resolvedor', 'id_grupo_resolvedor');
    }

    public function servico(): HasMany
    {
        return $this->hasMany(PreOrdemServicoServicos::class, 'id_pre_os', 'id_pre_os');
    }

    public function ordemServico(): HasOne
    {
        return $this->hasOne(OrdemServico::class, 'id_pre_os', 'id_pre_os');
    }
}
