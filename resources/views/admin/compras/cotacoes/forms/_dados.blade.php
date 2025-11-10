<div>
    <!-- Informações da Solicitação -->
    <section>
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Dados da Solicitação
            </h3>
            <p class="mt-1 text-sm text-gray-600">Informações principais da solicitação de compra.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="id_solicitacoes_compras" class="block text-sm font-medium text-gray-700">Código
                    Solicitação</label>
                <input type="text" id="id_solicitacoes_compras" name="id_solicitacoes_compras" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $cotacoes->id_solicitacoes_compras ?? '' }}">
            </div>

            <div>
                <label for="data_alteracao" class="block text-sm font-medium text-gray-700">
                    Data Alteração</label>
                <input type="text" id="data_alteracao" name="data_alteracao" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $cotacoes->solicitacaoCompra->data_alteracao ?? '' }}">
            </div>

            <div>
                <label for="comprador" class="block text-sm font-medium text-gray-700">
                    Comprador</label>
                <input type="text" id="comprador" name="comprador" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $solicitacaoCompra->comprador->name ?? '' }}">

            </div>

            <div>
                <label for="solicitante" class="block text-sm font-medium text-gray-700">
                    Solicitante</label>
                <input type="text" id="solicitante" name="solicitante" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $cotacoes->solicitante ?? '' }}">
            </div>
        </div>
    </section>

    <!-- Informações da Ordem de Serviço -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                </svg>
                Informações do Serviço
            </h3>
            <p class="mt-1 text-sm text-gray-600">Detalhes da ordem de serviço e situação da compra.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="id_ordem_servico" class="block text-sm font-medium text-gray-700">Código
                    Ordem Serviço </label>
                <input type="text" id="id_ordem_servico" name="id_ordem_servico" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $cotacoes->id_ordem_servico ?? '' }}">
            </div>

            <div>
                <label for="placa" class="block text-sm font-medium text-gray-700">
                    Placa</label>
                <input type="text" id="placa" name="placa" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $cotacoes->placa ?? '' }}">
            </div>

            <div>
                <label for="situacao_compra" class="block text-sm font-medium text-gray-700">
                    Situação Compra</label>
                <input type="text" id="situacao_compra" name="situacao_compra" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $cotacoes->situacao_compra ?? '' }}">
            </div>
        </div>
    </section>

    <!-- Configurações de Entrega e Faturamento -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                Configurações de Entrega e Faturamento
            </h3>
            <p class="mt-1 text-sm text-gray-600">Configurações de filiais, prioridade e grupo de despesa.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="id_solicitacao_pecas" class="block text-sm font-medium text-gray-700">Código
                    Solicitação Peças </label>
                <input type="text" id="id_solicitacao_pecas" name="id_solicitacao_pecas" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $solicitacaoCompra->id_solicitacao_pecas ?? '' }}">
            </div>

            <div>
                <label for="prioridade" class="block text-sm font-medium text-gray-700">
                    Prioridade</label>
                <input type="text" id="prioridade" name="prioridade" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $cotacoes->prioridade ?? '' }}">
            </div>

            <div>
                <label for="id_grupo_despesas" class="block text-sm font-medium text-gray-700">
                    Grupo de Despesa</label>
                <input type="text" id="id_grupo_despesas" name="id_grupo_despesas" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $solicitacaoCompra->grupoDespesa->descricao_grupo ?? '' }}">
            </div>

            <div>
                <x-forms.smart-select name="filial" label="Filial" placeholder="Selecione a filial..."
                    :options="$filiais" :selected="old('filial', $solicitacaoCompra->id_filial ?? '')" valueField="value" textField="label" asyncSearch="false" />
            </div>

            <div>
                <x-forms.smart-select name="filial_entrega" label="Filial Entrega"
                    placeholder="Selecione a filial..." :options="$filiais" :selected="old('filial_entrega', $solicitacaoCompra->filial_entrega ?? '')" valueField="value"
                    textField="label" asyncSearch="false" />
            </div>

            <div>
                <x-forms.smart-select name="filial_faturamento" label="Filial Faturamento"
                    placeholder="Selecione a filial..." :options="$filiais" :selected="old('filial_faturamento', $solicitacaoCompra->filial_faturamento ?? '')" valueField="value"
                    textField="label" asyncSearch="false" />
            </div>
        </div>
    </section>

    <!-- Observações -->
    <section class="mt-8">
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                    </path>
                </svg>
                Observações
            </h3>
            <p class="mt-1 text-sm text-gray-600">Comentários e observações sobre a solicitação.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                <textarea name="observacao" id="observacao" rows="3" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $solicitacaoCompra->observacao ?? '' }}</textarea>
            </div>
            @if ($solicitacaoCompra->tipo_solicitacao === 1)
                <div>
                    <label for="observacao_aprovador" class="block text-sm font-medium text-gray-700">Observação do
                        Aprovador</label>
                    <textarea name="observacao_aprovador" id="observacao_aprovador" rows="3" readonly
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $solicitacaoCompra->observacao_aprovador ?? '' }}</textarea>
                </div>
            @endif
        </div>

        <div class="mt-6">
            <div>
                <label for="observacaocomprador" class="block text-sm font-medium text-gray-700">Observação do
                    Comprador</label>
                <textarea name="observacaocomprador" id="observacaocomprador" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $solicitacaoCompra->observacaocomprador ?? '' }}</textarea>
            </div>
        </div>
    </section>
</div>
