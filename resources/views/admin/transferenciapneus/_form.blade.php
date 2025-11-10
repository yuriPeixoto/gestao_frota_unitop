@if (session('notification'))
    <x-notification :notification="session('notification')" />
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <x-forms.input name="id_transferencia_pneus" label="Código Transferência Pneus" disabled
                    value="{{ old('id_transferencia_pneus', $transferenciaPneus->id_transferencia_pneus ?? '') }}" />

                <x-forms.input name="nome_filial" label="Filial" disabled
                    value="{{ old('nome_filial', $transferenciaPneus->filial->name ?? '') }}" />

                <x-forms.input name="id_usario" label="Usuário" disabled
                    value="{{ old('id_usario', $transferenciaPneus->usuario->name ?? '') }}" />

                <x-forms.input name="filial_baixa" label="Filial Baixa" disabled
                    value="{{ old('filial_baixa', $transferenciaPneus->filialBaixa->name ?? '') }}" />

                <x-forms.input name="usario_baixa" label="Usuário Baixa" disabled
                    value="{{ old('usario_baixa', $transferenciaPneus->usuarioBaixa->name ?? '') }}" />

                <x-forms.input name="situacao" label="Situação" disabled
                    value="{{ old('situacao', $transferenciaPneus->situacao ?? '') }}" />

            </div>
            <div class="flex items-center grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label for="observacao_baixa" class="block text-sm font-medium text-gray-700">Observação da Saída de
                        Pneus:</label>
                    <textarea name="observacao_baixa" rows="4" cols="50" disabled
                        class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        value="{{ old('observacao_baixa', $transferenciaPneus->observacao_baixa ?? '') }}"></textarea>
                </div>
                <div>
                    <label for="observacao_saida" class="block text-sm font-medium text-gray-700">Observação da Saída de
                        Pneus:</label>
                    <textarea name="observacao_saida" rows="4" cols="50"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao_saida', $transferenciaPneus->observacao_saida ?? '') }}</textarea>
                </div>
                <div>
                    <label for="recebido" class="block text-sm font-medium text-gray-700">Recebido:</label>
                    <!-- Opção SIM -->
                    <label class="inline-flex items-center">
                        <input type="radio" name="recebido" value="1" class="form-radio h-4 w-4 text-green-600"
                            onclick="onYesFinalizarBaixaPneu({{ $transferenciaPneus->id_transferencia_pneus }})"
                            {{ isset($transferenciaPneus) && isset($transferenciaPneus->recebido) && $transferenciaPneus->recebido == 1 ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Sim</span>
                    </label>

                    <!-- Opção NÃO -->
                    <label class="inline-flex items-center">
                        <input type="radio" name="recebido" value="0" class="form-radio h-4 w-4 text-red-600"
                            {{ isset($transferenciaPneus) && isset($transferenciaPneus->recebido) && $transferenciaPneus->recebido == 0 ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Não</span>
                    </label>
                </div>
            </div>
            <div class="p-6 bg-white border-gray-200">
                <h1 class="text-2xl font-bold mb-4">Confirmar Recebimento de Pneus</h1>
                <hr>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-forms.input name="modelo_pneu" label="Modelos Requisitados:" disabled />

                <x-forms.input name="quantidade" label="Quantidade:" disabled />

                <div class="col-span-2">
                    <!-- Campo hidden para armazenar os históricos -->
                    <input type="hidden" name="pneusItens" id="pneusItens_json"
                        value="{{ isset($transferenciaPneusItens) ? json_encode($transferenciaPneusItens) : '[]' }}">

                    <input type="hidden" name="pneusSelecionados" id="pneusSelecionados_json">

                    <div>
                        <table
                            class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaPneusRecebidosBody">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" id="selecionarTodos"
                                            class="rounded text-indigo-600 focus:ring-indigo-500"
                                            onchange="selecionarTodosPneus(this.checked)">
                                        <label for="selecionarTodos">Todos</label>
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pneus Recebidos
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabelaPneusRecebidosBody" class="bg-white divide-y divide-gray-200">
                                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="flex justify-right mt-4">
                <button type="button" x-on:click="adicionarTransferenciaPneus"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.plus />
                    Adicionar
                </button>
            </div>

            <div class="p-6 bg-white border-gray-200">
                <!-- Campo hidden para armazenar os pneus Itens -->
                <input type="hidden" name="tranfPneus" id="tranfPneus_json"
                    value="{{ isset($transferenciaPneuModelos) ? json_encode($transferenciaPneuModelos) : '[]' }}">

                <div class="col-span-full">
                    <table
                        class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaTransferenciaPneus">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ações
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Inclusão
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Alteração
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Modelos Requisitados
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantidade
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantidade Baixa
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tabelaTransferenciaPneusBody" class="bg-white divide-y divide-gray-200">
                            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Botões -->
        <div class="flex justify-right space-x-3 col-span-full mt-4">
            <button type="submit" id="submit-form"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Salvar
            </button>
            <a href="{{ route('admin.transferenciapneus.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Voltar
            </a>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('js/transferencia_pneus/transferencia_pneus.js') }}"></script>
    @include('admin.transferenciapneus._scripts')
@endpush
