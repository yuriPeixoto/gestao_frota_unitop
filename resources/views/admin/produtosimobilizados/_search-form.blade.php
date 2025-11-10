<form method="GET" action="{{ route('admin.produtosimobilizados.index') }}" class="space-y-4"
    hx-get="{{ route('admin.produtosimobilizados.index') }}" hx-target="#results-table" elect="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            {{-- Cod. Produtos Imobilizados --}}
            <x-forms.input name="id_produtos_imobilizados" label="Cód. Produtos Imobilizados"
                value="{{ request('id_produtos_imobilizados') }}" />
        </div>

        <div>
            {{-- Cod. Patrimonio --}}
            <x-forms.input name="cod_patrimonio" label="Cód. Patrimonio" value="{{ request('cod_patrimonio') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_imobilizados" label="Tipo Imobilizados"
                placeholder="Selecione o tipo de imobilizado..." :options="$tipoImobilizados"
                :selected="request('id_tipo_imobilizados')" asyncSearch="false" />
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>

            {{-- Produto --}}
            <x-forms.smart-select name="id_produto" label="Produto" placeholder="Selecione o produto..."
                :options="$produto" :searchUrl="route('admin.api.produtos.search')" asyncSearch="true" />
        </div>

        <div>
            {{-- Status --}}
            <x-forms.smart-select name="status" label="Status" placeholder="Selecione o status..." :options="$status"
                :selected="request('status')" asyncSearch="false" />
        </div>

        <div>
            {{-- Placa --}}
            <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione o veículo..."
                :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')" asyncSearch="true" />
        </div>

        <div>
            {{-- Filial --}}
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..." :options="$filial"
                asyncSearch="true" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.produtosimobilizados" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.produtosimobilizados.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>