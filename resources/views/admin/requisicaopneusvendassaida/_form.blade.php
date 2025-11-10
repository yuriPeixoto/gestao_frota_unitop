@php
    use Illuminate\Support\Facades\Storage;
@endphp
<div class="space-y-6">
    @if (session('notification'))
        <x-notification :notification="session('notification')" />
    @endif
    <div class="flex gap-2">
        <x-forms.input name="id_requisicao_pneu" readonly label="Cód"
            value="{{ old('id_requisicao_pneu', $requisicaoPneus->id_requisicao_pneu ?? '') }}" />

        <x-forms.input name="id_usuario_solicitante" readonly label="Usuário Solicitante"
            value="{{ old('id_usuario_solicitante', $requisicaoPneus->usuarioSolicitante->name ?? '') }}" />

        <x-forms.input name="situacao" readonly label="Situação"
            value="{{ old('situacao', $requisicaoPneus->situacao ?? '') }}" />

        <x-forms.input name="id_terceiro" readonly label="Terceiro"
            value="{{ old('id_terceiro', $requisicaoPneus->terceiro->nome_fornecedor ?? '') }}" />
    </div>

    <div>
        <div>
            <label>Observação Solicitação</label>
            <textarea class="p-2 bg-gray-100 w-full rounded-md border-gray-200 resize-none" name="observacao_solicitante"
                readonly={true} label="Observação Solicitante">{{ old('observacao_solicitante', $requisicaoPneus->observacao_solicitante ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div>
                <label>Observação</label>
                <textarea class="p-2 bg-gray-100 w-full rounded-md border-gray-200 resize-none" name="observacao" readonly={true}
                    label="Observação Solicitante">{{ old('observacao', $requisicaoPneus->observacao ?? '') }}</textarea>
            </div>
            <div>
                <label>Justificativa de Finalização</label>
                <textarea class="p-2 w-full rounded-md border-gray-200 resize-none" name="justificativa_de_finalizacao"
                    label="Observação Solicitante">{{ old('justificativa_de_finalizacao', $requisicaoPneus->justificativa_de_finalizacao ?? '') }}</textarea>
            </div>
            @php
                $arquivoExiste = $requisicaoPneus->documento_autorizacao
                    ? Storage::disk('public')->exists($requisicaoPneus->documento_autorizacao)
                    : false;

                $classeDivPai = $arquivoExiste ? 'grid grid-rows-2 gap-4' : 'flex items-center';
            @endphp

            <div class="{{ $classeDivPai }}">
                <div>
                    <label for="documento_autorizacao">Documento Autorização</label>
                    <input type="file" name="documento_autorizacao" id="documento_autorizacao"
                        value="{{ old('documento_autorizacao', $requisicaoPneus->documento_autorizacao ?? '') }}">
                </div>
                @if ($requisicaoPneus->documento_autorizacao)
                    @php
                        $urlArquivo = $arquivoExiste
                            ? route('admin.arquivo.show', ['path' => $requisicaoPneus->documento_autorizacao])
                            : '#';
                    @endphp
                    <div class="flex items-center justify-center">
                        <a href="{{ $urlArquivo }}" type="button"
                            class="w-1/2 inline-flex
                            items-center justify-center p-1 border border-transparent rounded-lg shadow-sm
                            text-green-600 bg-green-200 hover:bg-green-700 hover:text-white focus:outline-none
                            focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-25 transition
                            ease-in-out duration-150"
                            title="{{ $arquivoExiste ? 'Visualizar documento' : 'Documento não encontrado' }}"
                            {{ !$arquivoExiste ? 'onclick="event.preventDefault(); alert(\'Arquivo não encontrado no servidor.\')"' : 'target="_blank"' }}>
                            <x-icons.eye class="h-4 w-4" />
                            Visualizar Documento
                        </a>
                    </div>
                @endif
            </div>

        </div>


        <h3 class="font-medium text-gray-800 mt-10 uppercase">Dados do Pedido</h3>
        <hr class="mb-10">

        <div class="grid grid-cols-7 gap-2 items-start">

            <div class="col-span-2">
                <x-forms.input name="modelo_pneu" readonly label="Modelo de Pneu Requisito" />
            </div>
            <x-forms.input name="quantidade" readonly label="Quantidade" />

            <div class="col-span-2">
                <!-- Campo hidden para armazenar os históricos -->
                <input type="hidden" name="pneusSelecionados" id="pneusSelecionados_json">

                <div class="w-full mt-4">
                    <table
                        class="w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaRequisicaoPneusItens">
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
                                    Estoque Modelo
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tabelaRequisicaoPneusItens" class="bg-white divide-y divide-gray-200">
                            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <x-forms.input type="date" name="data_baixa" label="Data Baixa" />
            <div>
                <x-forms.input name="valor_total" label="Valor Total Venda" data-mask="valor" />
                <p class="text-xs text-gray-500 mb-5">*Valor Venda dos pneus selecionados.</p>
            </div>
        </div>

        <div class="flex justify-end w-full">
            <button type="button" onclick="adicionarrequisicaoPneus()"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.plus />
                Adicionar
            </button>
        </div>


        <div class="p-6 bg-white border-gray-200">
            <!-- Campo hidden para armazenar os pneus Itens -->
            <input type="hidden" name="requisicaoPneusModelos" id="requisicaoPneusModelos_json"
                value="{{ isset($requisicaoPneusModelos) ? json_encode($requisicaoPneusModelos) : '[]' }}">

            <div class="col-span-full">
                <table
                    class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaRequisicaoModeloPneus">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código Requisição Pneus
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
                                Modelos dos Pneus Requisitados
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantidade
                            </th>

                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantidade Baixa
                            </th>

                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Baixa
                            </th>

                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Valor Total Venda
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaRequisicaoModeloPneus" class="bg-white divide-y divide-gray-200">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Botões -->
        <div class="flex justify-right space-x-3 col-span-full mt-4">
            <button type="submit" id="submit-form"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Salvar
            </button>

            <a href="{{ route('admin.requisicaopneusvendassaida.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Voltar
            </a>

            <a href="{{ route('admin.requisicaopneusvendassaida.imprimir', $requisicaoPneus->id_requisicao_pneu) }}"
                class="px-4 py-2 inline-flex items-center justify-center p-1 border border-gray-300 rounded shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ease-in-out duration-150">
                <x-icons.print class="w-5 h-5 mr-2 text-red-500" />
                Imprimir Documento de Venda
            </a>

            @php
                if ($requisicaoPneus->situacao == 'FINALIZADA') {
                    $classe =
                        'px-4 py-2 inline-flex items-center justify-center p-1 border border-gray-300 bg-gray-300 rounded shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ease-in-out duration-150';
                } else {
                    $classe =
                        'px-4 py-2 inline-flex items-center justify-center p-1 border border-gray-300 rounded shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ease-in-out duration-150';
                }
            @endphp
            <a href="{{ route('admin.requisicaopneusvendassaida.finalizar', $requisicaoPneus->id_requisicao_pneu) }}"
                class="{{ $classe }} {{ $requisicaoPneus->situacao == 'FINALIZADA' ? 'opacity-50 cursor-not-allowed' : '' }}"
                @if ($requisicaoPneus->situacao == 'FINALIZADA') onclick="event.preventDefault();" aria-disabled="true" tabindex="-1" @endif>
                <x-icons.box class="w-5 h-5 mr-2 text-red-500" />
                Finaliza Venda Pneu
            </a>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/requisicao_pneus/requisicao_pneus.js') }}"></script>
        @include('admin.requisicaopneusvendassaida._scripts')
    @endpush
