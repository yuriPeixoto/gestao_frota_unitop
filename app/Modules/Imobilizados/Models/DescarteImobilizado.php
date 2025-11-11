<?php

namespace App\Modules\Imobilizados\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescarteImobilizado extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $connection = 'pgsql';
    protected $table = 'descarte_imobilizados';

    protected $primaryKey = 'id_descarte_imobilizados';

    public $timestamps = false;

    protected $fillable = [
        'id_produtos_imobilizados',
        'motivo_descarte',
        'id_usuario',
        'id_filial',
        'data_inclusao',
        'data_alteracao',
        'laudo_descarte'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];


    public function produtoImobilizado(): BelongsTo
    {
        return $this->belongsTo(ProdutosImobilizados::class, 'id_produtos_imobilizados', 'id_produtos_imobilizados');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
