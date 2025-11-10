<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaPorTipoEquipamento extends Model
{
    use LogsActivity;

    protected $table = 'meta_por_tipo_equipamento';
    protected $primaryKey = 'id_meta';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'vlr_meta',
        'id_filial',
        'ativo',
        'id_equipamento',
        'data_inicial',
        'data_final'
    ];

    // Adicionar cast para datas
    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_inicial' => 'date',
        'data_final' => 'date'
    ];

    // Corrigindo o relacionamento para carregar a filial corretamente
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    // Relacionamento com TipoEquipamento usando o nome correto da tabela
    public function tipoEquipamento(): BelongsTo
    {
        return $this->belongsTo(TipoEquipamento::class, 'id_equipamento', 'id_tipo_equipamento');
    }

    // Método para formatar a meta como moeda
    public function getVlrMetaFormatadoAttribute(): string
    {
        return 'R$ ' . number_format((float)$this->vlr_meta, 2, ',', '.');
    }

    // Método para verificar se o registro está ativo
    public function getStatusAtivoAttribute(): string
    {
        return $this->ativo ? 'Sim' : 'Não';
    }

    // Método auxiliar para formatar a data de inclusão
    public function getDataInclusaoFormatadaAttribute(): string
    {
        return $this->data_inclusao ? $this->data_inclusao->format('d/m/Y H:i') : '';
    }

    // Método auxiliar para formatar a data de alteração
    public function getDataAlteracaoFormatadaAttribute(): string
    {
        return $this->data_alteracao ? $this->data_alteracao->format('d/m/Y H:i') : '';
    }

    // Método auxiliar para formatar a data inicial
    public function getDataInicialFormatadaAttribute(): string
    {
        return $this->data_inicial ? $this->data_inicial->format('d/m/Y') : '';
    }

    // Método auxiliar para formatar a data final
    public function getDataFinalFormatadaAttribute(): string
    {
        return $this->data_final ? $this->data_final->format('d/m/Y') : '';
    }
}
