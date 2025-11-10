<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="space-y-6">
                @if($errors->any())
                <div class="mb-4 bg-red-50 p-4 rounded">
                    <ul class="list-disc list-inside text-red-600">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form id="saidaRelacaoImobilizadoForm" method="POST" action="{{ $action }}" class="space-y-4"
                    enctype="multipart/form-data">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif


                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            {{-- Cod. Requisição Imobilizado --}}
                            <label for="id_relacao_imobilizados" class="block text-sm font-medium text-gray-700">Código
                                Requisição
                                Imobilizado</label>
                            <input type="text" id="id_relacao_imobilizados" name="id_relacao_imobilizados" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $relacaoImobilizados->id_relacao_imobilizados ?? '' }}">
                        </div>

                        <div>
                            {{-- Usuario --}}
                            <label for="id_usuario" class="block text-sm font-medium text-gray-700">Usuário</label>

                            <!-- Input visível com o nome do usuário (somente leitura) -->
                            <input type="text" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $relacaoImobilizados->user->name ?? auth()->user()->name }}">

                            <!-- Input oculto com o ID do usuário, que será enviado no form -->
                            <input type="hidden" name="id_usuario"
                                value="{{ $relacaoImobilizados->user->id ?? auth()->user()->id }}">
                        </div>

                        <div>
                            {{-- Filial --}}
                            <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>

                            <!-- Input visível com o nome do usuário (somente leitura) -->
                            <input type="text" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $relacaoImobilizados->filial->name ?? auth()->user()->filial->name }}">

                            <!-- Input oculto com o ID do usuário, que será enviado no form -->
                            <input type="hidden" name="id_filial"
                                value="{{ $relacaoImobilizados->user->id_filial ?? auth()->user()->filial_id }}">
                        </div>

                        <div>
                            {{-- Ordem de serviço --}}
                            <label for="id_orderm_servico" class="block text-sm font-medium text-gray-700">Ordem
                                de
                                Serviço</label>
                            <input type="text" id="id_orderm_servico" name="id_orderm_servico" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $relacaoImobilizados->id_orderm_servico ?? '' }}">
                        </div>

                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                        <div>
                            {{-- Observação Solicitação --}}
                            <label for="motivo_transferencia" class="block text-sm font-medium text-gray-700">
                                Observação
                                Solicitação</label>

                            <textarea id="motivo_transferencia" name="motivo_transferencia" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        {{ old('motivo_transferencia', $relacaoImobilizados->motivo_transferencia ?? '') }}</textarea>
                        </div>

                    </div>

                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Produtos Imobilizados</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div>

                            {{-- Produto --}}
                            <label for="id_produtos" class="block text-sm font-medium text-gray-700">
                                Produto</label>

                            <input type="text"
                                class="relative w-full flex items-center bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                name="id_produtos" placeholder="Produto" readonly>
                        </div>


                        <div>
                            {{-- Produtos imobilizados --}}
                            <x-forms.smart-select id="id_produtos_imobilizados" name="id_produtos_imobilizados"
                                label="Produto Imobilizado" placeholder="Selecione o produto..." :options="$produto"
                                required="true" readonly="true"
                                :searchUrl="route('admin.api.produtosimobilizados.search')" asyncSearch="true" />
                        </div>

                        <div class="mt-7">
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                onclick="onSalvarProduto()">Salvar</button>
                        </div>
                    </div>

                    @if ($requisicaoImobilizadosTransferencia['transferencia_estoque_imobilizado_aux'])
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>
                            {{-- Responsavel --}}
                            <x-forms.smart-select id="id_reponsavel" name="id_reponsavel" label="Responsável"
                                placeholder="Selecione o responsável..." :options="$pessoal"
                                :searchUrl="route('admin.api.pessoal.search')" asyncSearch="true" />
                        </div>


                        <div>
                            {{-- Lider Setor --}}
                            <x-forms.smart-select id="id_lider" name="id_lider" label="lider Setor"
                                placeholder="Selecione o lider..." :options="$liderSetor"
                                :searchUrl="route('admin.api.pessoal.search')" asyncSearch="true" />
                        </div>

                        <div>
                            {{-- Departamento --}}
                            <x-forms.smart-select id="id_departamento" name="id_departamento" label="Departamento"
                                placeholder="Selecione o departamento..." :options="$departamento"
                                :searchUrl="route('admin.api.departamento.search')" asyncSearch="true" />
                        </div>

                        <div>
                            {{-- Veiculo --}}
                            <x-forms.smart-select id="id_veiculo" name="id_veiculo" label="Veiculo"
                                placeholder="Selecione o veiculo..." :options="$veiculosFrequentes"
                                :searchUrl="route('admin.api.veiculos.search')" asyncSearch="true" />
                        </div>

                    </div>
                    @endif
                    <input type="hidden" name="id_reponsavel">
                    <input type="hidden" name="id_lider">
                    <input type="hidden" name="id_departamento">
                    <input type="hidden" name="id_veiculo">

                    <!-- Campo hidden para armazenar os históricos -->
                    <input type="hidden" name="historicos" id="historicos_json">

                    <div class="col-span-full">
                        <table class="min-w-full divide-y divide-gray-200 tabelaHistorico">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produtos
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cód. Produto Imobilizado
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data Inclusão
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabelaHistoricoBody" class="bg-white divide-y divide-gray-200">
                                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end space-x-3 col-span-full">
                        <a href="{{ route('admin.saidarelacaoimobilizado.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>

                        <a href="#"
                            onclick="onFinalizar({{ $relacaoImobilizados->id_relacao_imobilizados }}); return false;"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <x-icons.box class="h-4 w-4 mr-2" />
                            Finalizar
                        </a>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>

<x-bladewind.modal name="upload-termo-responsabilidade" size="omg" cancel_button_label="Cancelar" ok_button_label=""
    title="Registro do termo de Responsabilidade">

    @csrf

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            {{-- Cód. Requisição --}}
            <label for="relacao_imobilizados" class="block text-sm font-medium text-gray-700">Cód. Requisição</label>
            <input type="text" id="relacao_imobilizados" name="relacao_imobilizados" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>


        <div>
            {{-- Requisição Produto --}}
            <label for="id_relacao_imobilizado_itens" class="block text-sm font-medium text-gray-700">Cód. Requisição
                Itens</label>
            <input type="text" id="id_relacao_imobilizado_itens" name="id_relacao_imobilizado_itens" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            {{-- Produtos imobilizados --}}
            <label for="produtos_imobilizados" class="block text-sm font-medium text-gray-700">Cód. Produto
                Imobilizado</label>
            <input type="text" id="produtos_imobilizados" name="produtos_imobilizados" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            {{-- Codigo Patrimonio --}}
            <label for="cod_patrimonio" class="block text-sm font-medium text-gray-700">Cód.
                Patrimonio</label>
            <input type="text" id="cod_patrimonio" name="cod_patrimonio" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

        <div>
            {{-- Produto --}}
            <label for="id_produtos" class="block text-sm font-medium text-gray-700">Produto</label>
            <input type="text" id="id_produtos" name="id_produtos" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>


        <div>
            {{-- upload termo --}}
            <label for="arquivo" class="block text-sm font-medium text-gray-700">
                Termo
            </label>
            <input type="file" id="arquivo" name="arquivo" accept=".pdf,.jpg,.jpeg,.png"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <p class="mt-1 text-sm text-gray-500">
                Formatos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB.
            </p>

        </div>
    </div>


    <a href="#" onclick="onSalvarTermo()"
        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        Salvar
    </a>

</x-bladewind.modal>

@push('scripts')
@include('admin.saidarelacaoimobilizado._scripts')
@endpush