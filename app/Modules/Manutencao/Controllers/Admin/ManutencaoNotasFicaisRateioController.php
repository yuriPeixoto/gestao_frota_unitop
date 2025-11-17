<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Models\Fornecedor;
use App\Models\NotaFiscalServico;
use App\Models\NotaFiscalServicoItens;
use App\Models\Servico;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ManutencaoNotasFicaisRateioController extends Controller
{
    public function index(Request $request)
    {
        $query = NotaFiscalServico::query()
            ->with('fornecedor');


        if ($request->filled('id_nota_fiscal_servico')) {
            $query->where('id_nota_fiscal_servico', $request->id_nota_fiscal_servico);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        $cadastros = $query->latest('id_nota_fiscal_servico')
            ->paginate(10);

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        return view('admin.listagemoslacamentoservicorateio.index', compact(
            'cadastros',
            'fornecedoresFrequentes'
        ));
    }

    public function create()
    {
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();

        return view('admin.listagemoslacamentoservicorateio.create', compact(
            'fornecedoresFrequentes',
            'servicosFrequentes',
        ));
    }

    public function edit($id)
    {
        $cadastros = NotaFiscalServico::where('id_nota_fiscal_servico', $id)
            ->with('servicos', 'servicos.servico')
            ->first();

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $servicosFrequentes = $this->getServicosFrequentes();

        return view('admin.listagemoslacamentoservicorateio.edit', compact(
            'cadastros',
            'fornecedoresFrequentes',
            'servicosFrequentes',
        ));
    }


    public function store(Request $request)
    {
        $nfServico = $request->validate([
            'id_fornecedor'         => 'required|string',
            'numero_serie'          => 'required|string',
            'numero_nota_fiscal'    => 'required|string',
            'valor_total_servico'   => 'required|string',
            'rateio_nf'             => 'required|string',
            'data_servico'          => 'required|string',
        ]);

        $nfServico['data_inclusao'] = now();

        $servicos = json_decode($request->input('servicos'), true);

        try {
            DB::beginTransaction();

            $servicoCreate = NotaFiscalServico::create($nfServico);

            foreach ($servicos as $servico) {
                NotaFiscalServicoItens::create([
                    'data_inclusao'          => now(),
                    'id_servico'             => $servico['id_servico'],
                    'id_nota_fiscal_servico' => $servicoCreate->id_nota_fiscal_servico,
                    'quantidade'             => $servico['quantidade'],
                    'valor_produto'          => $servico['valor_produto'],
                    'total_produto'          => $servico['total_produto'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.listagemoslacamentoservicorateio.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'NF Rateio cadastrada com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação da NF Rateio:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.listagemoslacamentoservicorateio.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar a NF Rateio."
                ]);
        }
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());

        $nfServico = $request->validate([
            'id_fornecedor'         => 'required|string',
            'numero_serie'          => 'required|string',
            'numero_nota_fiscal'    => 'required|string',
            'valor_total_servico'   => 'required|string',
            'rateio_nf'             => 'required|string',
            'data_servico'          => 'required|string',
        ]);

        $nfServico['data_alteracao'] = now();

        $servicos = json_decode($request->input('servicos'), true);

        try {
            DB::beginTransaction();

            $servicoUpdate = NotaFiscalServico::where('id_nota_fiscal_servico', $id)->first();
            $servicoUpdate->update($nfServico);

            NotaFiscalServicoItens::where('id_nota_fiscal_servico', $id)->delete();

            foreach ($servicos as $servico) {
                NotaFiscalServicoItens::create([
                    'data_inclusao'          => now(),
                    'id_servico'             => $servico['id_servico'],
                    'id_nota_fiscal_servico' => $servicoUpdate->id_nota_fiscal_servico,
                    'quantidade'             => $servico['quantidade'] ?? 0,
                    'valor_produto'          => $servico['valor_produto'] ?? 0,
                    'total_produto'          => $servico['total_produto'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.listagemoslacamentoservicorateio.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'NF Rateio editada com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            $e->getMessage();
            Log::error('Erro na criação da NF Rateio:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.listagemoslacamentoservicorateio.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar a NF Rateio."
                ]);
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Primeiro, deleta os itens relacionados
            NotaFiscalServicoItens::where('id_nota_fiscal_servico', $id)->delete();

            // Depois, deleta a nota fiscal
            NotaFiscalServico::where('id_nota_fiscal_servico', $id)->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getFornecedoresFrequentes()
    {
        $fornecedores =  Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                ->orderBy('nome_fornecedor')
                ->limit(20)
                ->get();
        });

        return $fornecedores;
    }

    public function getServicosFrequentes()
    {
        $servicos =  Cache::remember('servicos_frequentes', now()->addHours(12), function () {
            return Servico::select('id_servico as value', 'descricao_servico as label')
                ->orderBy('id_servico')
                ->limit(20)
                ->get();
        });

        return $servicos;
    }
}
