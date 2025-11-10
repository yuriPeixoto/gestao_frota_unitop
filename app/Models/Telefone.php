<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Telefone extends Model
{
    use LogsActivity;

    protected $table = 'telefone';

    protected $primaryKey = 'id_telefone';

    public $timestamps = false;

    protected $fillable = ['data_inclusao', 'data_alteracao', 'telefone_fixo', 'telefone_celular', 'id_pessoal', 'id_fornecedor', 'user_id'];

    public function fornecedor()
    {
        return $this->hasMany(Fornecedor::class, 'id_fornecedor');
    }

    public function getDescricaoEmpresaAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function pessoal(): BelongsTo
    {
        return $this->belongsTo(Pessoal::class, 'id_pessoal');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::creating(function ($telefone) {
            if (empty($telefone->data_inclusao)) {
                $telefone->data_inclusao = now();
            }
            if (empty($telefone->data_alteracao)) {
                $telefone->data_alteracao = now();
            }
        });

        static::updating(function ($telefone) {
            $telefone->data_alteracao = now();
        });
    }
}
