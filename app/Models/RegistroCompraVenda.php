<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroCompraVenda extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'registrocompravenda';
    protected $primaryKey = 'id_registro_compra_venda';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'financiador',
        'data_inicio_financiamento',
        'valor_do_bem',
        'numero_de_parcelas',
        'valor_parcela',
        'restricao_data',
        'numero_processo',
        'valor_processo',
        'reclamante_nome',
        'id_fornecedor_vendedor',
        'id_fornecedor_comprador',
        'data_compra',
        'valor_da_compra',
        'numero_patrimonio',
        'data_venda',
        'motivo_venda',
        'km_final',
        'hora_final',
        'id_veiculo',
        'id_filial',
        'valor_da_venda',
        'id_usuario_cadastro',
        'id_usuario_alteracao'
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }
}
