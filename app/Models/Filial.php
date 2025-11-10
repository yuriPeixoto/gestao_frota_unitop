<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use App\Models\TipoFornecedor;
use App\Models\Estado;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filial extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'filiais';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];


    public function veiculo(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'id_filial', 'id');
    }

    public function pessoal(): HasMany
    {
        return $this->hasMany(Pessoal::class, 'id_filial', 'id');
    }

    public function metaTipoEquipamento(): HasMany
    {
        return $this->hasMany(MetaPorTipoEquipamento::class, 'id_filial');
    }

    public function calibragens()
    {
        return $this->hasMany(CalibragemPneus::class, 'id_filial');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_uf', 'id_uf');
    }
}
