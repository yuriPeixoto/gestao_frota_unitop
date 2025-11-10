<div class="flex w-full gap-3">
    <div class="w-full">
        <x-forms.input name="id_pedido_compras" label="Cód:" />
    </div>

    <div class="w-full">
        <x-forms.input name="chave_nf" label="Chave NF:" />
    </div>

    <div class="w-full">
        <x-forms.smart-select name="id_fornecedor" label="Fornecedor" />
    </div>

    <div class="w-full">
        <x-forms.input name="numero_nf" label="Nº NF:" />
    </div>

    <div class="w-full">
        <x-forms.input name="serie_nf" label="Serie:" />
    </div>

    <div class="w-full">
        <x-forms.input type="date" name="data_emissao" label="Emissão:" />
    </div>

    <div class="w-full">
        <x-forms.input name="valor_total_nota" label="Valor Total NF:" />
    </div>

    <div class="w-full">
        <x-forms.input name="valor_servico" label="Valor Serviço:" />
    </div>
</div>