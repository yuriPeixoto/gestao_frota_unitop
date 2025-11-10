<form method="GET" action="{{ route('admin.autorizacoesesptransitos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.autorizacoesesptransitos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid sm:grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_certificado_veiculo" label="Código Certificado"
                value="{{ request('id_certificado_veiculo') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" placeholder="Selecione o veículo..."
                :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="filial_veiculo" label="Filial do Veiculo" :options="$filialVeiculos ?? []" :selected="request('filial_veiculo')"
                asyncSearch="false" />
        </div>

        <div>
            <x-forms.input name="numero_certificado" label="Número do Certificado"
                value="{{ request('numero_certificado') }}" />
        </div>

        <div>
            <x-forms.smart-select name="tipo_certificado" label="Tipo de Certificado" placeholder="Todos os tipos"
                :options="$tiposCertificados ?? []" :selected="request('tipo_certificado')" asyncSearch="false" />
        </div>
    </div>

    <div class="grid sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div>
            <x-forms.input type="date" name="data_vencimento_inicio" label="Vencimento (Inicial)"
                value="{{ request('data_vencimento_inicio') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_vencimento_fim" label="Vencimento (Final)"
                value="{{ request('data_vencimento_fim') }}" />
        </div>

        <div>
            <x-forms.smart-select name="status" label="Status" placeholder="Selecione..." :options="[['value' => 'ativo', 'label' => 'Ativo'], ['value' => 'inativo', 'label' => 'Inativo']]"
                :selected="request('status')" />
        </div>

        <div>
            <label for="situacao" class="block text-sm font-medium text-gray-700">Situação</label>
            <select name="situacao"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                <option value="A Vencer" {{ old('situacao', request('situacao')) == 'A Vencer' ? 'selected' : '' }}>A
                    Vencer</option>
                <option value="Vencido" {{ old('situacao', request('situacao')) == 'Vencido' ? 'selected' : '' }}>
                    Vencido</option>
                <option value="Cancelado" {{ old('situacao', request('situacao')) == 'Cancelado' ? 'selected' : '' }}>
                    Cancelado</option>
            </select>
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.autorizacoesesptransitos.index') }}"
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

        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.autorizacoesesptransitos" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>
