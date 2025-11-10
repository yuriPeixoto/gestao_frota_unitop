<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Lançamento</x-tables.head-cell>
            <x-tables.head-cell>Data/Hora Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data/Hora Alteração</x-tables.head-cell>
            <x-tables.head-cell>Data Abertura</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Recepcionista</x-tables.head-cell>
            <x-tables.head-cell>Processado</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($ordemservicoauxiliares as $index => $ordemServicoAuxiliar)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        @if (!$ordemServicoAuxiliar->processado)
                        <x-tooltip content="Editar">
                            <a
                                href="{{ route('admin.ordemservicoauxiliares.edit', $ordemServicoAuxiliar->id_os_auxiliar) }}">
                                <x-icons.edit class="w-4 h-4 text-blue-600" alt="Editar" />
                            </a>
                        </x-tooltip>
                        @endif

                        <x-tooltip content="Visualizar">
                            <a href="#"
                                onclick="visualizarServicos({{ $ordemServicoAuxiliar->id_os_auxiliar }}); return false;">
                                <x-icons.magnifying-glass class="w-4 h-4 text-blue-600" />
                            </a>
                        </x-tooltip>

                        @if (auth()->user()->is_superuser || in_array(auth()->user()->id, [3, 4, 25]))
                        <x-tooltip content="Excluir">
                            <button type="button"
                                onclick="confirmarExclusao({{ $ordemServicoAuxiliar->id_os_auxiliar }})"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-icons.trash class="h-3 w-3" />
                            </button>
                        </x-tooltip>
                        @endif
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $ordemServicoAuxiliar->id_os_auxiliar }}</x-tables.cell>
                <x-tables.cell>{{ format_date($ordemServicoAuxiliar->data_inclusao, 'd/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell>{{ format_date($ordemServicoAuxiliar->data_alteracao, 'd/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell>{{ format_date($ordemServicoAuxiliar->data_abertura, 'd/m/Y') }}</x-tables.cell>
                <x-tables.cell>{{ $ordemServicoAuxiliar->departamento->descricao_departamento ?? 'Não Encontrado' }}
                </x-tables.cell>
                <x-tables.cell>{{ $ordemServicoAuxiliar->usuarioEncerramento->name ?? 'Não Encontrado' }}
                </x-tables.cell>
                <x-tables.cell>{{ $ordemServicoAuxiliar->processado ? 'Sim' : 'Não' }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <x-bladewind.modal name="vizualizar-OS-servicos" size="omg" cancel_button_label="" ok_button_label="Ok"
        title="Serviços">
        <x-tables.table>
            <x-tables.header id="tabelaHeader">
                <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
                <x-tables.head-cell>Cód. Ordem Serviço</x-tables.head-cell>
                <x-tables.head-cell>Placa</x-tables.head-cell>
                <x-tables.head-cell>Data Abertura Aux.</x-tables.head-cell>
                <x-tables.head-cell>Situação Ordem Serviço</x-tables.head-cell>
                <x-tables.head-cell>Recepcionista</x-tables.head-cell>
                <x-tables.head-cell>Local Mauntenção</x-tables.head-cell>
                <x-tables.head-cell>Recepcionista Encerramento</x-tables.head-cell>
                <x-tables.head-cell>Cód. Lcto. O.S. Auxiliar</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body id="tabelaBody"></x-tables.body>

        </x-tables.table>


    </x-bladewind.modal>

    <div class="mt-4">
        {{ $ordemservicoauxiliares->links() }}
    </div>
</div>