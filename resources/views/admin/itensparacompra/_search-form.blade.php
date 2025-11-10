<form method="GET" action="{{ route('admin.itensparacompra.index') }}" class="space-y-4"
    hx-get="{{ route('admin.itensparacompra.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">

        {{-- ID do Item --}}
        <div>
            <x-forms.input name="id" label="ID do Item" placeholder="Código do item" value="{{ request('id') }}" />
        </div>

        {{-- Código do Produto --}}
        <div>
            <x-forms.input name="codigo_produto" label="Código do Produto" placeholder="Código do produto"
                value="{{ request('codigo_produto') }}" />
        </div>

        {{-- Grupo de Serviço --}}
        <div>
            <x-forms.select name="grupo_servico" label="Grupo" id="grupo_servico"
                value="{{ request('grupo_servico') }}">
                <option value="">Todos os grupos</option>
                @foreach ($grupos as $grupo)
                    <option value="{{ $grupo->id_grupo }}">{{ $grupo->descricao_grupo }}</option>
                @endforeach
            </x-forms.select>
        </div>

        {{-- Subgrupo de Serviço --}}
        <div>
            <x-forms.select name="subgrupo_servico" label="Subgrupo" id="subgrupo_servico"
                value="{{ request('subgrupo_servico') }}">
                <option value="">Todos os subgrupos</option>
                @foreach ($subgrupos as $subgrupo)
                    <option value="{{ $subgrupo->id_subgrupo }}">{{ $subgrupo->descricao_subgrupo }}</option>
                @endforeach
            </x-forms.select>
        </div>

        {{-- Situação --}}
        <div>
            <x-forms.select name="situacao" label="Situação" value="{{ request('situacao') }}">
                <option value="">Todas as situações</option>
                <option value="COMPRAS">COMPRAS</option>
                <option value="COMPRA PARCIAL">COMPRA PARCIAL</option>
            </x-forms.select>
        </div>

    </div>

    <div class="mt-4 flex justify-between">
        <div>
            <p class="text-sm text-gray-600">
                Produtos que necessitam de compra ou compra parcial
            </p>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.itensparacompra.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <x-icons.trash class="mr-2 h-4 w-4" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <x-icons.magnifying-glass class="mr-2 h-4 w-4" />
                Buscar
            </button>
        </div>
    </div>
</form>
