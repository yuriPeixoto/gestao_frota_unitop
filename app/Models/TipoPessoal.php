<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;

class TipoPessoal extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $table = 'tipopessoal';
    protected $primaryKey = 'id_tipo_pessoal';
    public $timestamps = false;

    protected $fillable = [
        'descricao_tipo',
        'data_inclusao',
        'data_alteracao',
        'is_ativo',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    public function getDescricaoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    // Relacionamento com usuários
    public function usuarios()
    {
        return $this->hasMany(User::class, 'pessoal_id', 'id_tipo_pessoal');
    }

    // Hooks para datas
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->data_inclusao = now();
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }
}
