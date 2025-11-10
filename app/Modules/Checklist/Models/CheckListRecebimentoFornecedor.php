<?php

namespace App\Modules\Checklist\Models;

use Illuminate\Database\Eloquent\Model;

class CheckListRecebimentoFornecedor extends Model
{
    protected $table = 'checklist_recebimento_fornecedor';
    protected $primaryKey = 'id_checklist_recebimento_fornecedor';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'checklist_fornecedor_prazo',
        'checklist_fornecedor_pontualidade',
        'checklist_fornecedor_quantidade_conforme',
        'checklist_fornecedor_integridade_embalagens',
        'checklist_observacao_prazo',
        'checklist_observacao_pontualidade',
        'checklist_observacao_quantidade_conforme',
        'checklist_observacao_integridade_embalagens',
        'id_entrada_manutencao_pneu',
        'id_nota_fiscal_entrada,'
    ];

    public function entradaManutencaoPneu()
    {
        return $this->belongsTo(ManutencaoPneusEntrada::class, 'id_entrada_manutencao_pneu');
    }

    public function notaFiscalEntrada()
    {
        return $this->belongsTo(NotaFiscalEntrada::class, 'id_nota_fiscal_entrada');
    }
}
