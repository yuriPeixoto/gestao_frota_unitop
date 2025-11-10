<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucaoMateriais extends Model
{
    use LogsActivity;

    protected $table = 'devolucao_materiais';

    protected $primaryKey = 'id_devolucao_materiais';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'justificativa',
        'id_produto',
        'quantidade',
        'id_relacaosolicitacoespecas',
        'id_filial'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }
}
