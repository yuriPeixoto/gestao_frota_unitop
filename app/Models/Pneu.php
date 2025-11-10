<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Pneu extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';

    protected $table = 'pneu';

    protected $primaryKey = 'id_pneu';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_departamento',
        'id_filial',
        'cod_antigo',
        'status_pneu',
        'id_sequencial',
        'deleted_at',
        'id_modelo_pneu',
        'id_controle_vida_pneu',
        'sulco',
    ];

    public function filialPneu(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function calibragemPneuItes(): BelongsTo
    {
        return $this->belongsTo(CalibragemPneus::class, 'id_calibragem_pneus_itens');
    }

    public function calibragem(): BelongsTo
    {
        return $this->belongsTo(CalibragemPneus::class, 'id_calibragem');
    }

    public function controleVidaPneus()
    {
        return $this->hasOne(ControleVidaPneus::class, 'id_controle_vida_pneu', 'id_pneu');
    }

    public function departamentoPneu(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }
    public function notasFiscais()
    {
        return $this->hasMany(NotaFiscalPneu::class, 'id_pneu', 'id_pneu');
    }
    public function modeloPneu(): BelongsTo
    {
        return $this->belongsTo(ModeloPneu::class, 'id_modelo_pneu', 'id_modelo_pneu');
    }

    /**
     * Get the ModeloPneu from the most recent historicopneu record
     */
    public function getModeloPneuFromHistorico()
    {
        $historicoMaisRecente = DB::connection('pgsql')
            ->table('historicopneu')
            ->where('id_pneu', $this->id_pneu)
            ->whereNotNull('id_modelo')
            ->orderBy('data_inclusao', 'desc')
            ->first();

        if ($historicoMaisRecente && $historicoMaisRecente->id_modelo) {
            return ModeloPneu::find($historicoMaisRecente->id_modelo);
        }

        return null;
    }

    public function tipoDesenhoPneu()
    {
        return $this->belongsTo(TipoDesenhoPneu::class, 'id_desenho_pneu', 'id_desenho_pneu');
    }

    public function ultimaManutencaoEntrada(): HasOne
    {
        return $this->hasOne(ManutencaoPneusEntradaItens::class, 'id_pneu')
            ->latestOfMany('data_inclusao');
    }

    // ✅ NOVOS SCOPES PARA AUTO-SAVE
    public function scopeAplicadosEm($query, $kmInicial, $kmFinal = null)
    {
        $query->whereBetween('km_aplicacao', [$kmInicial, $kmFinal ?? PHP_INT_MAX]);

        return $query;
    }

    public function scopeEstoque($query)
    {
        return $query->where('status_pneu', 'ESTOQUE');
    }

    public function scopeAplicado($query)
    {
        return $query->where('status_pneu', 'APLICADO');
    }

    public function scopeManutencao($query)
    {
        return $query->where('status_pneu', 'MANUTENCAO');
    }

    public function scopeDescarte($query)
    {
        return $query->where('status_pneu', 'DESCARTE');
    }

    // ✅ MÉTODO HELPER PARA AUTO-SAVE
    public function getTipoPneuInfo()
    {
        $ultimaManutencao = $this->ultimaManutencaoEntrada;

        if (! $ultimaManutencao || ! $ultimaManutencao->tipoReforma) {
            return [
                'tipo' => 'NOVO',
                'permite_primeiro_eixo' => true,
                'descricao' => 'Pneu novo',
            ];
        }

        $tipoReforma = $ultimaManutencao->tipoReforma->descricao_tipo_reforma;
        $isRecapado = in_array($ultimaManutencao->id_tipo_reforma, [1, 2]); // IDs para vulcanizado/recapado

        return [
            'tipo' => $tipoReforma,
            'permite_primeiro_eixo' => ! $isRecapado,
            'descricao' => $tipoReforma,
            'is_recapado' => $isRecapado,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($pneu) {
            Cache::forget('pneu_' . $pneu->id_pneu);
            Cache::forget('pneu');

            $cacheKeys = Cache::get('pneu_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::put('pneu_search_keys', [], now()->addDay());
        });

        static::deleted(function ($pneu) {
            Cache::forget('pneu_' . $pneu->id_pneu);
            Cache::forget('pneu');

            $cacheKeys = Cache::get('pneu_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        });
    }
}
