<!-- Filtros -->
<div class="mb-6 rounded-lg bg-white p-6 shadow-md">
    <form method="GET" action="{{ route('admin.compras.cotacoes.index') }}">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <!-- Solicitação de Compras -->
            <div>
                <x-forms.smart-select name="id_solicitacoes_compras" label="Solicitação de Compras"
                    placeholder="Selecione a solicitação de compras..." :options="$id_solicitacoes_compras" asyncSearch="false" />
            </div>

            <!-- Ordem de serviço -->
            <div>
                <x-forms.smart-select name="ordens_servico" label="Ordem de serviço"
                    placeholder="Selecione a ordem de serviço..." :options="$id_solicitacoes_compras" asyncSearch="false" />
            </div>

            <!-- Veículos -->
            <div>
                <x-forms.smart-select name="veiculos" label="Veículos" placeholder="Selecione o veículo..."
                    :options="$veiculos" asyncSearch="false" />
            </div>

            <!-- Situação da Compra -->
            <div>
                <x-forms.smart-select name="situacao_compra" label="Situação da Compra"
                    placeholder="Selecione a situação da compra..." :options="$situacoes_compra" asyncSearch="false" />
            </div>

            <!-- Data Início -->
            <div>
                <label for="data_inicio" class="mb-1 block text-sm font-medium text-gray-700">Data Início</label>
                <input type="date" name="data_inicio" id="data_inicio"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    value="{{ request('data_inicio') }}" />
            </div>

            <!-- Data Final -->
            <div>
                <label for="data_final" class="mb-1 block text-sm font-medium text-gray-700">Data Final</label>
                <input type="date" name="data_final" id="data_final"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    value="{{ request('data_final') }}" />
            </div>

            <!-- Grupo de despesa -->
            <div>
                <x-forms.smart-select name="grupos" label="Grupo de Despesa"
                    placeholder="Selecione o grupo de despesa..." :options="$grupos" asyncSearch="false" />
            </div>

            <!-- Departamento -->
            <div>
                <x-forms.smart-select name="departamento" label="Departamento" placeholder="Selecione o departamento..."
                    :options="$departamentos" asyncSearch="false" />
            </div>

            <!-- Comprador -->
            <div>
                <x-forms.smart-select name="comprador" label="Comprador" placeholder="Selecione o comprador..."
                    :options="$compradores" asyncSearch="false" />
            </div>

            <!-- Filial -->
            <div>
                <x-forms.smart-select name="filial" label="Filial" placeholder="Selecione a filial..."
                    :options="$filiais" asyncSearch="false" />
            </div>

            <!-- Solicitante -->
            <div>
                <x-forms.smart-select name="solicitante" label="Solicitante" placeholder="Selecione o solicitante..."
                    :options="$solicitantes" asyncSearch="false" />
            </div>

            <!-- Tipo Solicitação -->
            <div>
                <x-forms.smart-select name="tipo_solicitacao" label="Tipo Solicitação"
                    placeholder="Selecione o tipo de solicitação..." :options="$tipos_solicitacao" asyncSearch="false" />
            </div>
        </div>

        <div class="mt-4 flex justify-between">
            <div class="flex items-center space-x-4">
                @can('juntar', App\Models\SolicitacaoCompra::class)
                    <a href="{{ route('admin.compras.cotacoes.unificar.form') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                        Unificar Cotações
                    </a>
                @endcan
                @can('juntar', App\Models\SolicitacaoCompra::class)
                    <a href="{{ route('admin.compras.cotacoes.unificar-itens.form') }}"
                        class="inline-flex items-center rounded-md border border-indigo-600 bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                        Unificar Itens
                    </a>
                @endcan
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.compras.cotacoes.index') }}"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Limpar
                </a>
                <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Filtrar
                </button>
            </div>
        </div>
    </form>
</div>
