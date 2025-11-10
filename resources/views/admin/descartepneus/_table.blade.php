@php
use Illuminate\Support\Facades\Storage;
@endphp

<x-tables.table>
    <x-tables.header>
        <x-tables.head-cell>Cód. Baixa</x-tables.head-cell>
        <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
        <x-tables.head-cell>Nº Fogo</x-tables.head-cell>
        <x-tables.head-cell>Tipo Descarte</x-tables.head-cell>
        <x-tables.head-cell>Status</x-tables.head-cell>
        <x-tables.head-cell>Origem</x-tables.head-cell>
        <x-tables.head-cell>Valor Venda</x-tables.head-cell>
        <x-tables.head-cell>Ações</x-tables.head-cell>
    </x-tables.header>

    <x-tables.body>
        @forelse ($descartePneus as $index => $descartePneu)
        <x-tables.row :index="$index">
            {{-- ✅ CÓDIGO DA BAIXA --}}
            <x-tables.cell>
                <span class="font-medium text-gray-900">
                    {{ $descartePneu->id_descarte_pneu }}
                </span>
            </x-tables.cell>

            {{-- ✅ DATA INCLUSÃO --}}
            <x-tables.cell nowrap>
                <div class="text-sm">
                    <div class="font-medium text-gray-900">
                        {{ $descartePneu->data_inclusao->format('d/m/Y') }}
                    </div>
                    <div class="text-gray-500">
                        {{ $descartePneu->data_inclusao->format('H:i') }}
                    </div>
                </div>
            </x-tables.cell>

            {{-- ✅ NÚMERO DO FOGO --}}
            <x-tables.cell>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $descartePneu->id_pneu ?? 'N/A' }}
                </span>
            </x-tables.cell>

            {{-- ✅ TIPO DESCARTE --}}
            <x-tables.cell>
                <span class="text-sm font-medium text-gray-900">
                    {{ $descartePneu->tipoDescarte->descricao_tipo_descarte ?? 'Não Informado' }}
                </span>
            </x-tables.cell>

            {{-- ✅ STATUS COM BADGES COLORIDOS --}}
            <x-tables.cell>
                @switch($descartePneu->status_processo ?? 'aguardando_inicio')
                @case('aguardando_inicio')
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                    </svg>
                    Aguardando Início
                </span>
                @break
                @case('em_andamento')
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                            clip-rule="evenodd" />
                    </svg>
                    Em Andamento
                </span>
                @break
                @case('finalizado')
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    Finalizado
                </span>
                @if($descartePneu->finalizado_em)
                <div class="text-xs text-gray-500 mt-1">
                    {{ \Carbon\Carbon::parse($descartePneu->finalizado_em)->format('d/m/Y H:i') }}
                </div>
                @endif
                @break
                @default
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Indefinido
                </span>
                @endswitch
            </x-tables.cell>

            {{-- ✅ ORIGEM COM BADGES --}}
            <x-tables.cell>
                @switch($descartePneu->origem ?? 'manual')
                @case('manual')
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                    </svg>
                    Manual
                </span>
                @break
                @case('manutencao')
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                            clip-rule="evenodd" />
                    </svg>
                    Manutenção
                </span>
                @break
                @case('nao_informado')
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 3a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 000 2v4a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Não Informado
                </span>
                @break
                @default
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    N/A
                </span>
                @endswitch
            </x-tables.cell>

            {{-- ✅ VALOR VENDA --}}
            <x-tables.cell>
                @if($descartePneu->valor_venda_pneu)
                <span class="text-sm font-medium text-green-600">
                    R$ {{ number_format((float)$descartePneu->getRawOriginal('valor_venda_pneu'), 2, ',', '.') }}
                </span>
                @else
                <span class="text-sm text-gray-400">-</span>
                @endif
            </x-tables.cell>

            {{-- ✅ AÇÕES MELHORADAS --}}
            <x-tables.cell>
                <div class="flex items-center space-x-2">
                    {{-- BOTÃO EDITAR --}}
                    @if($descartePneu->status_processo !== 'finalizado' || auth()->user()->isSuperuser())
                    <a href="{{ route('admin.descartepneus.edit', $descartePneu->id_descarte_pneu) }}"
                        class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150"
                        title="Editar">
                        <x-icons.pencil class="h-3 w-3" />
                    </a>
                    @else
                    <span class="inline-flex items-center p-1.5 rounded-full bg-gray-200 text-gray-400"
                        title="Processo finalizado">
                        <x-icons.pencil class="h-3 w-3" />
                    </span>
                    @endif

                    {{-- BOTÃO FINALIZAR --}}
                    @if($descartePneu->status_processo !== 'finalizado' && ($descartePneu->nome_arquivo ||
                    $descartePneu->id_foto))
                    <button type="button" onclick="finalizarDescarte({{ $descartePneu->id_descarte_pneu }})"
                        class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150"
                        title="Finalizar Processo">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    @endif

                    {{-- BOTÃO EXCLUIR --}}
                    @if(auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 17, 25]) ||
                    ($descartePneu->status_processo !== 'finalizado' || auth()->user()->isSuperuser()))
                    <form onsubmit="return false;">
                        @csrf
                        <button type="button"
                            data-url="{{ route('admin.descartepneus.destroy', $descartePneu->id_descarte_pneu) }}"
                            onclick="destroyDescarte(this)"
                            class="inline-flex items-center p-1.5 rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700"
                            title="Excluir">
                            <x-icons.trash class="h-3 w-3" />
                        </button>
                    </form>
                    @endif

                    {{-- BOTÃO VISUALIZAR LAUDO --}}
                    @if ($descartePneu->nome_arquivo || $descartePneu->id_foto)
                    @php
                    // Verificar se é arquivo no storage Laravel ou base64 legado
                    $arquivoExiste = false;
                    $urlArquivo = '#';

                    if ($descartePneu->nome_arquivo && Storage::disk('public')->exists($descartePneu->nome_arquivo)) {
                    $arquivoExiste = true;
                    $urlArquivo = Storage::disk('public')->url($descartePneu->nome_arquivo);
                    } elseif ($descartePneu->id_foto) {
                    // Para arquivos em base64 (sistema legado)
                    $arquivoExiste = true;
                    $urlArquivo = route('admin.descartepneus.obter-laudo', $descartePneu->id_descarte_pneu);
                    }
                    @endphp

                    <a href="{{ $urlArquivo }}"
                        class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-150"
                        title="{{ $arquivoExiste ? 'Visualizar Laudo' : 'Laudo não encontrado' }}" {{ !$arquivoExiste
                        ? 'onclick="event.preventDefault(); alert(\' Arquivo não encontrado no servidor.\')"' : '' }} {{
                        $arquivoExiste && $descartePneu->nome_arquivo ? 'target="_blank"' : '' }}>
                        <x-icons.document class="h-3 w-3" />
                    </a>
                    @else
                    {{-- INDICADOR DE SEM LAUDO --}}
                    <span class="inline-flex items-center p-1.5 rounded-full bg-yellow-100 text-yellow-600"
                        title="Aguardando Laudo">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    @endif
                </div>
            </x-tables.cell>

        </x-tables.row>

        {{-- ✅ MODAL DE CONFIRMAÇÃO INDIVIDUAL --}}
        <x-bladewind.modal name="delete-{{ $descartePneu->id_descarte_pneu }}" cancel_button_label="Cancelar"
            ok_button_label="" type="error" title="Confirmar Exclusão">
            <div class="text-center">
                <svg class="mx-auto mb-4 w-14 h-14 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="mb-4">Tem certeza que deseja excluir a baixa do pneu <strong>{{ $descartePneu->id_pneu
                        }}</strong>?</p>

                @if($descartePneu->status_processo === 'finalizado')
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Atenção:</strong> Este processo já foi finalizado. A exclusão irá reverter o
                                status do pneu.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <p class="text-sm text-gray-500 mb-4">Esta ação não pode ser desfeita.</p>
            </div>
            <x-bladewind::button name="botao-delete-{{ $descartePneu->id_descarte_pneu }}" type="button" color="red"
                onclick="confirmarExclusao({{ $descartePneu->id_descarte_pneu }})" class="mt-3 text-white w-full">
                Excluir Definitivamente
            </x-bladewind::button>
        </x-bladewind.modal>

        @empty
        <x-tables.row>
            <x-tables.cell colspan="8" class="text-center py-12">
                <div class="flex flex-col items-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma baixa de pneu encontrada</h3>
                    <p class="text-gray-500 text-center max-w-sm">
                        Não há registros de baixa de pneus que correspondam aos filtros aplicados. Tente ajustar os
                        critérios de busca.
                    </p>
                    @if(auth()->user()->isSuperuser())
                    <a href="{{ route('admin.descartepneus.create') }}"
                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Criar Primeira Baixa
                    </a>
                    @endif
                </div>
            </x-tables.cell>
        </x-tables.row>
        @endforelse
    </x-tables.body>
</x-tables.table>

{{-- ✅ PAGINAÇÃO MELHORADA --}}
<div class="mt-6 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-b-lg">
    <div class="flex flex-1 justify-between sm:hidden">
        @if($descartePneus->hasPages())
        <div class="flex space-x-2">
            @if($descartePneus->onFirstPage())
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md">Anterior</span>
            @else
            <a href="{{ $descartePneus->previousPageUrl() }}"
                class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Anterior</a>
            @endif

            @if($descartePneus->hasMorePages())
            <a href="{{ $descartePneus->nextPageUrl() }}"
                class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Próximo</a>
            @else
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md">Próximo</span>
            @endif
        </div>
        @endif
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Mostrando <span class="font-medium">{{ $descartePneus->firstItem() ?? 0 }}</span> até
                <span class="font-medium">{{ $descartePneus->lastItem() ?? 0 }}</span> de
                <span class="font-medium">{{ $descartePneus->total() }}</span> resultados
            </p>
        </div>
        <div>
            {{ $descartePneus->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ✅ FUNÇÃO PARA FINALIZAR DESCARTE
        function finalizarDescarte(id) {
            if (confirm('Tem certeza que deseja finalizar este processo de baixa? Esta ação enviará notificações e não poderá ser desfeita.')) {
                fetch(`/admin/descartepneus/${id}/finalizar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Sucesso!', data.message, 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showNotification('Erro!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Erro!', 'Erro ao finalizar processo', 'error');
                    });
            }
        }
        function destroyDescarte(btn) {
            const url = btn.dataset.url;
            if (!confirm('Confirma exclusão?')) return;

            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const token = tokenEl ? tokenEl.getAttribute('content') : '';

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({}) // corpo pode ficar vazio
            })
            .then(response => {
                if (!response.ok) return response.json().then(j => Promise.reject(j));
                return response.json();
            })
            .then(json => {
                // sucesso: remover linha da tabela (assumindo que esteja em <tr>)
                const row = btn.closest('tr');
                if (row) row.remove();

                // notificação simples (troque por seu toast)
                alert(json.notification?.message ?? 'Excluído com sucesso');
            })
            .catch(err => {
                console.error(err);
                const message = err?.notification?.message ?? 'Erro ao excluir';
                alert(message);
            });
        }
</script>
@endpush