<?php

namespace App\Modules\Checklist\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TipoChecklist;
use App\Models\ChecklistResposta;
use App\Models\ColunaChecklist;

class CheckList extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'checklists';
    protected $primaryKey = 'id';
    protected $fillable = [
        'checklist_type_id',
        'title',
        'description',
        'entity_type',
        'entity_id',
        'status',
        'current_stage',
        'total_stages',
        'created_by',
        'assigned_to',
        'due_date',
        'started_at',
        'completed_at',
        'created_at',
        'updated_at',
        'id_realizador',
        'assinatura_responsavel',
        'is_tratado_check',
        'is_devolucao_feita',
        'data_realizacao',
        'km_atual',
        'horimetro',
        'nome_responsavel_frota',
        'observacao_geral',
        'id_filial',
        'id_check_devolucao',
        'ultimo_recebimento'
    ];
}
