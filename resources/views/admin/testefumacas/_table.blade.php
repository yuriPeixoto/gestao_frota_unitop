@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-tables.table>
    <x-tables.header>
        <x-tables.head-cell>Código</x-tables.head-cell>
        <x-tables.head-cell>Placa</x-tables.head-cell>
        <x-tables.head-cell>Filial do Veiculo</x-tables.head-cell>
        <x-tables.head-cell>Data Realização</x-tables.head-cell>
        <x-tables.head-cell>Data Vencimento</x-tables.head-cell>
        <x-tables.head-cell>Situacao</x-tables.head-cell>
        <x-tables.head-cell>K Máximo</x-tables.head-cell>
        <x-tables.head-cell>K Médio</x-tables.head-cell>
        <x-tables.head-cell>Resultado</x-tables.head-cell>
        <x-tables.head-cell>Status</x-tables.head-cell>
        <x-tables.head-cell>Ações</x-tables.head-cell>
    </x-tables.header>

    <x-tables.body>
        @forelse ($testesFumaca as $index => $teste)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $teste->id_teste_fumaca }}</x-tables.cell>
                <x-tables.cell>{{ $teste->placa ?? 'N/D' }}</x-tables.cell>
                <x-tables.cell>{{ $teste->veiculo->filial->name ?? 'N/D' }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $teste->data_de_realizacao?->format('d/m/Y') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $teste->data_de_vencimento?->format('d/m/Y') }}</x-tables.cell>
                <x-tables.cell><span id="status-badge-{{ $teste->situacao }}"
                        class="px-2 py-1 text-xs font-medium rounded-full 
                    @if ($teste->situacao == 'A Vencer') bg-green-100 text-green-800
                    @elseif($teste->situacao == 'Vencido') bg-red-100 text-red-800
                    @elseif($teste->situacao == 'Cancelado') bg-gray-100 text-gray-800 @endif">
                        {{ $teste->situacao }}
                    </span></x-tables.cell>
                <x-tables.cell>{{ $teste->kmaximo }}</x-tables.cell>
                <x-tables.cell>{{ $teste->kmedido }}</x-tables.cell>
                <x-tables.cell>{{ $teste->resultado }}</x-tables.cell>
                <x-tables.cell>
                    <span id="status-badge-{{ $teste->id_teste_fumaca }}"
                        class="px-2 py-1 text-xs font-medium inline-flex items-center rounded-full {{ $teste->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        @if ($teste->is_ativo)
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
                            @if ($teste->is_ativo)
                                <a href="{{ route('admin.testefumacas.edit', $teste->id_teste_fumaca) }}"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-icons.pencil class="h-3 w-3" />
                                </a>
                            @else
                                <span
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 opacity-25"
                                    onclick="return alert('Este registro não pode ser editado, pois está inativo')">
                                    <x-icons.pencil class="h-3 w-3" />
                                </span>
                            @endif
                        </x-tooltip>

                        <x-tooltip content="Replicar Certificado" placement="top">
                            @if ($teste->is_ativo)
                                <a href="#" onclick="cloneUser({{ $teste->id_teste_fumaca }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                </a>
                            @else
                                <span
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 opacity-25"
                                    onclick="return alert('Este registro não pode ser replicado, pois está inativo')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                </span>
                            @endif
                        </x-tooltip>

                        <x-tooltip content="Desativar" placement="top">
                            @if ((auth()->user()->is_superuser || in_array(auth()->user()->id, [3, 4, 17, 25])) && $teste->is_ativo)
                                <button type="button" onclick="destroyOrdemServico({{ $teste->id_teste_fumaca }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.disable class="h-4 w-4" />
                                </button>
                            @else
                                <span
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 opacity-25"
                                    onclick="return alert('Este registro não pode ser ativado.')">
                                    <x-icons.disable class="h-4 w-4" />
                                </span>
                            @endif
                        </x-tooltip>

                        <x-tooltip content="Visualizar Arquivo" placement="top">
                            @if ($teste->anexo_laudo)
                                <a href="{{ $teste->anexo_laudo && Storage::disk('public')->exists($teste->anexo_laudo) ? Storage::url($teste->anexo_laudo) : '#' }}"
                                    target="{{ $teste->anexo_laudo && Storage::disk('public')->exists($teste->anexo_laudo) ? '_blank' : '_self' }}"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white {{ $teste->anexo_laudo && Storage::disk('public')->exists($teste->anexo_laudo) ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 cursor-not-allowed' }}"
                                    @if (!$teste->anexo_laudo || !Storage::disk('public')->exists($teste->anexo_laudo)) aria-disabled="true" @endif>
                                    <x-icons.document class="h-3 w-3" />
                                </a>
                            @else
                                <span
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 opacity-25">
                                    <x-icons.document class="h-4 w-4" />
                                </span>
                            @endif
                        </x-tooltip>
                    </div>
                </x-tables.cell>

                <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                    type="error" title="Confirmar Desativação">
                    Tem certeza que deseja desativar o teste de fumaça <b class="title"></b>?
                    <br>
                    <x-bladewind::button name="botao-delete" type="button" color="red"
                        onclick="confirmarExclusao({{ $teste->id_teste_fumaca }})" class="mt-3 text-white">
                        Desativar
                    </x-bladewind::button>
                </x-bladewind.modal>

            </x-tables.row>
        @empty
            <x-tables.empty cols="11" message="Nenhum teste de fumaça encontrado" />
        @endforelse
    </x-tables.body>
</x-tables.table>

<div class="mt-4">
    {{ $testesFumaca->appends(request()->query())->links() }}
</div>
