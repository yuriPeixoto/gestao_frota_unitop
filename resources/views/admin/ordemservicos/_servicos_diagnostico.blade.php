{{-- Manutenção --}}
<div>
    <label for="id_manutencao" class="block text-sm font-medium text-gray-700 mb-1">Manutenção:</label>
    <select name="id_manutencao" id="id_manutencao" @change="atualizarValores($event)" x-model="novoServico.id_manutencao"
        class="relative w-full flex items-center bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        <option value="">Selecione uma manutenção...</option>
        @foreach ($formOptions['manutencao'] as $manutencao)
            <option value="{{ $manutencao['value'] }}">{{ $manutencao['label'] }}</option>
        @endforeach
    </select>
</div>


<div class="grid grid-cols-2 md:grid-cols-2 gap-2 mt-4">
    {{-- Fornecedor --}}
    <div>
        <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
            onSelectCallback="atualizarServicoFornecedor" :options="$fornecedoresFrequentes ?? []" :searchUrl="route('admin.api.fornecedores.search')" :selected="old('id_fornecedor', $ordemServico->id_fornecedor ?? '')"
            asyncSearch="true" />
    </div>

    {{-- Serviço --}}
    <div>
        <x-forms.smart-select name="id_servicos" label="Serviço" placeholder="Selecione o serviço..."
            onSelectCallback="atualizarServico" :options="$servicosFrequentes" :searchUrl="route('admin.api.servico.search')" :selected="old('id_servico', $ordemServico->id_servico ?? '')"
            asyncSearch="true" />
    </div>
</div>

<div class="grid grid-cols-4 md:grid-cols-4 gap-2 mt-4">
    <div>
        {{-- valor_servico --}}
        <label for="valor_servico" class="block text-sm font-medium text-gray-700">
            Valor do Serviço:
        </label>
        <input type="text" x-model="novoServico.valor_servico" id="servico_valor" name="servico_valor"
            x-init="window.formatInputMoeda($el)" @input="window.formatInputMoeda($el)"
            class="relative w-full flex items-center bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            @input="novoServico.valor_servico = $event.target.value.replace(/,/g, '.')" />
    </div>
    <div>
        {{-- Quantidade Serviços --}}
        <label for="quantidade_servico" class="block text-sm font-medium text-gray-700">
            Quantidade de Serviço:
        </label>
        <input type="number" id="servico_quantidade" name="quantidade_servico" step="1" min="1"
            x-model="novoServico.quantidade_servico"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->quantidade_servico ?? '' }}"></input>
    </div>
    <div>
        {{-- total_desconto --}}
        <label for="valor_descontoservico" class="block text-sm font-medium text-gray-700">
            Valor Desconto Serviço:
        </label>
        <input type="text" id="valor_descontoservico" name="valor_descontoservico" x-init="window.formatInputMoeda($el)"
            @input="window.formatInputMoeda($el)" x-model="novoServico.valor_descontoservico" step="0.01"
            min="0"
            class="resultado mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_descontoservico ?? '' }}"></input>
    </div>
    <div>
        <label for="valor_total_com_desconto" class="block text-sm font-medium text-gray-700">
            Valor Total com Desconto:
        </label>
        <input type="text" id="valor_total_com_desconto" name="valor_total_com_desconto"
            x-model="novoServico.valor_total" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_total_com_desconto ?? '' }}"></input>
    </div>
</div>


<div class="flex justify-left mt-4">
    <button type="button" x-on:click="adicionarItemServico('diagnostico')"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <x-icons.plus />
        Adicionar
    </button>
</div>

<!-- Tabela de Itens -->
<div class="p-6 bg-white border-gray-200">
    <!-- Campo hidden para armazenar os socorristas -->
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

    <div class="col-span-full">
        <table class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaServicosBody">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th class="px-6 py-4 whitespace-nowrap">
                        <!-- Checkbox Master -->
                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    </th>
                    <th scope="col" class="py-3 px-6">Ação</th>
                    <th scope="col" class="py-3 px-6">Código Serviço</th>
                    <th scope="col" class="py-3 px-6">Fornecedor</th>
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
