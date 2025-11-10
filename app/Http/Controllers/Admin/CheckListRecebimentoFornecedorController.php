<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckListRecebimentoFornecedor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CheckListRecebimentoFornecedorController extends Controller
{
    public function index($idNotaFiscalEntrada = null, $idEntradaManutencaoPneu = null)
    {
        return view('admin.checklistrecebimentofornecedor.index', compact('idNotaFiscalEntrada', 'idEntradaManutencaoPneu'));
    }

    public function store(Request $request)
    {
        try {
            db::beginTransaction();

            $checklist = new CheckListRecebimentoFornecedor();

            $checklist->data_inclusao                               = now();
            $checklist->checklist_fornecedor_prazo                  = $request->checklist_fornecedor_prazo;
            $checklist->checklist_fornecedor_pontualidade           = $request->checklist_fornecedor_pontualidade;
            $checklist->checklist_fornecedor_quantidade_conforme    = $request->checklist_fornecedor_quantidade_conforme;
            $checklist->checklist_fornecedor_integridade_embalagens = $request->checklist_fornecedor_integridade_embalagens;
            $checklist->checklist_observacao_prazo                  = $request->checklist_observacao_prazo;
            $checklist->checklist_observacao_pontualidade           = $request->checklist_observacao_pontualidade;
            $checklist->checklist_observacao_quantidade_conforme    = $request->checklist_observacao_quantidade_conforme;
            $checklist->checklist_observacao_integridade_embalagens = $request->checklist_observacao_integridade_embalagens;
            $checklist->id_nota_fiscal_entrada                      = $request->id_nota_fiscal_entrada;

            $checklist->save();

            db::commit();
            return redirect()->route('admin.notafiscalentrada.index')->with('success', 'Nota Fiscal de Entrada cadastrada com sucesso');
        } catch (\Exception $e) {
            db::rollBack();
            LOG::ERROR('Erro ao cadastrar Checklist de Recebimento de Fornecedor: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Não foi possível realizar o cadastro');
        }
    }
}
