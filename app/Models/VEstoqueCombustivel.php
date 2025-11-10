<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VEstoqueCombustivel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'v_estoque_combustivel';
    protected $primaryKey = 'id_tanque';
    public $timestamps = false;

    protected $fillable = [
        'id_tanque',
        'tanque',
        'tipo_combustivel',
        'nome_filial',
        'quantidade_em_estoque',
        'data_alteracao',
        'capacidade_tanque'
    ];

    protected $casts = [
        'id_tanque' => 'integer',
        'quantidade_em_estoque' => 'float',
        'data_alteracao' => 'datetime',
        'capacidade_tanque' => 'integer'
    ];

    public static function getByLocation(string $location)
    {
        return self::where('nome_filial', $location)->get();
    }

    public static function getById(int $tankId)
    {
        return self::find($tankId);
    }

    public function getPercentage()
    {
        if ($this->capacidade_tanque == 0) {
            return 0;
        }

        return round(($this->quantidade_em_estoque / $this->capacidade_tanque) * 100, 2);
    }

    public function tanque()
    {
        return $this->belongsTo(Tanque::class, 'id_tanque');
    }
}
