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

                @csrf

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
                        {{-- Departamento --}}
                        <label for="id_departamento"
                            class="block text-sm font-medium text-gray-700">Departamento</label>

                        <!-- Input visível com o nome do usuário (somente leitura) -->
                        <input type="text" readonly
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ $relacaoImobilizados->departamento->descricao_departamento ?? auth()->user()->departamento->descricao_departamento }}">

                        <!-- Input oculto com o ID do usuário, que será enviado no form -->
                        <input type="hidden" name="id_departamento"
                            value="{{ $relacaoImobilizados->user->id_departamento ?? auth()->user()->departamento_id }}">
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

                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                    <div>
                        {{-- Observação Solicitação --}}
                        <label for="motivo_transferencia" class="block text-sm font-medium text-gray-700">
                            Observação
                            Solicitação</label>

                        <textarea id="motivo_transferencia" name="motivo_transferencia" readonly
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    {{ old('motivo_transferencia', $relacaoImobilizados->motivo_transferencia ?? '') }}
                                </textarea>
                    </div>

                </div>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Produtos Imobilizados</h2>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                    <div>
                        {{-- Produto --}}
                        <x-forms.smart-select name="id_produtos" label="Produto" placeholder="Selecione o produto..."
                            :options="$produto" required="true" :searchUrl="route('admin.api.produto.search')"
                            asyncSearch="true" :selected="old('id_produto', $relacaoImobilizados->id_produtos ?? '')" />
                    </div>

                </div>

                <div class="flex justify-start items-center">
                    <button type="button" onclick="adicionarHistorico()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Adicionar Manutenção
                    </button>
                </div>


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

                {{-- Observação do lider --}}
                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                    <div>
                        {{-- Observação do lider --}}
                        <label for="observacao_lider" class="block text-sm font-medium text-gray-700"> Observação
                            do lider</label>

                        <textarea id="observacao_lider" name="observacao_lider"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    {{ old('observacao_lider', $relacaoImobilizados->observacao_lider ?? '') }}
                                </textarea>
                    </div>

                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3 col-span-full">
                    <a href="{{ route('admin.aprovacaorelacaoimobilizado.index') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancelar
                    </a>

                    <a href="#" onclick="onReprovar({{ $relacaoImobilizados->id_relacao_imobilizados }}); return false;"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <x-icons.hand-thumb-down class="h-4 w-4 mr-2 " />
                        Reprovar
                    </a>

                    <a href="#" onclick="onAprovar({{ $relacaoImobilizados->id_relacao_imobilizados }}); return false;"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <x-icons.hand-thumb-up class="h-4 w-4 mr-2" />
                        Aprovar
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>



@push('scripts')
@include('admin.aprovacaorelacaoimobilizado._scripts')
@endpush