<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final_abastecimento" label="Data Inclusão Final"
                value="{{ request('data_final_abastecimento') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
                :options="$fornecedores" :searchUrl="route('admin.api.fornecedor.search')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" placeholder="Selecione o veículo..."
                :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')" asyncSearch="false" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.integracao486Ssw.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" 
                x-on:click="$store.utils.integracao486Ssw()"
                :disabled="$store.utils.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                
                <!-- Ícone de loading (quando carregando) -->
                <span x-show="$store.utils.loading" class="loading-spinner mr-2"></span>
                <!-- Ícone normal (quando não carregando) -->
                <x-icons.magnifying-glass x-show="!$store.utils.loading" class="h-4 w-4 mr-2" />
                
                <!-- Texto do botão -->
                <span x-text="$store.utils.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button>

            <button type="button" 
                x-on:click="$store.utils.integracao486SswExcel()"
                :disabled="$store.utils.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                
                <!-- Ícone de loading (quando carregando) -->
                <span x-show="$store.utils.loading" class="loading-spinner mr-2"></span>
                <!-- Ícone normal (quando não carregando) -->
                <x-icons.magnifying-glass x-show="!$store.utils.loading" class="h-4 w-4 mr-2" />
                
                <!-- Texto do botão -->
                <span x-text="$store.utils.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>
        </div>
    </div>
</div>