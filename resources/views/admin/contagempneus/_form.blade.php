<div class="space-y-6">
    @if (session('notification'))
        <x-notification :notification="session('notification')" />
    @endif
    <div class="p-6 bg-white border-b border-gray-200">
        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados da Contagem</h3>
        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

            <x-forms.smart-select name="id_modelo_pneu" label="Modelo do Pneu" placeholder="Selecione o modelo do pneu"
                :options="$formOptions['modelopneu']" value="{{ old('id_modelo_pneu', $contagemPneus->id_modelo_pneu ?? '') }}"
                :selected="old('id_modelo_pneu', $contagemPneus->id_modelo_pneu ?? '')" />

            <x-forms.input name="contagem_usuario" type="number" label="Valor Contada pelo Usuário"
                value="{{ old('contagem_usuario', $contagemPneus->contagem_usuario ?? '') }}" />

            <div>
                <x-forms.smart-select name="id_responsavel_contagem" label="Usuário Responsável"
                    placeholder="Selecione a pessoa..." :options="$pessoasFrequentes" :searchUrl="route('admin.api.pessoal.search')" :selected="old('id_responsavel_contagem', $contagemPneus->id_responsavel_contagem ?? '')"
                    asyncSearch="true" />
            </div>

            <div>
                <x-forms.smart-select name="id_filial" label="filial" placeholder="Selecione a filial..."
                    :disabled="true" :options="$formOptions['filiais']" :selected="request('id_filial')" asyncSearch="false" :selected="old('id_filial', $contagemPneus->id_filial ?? 1)" />
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="flex justify-end gap-2 p-4">
        <a href="{{ route('admin.contagempneus.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit" id="submit-form"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            {{ isset($contagemPneus) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>
