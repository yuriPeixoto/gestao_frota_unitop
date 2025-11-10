<!-- Filtros -->
<div class="mb-6 rounded-lg bg-white p-6 shadow-md">
    <form action="{{ route('admin.compras.solicitacoes.index') }}" method="GET">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <!-- Situação da Compra -->
            <div>
                <label for="situacao_compra" class="mb-1 block text-sm font-medium text-gray-700">Situação</label>
                <select id="situacao_compra" name="situacao_compra"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Todas</option>
                    @foreach ($situacoesCompra as $valor => $label)
                        <option value="{{ $valor }}"
                            {{ request('situacao_compra') == $valor ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Departamento -->
            <div>
                <label for="departamento_id" class="mb-1 block text-sm font-medium text-gray-700">Departamento</label>
                <select id="departamento_id" name="departamento_id"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Todos</option>
                    @foreach ($departamentos as $departamento)
                        <option value="{{ $departamento->id_departamento }}"
                            {{ $departamentoId == $departamento->id_departamento ? 'selected' : '' }}>
                            {{ $departamento->descricao_departamento }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filial -->
            <div>
                <label for="filial_id" class="mb-1 block text-sm font-medium text-gray-700">Filial</label>
                <select id="filial_id" name="filial_id"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Todas</option>
                    @foreach ($filiais as $filial)
                        <option value="{{ $filial->id }}" {{ $filialId == $filial->id ? 'selected' : '' }}>
                            {{ $filial->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Data Início -->
            <div>
                <label for="data_inicio" class="mb-1 block text-sm font-medium text-gray-700">Data Início</label>
                <input type="date" id="data_inicio" name="data_inicio" value="{{ $dataInicio }}"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>

            <!-- Data Fim -->
            <div>
                <label for="data_fim" class="mb-1 block text-sm font-medium text-gray-700">Data Fim</label>
                <input type="date" id="data_fim" name="data_fim" value="{{ $dataFim }}"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>

            <!-- Termo de Busca -->
            <div>
                <label for="termo" class="mb-1 block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" id="termo" name="termo" value="{{ $termo }}"
                    placeholder="ID, solicitante, departamento..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <a href="{{ route('admin.compras.solicitacoes.index') }}"
                class="mr-2 inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Limpar
            </a>
            <button type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Filtrar
            </button>
        </div>
    </form>
</div>
