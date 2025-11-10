<form method="GET" action="{{ route('admin.contagempneus.index') }}" class="space-y-4"
    hx-get="{{ route('admin.contagempneus.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
        <div>
            <div>
                <x-forms.input name="id_contagem_pneu" label="Código Contagem Pneu"
                    value="{{ request('id_contagem_pneu') }}" />
            </div>
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
            <x-forms.smart-select name="id_modelo_pneu" label="Modelo do Pneu" placeholder="Selecione o modelo do pneu"
                :options="$modeloPneu" value="{{ old('id_modelo_pneu', request('id_modelo_pneu') ?? '') }}"
                selected_value="{{ old('id_modelo_pneu', request('id_modelo_pneu') ?? '') }}" />
        </div>

        <div>
            <x-forms.input name="contagem_usuario" type="number" label="Valor Contado pelo Usuário"
                value="{{ request('contagem_usuario') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4 col-span-2">
        <div>
            <div class="flex space-x-2">
                <button type="submit"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                    Buscar
                </button>

                <a href="{{ route('admin.contagempneus.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.trash class="h-4 w-4 mr-2" />
                    Limpar
                </a>
            </div>
        </div>
    </div>
    </div>
</form>
