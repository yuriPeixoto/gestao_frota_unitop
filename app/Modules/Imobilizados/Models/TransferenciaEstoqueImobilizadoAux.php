<?php

namespace App\Modules\Imobilizados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class TransferenciaEstoqueImobilizadoAux extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'transferencia_estoque_imobilizado_aux';

    protected $primaryKey = 'id_transferencia_estoque_imobilizado_aux';

    protected $fillable = [
        'id_relacao_novo',
        'id_relacao_antigo',
        'id_produtos_imobilizados',
        'id_produtos',
        'id_recebimento',
        'id_filial',
        'indicador',
        'data_inclusao',
        'data_alteracao',
    ];
}
