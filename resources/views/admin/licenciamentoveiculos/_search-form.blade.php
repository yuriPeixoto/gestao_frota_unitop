<form method="GET" action="{{ route('admin.licenciamentoveiculos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.licenciamentoveiculos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div x-data="licenciamentoVeiculosSearchForm()">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <x-forms.input name="id_licenciamento" label="Cód. Licenciamento"
                    value="{{ request('id_licenciamento') }}" />
            </div>

            <div>
                <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione o veículo..."
                    :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')"
                    :selected="request('id_veiculo')" asyncSearch="true" />
            </div>

            <div>
                <x-forms.input name="filial_nome" label="Filial"
                    value="{{ auth()->user()->filial->name ?? 'Sem filial vinculada' }}" disabled />

                <input type="hidden" id="hidden_filial" name="id_filial" value="{{ auth()->user()->filial->id ?? '' }}">
            </div>


            <div>
                <x-forms.smart-select name="ano_licenciamento" label="Ano Licenciamento" placeholder="Selecione..."
                    :options="$ano_licenciamento" :selected="request('ano_licenciamento')" asyncSearch="false" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <div>
                <x-forms.input type="date" name="data_emissao_crlv" label="Data Emissão CRLV"
                    value="{{ request('data_emissao_crlv') }}" />
            </div>

            <div>
                <x-forms.smart-select name="crlv" label="CRLV" placeholder="Selecione..." :options="$crlv"
                    :selected="request('crlv')" asyncSearch="false" />
            </div>

            <div>
                <x-forms.input type="date" name="data_vencimento" label="Data Vencimento"
                    value="{{ request('data_vencimento') }}" />
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Selecione...</option>
                    <option value="ativo" {{ request('status')=='ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="inativo" {{ request('status')=='inativo' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>

            <div>
                <x-input-label for="situacao" value="Situação" />
                <select id="situacao" name="situacao" class="w-full border border-gray-300 rounded-md">
                    <option value="">Selecione...</option>
                    <option value="Cancelados" {{ request('situacao')=='Cancelados' ? 'selected' : '' }}>Cancelados
                    </option>
                    <option value="A vencer" {{ request('situacao')=='A vencer' ? 'selected' : '' }}>A vencer</option>
                    <option value="Vencido" {{ request('situacao')=='Vencido' ? 'selected' : '' }}>Vencido</option>
                    <option value="Quitado" {{ request('situacao')=='Quitado' ? 'selected' : '' }}>Quitado</option>
                </select>
            </div>
        </div>

        <div class="flex justify-between mt-4 me-1">
            <div>
                {{-- Usar o novo componente de botões de exportação --}}
                <x-ui.export-buttons route="admin.licenciamentoveiculos" :formats="['pdf', 'csv', 'xls', 'xml']" />
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('admin.licenciamentoveiculos.index') }}"
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
    </div>
</form>