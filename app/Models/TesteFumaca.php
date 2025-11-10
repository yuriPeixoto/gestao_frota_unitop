<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ToggleIsActiveOnSoftDelete;

class TesteFumaca extends Model
{
    // use LogsActivity;
    // use SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $activeField = 'is_ativo';

    protected $table = 'teste_fumaca';
    protected $primaryKey = 'id_teste_fumaca';
    public $timestamps = false;

    protected $fillable = [
        'id_veiculo',
        'placa',
        'prefixo',
        'data_de_realizacao',
        'data_de_vencimento',
        'kmaximo',
        'kmedido',
        'resultado',
        'transportador',
        'tecnico',
        'anexo_laudo',
        'is_ativo',
        'situacao',
        'deleted_at',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'data_de_realizacao' => 'datetime',
        'data_de_vencimento' => 'date',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeAtivos($query)
    {
        return $query->where('situacao', true);
    }

    public function scopeInativos($query)
    {
        return $query->where('situacao', false);
    }

    public function scopeTodos($query)
    {
        // simplesmente retorna tudo, sem filtro
        return $query;
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }
}
