<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Baixa de Pneus') }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- ✅ BOTÃO CRIAR - APENAS SUPERUSER --}}
                @if(auth()->user()->isSuperuser())
                    <x-button-link href="{{ route('admin.descartepneus.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150 shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Cadastrar Baixa Manual
                    </x-button-link>
                @endif

                {{-- ✅ BOTÃO LAUDO MÚLTIPLO --}}
                <button type="button" id="btn-laudo-multiplo"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition-colors duration-150 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Anexar Laudo Múltiplo
                </button>

                {{-- BOTÃO AJUDA --}}
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                         class="origin-top-right absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-3 px-4">
                            <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                Ajuda - Baixa de Pneus
                            </p>
                            <p class="mt-2 text-xs leading-5 text-gray-600">
                                Esta tela gerencia o processo completo de baixa de pneus. Use os filtros para refinar sua busca.
                                <br><br>
                                <strong>Status dos Processos:</strong><br>
                                • <span class="text-yellow-600">Aguardando Início:</span> Pneu aguarda anexo do laudo<br>
                                • <span class="text-blue-600">Em Andamento:</span> Laudo anexado, aguarda finalização<br>
                                • <span class="text-green-600">Finalizado:</span> Processo concluído
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <x-bladewind::notification />

            {{-- ✅ FORMULÁRIO DE BUSCA COM MELHOR ESPAÇAMENTO --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                @include('admin.descartepneus._search-form')
            </div>

            {{-- ✅ ESTATÍSTICAS RÁPIDAS --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">Aguardando</p>
                            <p class="text-lg font-semibold text-yellow-900">{{ $totalAguardando }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">Em Andamento</p>
                            <p class="text-lg font-semibold text-blue-900">
                                {{ $totalEmAndamento }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">Finalizados</p>
                            <p class="text-lg font-semibold text-green-900">
                                {{ $totalFinalizados }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Total</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $totalGeral }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ TABELA COM MELHOR ORGANIZAÇÃO --}}
            <div class="overflow-hidden">
                <div id="results-table" class="overflow-x-auto">
                    @include('admin.descartepneus._table')
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ MODAL PARA LAUDO MÚLTIPLO --}}
    <div id="modal-laudo-multiplo" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Anexar Laudo a Múltiplos Pneus</h3>
                    <button type="button" id="btn-fechar-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="form-laudo-multiplo" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selecionar Pneus (Aguardando Descarte)
                        </label>
                        <div class="max-h-48 overflow-y-auto border rounded-md p-3 bg-gray-50">
                            @foreach($pneusAguardando as $pneu)
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" name="pneus_selecionados[]" value="{{ $pneu->id_pneu }}"
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-2 text-sm text-gray-700">
                                        Pneu {{ $pneu->id_pneu }} - {{ $pneu->tipoDescarte->descricao_tipo_descarte ?? 'N/A' }}
                                        <span class="text-gray-500">({{ $pneu->data_inclusao->format('d/m/Y') }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="arquivo_laudo" class="block text-sm font-medium text-gray-700 mb-2">
                            Arquivo do Laudo
                        </label>
                        <input type="file" name="arquivo_laudo" id="arquivo_laudo" accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                               required>
                        <p class="mt-1 text-sm text-gray-500">PDF, JPG, JPEG ou PNG. Máximo 2MB.</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" id="btn-cancelar-modal"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-150">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150">
                            Anexar Laudo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ✅ MODAL DE EXCLUSÃO MELHORADO --}}
    <x-bladewind.modal name="delete-pneu" cancel_button_label="Cancelar" ok_button_label="" type="error"
                       title="Confirmar Exclusão">
        <div class="text-center">
            <svg class="mx-auto mb-4 w-14 h-14 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <p class="mb-4">Tem certeza que deseja excluir a baixa do pneu <strong class="title"></strong>?</p>
            <p class="text-sm text-gray-500 mb-4">Esta ação não pode ser desfeita.</p>
        </div>
        <x-bladewind::button name="botao-delete" type="button" color="red" has_spinner="true" onclick="confirmDelete()"
                             class="mt-3 text-white w-full">
            Excluir Definitivamente
        </x-bladewind::button>
    </x-bladewind.modal>

    @push('scripts')
        @include('admin.descartepneus._scripts')

        <script>
            // ✅ SCRIPT PARA LAUDO MÚLTIPLO
            document.getElementById('btn-laudo-multiplo').addEventListener('click', function() {
                document.getElementById('modal-laudo-multiplo').classList.remove('hidden');
            });

            document.getElementById('btn-fechar-modal').addEventListener('click', function() {
                document.getElementById('modal-laudo-multiplo').classList.add('hidden');
            });

            document.getElementById('btn-cancelar-modal').addEventListener('click', function() {
                document.getElementById('modal-laudo-multiplo').classList.add('hidden');
            });

            document.getElementById('form-laudo-multiplo').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('{{ route("admin.descartepneus.anexar-laudo-multiplo") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Sucesso!', data.message, 'success');
                            document.getElementById('modal-laudo-multiplo').classList.add('hidden');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showNotification('Erro!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('Erro!', 'Erro ao processar solicitação', 'error');
                        console.error('Error:', error);
                    });
            });
        </script>
    @endpush
</x-app-layout>
