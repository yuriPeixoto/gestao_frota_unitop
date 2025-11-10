<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 items-center" id="formPneuNF">

    <x-forms.input name="nf_entrada_pneu" label="Número Nota Fiscal" />

    <x-forms.input name="serie" label="Série" />

    <x-forms.input name="data_emissao" type="date" label="Data Emissão:" />

    <x-forms.smart-select name="id_fornecedor_nf" label="Fornecedor" placeholder="Selecione o fornecedor..."
        :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')"
        :selected="old('id_fornecedor', $pneus->notaFiscalPneu->id_fornecedor ?? '')" asyncSearch="true" />

    <x-forms.input name="valor_unitario" label="Valor Unitário:" />

    <x-forms.input name="valor_total" label="Valor Total:" />

    @if ($isCreate)
    <div class="flex justify-left items-center mb-4">
        <button type="button" onclick="adicionarNFPneu()"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Adicionar Nota fiscal pneu
        </button>
    </div>
    @endif
</div>
<div class="shadow overflow-hidden sm:rounded-md mt-4">
    <!-- Campo hidden para armazenar os históricos -->
    <input type="hidden" name="notafiscal" id="pneus_nf_json" value='@json($pneus->notasFiscais)'>

    <div id="tabela-transferencia">
        <x-tables.table>
            <x-tables.header>
                <x-tables.head-cell>Cód. Fornecedor</x-tables.head-cell>
                <x-tables.head-cell>Data inclusao </x-tables.head-cell>
                <x-tables.head-cell>Data Alteração</x-tables.head-cell>
                <x-tables.head-cell>Nº NF</x-tables.head-cell>
                <x-tables.head-cell>Série</x-tables.head-cell>
                <x-tables.head-cell>Valor unitário</x-tables.head-cell>
                <x-tables.head-cell>Valor Total</x-tables.head-cell>
                <x-tables.head-cell>Data Emissão</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body id="tabela-nf-body">
            </x-tables.body>
        </x-tables.table>
    </div>
</div>