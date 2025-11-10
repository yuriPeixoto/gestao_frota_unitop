<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalibragemPneus extends Model
{
    protected $table = 'calibragem_pneu';
    //protected $table = 'calibragem_pneu_itens';
    protected $primaryKey = 'id_calibragem_pneu';
    protected $appends = ['total_calibragens_veiculo'];
    public $timestamps = false;
    protected $fillable = [
        'id_calibragem_pneu',
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'id_user_calibragem',
        'id_filial',
        'finalizada',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    /*
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Pessoal::class, 'id_user_calibragem');
        // ou Users::class se você estiver usando um model User
    }
    */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }

    public function pneu(): HasMany
    {
        return $this->hasMany(Pneu::class, 'id_calibragem');
    }

    public function pneus(): HasMany
    {
        return $this->hasMany(CalibragemPneusItens::class, 'id_calibragem', 'id_calibragem_pneu');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user_calibragem');
    }

    public function getTotalCalibragensVeiculoAttribute()
    {
        return CalibragemPneus::where('id_veiculo', $this->id_veiculo)->count();
    }
}
