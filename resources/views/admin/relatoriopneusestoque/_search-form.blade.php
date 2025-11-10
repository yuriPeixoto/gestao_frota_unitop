<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <!-- Filial -->
        <x-forms.smart-select name="id_filial" label="Filial:" placeholder="Selecionar" :options="$filiais"
            value="{{ request('id_filial') }}" />

        <x-forms.smart-select name="id_pneu" label="N° Fogo:" placeholder="Selecionar" :options="$pneu"
            value="{{ request('id_pneu') }}" :searchUrl="route('admin.api.pneu.search')" asyncSearch="false" />

        <x-forms.smart-select name="id_modelo_pneu" label="Modelo do Pneu:" placeholder="Selecionar" :options="$modelo"
            value="{{ request('id_modelo_pneu') }}" :searchUrl="route('admin.api.modelopneu.search')"
            asyncSearch="false" />

        <x-forms.smart-select name="id_controle_vida_pneu" label="Vida do Pneu:" placeholder="Selecionar"
            :options="$vidaPneu" value="{{ request('id_controle_vida_pneu') }}" />

    </div>

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriopneusestoque.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="button" x-on:click="$store.relatoriopneusestoque.gerarPdf()"
                :disabled="$store.relatoriopneusestoque.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriopneusestoque.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriopneusestoque.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatoriopneusestoque.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button>

            <button type="button" x-on:click="$store.relatoriopneusestoque.gerarExcel()"
                :disabled="$store.relatoriopneusestoque.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriopneusestoque.loading" class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriopneusestoque.loading" class="h-4 w-4 mr-2" />
                <span x-text="$store.relatoriopneusestoque.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>

        </div>
    </div>
</div>