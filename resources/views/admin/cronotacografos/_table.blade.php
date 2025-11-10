<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <!-- Loading indicator -->
    <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <!-- Actual results -->
    <div id="results-table" class="opacity-0 transition-opacity duration-300">
        <x-tables.table>
            <x-tables.header>
                <x-tables.head-cell>Cód.</x-tables.head-cell>
                <x-tables.head-cell>Tipo Certificado</x-tables.head-cell>
                <x-tables.head-cell>Número Certificado</x-tables.head-cell>
                <x-tables.head-cell>Placa</x-tables.head-cell>
                <x-tables.head-cell>Filial Veiculo</x-tables.head-cell>
                <x-tables.head-cell>Situação</x-tables.head-cell>
                <x-tables.head-cell>Data Certificação</x-tables.head-cell>
                <x-tables.head-cell>Data Vencimento</x-tables.head-cell>
                <x-tables.head-cell>Status</x-tables.head-cell>
                <x-tables.head-cell>Ações</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body>
                @forelse ($cronotacografos as $index => $cronotacografo)
                    <x-tables.row :index="$index">
                        <x-tables.cell>
                            {{ $cronotacografo->id_certificado_veiculo }}
                        </x-tables.cell>
                        <x-tables.cell>
                            {{ $cronotacografo->tipocertificado->descricao_certificado ?? 'Não informado' }}
                        </x-tables.cell>
                        <x-tables.cell>
                            {{ $cronotacografo->numero_certificado ?? 'Não informado' }}
                        </x-tables.cell>
                        <x-tables.cell>
                            {{ $cronotacografo->veiculo->placa ?? 'Não informado' }}
                        </x-tables.cell>
                        <x-tables.cell>
                            {{ $cronotacografo->veiculo->filial->name ?? 'Não informado' }}
                        </x-tables.cell>
                        <x-tables.cell>
                            <span id="status-badge-{{ $cronotacografo->situacao }}"
                                class="px-2 py-1 text-xs font-medium rounded-full 
                                @if ($cronotacografo->situacao == 'A Vencer') bg-green-100 text-green-800
                                @elseif($cronotacografo->situacao == 'Vencido') bg-red-100 text-red-800
                                @elseif($cronotacografo->situacao == 'Cancelado') bg-gray-100 text-gray-800 @endif">
                                {{ $cronotacografo->situacao }}
                            </span>
                        </x-tables.cell>
                        <x-tables.cell nowrap>
                            {{ $cronotacografo->data_certificacao?->format('d/m/Y') }}
                        </x-tables.cell>
                        <x-tables.cell nowrap>
                            {{ $cronotacografo->data_vencimento?->format('d/m/Y') }}
                        </x-tables.cell>
                        <x-tables.cell>
                            <span id="status-badge-{{ $cronotacografo->id_certificado_veiculo }}"
                                class="px-2 py-1 text-xs font-medium inline-flex items-center rounded-full {{ $cronotacografo->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                @if ($cronotacografo->is_ativo)
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
                                <x-tooltip content="Editar" placement="top">
                                    @if ($cronotacografo->is_ativo)
                                        <a href="{{ route('admin.cronotacografos.edit', $cronotacografo->id_certificado_veiculo) }}"
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <x-icons.pencil class="h-4 w-4" />
                                        </a>
                                    @else
                                        <span
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 opacity-25"
                                            onclick="return alert('Este registro não pode ser editado, pois está inativo')">
                                            <x-icons.pencil class="h-4 w-4" />
                                        </span>
                                    @endif
                                </x-tooltip>

                                <x-tooltip content="Desativar" placement="top">
                                    @if ((auth()->user()->is_superuser || in_array(auth()->user()->id, [3, 4, 25])) && $cronotacografo->is_ativo)
                                        <button type="button"
                                            onclick="confirmarExclusao({{ $cronotacografo->id_certificado_veiculo }})"
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <x-icons.disable class="h-3 w-3" />
                                        </button>
                                    @else
                                        <span
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 opacity-25"
                                            onclick="return alert('Este registro não pode ser replicado, pois está inativo')">
                                            <x-icons.disable class="h-3 w-3" />
                                        </span>
                                    @endif
                                </x-tooltip>

                                <x-tooltip content="Replicar Registro" placement="top">
                                    @if ($cronotacografo->is_ativo)
                                        <a href="{{ route('admin.cronotacografos.replicar', $cronotacografo->id_certificado_veiculo) }}"
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                            </svg>
                                        </a>
                                    @else
                                        <span
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-400 opacity-25"
                                            onclick="return alert('Este registro não pode ser Ativado.')">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                            </svg>
                                        </span>
                                    @endif
                                </x-tooltip>

                                <x-tooltip content="Visualizar" placement="top">
                                    @if ($cronotacografo->caminho_arquivo)
                                        <a href="{{ asset('storage/' . $cronotacografo->caminho_arquivo) }}"
                                            target="_blank"
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <x-icons.document class="h-4 w-4" />
                                        </a>
                                    @else
                                        <span
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 opacity-25"
                                            onclick="return alert('Este registro não pode ser editado, pois está inativo')">
                                            <x-icons.document class="h-4 w-4" />
                                        </span>
                                    @endif
                                </x-tooltip>
                            </div>
                        </x-tables.cell>

                        <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                            type="error" title="Confirmar exclusão">
                            Tem certeza que deseja excluir o Cronotacografo <b class="title"></b>? <br>
                            Esta ação não pode ser desfeita. <br>
                            <x-bladewind::button name="botao-delete" type="button" color="red"
                                onclick="confirmarExclusao({{ $cronotacografo->id_certificado_veiculo }})"
                                class="mt-3 text-white">
                                Excluir
                            </x-bladewind::button>
                        </x-bladewind.modal>

                    </x-tables.row>
                @empty
                    <x-tables.empty cols="6" message="Nenhum registro encontrado" />
                @endforelse
            </x-tables.body>
        </x-tables.table>

        <div class="mt-4">
            {{ $cronotacografos->links() }}
        </div>
    </div>
</div>
