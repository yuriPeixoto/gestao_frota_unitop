<?php

namespace App\Modules\Checklist\Models;

use Illuminate\Database\Eloquent\Model;

class ColunaCheckList extends Model
{
    protected $table = 'coluna_checklists';
    protected $fillable = [
        'tipo',
        'descricao',
        'checklist_id',
        'relacionado_a_tabela',
        'chave_estrangeira_relacionado'
    ];

    protected $casts = [
        'checklist_id' => 'integer',
    ];


    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id', 'id');
    }
}
