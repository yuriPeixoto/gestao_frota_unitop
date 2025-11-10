<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    {{-- Mensagens de erro --}}
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    @if ($errors->any())
    <div class="mb-4 bg-red-50 p-4 rounded">
        <ul class="list-disc list-inside text-red-600">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="p-4 bg-white border-b border-gray-200">


        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form id="cadastroImobilizado" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="mx-auto">
                        <!-- Botões das abas -->
                        <div class="flex space-x-1 grid grid-cols-1 md:grid-cols-4 gap-1">
                            <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba1')">
                                Registro do Imobilizado
                            </button>
                            <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba2')">
                                Registro da compra
                            </button>
                        </div>

                        <hr class="mb-5">

                    </div>


                    <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
                        <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados do Imobilizado</h3>
                        <div class="p-4 rounded-lg mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    {{-- Cod. Cadastro Imobilizado --}}
                                    <label for="id_cadastro_imobilizado"
                                        class="block text-sm font-medium text-gray-700">Código Cadastro
                                        Imobilizado</label>
                                    <input type="text" id="id_cadastro_imobilizado" name="id_cadastro_imobilizado"
                                        readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $cadastroImobilizado->id_cadastro_imobilizado ?? '' }}">
                                </div>
                                @if (!empty($cadastroImobilizado?->veiculo?->placa))
                                <div>
                                    {{-- veiculo --}}
                                    <x-forms.smart-select name="id_veiculo" label="Veiculo" placeholder="..."
                                        :options="$veiculo" :searchUrl="route('admin.api.veiculos.search')"
                                        asyncSearch="true"
                                        :selected="old('id_veiculo', $cadastroImobilizado->id_veiculo ?? '')"
                                        asyncSearch="true" :disabled="true" />
                                </div>
                                @endif

                                @if (!empty($cadastroImobilizado?->cod_produto))
                                <div>
                                    {{-- Produto --}}
                                    <x-forms.smart-select name="cod_produto" label="Produto" placeholder="..."
                                        :options="$produto" :searchUrl="route('admin.api.produto.search')"
                                        asyncSearch="true"
                                        :selected="old('cod_produto', $cadastroImobilizado->cod_produto ?? '')"
                                        asyncSearch="true" />
                                </div>
                                @endif

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

                                <div>
                                    {{-- Usuario --}}
                                    <label for="id_usuario"
                                        class="block text-sm font-medium text-gray-700">Usuário</label>

                                    <!-- Input visível com o nome do usuário (somente leitura) -->
                                    <input type="text" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $relacaoImobilizados->user->name ?? auth()->user()->name }}">

                                    <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                    <input type="hidden" name="id_usuario"
                                        value="{{ $relacaoImobilizados->user->id ?? auth()->user()->id }}">
                                </div>

                                <div>
                                    {{-- Status cadastro imobilizado --}}
                                    <x-forms.smart-select name="status_cadastro_imobilizado" label="Status"
                                        class="border-gray-300 bg-gray-100" placeholder="Selecione o Status.."
                                        :options="$statusCadastroImobilizado" :disabled="true"
                                        :selected="old('status_cadastro_imobilizado', $cadastroImobilizado->status_cadastro_imobilizado ?? '1')"
                                        asyncSearch="true" />
                                </div>

                                <div>
                                    {{-- Tipo do imobilizado --}}
                                    <x-forms.smart-select name="id_tipo_imobilizado" label="Tipo do imobilizado"
                                        placeholder="Selecione o Tipo do imobilizado..." :options="$tipoImobilizado"
                                        required="true"
                                        :selected="old('id_tipo_imobilizado', $cadastroImobilizado->id_tipo_imobilizado ?? '')"
                                        asyncSearch="true" />
                                </div>

                                <div>
                                    {{-- Filial --}}
                                    <x-forms.smart-select name="id_filial" label="Filial"
                                        placeholder="Selecione a Filial..." :options="$filial" required="true"
                                        :selected="old('id_filial', $cadastroImobilizado->id_filial ?? '')"
                                        asyncSearch="true" />
                                </div>

                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">
                                <div>
                                    {{-- Obsercação --}}
                                    <label for="observacao"
                                        class="block text-sm font-medium text-gray-700">Obsercação</label>
                                    <textarea name="observacao"
                                        class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400">{{ old('observacao', $cadastroImobilizado->observacao ?? '') }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="Aba2" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Fiscal</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                {{-- Fornecedor --}}
                                <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                                    placeholder="Selecione o fornecedor..." :options="$fornecedor" required="true"
                                    :searchUrl="route('admin.api.fornecedores.search')" asyncSearch="true"
                                    :selected="old('id_fornecedor', $cadastroImobilizado->id_fornecedor ?? '')"
                                    asyncSearch="true" />
                            </div>

                            <div>
                                {{-- Numero nota --}}
                                <label for="nota_fiscal" class="block text-sm font-medium text-gray-700"> Numero da
                                    Nota
                                    Fiscal</label>
                                <input type="number" id="numero_nota_fiscal" name="numero_nota_fiscal"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastroImobilizado->numero_nota_fiscal ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">

                            <div>
                                {{-- Numero nota --}}
                                <label for="chave_nf" class="block text-sm font-medium text-gray-700"> Chave da Nota
                                    Fiscal</label>
                                <input type="number" id="chave_nf" name="chave_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastroImobilizado->chave_nf ?? '' }}">
                            </div>

                        </div>

                        <hr class="mt-10 mb-5">

                        <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados da Compra</h3>

                        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-5 items-center ">

                            <x-forms.input readonly={true} name="id_usuario_cadastro" type="text" label="Usuário"
                                value="{{ old('id_usuario_cadastro', $registroCompra->id_usuario_cadastro ?? Auth::user()->name) }}" />

                            <div class="col-span-4"></div>

                            {{-- Lembrar que o ID fornecedor vem do fornecedor selecionado anteriormente --}}

                            <x-forms.input name="financiador" type="text" label="Financiador"
                                value="{{ old('financiador', $registroCompra->financiador ?? Auth::user()->name) }}" />

                            <x-forms.input name="data_inicio_financiamento" type="date" label="Data de Início"
                                value="{{ old('data_inicio_financiamento', $registroCompra->data_inicio_financiamento ?? '') }}" />

                            <x-forms.input name="data_compra" type="date" label="Data Compra"
                                value="{{ old('data_compra', $registroCompra->data_compra ?? '') }}" />

                            <x-forms.input name="valor_do_bem" type="text" label="Valor Financiamento"
                                oninput="formatarMoedaBrasileira(this)"
                                value="{{ old('valor_do_bem', $registroCompra->valor_do_bem ?? '') }}" />

                            <x-forms.input name="numero_de_parcelas" type="number" label="Nº de Parcelas"
                                value="{{ old('numero_de_parcelas', $registroCompra->numero_de_parcelas ?? '') }}" />

                            <x-forms.input name="valor_parcela" type="text" label="Valor Parcelas"
                                oninput="formatarMoedaBrasileira(this)"
                                value="{{ old('valor_parcela', $registroCompra->valor_parcela ?? '') }}" />

                            <x-forms.input name="numero_processo" type="number" label="Nº Processo"
                                value="{{ old('numero_processo', $registroCompra->numero_processo ?? '') }}" />

                            <x-forms.input name="reclamante_nome" type="text" label="Reclamante"
                                value="{{ old('reclamante_nome', $registroCompra->reclamante_nome ?? '') }}" />

                            <x-forms.input name="valor_processo" type="text" label="Valor Processo"
                                oninput="formatarMoedaBrasileira(this)"
                                value="{{ old('valor_processo', $registroCompra->valor_processo ?? '') }}" />

                            <x-forms.input name="valor_da_compra" type="text" label="Valor do Veículo"
                                oninput="formatarMoedaBrasileira(this)"
                                value="{{ old('valor_da_compra', $registroCompra->valor_da_compra ?? '') }}" />

                            <x-forms.input name="numero_patrimonio" type="text" label="Nº Patrimonio"
                                value="{{ old('numero_patrimonio', $registroCompra->numero_patrimonio ?? '') }}" />

                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('admin.cadastroimobilizado.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Voltar
                            </a>

                            <button type="submit" id="submit-button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function formatarMoedaBrasileira(input) {
        // Remove tudo que não é dígito
        let valor = input.value.replace(/\D/g, '');

        // Se estiver vazio, retorna vazio
        if (valor === '') {
            input.value = '';
            return;
        }

        // Converte para número e divide por 100 para obter os centavos
        const valorNumerico = parseInt(valor, 10) / 100;

        // Formata para o padrão brasileiro
        input.value = valorNumerico.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        });

        // Mantém o cursor na posição correta
        const length = input.value.length;
        input.setSelectionRange(length, length);
    }

</script>
@include('admin.cadastroimobilizado._scripts')
@endpush