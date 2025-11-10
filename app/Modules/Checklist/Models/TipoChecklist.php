<?php

namespace App\Modules\Checklist\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CheckList;
use App\Models\Departamento;

class TipoChecklist extends Model
{
    protected $table = 'tipo_checklist';

    protected $fillable = [
        'nome',
        'descricao',
        'filtro',
        'departamento_id',
        'cargo_id',
        'multiplas_etapas',

    ];

    public function checklists()
    {
        return $this->hasMany(CheckList::class, 'tipo_checklist_id', 'id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }
}
