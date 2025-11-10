<div class="p-6 bg-white border-b border-gray-200">
    <form id="devolucaoMateriais" method="POST" action="{{ $action }}" class="space-y-4"
        enctype="multipart/form-data">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <x-forms.input name="id_transferencia_direta_estoque" label="Código Transferência"
                    value="{{ $transferencia->id_transferencia_direta_estoque ?? '' }}" readonly />
            </div>

            <div>
                <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..." disabled
                    :options="$filiais" :selected="old('id_filial', $transferencia->filial ?? GetterFilial())" asyncSearch="false" />
                <input type="hidden" name="id_filial"
                    value="{{ old('id_filial', $devolucaoProdutos->id_filial ?? GetterFilial()) }}" />
            </div>

            <div>
                <x-forms.smart-select name="id_departamento" label="Departamento" disabled
                    placeholder="Selecione o departamento..." :options="$departamentos" :selected="old('id_departamento', $transferencia->id_departamento ?? '')" asyncSearch="false" />
            </div>

            <div>
                <x-forms.input name="id_usuario" label="Usuário" value="{{ Auth::user()->name }}" readonly />
            </div>
        </div>

        <h2>Inserir Produtos</h2>
        <hr>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <x-forms.smart-select name="id_produto" label="Produtos" placeholder="Selecione o produto..." disabled
                    :options="[]" :selected="old('id_produto')" asyncSearch="true" />
            </div>

            <div>
                <x-forms.smart-select name="unidadeProduto" label="Unidade" placeholder="Selecione a unidade..."
                    disabled :options="[]" :selected="old('unidadeProduto')" asyncSearch="true" />
            </div>
            <div>
                <x-forms.input name="qtde_produto" label="Quantidade Recebida" readonly />
            </div>
            <div>
                <x-forms.input name="qtde_devolucao" label="Quantidade Devolvida" />
            </div>
            <div>
                <x-forms.button onclick="adicionartransfDiretaEstoque()">
                    <x-icons.disk class="h-4 w-4 mr-2 text-white" />
                    Gravar Baixa
                </x-forms.button>
            </div>
        </div>

        <div class="p-6 bg-white border-gray-200">
            <!-- Campo hidden para armazenar os pneus Itens -->
            <input type="hidden" name="devTransfDiretaEstoque" id="devTransfDiretaEstoque_json"
                value="{{ isset($devTransfDiretaEstoque) ? json_encode($devTransfDiretaEstoque) : '[]' }}">

            <div class="col-span-full">
                <table
                    class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaTransfDiretaEstoque">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Produto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantidade Recebida
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quatidade Devolução
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaTransfDiretaEstoqueBody" class="bg-white divide-y divide-gray-200">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Botões -->
        <div class="flex
                            justify-right space-x-3 col-span-full">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                {{ isset($transferencia) ? 'Gerar Devolução' : 'Salvar' }}
            </button>

            <a href="{{ route('admin.devolucoes.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Cancelar
            </a>
        </div>
    </form>
</div>
