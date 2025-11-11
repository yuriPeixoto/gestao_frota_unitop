<?php

namespace App\Modules\Certificados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\Veiculo;
use App\Models\Pessoal;
use App\Models\ClassificacaoMulta;
use App\Models\DetalheMulta;

class Multa extends Model
{
    use LogsActivity;

    protected $table = 'motivo_multa';
    protected $primaryKey = 'id_motivo_multa';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'descricao',
        'id_orgao',
        'id_classificacao_multa',
        'valor_multa',
        'aquivo_multa',
        'nome_arquivo',
        'id_veiculo',
        'id_condutor',
        'numero_ocorrencia',
        'data_infracao',
        'vencimento_multa',
        'id_departamento',
        'id_filial',
        'auto_infracao',
        'notificacao',
        'situacao',
        'responsabilidade',
        'localizacao',
        'id_municipio',
        'debitar_condutor',
        'parcelas',
        'data_envio_departamento',
        'status_multa',
        'data_envio_financeiro',
        'id_departamento_responsavel',
        'id_filial_responsavel',
        'data_prazo_ident',
        'arquivo_boleto',
        'is_assinado',
        'assinatura'
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function condutor(): BelongsTo
    {
        return $this->belongsTo(Pessoal::class, 'id_condutor', 'id_pessoal');
    }

    public function classificacaoMulta(): BelongsTo
    {
        return $this->belongsTo(ClassificacaoMulta::class, 'id_classificacao_multa');
    }

    public function detalheMulta(): HasMany
    {
        return $this->hasMany(DetalheMulta::class, 'id_motivo_multa');
    }

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}
