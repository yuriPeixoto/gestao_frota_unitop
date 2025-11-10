<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class RequisicaoPneuModelos extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'requisicao_pneu_modelos';

    protected $primaryKey = 'id_requisicao_pneu_modelos';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_requisicao_pneu',
        'id_modelo_pneu',
        'quantidade',
        'quantidade_baixa',
        'data_baixa',
        'id_filial',
        'valor_total',
        'documento',
        'id_produto'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_baixa' => 'datetime',
        'valor_total' => 'float'
    ];

    public function getValorTotalAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return 'R$ ' . number_format((float)$value, 2, ',', '.');
        }
        return $value;
    }

    public function setValorTotalAttribute($value)
    {

        if (empty($value)) {
            $this->attributes['valor_total'] = 0;
            return;
        }

        $value = (string)$value;

        $value = trim($value);
        Log::debug('Após trim: "' . $value . '"');

        if (strpos($value, 'R$') !== false) {
            $value = str_replace('R$', '', $value);
            $value = trim($value);
            Log::debug('Após remover R$ e trim: "' . $value . '"');

            $value = str_replace('.', '', $value);
            Log::debug('Após remover pontos: "' . $value . '"');

            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9.]/', '', $value);

        if (is_numeric($value)) {
            $floatValue = (float)$value;
            $this->attributes['valor_total'] = $floatValue;
        } else {
            Log::debug('Valor ainda não é numérico: "' . $value . '", definindo como 0');
            $this->attributes['valor_total'] = 0;
        }
    }

    public function modeloPneu(): BelongsTo
    {
        return $this->belongsTo(ModeloPneu::class, 'id_modelo_pneu', 'id_modelo_pneu');
    }

    // Alias for backward compatibility
    public function modelo(): BelongsTo
    {
        return $this->modeloPneu();
    }

    public function requisicao(): BelongsTo
    {
        return $this->belongsTo(RequisicaoPneu::class, 'id_requisicao_pneu', 'id_requisicao_pneu');
    }

    public function requisicaoPneuItens()
    {
        return $this->hasMany(RequisicaoPneuItens::class, 'id_requisicao_pneu_modelos');
    }

    // Alias for backward compatibility
    public function requisicaoItens()
    {
        return $this->requisicaoPneuItens();
    }
}
