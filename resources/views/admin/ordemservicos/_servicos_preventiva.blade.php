{{-- Fornecedor --}}

{{-- Alerta de edição em andamento --}}
<div id="alertaEdicaoServico" class="hidden col-span-2 bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-yellow-700">
                <strong>Modo de edição ativo:</strong> Você está editando um serviço. Complete a edição clicando em
                "Adicionar" ou cancele para liberar o salvamento do formulário.
            </p>
        </div>
    </div>
</div>

<div>
    <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
        onSelectCallback="atualizarServicoFornecedor" :options="$fornecedoresFrequentes ?? []" :searchUrl="route('admin.api.fornecedores.search')" :selected="old('id_fornecedor', $ordemServico->id_fornecedor ?? '')"
        asyncSearch="true" />
</div>

<div class="grid grid-cols-2 md:grid-cols-2 gap-2 mt-4">
    {{-- Manutencao --}}

    <div>
        <x-forms.smart-select name="id_manutencao" label="Manutenção" placeholder="Selecione uma manutenção..."
            :options="[]" :selected="old('id_manutencao', $ordemServico->id_manutencao ?? '')" asyncSearch="true" />
    </div>
    {{-- Serviço --}}
    <div>
        <x-forms.smart-select name="id_servicos" label="Serviço" placeholder="Selecione o serviço..."
            onSelectCallback="atualizarServico" :options="$servicosFrequentes" :searchUrl="route('admin.ordemservicos.getServicosSearch', ['origem' => 1])" :selected="old('id_servico', $ordemServico->id_servico ?? '')"
            asyncSearch="true" />
    </div>
</div>

<div class="grid grid-cols-4 md:grid-cols-4 gap-2 mt-4">
    <div>
        {{-- Quantidade Serviços --}}
        <label for="quantidade_servico" class="block text-sm font-medium text-gray-700">
            Quantidade de Serviço:
        </label>
        <input type="number" id="servico_quantidade" name="quantidade_servico" step="1" min="1"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->quantidade_servico ?? '' }}"></input>
    </div>
    <div>
        {{-- valor_servico --}}
        <label for="valor_servico" class="block text-sm font-medium text-gray-700">
            Valor do Serviço:
        </label>
        <input type="text" id="servico_valor" name="servico_valor" readonly
            class="relative w-full flex items-center bg-gray-100 border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
    </div>
    <div>
        {{-- total_desconto --}}
        <label for="valor_descontoservico" class="block text-sm font-medium text-gray-700">
            Valor Desconto Serviço:
        </label>
        <input type="text" id="valor_descontoservico" name="valor_descontoservico" min="0" readonly
            class="resultado mt-1 bg-gray-100 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_descontoservico ?? '' }}"></input>
    </div>
    <div>
        <label for="valor_total_com_desconto" class="block text-sm font-medium text-gray-700">
            Valor Total com Desconto:
        </label>
        <input type="text" id="valor_total_com_desconto" name="valor_total_com_desconto" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_total_com_desconto ?? '' }}"></input>
    </div>
</div>


<div class="flex justify-left mt-4 gap-2">
    <button type="button" onclick="adicionarServicos()"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <x-icons.plus />
        Adicionar
    </button>

    <button type="button" onclick="cancelarEdicaoServico()" id="btnCancelarEdicaoServico"
        class="hidden inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Cancelar Edição
    </button>
</div>

<div class="p-6 bg-white border-gray-200">
    <input type="hidden" name="tabelaServicos" id="tabelaServicos_json"
        value="{{ isset($tabelaServicos) ? json_encode($tabelaServicos) : '[]' }}">

    @if (isset($tabelaServicos) && count($tabelaServicos) > 0)
        <div class="p-2">
            <x-forms.button onclick="FinalizarServico()" type="success">
                <x-icons.check class="w-4 h-4 mr-2" />
                Finalizar Serviço
            </x-forms.button>

            <x-forms.button onclick="onDeletarServico()" type="danger">
                <x-icons.trash class="w-4 h-4 mr-2" />
                Excluir
            </x-forms.button>
        </div>
    @endif

    <div class="col-span-full overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaServicosBody">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th class="px-6 py-4 whitespace-nowrap">
                        <!-- Checkbox Master -->
                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    </th>
                    <th scope="col" class="py-3 px-6">Ações</th>
                    <th scope="col" class="py-3 px-6">Código Serviço</th>
                    <th scope="col" class="py-3 px-6">Fornecedor</th>
                    <th scope="col" class="py-3 px-6">Manutenção</th>
                    <th scope="col" class="py-3 px-6">Quantidade de Serviço</th>
                    <th scope="col" class="py-3 px-6">Serviços</th>
                    <th scope="col" class="py-3 px-6">Valor Serviço</th>
                    <th scope="col" class="py-3 px-6">Valor Desconto Serviço</th>
                    <th scope="col" class="py-3 px-6">Valor Total com Desconto</th>
                    <th scope="col" class="py-3 px-6">Serviço Finalizado?</th>
                    <th scope="col" class="py-3 px-6">Nota Fiscal de Serviço</th>
                    <th scope="col" class="py-3 px-6">Status Serviço</th>
                </tr>
            </thead>
            <tbody id="tabelaServicosBody" class="bg-white divide-y divide-gray-200">
                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
    @include('admin.ordemservicos._scripts')
@endpush
