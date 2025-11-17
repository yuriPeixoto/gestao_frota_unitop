<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class FornecedorXMecanico extends Model
{
    protected $table = 'fornecedorxmecanico';
    protected $primaryKey = 'id_fornecedor_mecanico';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_fornecedor',
        'nome_mecanico',
        'id_user_mecanico' //user
    ];


    public function mecanicoInterno()
    {
        return $this->belongsTo(User::class, 'id_user_mecanico', 'id');
    }
}
