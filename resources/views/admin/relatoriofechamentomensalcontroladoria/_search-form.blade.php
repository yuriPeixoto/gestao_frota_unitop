<div class="space-y-4">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    {{-- Formato front relatório --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-forms.input type="date" name="data_inclusao" label="Data Inicial:" value="{{ request('data_inclusao') }}" />
        <x-forms.input type="date" name="data_final" label="Data Final:" value="{{ request('data_final') }}" />

        <div>
            <label class="block text-sm font-medium text-gray-700">Relatório:</label>
            <select name="relatorio"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecionar</option>
                <option value="1" {{ request('relatorio')=='1' ? 'selected' : '' }}>Relatório Entrada de NF de Produtos
                </option>
                <option value="2" {{ request('relatorio')=='2' ? 'selected' : '' }}>Relatório Saída de Produtos</option>
                <option value="3" {{ request('relatorio')=='3' ? 'selected' : '' }}>Relatório Total NFs</option>
                <option value="4" {{ request('relatorio')=='4' ? 'selected' : '' }}>Relatório de Cotações</option>
                <option value="5" {{ request('relatorio')=='5' ? 'selected' : '' }}>Relatório Final</option>
            </select>
        </div>
    </div>
    @if ($dados)
    <div class="mt-5 p-4 bg-white rounded shadow-sm">
        <h2 class="text-lg font-semibold mb-2">Você selecionou:</h2>
        <p class="text-gray-700">{{ $dados }}</p>
    </div>
    @endif

    {{-- Formato Ações botão limpar - pdf excel --}}
    <div class="flex justify-between mt-4">
        <div></div>

        <div class="flex space-x-2">

            <a href="{{ route('admin.relatoriofechamentomensalcontroladoria.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            {{-- <button type="button" x-on:click="$store.relatoriofechamentomensalcontroladoria.gerarPdf()"
                :disabled="$store.relatoriofechamentomensalcontroladoria.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriofechamentomensalcontroladoria.loading"
                    class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriofechamentomensalcontroladoria.loading"
                    class="h-4 w-4 mr-2" />
                <span
                    x-text="$store.relatoriofechamentomensalcontroladoria.loading ? 'Gerando...' : 'Buscar PDF'"></span>
            </button> --}}

            <button type="button" x-on:click="$store.relatoriofechamentomensalcontroladoria.gerarExcel()"
                :disabled="$store.relatoriofechamentomensalcontroladoria.loading"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="$store.relatoriofechamentomensalcontroladoria.loading"
                    class="loading-spinner mr-2"></span>
                <x-icons.magnifying-glass x-show="!$store.relatoriofechamentomensalcontroladoria.loading"
                    class="h-4 w-4 mr-2" />
                <span
                    x-text="$store.relatoriofechamentomensalcontroladoria.loading ? 'Gerando...' : 'Buscar Excel'"></span>
            </button>

        </div>
    </div>
</div>