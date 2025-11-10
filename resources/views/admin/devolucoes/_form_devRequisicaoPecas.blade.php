<div class="border-b border-gray-200 bg-white p-6">
    <form id="devolucaoRequisicaoPecas" method="POST" action="{{ $action }}" class="space-y-4"
        enctype="multipart/form-data">
        @csrf
        @if ($method === 'PUT')
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5">
            <div>
                <x-forms.input name="id_solicitacao_pecas" label="Código Solicitação"
                    value="{{ $solicitacao->id_solicitacao_pecas ?? '' }}" readonly />
            </div>

            <div>
                <x-forms.input name="id_orderm_servico" label="Código Ordem de Serviço"
                    value="{{ $solicitacao->id_orderm_servico ?? '' }}" readonly />
            </div>

            <div>
                <x-forms.input name="nome_usuario" label="Usuário"
                    value="{{ old('nome_usuario') ?? ($solicitacao->usuario->name ?? '') }}" readonly />
                <input type="hidden" name="id_usuario_abertura" value="{{ $solicitacao->id_usuario_abertura }}" />
            </div>

            <div>
                <x-forms.input name="placa" label="Veículo" value="{{ $solicitacao->veiculo->placa ?? '' }}"
                    readonly />
                <input type="hidden" name="id_veiculo" value="{{ $solicitacao->id_veiculo }}" />
            </div>

            <div>
                <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..." disabled
                    :options="$filiais" :selected="old('id_filial', $transferencia->filial ?? GetterFilial())" asyncSearch="false" />
                <input type="hidden" name="id_filial"
                    value="{{ old('id_filial', $devolucaoProdutos->id_filial ?? GetterFilial()) }}" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
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
                <x-forms.button onclick="adicionardevRequisicaoPeca()">
                    <x-icons.disk class="mr-2 h-4 w-4 text-white" />
                    Gravar Baixa
                </x-forms.button>
            </div>
        </div>

        <div class="border-gray-200 bg-white p-6">
            <!-- Campo hidden para armazenar os pneus Itens -->
            <input type="hidden" name="devRequisicaoPecas" id="devRequisicaoPecas_json"
                value="{{ isset($devRequisicaoPecas) ? json_encode($devRequisicaoPecas) : '[]' }}">

            <div class="col-span-full">
                <table
                    class="tabeladevRequisicaoPecaBody min-w-full divide-y divide-gray-200 overflow-hidden shadow-md sm:rounded-md">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Ações
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Produto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Quantidade Recebida
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Quatidade Devolução
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabeladevRequisicaoPecaBody" class="divide-y divide-gray-200 bg-white">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botões -->
        <div class="justify-right col-span-full flex space-x-3">
            <button type="submit"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                {{ isset($solicitacao) ? 'Gerar Devolução' : 'Salvar' }}
            </button>

            <a href="{{ route('admin.devolucoes.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                Cancelar
            </a>
        </div>
    </form>
</div>
