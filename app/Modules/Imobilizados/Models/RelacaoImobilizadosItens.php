<?php

namespace App\Modules\Imobilizados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelacaoImobilizadosItens extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $connection = 'pgsql';
    protected $table = 'relacao_imobilizados_itens';

    protected $primaryKey = 'id_relacao_imobilizados_itens';

    public $timestamps = false;

    protected $fillable = [
        'id_relacao_imobilizados',
        'id_produtos',
        'id_produtos_imobilizados',
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'id_departamento',
        'id_reponsavel',
        'id_lider',
        'caminho_imobilizado',
        'quantidade'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function relacaoImobilizados(): BelongsTo
    {
        return $this->belongsTo(RelacaoImobilizados::class, 'id_relacao_imobilizados');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produtos');
    }


    public function produtoImobilizado(): BelongsTo
    {
        return $this->belongsTo(ProdutosImobilizados::class, 'id_produtos_imobilizados');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }
}
