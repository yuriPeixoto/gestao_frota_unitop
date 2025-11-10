<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem Ordem de Serviço - Borracharia') }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- <a href="{{ route('admin.ordemservicos.create', ['origem' => 'borracharia']) }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Abrir Ordem de Serviço
                </a> --}}

                <x-bladewind::notification />

                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                    Ajuda - Borracharia OS
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela exibe as ordens de serviço específicas da borracharia. Use os filtros
                                    abaixo para buscar registros específicos!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                <form method="GET" action="{{ route('admin.pneus.borracharia.index') }}" class="space-y-4"
                    hx-get="{{ route('admin.pneus.borracharia.index') }}" hx-target="#results-table"
                    hx-select="#results-table" hx-trigger="change delay:500ms, search">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <x-forms.input name="id_ordem_servico" label="Cód. Ordem de Serviço"
                                value="{{ request('id_ordem_servico') }}" />
                        </div>

                        <div>
                            <x-forms.input type="date" name="data_abertura" label="Data abertura"
                                value="{{ request('data_abertura') }}" />
                        </div>

                        <div>
                            @php
                                $tipoOptions = $tiposOrdem
                                    ->pluck('descricao_tipo_ordem', 'id_tipo_ordem_servico')
                                    ->toArray();
                            @endphp
                            <x-forms.select name="id_tipo_ordem_servico" label="Tipo Ordem Serviço" :options="$tipoOptions"
                                :selected="request('id_tipo_ordem_servico')" />
                        </div>

                        <div>
                            @php
                                $statusOptions = $statusOrdem
                                    ->pluck('situacao_ordem_servico', 'id_status_ordem_servico')
                                    ->toArray();
                            @endphp
                            <x-forms.select name="id_status_ordem_servico" label="Situação Ordem Serviço"
                                :options="$statusOptions" :selected="request('id_status_ordem_servico')" />
                        </div>

                        <div>
                            <x-forms.input name="id_lancamento_os_auxiliar" label="Cód. Lançamento"
                                value="{{ request('id_lancamento_os_auxiliar') }}" />
                        </div>

                        <div>
                            <x-forms.input name="placa" label="Placa" value="{{ request('placa') }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div>
                            @php
                                $recepcionistaOptions = $recepcionistas->pluck('name', 'id')->toArray();
                            @endphp
                            <x-forms.select name="id_recepcionista" label="Recepcionista" :options="$recepcionistaOptions"
                                :selected="request('id_recepcionista')" />
                        </div>

                        <div>
                            <x-forms.select name="local_manutencao" label="Local Manutenção" :options="$locaisManutencao"
                                :selected="request('local_manutencao')" />
                        </div>

                        <div>
                            @php
                                $filialOptions = $filiais->pluck('name', 'id')->toArray();
                            @endphp
                            <x-forms.select name="id_filial" label="Filial" :options="$filialOptions" :selected="request('id_filial')" />
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                                Buscar
                            </button>

                            <a href="{{ route('admin.pneus.borracharia.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.trash class="h-4 w-4 mr-2" />
                                Limpar
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    <div class="mt-6 overflow-x-auto">
                        <div class="results-table">
                            <x-tables.table>
                                <x-tables.header>
                                    <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
                                    <x-tables.head-cell>Cód. O.S.</x-tables.head-cell>
                                    <x-tables.head-cell>Placa</x-tables.head-cell>
                                    <x-tables.head-cell>Data Abertura</x-tables.head-cell>
                                    <x-tables.head-cell>Tipo O.S.</x-tables.head-cell>
                                    <x-tables.head-cell>Situação O.S.</x-tables.head-cell>
                                    <x-tables.head-cell>Data Encerramento</x-tables.head-cell>
                                    <x-tables.head-cell>Recepcionista</x-tables.head-cell>
                                    <x-tables.head-cell>Local Manutenção</x-tables.head-cell>
                                    <x-tables.head-cell>Recepcionista Encerramento</x-tables.head-cell>
                                    <x-tables.head-cell>Cód. Lcto. OS Auxiliar</x-tables.head-cell>
                                </x-tables.header>

                                <x-tables.body>
                                    @forelse ($ordens as $index => $os)
                                        <x-tables.row :index="$index">
                                            <x-tables.cell>
                                                <div class="relative inline-block">
                                                    <button
                                                        class="dropdown-button bg-white border px-4 py-2 rounded shadow flex items-center space-x-2">
                                                        <x-icons.gear class="w-4 h-4" />
                                                        <span>Ações</span>
                                                    </button>
                                                    <ul
                                                        class="dropdown-menu absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg hidden z-50">
                                                        <li>
                                                            <a href="{{ route('admin.ordemservicos.edit', ['ordemservicos' => $os->id_ordem_servico, 'origem' => 'borracharia']) }}"
                                                                class="block px-4 py-2 text-blue-600 hover:bg-gray-100 flex items-center">
                                                                <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                                                {{ $os->id_tipo_ordem_servico === 3 ? 'Editar Borracharia' : 'Editar' }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.pneus.borracharia.print', $os->id_ordem_servico) }}"
                                                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center">
                                                                <x-icons.pdf-doc class="w-4 h-4 mr-2 text-red-600" />
                                                                Imprimir O.S
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.ordemservicoservicos.index', ['id_ordem_' => $os->id_ordem_servico]) }}"
                                                                class="block px-4 py-2 text-gray-500 hover:bg-gray-100 flex items-center">
                                                                <x-icons.magnifying-glass class="w-4 h-4 mr-2" />
                                                                Visualizar Serviços
                                                            </a>
                                                        </li>
                                                        @if (in_array($os->id_status_ordem_servico, [1, 5]) && !$os->id_recepcionista)
                                                            <li>
                                                                <form
                                                                    action="{{ route('admin.pneus.borracharia.assume', $os->id_ordem_servico) }}"
                                                                    method="POST" class="inline">
                                                                    @csrf
                                                                    <button
                                                                        onclick="return confirm('Deseja assumir esta OS?')"
                                                                        class="w-full text-left block px-4 py-2 text-green-600 hover:bg-gray-100 flex items-center">
                                                                        <x-icons.user-check class="w-4 h-4 mr-2" />
                                                                        Assumir OS
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @if ($os->id_status_ordem_servico == 4 && auth()->user()->can('reopen_os'))
                                                            <li>
                                                                <form
                                                                    action="{{ route('admin.pneus.borracharia.reopen', $os->id_ordem_servico) }}"
                                                                    method="POST" class="inline">
                                                                    @csrf
                                                                    <button
                                                                        onclick="return confirm('Deseja reabrir esta OS?')"
                                                                        class="w-full text-left block px-4 py-2 text-amber-600 hover:bg-gray-100 flex items-center">
                                                                        <x-icons.refresh class="w-4 h-4 mr-2" />
                                                                        Reabrir O.S.
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @can('delete_os')
                                                            <li>
                                                                <form
                                                                    action="{{ route('admin.pneus.borracharia.delete', $os->id_ordem_servico) }}"
                                                                    method="POST" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button
                                                                        onclick="return confirm('Deseja realmente excluir esta OS?')"
                                                                        class="w-full text-left flex items-center px-4 py-2 text-red-600 hover:bg-gray-100">
                                                                        <x-icons.trash class="h-4 w-4 mr-2 text-red-600" />
                                                                        Excluir
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </x-tables.cell>
                                            <x-tables.cell>{{ $os->id_ordem_servico }}</x-tables.cell>
                                            <x-tables.cell>{{ $os->veiculo->placa ?? 'Não informado' }}</x-tables.cell>
                                            <x-tables.cell>{{ format_date($os->data_abertura, 'd/m/Y H:i') }}</x-tables.cell>
                                            <x-tables.cell>{{ optional($os->tipoOrdemServico)->descricao_tipo_ordem ?? '-' }}</x-tables.cell>
                                            <x-tables.cell>{{ optional($os->statusOrdemServico)->situacao_ordem_servico ?? '-' }}</x-tables.cell>
                                            <x-tables.cell>{{ format_date($os->data_encerramento, 'd/m/Y H:i') }}</x-tables.cell>
                                            <x-tables.cell>{{ optional($os->recepcionista)->name ?? 'Não Encontrado' }}</x-tables.cell>
                                            <x-tables.cell>{{ $os->local_manutencao ?? '-' }}</x-tables.cell>
                                            <x-tables.cell>{{ optional($os->recepcionistaEncerramento)->name ?? 'Não Encontrado' }}</x-tables.cell>
                                            <x-tables.cell>
                                                @if ($os->id_lancamento_os_auxiliar)
                                                    <span
                                                        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-50 text-green-700 ring-1 ring-green-600/20 ring-inset">
                                                        {{ $os->id_lancamento_os_auxiliar }}
                                                    </span>
                                                @endif
                                            </x-tables.cell>
                                        </x-tables.row>
                                    @empty
                                        <x-tables.empty cols="11" message="Nenhum registro encontrado" />
                                    @endforelse
                                </x-tables.body>
                            </x-tables.table>

                            <div class="mt-4">
                                {{ $ordens->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50"
                id="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50"
                id="error-message">
                {{ session('error') }}
            </div>
        @endif

        @push('scripts')
            <script src="{{ asset('js/ordemservico-dropdown.js') }}"></script>
            <script>
                // Auto-hide messages after 5 seconds
                setTimeout(function() {
                    const successMsg = document.getElementById('success-message');
                    const errorMsg = document.getElementById('error-message');
                    if (successMsg) successMsg.style.display = 'none';
                    if (errorMsg) errorMsg.style.display = 'none';
                }, 5000);
            </script>
        @endpush
</x-app-layout>
