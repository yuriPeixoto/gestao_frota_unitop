<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoPneu extends Model
{
    use LogsActivity;

    protected $table = 'historicopneu';
    protected $primaryKey = 'id_historico_pneu';
    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_veiculo',
        'id_pneu',
        'km_inicial',
        'km_final',
        'hr_inicial',
        'hr_final',
        'eixo_aplicado',
        'data_retirada',
        'id_modelo',
        'id_vida_pneu',
        'status_movimentacao',
        // ✅ NOVOS CAMPOS PARA AUTO-SAVE
        'origem_operacao',
        'observacoes_operacao',
        'id_usuario'
    ];

    // ✅ RELACIONAMENTOS EXISTENTES
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function vidaPneu()
    {
        return $this->belongsTo(ControleVidaPneus::class, 'id_vida_pneu');
    }

    public function nfPneu()
    {
        return $this->belongsTo(NotaFiscalPneu::class, 'id_pneu');
    }

    // ✅ NOVO RELACIONAMENTO PARA AUTO-SAVE
    public function pneu(): BelongsTo
    {
        return $this->belongsTo(Pneu::class, 'id_pneu');
    }

    // ✅ NOVOS SCOPES PARA AUTO-SAVE
    public function scopeAutoSave($query)
    {
        return $query->where('origem_operacao', 'AUTO_SAVE');
    }

    public function scopeManual($query)
    {
        return $query->where('origem_operacao', 'MANUAL');
    }

    public function scopeRodizio($query)
    {
        return $query->where('status_movimentacao', 'RODIZIO');
    }

    public function scopeAplicacao($query)
    {
        return $query->where('status_movimentacao', 'APLICADO');
    }

    public function scopeRemocao($query)
    {
        return $query->whereIn('status_movimentacao', ['ESTOQUE', 'MANUTENCAO', 'DESCARTE']);
    }

    // ✅ MÉTODOS HELPER PARA AUTO-SAVE
    public function getKmRodados()
    {
        if ($this->km_inicial && $this->km_final) {
            return $this->km_final - $this->km_inicial;
        }
        return null;
    }

    public function getHorasRodadas()
    {
        if ($this->hr_inicial && $this->hr_final) {
            return $this->hr_final - $this->hr_inicial;
        }
        return null;
    }

    public function isAtivo()
    {
        return is_null($this->data_retirada) && is_null($this->km_final);
    }

    public function getDuracaoFormatada()
    {
        if ($this->data_inclusao && $this->data_retirada) {
            $inicio = \Carbon\Carbon::parse($this->data_inclusao);
            $fim = \Carbon\Carbon::parse($this->data_retirada);
            return $inicio->diffForHumans($fim, true);
        }

        if ($this->data_inclusao) {
            $inicio = \Carbon\Carbon::parse($this->data_inclusao);
            return $inicio->diffForHumans() . ' (ainda ativo)';
        }

        return null;
    }

    // ✅ MÉTODO PARA VERIFICAR SE É OPERAÇÃO AUTO-SAVE
    public function isAutoSave()
    {
        return $this->origem_operacao === 'AUTO_SAVE';
    }

    // ✅ MÉTODO PARA OBTER RESUMO DA OPERAÇÃO
    public function getResumoOperacao()
    {
        $resumo = [
            'tipo' => $this->status_movimentacao,
            'origem' => $this->origem_operacao,
            'veiculo_id' => $this->id_veiculo,
            'pneu_id' => $this->id_pneu,
            'eixo' => $this->eixo_aplicado,
            'km_inicial' => $this->km_inicial,
            'km_final' => $this->km_final,
            'km_rodados' => $this->getKmRodados(),
            'duracao' => $this->getDuracaoFormatada(),
            'ativo' => $this->isAtivo()
        ];

        if ($this->observacoes_operacao) {
            $resumo['observacoes'] = $this->observacoes_operacao;
        }

        return $resumo;
    }
}
