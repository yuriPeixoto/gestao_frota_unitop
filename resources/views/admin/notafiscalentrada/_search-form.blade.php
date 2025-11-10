<form method="GET" action="{{ route('admin.notafiscalentrada.index') }}" class="space-y-4"
    hx-get="{{ route('admin.notafiscalentrada.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_nota_fiscal_entrada" label="Código Nota Fiscal Entrada"
                value="{{ request('id_nota_fiscal_entrada') }}" />
        </div>

        <div>
            <x-forms.input name="id_pedido_compras" label="Código do Pedido"
                value="{{ request('id_pedido_compras') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_inicial" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_final" label="Data Inclusão Final"
                value="{{ request('data_inclusao_final') }}" />
        </div>

        <div>
            <x-forms.input name="cnpj" label="CNPJ" value="{{ request('cnpj') }}" />
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
        <div>
            <x-forms.input name="nome_empresa" label="Nome da Empresa" value="{{ request('nome_empresa') }}" />
        </div>

        <div>
            <x-forms.input name="numero" label="Número" value="{{ request('numero') }}" />
        </div>

        <div>
            <x-forms.input name="cod_nota_fiscal" label="Código Nota Fiscal" value="{{ request('cod_nota_fiscal') }}" />
        </div>

        <div>
            <x-forms.input name="natureza_operacao" label="Natureza da Operação"
                value="{{ request('natureza_operacao') }}" />
        </div>

        <div>
            <x-forms.input name="numero_nota_fiscal" label="Número Nota Fiscal"
                value="{{ request('numero_nota_fiscal') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_emissao_inicial" label="Data Emissão Inicial"
                value="{{ request('data_emissao_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_emissao_final" label="Data Emissão Final"
                value="{{ request('data_emissao_final') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            <div class="flex space-x-2">
                <button type="submit"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                    Buscar
                </button>

                <a href="{{ route('admin.notafiscalentrada.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.trash class="h-4 w-4 mr-2" />
                    Limpar
                </a>
            </div>
        </div>
    </div>
</form>
