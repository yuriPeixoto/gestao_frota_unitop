<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutosImobilizados extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $connection = 'pgsql';
    protected $table = 'produtos_imobilizados';

    protected $primaryKey = 'id_produtos_imobilizados';

    public $timestamps = false;

    protected $fillable = [
        'id_tipo_imobilizados',
        'id_produto',
        'valor',
        'id_departamento',
        'id_filial',
        'status',
        'is_descarte',
        'id_usuario',
        'id_filial_user',
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'numero_nf',
        'id_responsavel_imobilizado',
        'cod_patrimonio',
        'id_lider_setor',
        'vida_recondicionado',
        'id_ordem_servico',
        'data_aplicacao',
        'imagem_produto'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime'
    ];

    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function setValorFormatadoAttribute($value)
    {
        $cleanValue = str_replace(['R$', '.', ' '], '', $value);
        $cleanValue = str_replace(',', '.', $cleanValue);

        $this->attributes['valor'] = (float) $cleanValue;
    }


    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function tipoImobilizado(): BelongsTo
    {
        return $this->belongsTo(TipoImobilizado::class, 'id_tipo_imobilizados', 'id_tipo_imobilizados');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function manutencoesItem()
    {
        return $this->hasMany(ManutencaoImobilizadoItens::class, 'id_produtos_imobilizados');
    }
}
