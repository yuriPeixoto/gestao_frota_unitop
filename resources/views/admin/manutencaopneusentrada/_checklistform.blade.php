<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <input type="hidden" name="id" value="{{ $id ?? '' }}">
        <input type="hidden" name="nf_entrada" value="{{ $nf_entrada ?? '' }}">
    </div>

    <div><span>NOTA:</span></div>

    <div><span>OBSERVAÇÕES:</span></div>

    <span class="flex items-center">Prazo da Entrega Conforme a Tratativa?:</span>

    <div class="flex items-center">
        <x-forms.smart-select name="checklist_fornecedor_prazo" placeholder="Selecione a nota..." :options="$nota"
            :selected="old('teste', $manutencaoPneusEntrada->teste ?? '')" asyncSearch="true" />
    </div>
    <textarea name="checklist_observacao_prazo" id="checklist_observacao_prazo" rows="3"
        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>

    <span class="flex items-center">Pontualidade (Horários da Empresa):</span>

    <div class="flex items-center">
        <x-forms.smart-select name="checklist_fornecedor_pontualidade" placeholder="Selecione a nota..."
            :options="$nota" :selected="old('teste', $manutencaoPneusEntrada->teste ?? '')" asyncSearch="true" />
    </div>

    <textarea name="checklist_observacao_pontualidade" id="checklist_observacao_pontualidade" rows="3"
        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>

    <span class="flex items-center">Quantidade Conforme Documento de Compra?:</span>

    <div class="flex items-center">
        <x-forms.smart-select name="checklist_fornecedor_quantidade_conforme" placeholder="Selecione a nota..."
            :options="$nota" :selected="old('teste', $manutencaoPneusEntrada->teste ?? '')" asyncSearch="true" />
    </div>

    <textarea name="checklist_observacao_quantidade_conforme" id="checklist_observacao_quantidade_conforme" rows="3"
        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>

    <span class="flex items-center">Integridade das Embalagens:</span>

    <div class="flex items-center">
        <x-forms.smart-select name="checklist_fornecedor_integridade_embalagens" placeholder="Selecione a nota..."
            :options="$nota" :selected="old('teste', $manutencaoPneusEntrada->teste ?? '')" asyncSearch="true" />
    </div>

    <textarea name="checklist_observacao_integridade_embalagens" id="checklist_observacao_integridade_embalagens"
        rows="3"
        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>

    <x-forms.button button-type="submit" type="success" class="h-10 w-24">
        Salvar
    </x-forms.button>
</div>
