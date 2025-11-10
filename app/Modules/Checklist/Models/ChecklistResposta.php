<?php

namespace App\Modules\Checklist\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ColunaCheckList;

class ChecklistResposta extends Model
{
    protected $table = 'checklist_respostas';

    protected $fillable = [
        'foto',
        'assinatura',
        'valor_resposta',
        'relacionado_id',
        'checklist_id',
        'coluna_id',
        'status',
        'passo',
        'user_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'checklist_id' => 'integer',
        'coluna_id' => 'integer'    
    ];

    public function colunaChecklist()   
    {
        return $this->belongsTo(ColunaCheckList::class, 'coluna_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function checklist()
    {
        return $this->belongsTo(CheckList::class, 'checklist_id', 'id');
    }

}



