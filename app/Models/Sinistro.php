<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Sinistro extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'sinistro';
    protected $primaryKey = 'id_sinistro';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'id_veiculo', 'id_filial', 'id_motorista', 'data_sinistro', 'situacao_sinistro_processo', 'responsabilidade_sinistro', 'id_tipo_orgao', 'numero_auto_infracao', 'numero_processo', 'prazo_indicacao_condutor', 'notificacao', 'local_ocorrencia', 'descricao_ocorrencia', 'observacao_ocorrencia', 'valor_apagar', 'valor_pago', 'data_vencimento', 'data_pagamento_com_desconto', 'data_pagamento', 'id_tipo_ocorrencia', 'id_motivo', 'danoveiculos', 'id_categoria_veiculo', 'valorpagoseguradora', 'valorpagofrota', 'situacao_pista', 'estados_pista', 'topografica', 'sinalizacao', 'status', 'setor', 'prazo_em_dias', 'valor_pago_terceiro'];

    protected $casts = [
        'data_sinistro' => 'date',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_vencimento' => 'date',
        'data_pagamento_com_desconto' => 'date',
        'data_pagamento' => 'date',
        'sinalizacao' => 'boolean',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function pessoal()
    {
        return $this->belongsTo(Pessoal::class, 'id_motorista', 'id_pessoal');
    }

    public function orgao()
    {
        return $this->belongsTo(TipoOrgaoSinistro::class, 'id_tipo_orgao');
    }

    public function situacaoAtual()
    {
        return $this->hasOne(HistoricoEventosSinistro::class, 'id_sinistro', 'id_sinistro')
            ->latest('id_historico_eventos_sinistro');
    }
}
