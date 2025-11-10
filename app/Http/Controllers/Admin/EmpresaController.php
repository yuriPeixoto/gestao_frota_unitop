<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    /**
     * Gera o array de campos do formulário dinamicamente.
     *
     * @return array O array de campos configurados.
     */
    private function getFormFields(): array
    {

        $data = [
            'cnpj' => ['label' => 'CNPJ', 'type' => 'text', 'maxlength' => 18],
            'nomefantasia' => ['label' => 'Nome Fantasia', 'type' => 'text'],
            'sigla' => ['label' => 'Sigla', 'type' => 'text'],
            'email' => ['label' => 'Email', 'type' => 'email'],
            'telefone' => ['label' => 'Telefone', 'type' => 'text'],
            'razaosocial' => ['label' => 'Razão Social', 'type' => 'text'],
            'inscricaoestadual' => ['label' => 'Inscrição Estadual', 'type' => 'text', 'maxlength' => 20],
            'inscricaomunicipal' => ['label' => 'Inscrição Municipal', 'type' => 'text', 'maxlength' => 20],
            'municipio' => ['label' => 'Município', 'type' => 'text'],
            'cep' => ['label' => 'CEP', 'type' => 'text'],
            'bairro' => ['label' => 'Bairro', 'type' => 'text'],
            'numero' => ['label' => 'Número', 'type' => 'number'],
            'logradouro' => ['label' => 'Logradouro', 'type' => 'text'],
            'uf' => [
                'label' => 'UF',
                'type' => 'select',
                'options' => [
                    ['label' => 'AC', 'value' => 'AC'],
                    ['label' => 'AL', 'value' => 'AL'],
                    ['label' => 'AP', 'value' => 'AP'],
                    ['label' => 'AM', 'value' => 'AM'],
                    ['label' => 'BA', 'value' => 'BA'],
                    ['label' => 'CE', 'value' => 'CE'],
                    ['label' => 'DF', 'value' => 'DF'],
                    ['label' => 'ES', 'value' => 'ES'],
                    ['label' => 'GO', 'value' => 'GO'],
                    ['label' => 'MA', 'value' => 'MA'],
                    ['label' => 'MT', 'value' => 'MT'],
                    ['label' => 'MS', 'value' => 'MS'],
                    ['label' => 'MG', 'value' => 'MG'],
                    ['label' => 'PA', 'value' => 'PA'],
                    ['label' => 'PB', 'value' => 'PB'],
                    ['label' => 'PR', 'value' => 'PR'],
                    ['label' => 'PE', 'value' => 'PE'],
                    ['label' => 'PI', 'value' => 'PI'],
                    ['label' => 'RJ', 'value' => 'RJ'],
                    ['label' => 'RN', 'value' => 'RN'],
                    ['label' => 'RS', 'value' => 'RS'],
                    ['label' => 'RO', 'value' => 'RO'],
                    ['label' => 'RR', 'value' => 'RR'],
                    ['label' => 'SC', 'value' => 'SC'],
                    ['label' => 'SP', 'value' => 'SP'],
                    ['label' => 'SE', 'value' => 'SE'],
                    ['label' => 'TO', 'value' => 'TO'],
                ]
            ],
            'matriz' => [
                'label' => 'Matriz',
                'type' => 'radio',
                'options' => [
                    ['value' => '1', 'label' => 'Matriz'],
                    ['value' => '0', 'label' => 'Filial'],
                ],
            ],

            'rntrc' => ['label' => 'RNTRC', 'type' => 'number'],
            'situacao_rntrc' => ['label' => 'Situação RNTRC', 'type' => 'text', 'maxlength' => 200],
            'municipio_uf_rntrc' => ['label' => 'Município/UF RNTRC', 'type' => 'text', 'maxlength' => 200],
            'data_cadastro_rntrc' => ['label' => 'Data Cadastro RNTRC', 'type' => 'date'],
            'tipo_transporte_rntrc' => ['label' => 'Tipo Transporte RNTRC', 'type' => 'text'],
            'status' => [
                'label' => 'Status Empresa',
                'type' => 'radio',
                'options' => [
                    ['value' => '1', 'label' => 'Ativa'],
                    ['value' => '0', 'label' => 'Inativa'],
                ],
            ],
        ];

        /**
         * Caso haja uma matriz ja cadastrada na tabela, não será possivel cadastrar mais uma.
         */

        $temMatrizCadastrada = Empresa::where('matriz', true)->exists();

        if ($temMatrizCadastrada) {
            unset($data['matriz']);
        }

        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresas = Empresa::orderBy('idempresa', 'asc')->get();

        $empresasData = $empresas->map(function ($empresa) {
            return [
                'id'           => $empresa->idempresa,
                'apelido'      => $empresa->apelido,
                'matriz'       => $empresa->matriz ? 'Sim' : 'Não',
                'filial'       => $empresa->filial ? 'Sim' : 'Não',
                'status'       => $empresa->status ? 'Ativo' : 'Inativo',
                'razaosocial'  => $empresa->razaosocial,
                'sigla'        => $empresa->sigla,
                'cnpj'         => $empresa->cnpj,
                'email'        => $empresa->email,
                'uf'           => $empresa->uf,
                'município'    => $empresa->municipio
            ];
        })->toArray();

        return view('admin.empresas.index', compact('empresas', 'empresasData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = $this->getFormFields();
        return view('admin.empresas.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cnpj' => 'required|string|max:18',
            'razaosocial' => 'required|string|max:500',
            'sigla' => 'string|max:30',
            'email' => 'string|email',
            'telefone' => 'string|max:30',
            'nomefantasia' => 'string|max:500',
            'inscricaoestadual' => 'nullable|string|max:500',
            'inscricaomunicipal' => 'nullable|string|max:500',
            'municipio' => 'string|max:500',
            'cep' => 'string|max:9',
            'bairro' => 'string|max:500',
            'numero' => 'string|max:500',
            'logradouro' => 'string|max:500',
            'uf' => 'required|string|size:2',
            'matriz' => 'boolean',
            'status' => 'required|boolean',
            'logo'  => 'nullable|image|max:1024',
            'rntrc'  => 'nullable|integer',
            'situacao_rntrc'  => 'nullable|string|max:500',
            'municipio_uf_rntrc'  => 'nullable|string|max:500',
            'data_cadastro_rntrc'  => 'nullable|date',
            'tipo_transporte_rntrc' => 'nullable|string|max:500'
        ]);

        // Upload da imagem
        $fotoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $fotoPath = $request->file('logo')->store('logos', 'public');
        }

        $empresa = new Empresa();
        $empresa->data_inclusao = now();
        $empresa->cnpj = $request->cnpj;
        $empresa->razaosocial = $request->razaosocial;
        $empresa->sigla = $request->sigla;
        $empresa->email = $request->email;
        $empresa->telefone = $request->telefone;
        $empresa->nomefantasia = $request->nomefantasia;
        $empresa->inscricaoestadual = $request->inscricaoestadual;
        $empresa->inscricaomunicipal = $request->inscricaomunicipal;
        $empresa->municipio = $request->municipio;
        $empresa->cep = $request->cep;
        $empresa->bairro = $request->bairro;
        $empresa->numero = $request->numero;
        $empresa->logradouro = $request->logradouro;
        $empresa->uf = $request->uf;
        $empresa->matriz = $request->matriz ?? false;
        $empresa->filial = !$request->matriz;
        $empresa->status = $request->status;
        $empresa->logo = $fotoPath;
        $empresa->rntrc = $request->rntrc;
        $empresa->situacao_rntrc = $request->situacao_rntrc;
        $empresa->municipio_uf_rntrc = $request->municipio_uf_rntrc;
        $empresa->data_cadastro_rntrc = $request->data_cadastro_rntrc;
        $empresa->tipo_transporte_rntrc = $request->tipo_transporte_rntrc;
        $empresa->save();

        return redirect()
            ->route('admin.empresas.index')
            ->withNotification([
                'title'   => 'Empresa criada',
                'type'    => 'success',
                'message' => 'Empresa criada com sucesso!'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Empresa $empresa)
    {
        //
    }

    /**
     * Método responsável por preparar os dados necessários para exibir o formulário de edição de uma empresa.
     *
     * - Um array `$data` é utilizado para definir dinamicamente os campos a serem renderizados no Blade.
     *   Cada item do array representa um campo do formulário, contendo:
     *     - `label`: O texto exibido como rótulo do campo.
     *     - `type`: O tipo de input (ex.: text, email, number, select, radio).
     *     - `options` (opcional): Usado em campos do tipo select ou radio para definir as opções disponíveis.
     *
     * - Caso a empresa seja uma matriz (`matriz = true`), o campo `filial` é removido do array,
     *   pois uma matriz não pode ser configurada como filial.
     *
     * @param Empresa $empresa A instância da empresa a ser editada.
     * @return \Illuminate\View\View Retorna a view de edição com os dados da empresa e a configuração dos campos.
     * @author Leonardo Freire 13/12/2024 <email>
     */

    public function edit(Empresa $empresa)
    {
        $data = $this->getFormFields();
        return view('admin.empresas.edit', compact('empresa', 'data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresa $empresa)
    {

        $request->merge([
            'rntrc' => is_numeric($request->input('rntrc')) ? (int) $request->input('rntrc') : null,
        ]);

        $validated = $request->validate([
            'cnpj' => 'required|string|max:18',
            'razaosocial' => 'required|string|max:500',
            'sigla' => 'string|max:30',
            'email' => 'string|email',
            'telefone' => 'string|max:30',
            'nomefantasia' => 'string|max:500',
            'inscricaoestadual' => 'nullable|string|max:500',
            'inscricaomunicipal' => 'nullable|string|max:500',
            'municipio' => 'string|max:500',
            'cep' => 'string|max:9',
            'bairro' => 'string|max:500',
            'numero' => 'string|max:500',
            'logradouro' => 'string|max:500',
            'uf' => 'required|string|size:2',
            'logo'  => 'nullable|image|max:1024',
            'status' => 'required|boolean',
            'rntrc'  => 'nullable|integer',
            'situacao_rntrc'  => 'nullable|string|max:500',
            'municipio_uf_rntrc'  => 'nullable|string|max:500',
            'data_cadastro_rntrc'  => 'nullable|date',
            'tipo_transporte_rntrc' => 'nullable|string|max:500',
        ]);

        // Upload da imagem
        $fotoPath = $empresa->logo; // Mantém o caminho antigo como padrão
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {

            // Apaga a imagem antiga, se existir
            if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                Storage::disk('public')->delete($empresa->logo);
            }
            // Salva a nova imagem
            $fotoPath = $request->file('logo')->store('logos', 'public');
        }


        $updated = $empresa->update([
            'cnpj' => $validated['cnpj'],
            'razaosocial' => $validated['razaosocial'],
            'sigla' => $validated['sigla'],
            'email' => $validated['email'],
            'telefone' => $validated['telefone'],
            'nomefantasia' => $validated['nomefantasia'],
            'inscricaoestadual' => $validated['inscricaoestadual'],
            'inscricaomunicipal' => $validated['inscricaomunicipal'],
            'municipio' => $validated['municipio'],
            'cep' => $validated['cep'],
            'bairro' => $validated['bairro'],
            'numero' => $validated['numero'],
            'logradouro' => $validated['logradouro'],
            'uf' => $validated['uf'],
            'logo' => $fotoPath,
            'status' => $validated['status'],
            'rntrc'  => $validated['rntrc'],
            'situacao_rntrc'  => $validated['situacao_rntrc'],
            'municipio_uf_rntrc'  => $validated['municipio_uf_rntrc'],
            'data_cadastro_rntrc'  => $validated['data_cadastro_rntrc'],
            'tipo_transporte_rntrc' => $validated['tipo_transporte_rntrc'],
            'data_alteracao' => now()
        ]);


        if (!$updated) {
            return back()->withErrors('Não foi possível atualizar o registro.');
        }

        return redirect()
            ->route('admin.empresas.index')
            ->withNotification([
                'title'   => 'Empresa alterada',
                'type'    => 'success',
                'message' => 'Empresa alterada com sucesso!'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $empresa = Empresa::findOrFail($id);

            // Verifica se a empresa possui uma imagem e a deleta.
            if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                Storage::disk('public')->delete($empresa->logo);
            }

            $empresa->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Empresa excluída',
                    'type'    => 'success',
                    'message' => 'Empresa excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir a empresa: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}
