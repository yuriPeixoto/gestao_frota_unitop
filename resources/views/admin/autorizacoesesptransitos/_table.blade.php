@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div id="results-table" class="opacity-0 transition-opacity duration-300">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Filial do Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Tipo Certificado</x-tables.head-cell>
            <x-tables.head-cell>Nº Certificado</x-tables.head-cell>
            <x-tables.head-cell>Emissão</x-tables.head-cell>
            <x-tables.head-cell>Vencimento</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Certificado<br>Ativo</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($autorizacoesesptransitos as $index => $autorizacao)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $autorizacao->id_certificado_veiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $autorizacao->veiculo->placa ?? 'NÃO INFORMADO' }}</x-tables.cell>
                    <x-tables.cell>{{ $autorizacao->veiculo->filialVeiculo->name ?? 'NÃO INFORMADO' }}</x-tables.cell>
                    <x-tables.cell>{{ $autorizacao->tipocertificado->descricao_certificado ?? 'NÃO INFORMADO' }}</x-tables.cell>
                    <x-tables.cell>{{ $autorizacao->numero_certificado }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($autorizacao->data_certificacao, 'd/m/Y') }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($autorizacao->data_vencimento, 'd/m/Y') }}</x-tables.cell>
                    <x-tables.cell nowrap>
                        <span id="status-badge-{{ $autorizacao->situacao }}"
                            class="px-2 py-1 text-xs font-medium rounded-full 
                            @if ($autorizacao->situacao == 'A Vencer') bg-green-100 text-green-800
                            @elseif($autorizacao->situacao == 'Vencido') bg-red-100 text-red-800
                            @elseif($autorizacao->situacao == 'Cancelado') bg-gray-100 text-gray-800 @endif">
                            {{ $autorizacao->situacao }}
                        </span>
                    </x-tables.cell>
                    <x-tables.cell>
                        <span id="status-badge-{{ $autorizacao->id_certificado_veiculo }}"
                            class="px-2 py-1 text-xs font-medium inline-flex items-center rounded-full {{ $autorizacao->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            @if ($autorizacao->is_ativo)
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
                            @if ($autorizacao->is_ativo)
                                <x-tooltip content="Editar" placement="top">
                                    <a href="{{ route('admin.autorizacoesesptransitos.edit', $autorizacao->id_certificado_veiculo) }}"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <x-icons.pencil class="h-3 w-3" />
                                    </a>
                                </x-tooltip>
                            @else
                                <x-tooltip content="Editar" placement="top">
                                    <span
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 opacity-25"
                                        onclick="return alert('Este registro não pode ser editado, pois está inativo')">
                                        <x-icons.pencil class="h-3 w-3" />
                                    </span>
                                </x-tooltip>
                            @endif

                            @if ((auth()->user()->is_superuser || in_array(auth()->user()->id, [3, 4, 25])) && $autorizacao->is_ativo)
                                <x-tooltip content="Desativar" placement="top">
                                    <button type="button"
                                        onclick="confirmarExclusao({{ $autorizacao->id_certificado_veiculo }})"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <x-icons.disable class="h-3 w-3" />
                                    </button>
                                </x-tooltip>
                            @else
                                <x-tooltip content="Desativar" placement="top">
                                    <span
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 opacity-25"
                                        onclick="return alert('Este registro não pode ser editado, pois está inativo')">
                                        <x-icons.disable class="h-3 w-3" />
                                    </span>
                                </x-tooltip>
                            @endif

                            @if ($autorizacao->is_ativo)
                                <x-tooltip content="Replicar Certificado" placement="top">
                                    <a href="{{ route('admin.autorizacoesesptransitos.replicar', $autorizacao->id_certificado_veiculo) }}"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                        </svg>
                                    </a>
                                </x-tooltip>
                            @else
                                <x-tooltip content="Replicar Certificado" placement="top">
                                    <span
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-400 opacity-25"
                                        onclick="return alert('Este registro não pode ser replicado, pois está inativo')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                        </svg>
                                    </span>
                                </x-tooltip>
                            @endif

                            @if ($autorizacao->caminho_arquivo)
                                <x-tooltip content="Visualiza Arquivo" placement="top">
                                    <a href="{{ $autorizacao->caminho_arquivo && Storage::disk('public')->exists($autorizacao->caminho_arquivo) ? Storage::url($autorizacao->caminho_arquivo) : '#' }}"
                                        target="{{ $autorizacao->caminho_arquivo && Storage::disk('public')->exists($autorizacao->caminho_arquivo) ? '_blank' : '_self' }}"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white {{ $autorizacao->caminho_arquivo && Storage::disk('public')->exists($autorizacao->caminho_arquivo) ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 cursor-not-allowed' }}"
                                        @if (!$autorizacao->caminho_arquivo || !Storage::disk('public')->exists($autorizacao->caminho_arquivo)) aria-disabled="true" @endif>
                                        <x-icons.document class="h-3 w-3" />
                                    </a>
                                </x-tooltip>
                            @else
                                <x-tooltip content="Visualiza Arquivo" placement="top">
                                    <span
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-gray-500 cursor-not-allowed opacity-25">
                                        <x-icons.document class="h-3 w-3" />
                                    </span>
                                </x-tooltip>
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="10" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $autorizacoesesptransitos->links() }}
    </div>
</div>
