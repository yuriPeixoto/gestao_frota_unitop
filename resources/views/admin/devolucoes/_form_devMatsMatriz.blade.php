<div class="p-6 bg-white border-b border-gray-200">
    <form id="devolucaoMatsMatriz" method="POST" action="{{ $action }}" class="space-y-4"
        enctype="multipart/form-data">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-forms.input name="id" label="Código Devolução" value="{{ $devMatsMatriz->id ?? '' }}"
                    class="bg-gray-200" readonly />
            </div>

            <div>
                <x-forms.input name="id_transferencia_estoque" label="Código Entrada"
                    value="{{ $devMatsMatriz->id_transferencia_estoque ?? '' }}" class="bg-gray-200" readonly />
            </div>

            <div>
                <x-forms.input name="id_user_solicitante" label="Solicitante"
                    value="{{ $devMatsMatriz->usuario->name ?? '' }}" class="bg-gray-200" readonly />
            </div>

            <div class="col-span-1 md:col-span-2">
                <label for="liberado">Liberado:</label>
                <span class="text-gray-500 text-sm">Sim</span>
                <input type="radio" name="liberado" value="1" id="liberado"
                    {{ isset($devMatsMatriz) && $devMatsMatriz->liberado == 1 ? 'checked' : '' }}>
                <span class="text-gray-500 text-sm">Não</span>
                <input type="radio" name="liberado" value="0" id="liberado"
                    {{ isset($devMatsMatriz) && $devMatsMatriz->liberado == 0 ? 'checked' : '' }}>
            </div>

            <div class="col-span-1 md:col-span-2">
                <label for="aprovado">Aprovado:</label>
                <span class="text-gray-500 text-sm">Sim</span>
                <input type="radio" name="aprovado" value="1" id="aprovado_sim"
                    {{ isset($devMatsMatriz) && $devMatsMatriz->aprovado == 1 ? 'checked' : '' }}>
                <span class="text-gray-500 text-sm">Não</span>
                <input type="radio" name="aprovado" value="0" id="aprovado_nao"
                    {{ isset($devMatsMatriz) && $devMatsMatriz->aprovado == 0 ? 'checked' : '' }}>
            </div>

        </div>

        <h2>Produtos</h2>
        <hr>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <x-forms.smart-select name="id_produto" label="Produtos" placeholder="Selecione o produto..."
                    class="bg-gray-200" disabled :options="[]" :selected="old('id_produto')" asyncSearch="true" />
            </div>

            <div>
                <x-forms.smart-select name="unidadeProduto" label="Unidade" placeholder="Selecione a unidade..."
                    class="bg-gray-200" disabled :options="[]" :selected="old('unidadeProduto')" asyncSearch="true" />
            </div>

            <div>
                <x-forms.input name="qtd_enviada" label="Quantidade Enviada" readonly />
            </div>

            <div>
                <x-forms.input name="estoque_" label="Quantidade Estoque" class="bg-gray-200" readonly />
            </div>

            <div>
                <x-forms.input name="qtd_disponivel_envio" label="Quantidade Disponível Envio"
                    label="Quantidade Disponivel" />
            </div>


            <div>
                <x-forms.button onclick="adicionardevMatMatriz()">
                    <x-icons.disk class="h-4 w-4 mr-2 text-white" />
                    Gravar Baixa
                </x-forms.button>
            </div>
        </div>

        <div class="p-6 bg-white border-gray-200">
            <!-- Campo hidden para armazenar os pneus Itens -->
            <input type="hidden" name="devMatsMatrizItens" id="devMatsMatrizItens_json"
                value="{{ isset($devMatsMatrizItens) ? json_encode($devMatsMatrizItens) : '[]' }}">

            <div class="col-span-full">
                <table
                    class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaDevMatsMatrizItens">
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
                                Quantidade Enviada
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quatidade Disponivel
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Inclusão
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Alteração
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaDevMatsMatrizItens" class="bg-white divide-y divide-gray-200">
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
