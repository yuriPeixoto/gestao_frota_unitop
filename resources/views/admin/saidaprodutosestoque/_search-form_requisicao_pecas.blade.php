<form method="GET" action="{{ route('admin.saidaprodutosestoque.index') }}" class="space-y-4"
    hx-get="{{ route('admin.saidaprodutosestoque.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Campo hidden para manter a tab ativa --}}
    <input type="hidden" name="active_tab" id="active_tab_unificado" value="Aba2">

    {{-- Exibir mensagens de erro/confirmação --}}


    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <x-forms.input name="id_requisicao_RelacaoPecas" label="Código Requisição"
                value="{{ request('id_requisicao_RelacaoPecas') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_depto_RelacaoPecas" label="Descrição Departamento" :options="$departamentos"
                :selected_value="request('id_depto_RelacaoPecas')" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo_RelacaoPecas" label="Placa Veiculo" :options="$veiculos"
                :selected_value="request('id_veiculo_RelacaoPecas')" asyncSearch="true"
                searchUrl="{{ route('admin.api.veiculos.search') }}" />
        </div>

        <div>
            <x-forms.smart-select name="situacao_RelacaoPecas" label="Situação" :options="[
                ['value' => 'INICIADA', 'label' => 'INICIADA'],
                ['value' => 'AGUARDANDO APROVACAO', 'label' => 'AGUARDANDO APROVAÇÃO'],
                ['value' => 'APROVADO', 'label' => 'APROVADO'],
                ['value' => 'EM BAIXA PARCIAL', 'label' => 'EM BAIXA PARCIAL'],
            ]" :selected_value="request('situacao_RelacaoPecas')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.smart-select name="solicitante_RelacaoPecas" label="Solicitante" :options="$usuarioSolicitante"
                :selected_value="request('solicitante_RelacaoPecas')" asyncSearch="true"
                searchUrl="{{ route('admin.api.users.search')}}" />
        </div>

        <div>
            <x-forms.input name=" id_ordem_servico_RelacaoPecas" label="Ordem de Serviço"
                value="{{ request('id_ordem_servico_RelacaoPecas') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial_RelacaoPecas" label="Filial" :options="$filiais"
                value="{{ request('id_filial_RelacaoPecas') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">

        <div class="flex space-x-2">
            <a href="{{ route('admin.saidaprodutosestoque.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit" id="search-input"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>