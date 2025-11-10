<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />
    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Formato DIV input --}}
        <div>
            <x-forms.input type="date" name="data_realizacao" label="Data Inclusão Inicial:"
                value="{{ request('data_realizacao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Inclusão Final:"
                value="{{ request('data_final') }}" />
        </div>
        {{-- Formato FORMS select --}}

        <div>
            <x-forms.label for="tipo" value="Entrega/Recebimento" />
            <select name="tipo" id="tipo" class="form-select w-full">
                <option value="">Selecionar</option>
                <option value="Recebimento" {{ request('tipo')=='Recebimento' ? 'selected' : '' }}>Recebimento</option>
                <option value="Ambos" {{ request('tipo', 'Ambos' )=='Ambos' ? 'selected' : '' }}>Ambos</option>
                <option value="Entrega" {{ request('tipo')=='Entrega' ? 'selected' : '' }}>Entrega</option>
            </select>
        </div>



        <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecionar" :options="$veiculos"
            value="{{ request('id_veiculo') }}" :searchUrl="route('admin.api.veiculos.search')" asyncSearch="false" />

        <x-forms.smart-select name="motorista" label="Motorista:" placeholder="Selecionar" :options="$motorista"
            value="{{ request('idobtermotorista') }}" :searchUrl="route('admin.api.motorista.search')"
            asyncSearch="false" />



    </div>

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.relatoriogeralchecklist.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" x-on:click="$store.relatorioGeralChecklist.gerarPdf()"
                :disabled="$store.relatorioGeralChecklist.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">

                <!-- Ícone de loading (quando carregando) -->
                <span x-show="$store.relatorioGeralChecklist.loading" class="loading-spinner mr-2"></span>
                <!-- Ícone normal (quando não carregando) -->
                <x-icons.magnifying-glass x-show="!$store.relatorioGeralChecklist.loading" class="h-4 w-4 mr-2" />

                <!-- Texto do botão -->
                <span x-text="$store.relatorioGeralChecklist.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button>

            <button type="button" x-on:click="$store.relatorioGeralChecklist.gerarExcel()"
                :disabled="$store.relatorioGeralChecklist.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">

                <!-- Ícone de loading (quando carregando) -->
                <span x-show="$store.relatorioGeralChecklist.loading" class="loading-spinner mr-2"></span>
                <!-- Ícone normal (quando não carregando) -->
                <x-icons.magnifying-glass x-show="!$store.relatorioGeralChecklist.loading" class="h-4 w-4 mr-2" />

                <!-- Texto do botão -->
                <span x-text="$store.relatorioGeralChecklist.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>
        </div>

    </div>
</div>