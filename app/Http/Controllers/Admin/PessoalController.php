<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Endereco;
use App\Models\Estado;
use App\Models\Filial;
use App\Models\Municipio;
use App\Models\Pessoal;
use App\Models\Rotas;
use App\Models\Telefone;
use App\Models\TipoPessoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PessoalController extends Controller
{
    public function index(Request $request)
    {
        $query = Pessoal::query();

        // Aplicação dos filtros da busca
        if ($request->filled('id_pessoal')) {
            $query->where('id_pessoal', $request->id_pessoal);
        }

        if ($request->filled('nome')) {
            $query->where('nome', 'ilike', '%' . $request->nome . '%');
        }

        // Ordenação padrão e paginação
        $pessoas = $query->orderByDesc('id_pessoal')
            ->paginate(40)
            ->appends($request->query());

        // Responder apenas com a tabela se for uma requisição HTMX
        if ($request->header('HX-Request')) {
            return view('admin.pessoas._table', compact('pessoas'));
        }

        return view('admin.pessoas.index', compact('pessoas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Preenchimento do Select Tipo Pessoas
        $tipopessoas = TipoPessoal::select('id_tipo_pessoal as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo')
            ->get()
            ->toArray();

        // Preenchimento do Select Departamento
        $departamentos = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->get()
            ->toArray();

        $estados = Estado::orderBy('uf')->get();

        $filiais = Filial::select('name as label', 'id as value')
            ->orderBy('label')
            ->get()
            ->toArray();

        $rotas = Rotas::selectRaw('MIN(id_rotas) as value, destino as label')
            ->whereNotNull('destino')
            ->groupBy('destino')
            ->orderBy('destino')
            ->get();

        return view('admin.pessoas.create', compact('tipopessoas', 'departamentos', 'estados', 'filiais', 'rotas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validação dos campos com mensagens personalizadas
            $validatedData = $request->validate([
                'nome' => 'required|string|max:500',
                'rg' => 'nullable|string|max:20',
                'cpf' => 'required|string|max:14',
                'data_nascimento' => 'required|date',
                'cnh' => 'nullable|string|max:20',
                'validade_cnh' => 'nullable|date',
                'tipo_cnh' => 'nullable|string|max:10',
                'id_tipo_pessoal' => 'required|integer',
                'id_departamento' => 'required|integer',
                'id_filial' => 'required',
                'id_rota' => 'nullable|integer',
                'email' => 'nullable|email|max:500',
                'orgao_emissor' => 'nullable|string|max:25',
                'data_admissao' => 'nullable|date',
                'ativo' => 'nullable',
                'pis' => 'nullable|string|max:11',
                'matricula' => ['nullable', 'regex:/^\\d+$/', 'max:20'],
                'imagem_pessoal' => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            ], [
                'nome.required' => 'O nome é obrigatório',
                'nome.max' => 'O nome não pode ter mais de 500 caracteres',
                'rg.max' => 'O RG não pode ter mais de 20 caracteres',
                'cpf.required' => 'O CPF é obrigatório',
                'cpf.max' => 'O CPF não pode ter mais de 14 caracteres',
                'data_nascimento.required' => 'A data de nascimento é obrigatória',
                'data_nascimento.date' => 'A data de nascimento deve ser uma data válida',
                'cnh.max' => 'A CNH não pode ter mais de 20 caracteres',
                'validade_cnh.date' => 'A validade da CNH deve ser uma data válida',
                'tipo_cnh.max' => 'O tipo de CNH não pode ter mais de 10 caracteres',
                'id_tipo_pessoal.required' => 'O tipo de pessoa é obrigatório',
                'id_departamento.required' => 'O departamento é obrigatório',
                'id_filial.required' => 'A filial é obrigatória',
                'email.email' => 'O e-mail deve ser um endereço válido',
                'email.max' => 'O e-mail não pode ter mais de 500 caracteres',
                'orgao_emissor.max' => 'O órgão emissor não pode ter mais de 25 caracteres',
                'data_admissao.date' => 'A data de admissão deve ser uma data válida',
                'pis.max' => 'O PIS não pode ter mais de 11 caracteres',
                'matricula.max' => 'A matrícula não pode ter mais de 20 caracteres',
                'imagem_pessoal.image' => 'O arquivo deve ser uma imagem',
                'imagem_pessoal.mimes' => 'A imagem deve ser do tipo: jpeg, jpg, png',
                'imagem_pessoal.max' => 'A imagem não pode ter mais de 1MB',
            ]);

            DB::beginTransaction();

            // Upload da imagem
            $fotoPath = null;
            if ($request->hasFile('imagem_pessoal') && $request->file('imagem_pessoal')->isValid()) {
                // Garantir que o diretório exista
                if (! file_exists(storage_path('app/public/avatars'))) {
                    mkdir(storage_path('app/public/avatars'), 0755, true);
                }

                $file = $request->file('imagem_pessoal');
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(storage_path('app/public/avatars'), $filename);
                $fotoPath = 'avatars/' . $filename;
            }

            // Criar pessoa
            $pessoa = new Pessoal;
            $pessoa->nome = $validatedData['nome'];
            $pessoa->rg = $validatedData['rg'];
            $pessoa->cpf = $validatedData['cpf'];
            $pessoa->data_nascimento = $validatedData['data_nascimento'];
            $pessoa->cnh = $validatedData['cnh'] ?? null;
            $pessoa->validade_cnh = $validatedData['validade_cnh'] ?? null;
            $pessoa->tipo_cnh = $validatedData['tipo_cnh'] ?? null;
            $pessoa->id_tipo_pessoal = $validatedData['id_tipo_pessoal'];
            $pessoa->id_departamento = $validatedData['id_departamento'];
            $pessoa->id_filial = $validatedData['id_filial'];
            $pessoa->id_rota = $validatedData['id_rota'];
            $pessoa->email = $validatedData['email'] ?? null;
            $pessoa->orgao_emissor = $validatedData['orgao_emissor'] ?? null;
            $pessoa->data_admissao = $validatedData['data_admissao'] ?? null;
            $pessoa->ativo = $request->has('ativo') ? 1 : 0;
            $pessoa->pis = $validatedData['pis'] ?? null;
            $pessoa->matricula = $validatedData['matricula'] ?? null;
            $pessoa->imagem_pessoal = $fotoPath;
            $pessoa->data_inclusao = now();

            $pessoa->save();

            // Processar endereço
            if ($request->filled('cep') || $request->filled('rua')) {
                $endereco = new Endereco;
                $endereco->rua = $request->rua;
                $endereco->cep = $request->cep;
                $endereco->complemento = $request->complemento;
                $endereco->numero = $request->numero;
                $endereco->bairro = $request->bairro;
                $endereco->id_pessoal_endereco = $pessoa->id_pessoal;
                $endereco->data_inclusao = now();

                // Buscar município por nome
                if ($request->filled('nome_municipio')) {
                    $municipio = Municipio::whereRaw('LOWER(nome_municipio) ILIKE ?', [strtolower($request->nome_municipio)])
                        ->first();

                    if ($municipio) {
                        $endereco->id_municipio = $municipio->id_municipio;
                    }
                }

                // Adicionar UF
                if ($request->filled('id_uf')) {
                    $estado = Estado::where('uf', $request->id_uf)->first();
                    if ($estado) {
                        $endereco->id_uf = $estado->id_uf;
                    }
                }

                $endereco->save();
            }

            // Processar telefone
            if ($request->filled('telefone_fixo') || $request->filled('telefone_celular')) {
                $telefone = new Telefone;
                $telefone->telefone_fixo = $request->telefone_fixo;
                $telefone->telefone_celular = $request->telefone_celular;
                $telefone->id_pessoal = $pessoa->id_pessoal;
                $telefone->data_inclusao = now();
                $telefone->save();
            }

            DB::commit();

            return redirect()->route('admin.pessoas.index')
                ->with('success', 'Pessoa cadastrada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Para exceções de validação, queremos retornar à view com os erros e inputs antigos
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar pessoa: ' . $e->getMessage());

            // Para outras exceções, retornar com mensagem de erro
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar pessoa: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Buscar pessoa com seus relacionamentos
        $pessoas = Pessoal::with(['endereco.municipio', 'telefone'])->findOrFail($id);

        // Preenchimento do Select Tipo Pessoas
        $tipopessoas = TipoPessoal::select('id_tipo_pessoal as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo')
            ->get()
            ->toArray();

        // Preenchimento do Select Departamento
        $departamentos = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->get()
            ->toArray();

        $estados = Estado::orderBy('uf')->get();

        $filiais = Filial::select('name as label', 'id as value')
            ->orderBy('label')
            ->get()
            ->toArray();

        $rotas = Rotas::selectRaw('MIN(id_rotas) as value, destino as label')
            ->whereNotNull('destino')
            ->groupBy('destino')
            ->orderBy('destino')
            ->get();

        return view('admin.pessoas.edit', compact('pessoas', 'tipopessoas', 'departamentos', 'estados', 'filiais', 'rotas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validação dos campos com mensagens personalizadas
            $validatedData = $request->validate([
                'nome' => 'required|string|max:500',
                'rg' => 'nullable|string|max:20',
                'cpf' => 'required|string|max:14',
                'data_nascimento' => 'required|date',
                'cnh' => 'nullable|string|max:20',
                'validade_cnh' => 'nullable|date',
                'tipo_cnh' => 'nullable|string|max:10',
                'id_tipo_pessoal' => 'required|integer',
                'id_departamento' => 'required|integer',
                'id_filial' => 'required',
                'email' => 'nullable|email|max:500',
                'orgao_emissor' => 'nullable|string|max:25',
                'data_admissao' => 'nullable|date',
                'ativo' => 'nullable',
                'pis' => 'nullable|string|max:11',
                'matricula' => ['nullable', 'regex:/^\\d+$/', 'max:20'],
                'imagem_pessoal' => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            ], [
                'nome.required' => 'O nome é obrigatório',
                'nome.max' => 'O nome não pode ter mais de 500 caracteres',
                'rg.max' => 'O RG não pode ter mais de 20 caracteres',
                'cpf.required' => 'O CPF é obrigatório',
                'cpf.max' => 'O CPF não pode ter mais de 14 caracteres',
                'data_nascimento.required' => 'A data de nascimento é obrigatória',
                'data_nascimento.date' => 'A data de nascimento deve ser uma data válida',
                'cnh.max' => 'A CNH não pode ter mais de 20 caracteres',
                'validade_cnh.date' => 'A validade da CNH deve ser uma data válida',
                'tipo_cnh.max' => 'O tipo de CNH não pode ter mais de 10 caracteres',
                'id_tipo_pessoal.required' => 'O tipo de pessoa é obrigatório',
                'id_departamento.required' => 'O departamento é obrigatório',
                'id_filial.required' => 'A filial é obrigatória',
                'email.email' => 'O e-mail deve ser um endereço válido',
                'email.max' => 'O e-mail não pode ter mais de 500 caracteres',
                'orgao_emissor.max' => 'O órgão emissor não pode ter mais de 25 caracteres',
                'data_admissao.date' => 'A data de admissão deve ser uma data válida',
                'pis.max' => 'O PIS não pode ter mais de 11 caracteres',
                'matricula.max' => 'A matrícula não pode ter mais de 20 caracteres',
                'imagem_pessoal.image' => 'O arquivo deve ser uma imagem',
                'imagem_pessoal.mimes' => 'A imagem deve ser do tipo: jpeg, jpg, png',
                'imagem_pessoal.max' => 'A imagem não pode ter mais de 1MB',
            ]);

            DB::beginTransaction();

            // Buscar pessoa
            $pessoa = Pessoal::findOrFail($id);

            // Upload da imagem
            if ($request->hasFile('imagem_pessoal') && $request->file('imagem_pessoal')->isValid()) {
                // Garantir que o diretório exista
                if (! file_exists(storage_path('app/public/avatars'))) {
                    mkdir(storage_path('app/public/avatars'), 0755, true);
                }

                // Remover avatar antigo se existir
                if ($pessoa->imagem_pessoal && file_exists(storage_path('app/public/' . $pessoa->imagem_pessoal))) {
                    unlink(storage_path('app/public/' . $pessoa->imagem_pessoal));
                }

                $file = $request->file('imagem_pessoal');
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(storage_path('app/public/avatars'), $filename);
                $fotoPath = 'avatars/' . $filename;

                // Atualizar caminho
                $pessoa->imagem_pessoal = $fotoPath;
            }

            // Atualizar dados da pessoa
            $pessoa->nome = $validatedData['nome'];
            $pessoa->rg = $validatedData['rg'];
            $pessoa->cpf = $validatedData['cpf'];
            $pessoa->data_nascimento = $validatedData['data_nascimento'];
            $pessoa->cnh = $validatedData['cnh'] ?? null;
            $pessoa->validade_cnh = $validatedData['validade_cnh'] ?? null;
            $pessoa->tipo_cnh = $validatedData['tipo_cnh'] ?? null;
            $pessoa->id_tipo_pessoal = $validatedData['id_tipo_pessoal'];
            $pessoa->id_departamento = $validatedData['id_departamento'];
            $pessoa->id_filial = $validatedData['id_filial'];
            $pessoa->email = $validatedData['email'] ?? null;
            $pessoa->orgao_emissor = $validatedData['orgao_emissor'] ?? null;
            $pessoa->data_admissao = $validatedData['data_admissao'] ?? null;
            $pessoa->ativo = $request->has('ativo') ? 1 : 0;
            $pessoa->pis = $validatedData['pis'] ?? null;
            $pessoa->matricula = $validatedData['matricula'] ?? null;
            $pessoa->data_alteracao = now();

            $pessoa->save();

            // Atualizar ou criar endereço
            if ($request->filled('cep') || $request->filled('rua')) {
                $endereco = Endereco::where('id_pessoal_endereco', $id)->first();

                if (! $endereco) {
                    $endereco = new Endereco;
                    $endereco->id_pessoal_endereco = $pessoa->id_pessoal;
                    $endereco->data_inclusao = now();
                } else {
                    $endereco->data_alteracao = now();
                }

                $endereco->rua = $request->rua;
                $endereco->cep = $request->cep;
                $endereco->complemento = $request->complemento;
                $endereco->numero = $request->numero;
                $endereco->bairro = $request->bairro;

                // Buscar município por nome
                if ($request->filled('nome_municipio')) {
                    $municipio = Municipio::whereRaw('LOWER(nome_municipio) ILIKE ?', [strtolower($request->nome_municipio)])
                        ->first();

                    if ($municipio) {
                        $endereco->id_municipio = $municipio->id_municipio;
                    }
                }

                // Adicionar UF
                if ($request->filled('id_uf')) {
                    $estado = Estado::where('uf', $request->id_uf)->first();
                    if ($estado) {
                        $endereco->id_uf = $estado->id_uf;
                    }
                }

                $endereco->save();
            }

            // Atualizar ou criar telefone
            if ($request->filled('telefone_fixo') || $request->filled('telefone_celular')) {
                $telefone = Telefone::where('id_pessoal', $id)->first();

                if (! $telefone) {
                    $telefone = new Telefone;
                    $telefone->id_pessoal = $pessoa->id_pessoal;
                    $telefone->data_inclusao = now();
                } else {
                    $telefone->data_alteracao = now();
                }

                $telefone->telefone_fixo = $request->telefone_fixo;
                $telefone->telefone_celular = $request->telefone_celular;
                $telefone->save();
            }

            DB::commit();

            return redirect()->route('admin.pessoas.index')
                ->with('success', 'Pessoa atualizada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Para exceções de validação, queremos retornar à view com os erros e inputs antigos
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar pessoa: ' . $e->getMessage());

            // Para outras exceções, retornar com mensagem de erro
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar pessoa: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            // Remover registros relacionados primeiro
            Telefone::where('id_pessoal', $id)->delete();
            Endereco::where('id_pessoal_endereco', $id)->delete();
            Pessoal::where('id_pessoal', $id)->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir pessoa: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Busca de CEP via API
     */
    public function buscarCep(Request $request)
    {
        try {
            $cep = preg_replace('/[^0-9]/', '', $request->cep);

            if (strlen($cep) != 8) {
                return response()->json(['error' => 'CEP inválido'], 400);
            }

            // Buscar CEP na API ViaCEP
            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/")->json();

            if (isset($response['erro']) && $response['erro']) {
                return response()->json(['error' => 'CEP não encontrado'], 404);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar CEP: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao buscar CEP'], 500);
        }
    }

    /**
     * Busca de pessoas para autocomplete
     */
    public function search(Request $request)
    {
        // Compatibilidade com diferentes parâmetros (term para select2, search para smart-select)
        $term = strtolower($request->get('term') ?? $request->get('search', ''));

        if (strlen($term) < 3) {
            return response()->json([
                'results' => [],
            ]);
        }

        $pessoas = Cache::remember('pessoal_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return Pessoal::whereRaw('LOWER(nome) LIKE ?', ["%{$term}%"])
                ->where('ativo', true)
                ->orderBy('nome')
                ->limit(30)
                ->get(['id_pessoal as value', 'nome as label']);
        });

        // Formato para o smart-select
        return response()->json($pessoas);
    }

    /**
     * Busca de pessoa por ID
     */
    public function getById($id)
    {
        $pessoa = Cache::remember('pessoal_' . $id, now()->addHours(24), function () use ($id) {
            return Pessoal::findOrFail($id);
        });

        return response()->json([
            'value' => $pessoa->id_pessoal,
            'label' => $pessoa->nome,
        ]);
    }
}
