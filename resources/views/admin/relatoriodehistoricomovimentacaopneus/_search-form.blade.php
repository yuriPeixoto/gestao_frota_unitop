<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão Inicial:"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Final:" value="{{ request('data_final') }}" />
        </div>


        <x-forms.smart-select name="id_departamento" label="Departamento:" placeholder="Selecionar"
            :options="$departamento" value="{{ request('id_departamento') }}"
            :searchUrl="route('admin.api.departamento.search')" asyncSearch="false" />

        <x-forms.smart-select name="id_filial" label="Filial:" placeholder="Selecionar" :options="$filial"
            value="{{ request('id_filial') }}" />

        <x-forms.smart-select name="id_veiculo" label="Placas:" placeholder="Selecionar" :options="$placas"
            value="{{ request('id_veiculo') }}" :searchUrl="route('admin.api.veiculo.search')" asyncSearch="false" />

        <x-forms.smart-select name="id_pneu" label="Código de Fogo:" placeholder="Selecionar" :options="$pneu"
            value="{{ request('id_pneu') }}" :searchUrl="route('admin.api.pneu.search')" asyncSearch="false" />

        <x-forms.smart-select name="id_controle_vida_pneu" label="Vida do Pneu:" placeholder="Selecionar"
            :options="$vidaPneu" value="{{ request('id_controle_vida_pneu') }}" />





    </div>

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriodehistoricomovimentacaopneus.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" x-on:click="$store.relatoriodehistoricomovimentacaopneus.gerarPdf()"
                :disabled="$store.relatoriodehistoricomovimentacaopneus.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriodehistoricomovimentacaopneus.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriodehistoricomovimentacaopneus.loading"
                    class="h-4 w-4 mr-2" />
                <span
                    x-text="$store.relatoriodehistoricomovimentacaopneus.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button>

            <button type="button" x-on:click="$store.relatoriodehistoricomovimentacaopneus.gerarExcel()"
                :disabled="$store.relatoriodehistoricomovimentacaopneus.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriodehistoricomovimentacaopneus.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriodehistoricomovimentacaopneus.loading"
                    class="h-4 w-4 mr-2" />
                <span
                    x-text="$store.relatoriodehistoricomovimentacaopneus.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>

        </div>
    </div>
</div>