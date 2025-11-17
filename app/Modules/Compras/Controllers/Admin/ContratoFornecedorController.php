<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContratoFornecedor;
use App\Models\Fornecedor;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContratoFornecedorController extends Controller
{
    /**
     * Converte valor monetário brasileiro para formato decimal
     * Ex: "R$ 1.000,50" -> 1000.50
     */
    private function converterMoedaBrasileiraParaDecimal($valor)
    {
        // Remove todos os caracteres que não são dígitos, vírgula ou ponto
        $valor = preg_replace('/[^0-9.,]/', '', $valor);

        // Se estiver vazio, retorna 0
        if (empty($valor)) {
            return 0;
        }

        // Se contém vírgula e ponto, assume que a vírgula é o separador decimal
        if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
            // Remove pontos (separadores de milhares) e substitui vírgula por ponto
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (strpos($valor, ',') !== false) {
            // Apenas vírgula presente - assumir como separador decimal
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContratoFornecedor::query()
            ->where('is_valido', true);

        if ($request->filled('id_contrato_forn')) {
            $query->where('id_contrato_forn', $request->id_contrato_forn);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        if ($request->filled('data_inicio')) {
            $query->where('data_inicial', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data_final', '<=', $request->data_fim);
        }

        if ($request->filled('is_valido')) {
            $isValido = $request->is_valido === 'true' || $request->is_valido === '1';
            $query->where('is_valido', $isValido);
        }

        // Adicione o fornecedor via relacionamento eager loading
        $contratos = $query->with(['fornecedor', 'userCadastro'])
            ->orderBy('id_contrato_forn', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Se for uma requisição HTMX, retorne apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.contratos._table', compact('contratos'));
        }

        // Obter fornecedores para o filtro de pesquisa
        $fornecedores = Fornecedor::orderBy('nome_fornecedor')
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);

        return view('admin.contratos.index', compact('contratos', 'fornecedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Se não for uma edição específica para um fornecedor, obter todos os fornecedores
        $fornecedores = Fornecedor::orderBy('nome_fornecedor')
            ->limit(100) // Limitar para evitar sobrecarga inicial
            ->get(['id_fornecedor as value', 'nome_fornecedor as label']);


        return view('admin.contratos.create', compact('fornecedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validação dos dados do contrato
            $validated = $request->validate([
                'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
                'data_inicial' => 'required|date',
                'data_final' => 'required|date|after_or_equal:data_inicial',
                'valor_contrato' => 'required|string',
                'is_valido' => 'required|boolean',
                'arquivo' => 'nullable|file|mimes:pdf,doc,docx,jpeg,jpg,png|max:10240',
            ]);

            // Converter o valor monetário brasileiro para formato decimal
            $valorContrato = $this->converterMoedaBrasileiraParaDecimal($validated['valor_contrato']);

            DB::beginTransaction();

            // Criar o contrato
            $contrato = new ContratoFornecedor();
            $contrato->id_fornecedor = $validated['id_fornecedor'];
            $contrato->data_inicial = $validated['data_inicial'];
            $contrato->data_final = $validated['data_final'];
            $contrato->valor_contrato = $valorContrato;
            $contrato->is_valido = $validated['is_valido'];
            $contrato->id_user_cadastro = Auth::id();

            // Processar o documento do contrato, se enviado
            if ($request->hasFile('arquivo')) {
                $file = $request->file('arquivo');
                $fileName = time() . '_' . $file->getClientOriginalName();

                // Armazenar o arquivo em um diretório específico para contratos
                $path = $file->storeAs('contratos', $fileName, 'public');
                $contrato->doc_contrato = $path;
            }

            $contrato->save();

            // Inicializar o saldo do contrato com o valor total
            $contrato->saldo_contrato = $valorContrato;
            $contrato->save();

            DB::commit();

            // Redirecionar com base na origem da requisição
            $redirectRoute = $request->input('redirect_to_fornecedor')
                ? route('admin.fornecedores.edit', $contrato->id_fornecedor)
                : route('admin.contratos.index');

            return redirect($redirectRoute)
                ->with('success', 'Contrato cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar contrato: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar contrato: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contrato = ContratoFornecedor::with([
            'fornecedor',
            'userCadastro',
            'modelos.modelo',
            'servicosFornecedor',
            'pecasFornecedor'
        ])->findOrFail($id);

        return view('admin.contratos.show', compact('contrato'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contrato = ContratoFornecedor::findOrFail($id);

        // Obter o fornecedor deste contrato
        $fornecedor = $contrato->fornecedor;

        // Para o select de fornecedores
        $fornecedores = [
            ['value' => $fornecedor->id_fornecedor, 'label' => $fornecedor->nome_fornecedor]
        ];

        return view('admin.contratos.edit', compact('contrato', 'fornecedores', 'fornecedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validação dos dados do contrato
            $validated = $request->validate([
                'data_inicial' => 'required|date',
                'data_final' => 'required|date|after_or_equal:data_inicial',
                'valor_contrato' => 'required|string',
                'is_valido' => 'required|boolean',
                'arquivo' => 'nullable|file|mimes:pdf,doc,docx,jpeg,jpg,png|max:10240',
            ]);

            // Converter o valor monetário brasileiro para formato decimal
            $valorContrato = $this->converterMoedaBrasileiraParaDecimal($validated['valor_contrato']);

            DB::beginTransaction();

            // Buscar o contrato existente
            $contrato = ContratoFornecedor::findOrFail($id);

            // Atualizar os campos
            $contrato->data_inicial = $validated['data_inicial'];
            $contrato->data_final = $validated['data_final'];
            $contrato->valor_contrato = $valorContrato;
            $contrato->is_valido = $validated['is_valido'];

            // Se não havia saldo anteriormente ou se for para recalcular o saldo
            if ($contrato->saldo_contrato === null || $request->has('recalcular_saldo')) {
                $contrato->saldo_contrato = $valorContrato;
            }

            // Processar o documento do contrato, se enviado
            if ($request->hasFile('arquivo')) {
                // Se já existe um arquivo, excluir o anterior
                if ($contrato->doc_contrato) {
                    Storage::disk('public')->delete($contrato->doc_contrato);
                }

                $file = $request->file('arquivo');
                $fileName = time() . '_' . $file->getClientOriginalName();

                // Armazenar o arquivo em um diretório específico para contratos
                $path = $file->storeAs('contratos', $fileName, 'public');
                $contrato->doc_contrato = $path;
            }

            $contrato->save();

            DB::commit();

            // Redirecionar com base na origem da requisição
            $redirectRoute = $request->input('redirect_to_fornecedor')
                ? route('admin.fornecedores.edit', $contrato->id_fornecedor)
                : route('admin.contratos.index');

            return redirect($redirectRoute)
                ->with('success', 'Contrato atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar contrato: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar contrato: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $contrato = ContratoFornecedor::findOrFail($id);

            // Verificar se existem relações que impediriam a exclusão
            $hasModelos = $contrato->modelos()->count() > 0;
            $hasServicos = $contrato->servicosFornecedor()->count() > 0;
            $hasPecas = $contrato->pecasFornecedor()->count() > 0;
            $hasPedidos = $contrato->pedidosCompra()->count() > 0;

            if ($hasModelos || $hasServicos || $hasPecas || $hasPedidos) {
                throw new \Exception('Não é possível excluir este contrato pois existem registros dependentes associados.');
            }

            // Se houver documento, excluir o arquivo
            if ($contrato->doc_contrato) {
                Storage::disk('public')->delete($contrato->doc_contrato);
            }

            // Excluir o contrato
            $contrato->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir contrato: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Download do documento do contrato
     */
    public function downloadDocumento(string $id)
    {
        $contrato = ContratoFornecedor::findOrFail($id);

        if (!$contrato->doc_contrato) {
            return redirect()
                ->back()
                ->with('error', 'Este contrato não possui documento anexado.');
        }

        // Verificar se o arquivo existe
        if (!Storage::disk('public')->exists($contrato->doc_contrato)) {
            return redirect()
                ->back()
                ->with('error', 'O arquivo do contrato não foi encontrado.');
        }

        // Obter o caminho completo do arquivo
        $filePath = Storage::disk('public')->path($contrato->doc_contrato);
        $filename = basename($contrato->doc_contrato);

        return response()->download($filePath, $filename);
    }

    /**
     * Listar contratos de um fornecedor específico para integração com a tela de fornecedor
     */
    public function listarPorFornecedor(Request $request, $fornecedorId)
    {
        $contratos = ContratoFornecedor::where('id_fornecedor', $fornecedorId)
            ->with(['userCadastro'])
            ->orderBy('id_contrato_forn', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contratos
        ]);
    }

    /**
     * Clonar um contrato existente
     */
    public function clonar(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Buscar o contrato a ser clonado
            $contratoOriginal = ContratoFornecedor::findOrFail($id);

            // Criar um novo contrato com os mesmos dados
            $novoContrato = $contratoOriginal->replicate();
            $novoContrato->id_user_cadastro = Auth::id();
            $novoContrato->data_inclusao = now();
            $novoContrato->data_alteracao = null;

            // Ajustar datas se solicitado
            if ($request->has('ajustar_datas')) {
                $novoContrato->data_inicial = now();
                $novoContrato->data_final = now()->addYear();
            }

            // Salvar o novo contrato
            $novoContrato->save();

            // Clonar o documento se existir
            if ($contratoOriginal->doc_contrato && Storage::disk('public')->exists($contratoOriginal->doc_contrato)) {
                $extension = pathinfo($contratoOriginal->doc_contrato, PATHINFO_EXTENSION);
                $newFileName = 'contratos/' . time() . '_copia_' . $contratoOriginal->id_contrato_forn . '.' . $extension;

                // Copiar o arquivo para um novo local
                if (Storage::disk('public')->copy($contratoOriginal->doc_contrato, $newFileName)) {
                    $novoContrato->doc_contrato = $newFileName;
                    $novoContrato->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato clonado com sucesso!',
                'id' => $novoContrato->id_contrato_forn
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar contrato: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao clonar contrato: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Atualizar o saldo do contrato
     */
    public function atualizarSaldo(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'valor_ajuste' => 'required|numeric',
                'tipo_ajuste' => 'required|in:adicionar,subtrair,definir',
                'justificativa' => 'required|string|min:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $contrato = ContratoFornecedor::findOrFail($id);
            $valorAjuste = (float) $request->valor_ajuste;
            $tipoAjuste = $request->tipo_ajuste;

            // Calcular o novo saldo
            $saldoAnterior = (float) $contrato->saldo_contrato;
            $novoSaldo = $saldoAnterior;

            switch ($tipoAjuste) {
                case 'adicionar':
                    $novoSaldo = $saldoAnterior + $valorAjuste;
                    break;
                case 'subtrair':
                    $novoSaldo = $saldoAnterior - $valorAjuste;
                    break;
                case 'definir':
                    $novoSaldo = $valorAjuste;
                    break;
            }

            // Não permitir saldo negativo
            if ($novoSaldo < 0) {
                throw new \Exception('O saldo do contrato não pode ficar negativo.');
            }

            // Atualizar o saldo
            $contrato->saldo_contrato = $novoSaldo;
            $contrato->save();

            // Registrar o ajuste (se houver tabela para isso)
            // ...

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Saldo atualizado com sucesso!',
                'saldo_anterior' => $saldoAnterior,
                'saldo_atual' => $novoSaldo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar saldo do contrato: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
