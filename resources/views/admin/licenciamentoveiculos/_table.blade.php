<div class="mt-6 overflow-x-auto relative min-h-[400px]">


    <div class="results-table mt-3">
        <x-tables.table>
            <x-tables.header>
                <x-tables.head-cell>Código Licenciamento</x-tables.head-cell>
                <x-tables.head-cell>Placa</x-tables.head-cell>
                <x-tables.head-cell>Ano Licenciamento</x-tables.head-cell>
                <x-tables.head-cell>Data Emissão CRLV</x-tables.head-cell>
                <x-tables.head-cell>CRLV</x-tables.head-cell>
                <x-tables.head-cell>Data Vencimento</x-tables.head-cell>
                <x-tables.head-cell>Situação</x-tables.head-cell>
                <x-tables.head-cell>Licenciamento<br>Ativo</x-tables.head-cell>
                <x-tables.head-cell>Ações</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body>
                @forelse ($licenciamentoVeiculos as $index => $licenciamentoVeiculo)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $licenciamentoVeiculo->id_licenciamento }}</x-tables.cell>
                    <x-tables.cell>{{ $licenciamentoVeiculo->placa ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $licenciamentoVeiculo->ano_licenciamento }}</x-tables.cell>
                    <x-tables.cell>{{ $licenciamentoVeiculo->data_emissao_crlv?->format('d/m/Y H:i') ?? 'Não Informado'
                        }}
                    </x-tables.cell>
                    <x-tables.cell>{{ $licenciamentoVeiculo->crlv ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $licenciamentoVeiculo->data_vencimento?->format('d/m/Y H:i') ?? 'Não Informado' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        @statusBadge($licenciamentoVeiculo->situacao ?? 'Não informado')
                    </x-tables.cell>
                    <x-tables.cell>
                        <span id="status-badge-{{ $licenciamentoVeiculo->id_licenciamento }}"
                            class="px-2 py-1 text-xs font-medium inline-flex items-center rounded-full {{ $licenciamentoVeiculo->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            @if ($licenciamentoVeiculo->is_ativo)
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Ativo
                            @else
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Inativo
                            @endif
                        </span>
                    </x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            {{-- <a
                                href="{{ route('admin.licenciamentoveiculos.show', $licenciamentoVeiculo->id_licenciamento) }}"
                                title="Visualizar licenciamento"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <x-icons.eye class="h-3 w-3" />
                            </a> --}}

                            <a href="{{ route('admin.licenciamentoveiculos.edit', $licenciamentoVeiculo->id_licenciamento) }}"
                                title="Editar licenciamento"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            <x-tooltip content="Replicar Licenciamento" placement="top">
                                <a href="#" onclick="cloneUser({{ $licenciamentoVeiculo->id_licenciamento }})"
                                    title="Replicar licenciamento"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                </a>
                            </x-tooltip>

                            @if (auth()->user()->is_superuser || in_array(auth()->user()->id, [3, 4, 25, 52, 17]))
                            <form
                                action="{{ route('admin.licenciamentoveiculos.destroy', $licenciamentoVeiculo->id_licenciamento) }}"
                                method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Tem certeza que deseja desativar este licenciamento?')"
                                    title="Excluir licenciamento"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            </form>
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
                @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
                @endforelse
            </x-tables.body>
        </x-tables.table>

        <div class="mt-4">
            {{ $licenciamentoVeiculos->links() }}
        </div>
    </div>
</div>