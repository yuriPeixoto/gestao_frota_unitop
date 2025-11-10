<!-- Mensagens de Sucesso/Erro -->
@if (session('success'))
    <div class="mb-6 rounded-r-lg border-l-4 border-green-400 bg-green-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-r-lg border-l-4 border-red-400 bg-red-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('notification'))
    <x-notification :notification="session('notification')" />
@endif

@if ($errors->any())
    <div class="mb-6 rounded-r-lg border-l-4 border-red-400 bg-red-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Corrija os seguintes erros:</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Formulário de Cotação -->
<form id="form-cotacao" method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <!-- Card Principal -->
    <div class="overflow-hidden rounded-lg bg-white shadow-lg">
        <div class="px-6 py-8">
            <!-- Sistema de Abas -->
            <section>
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900">
                        <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Gestão de Cotações
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">Gerencie e acompanhe as cotações da solicitação de compra.</p>
                </div>

                <!-- Botões das abas -->
                <div class="flex flex-wrap space-x-1">
                    <button type="button"
                        class="tablink mb-2 rounded-t-lg bg-gray-200 px-4 py-2 text-gray-700 transition-colors hover:bg-indigo-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        onclick="if(window.openTab) window.openTab(event, 'Aba1'); else if(window.openTabFallback) window.openTabFallback(event, 'Aba1'); else console.error('Nenhuma função openTab disponível');">
                        <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Dados Solicitação
                    </button>
                    @if ($cotacoes->solicitacoesCompra->tipo_solicitacao === 1)
                        <button type="button"
                            class="tablink mb-2 rounded-t-lg bg-gray-200 px-4 py-2 text-gray-700 transition-colors hover:bg-indigo-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            onclick="if(window.openTab) window.openTab(event, 'Aba2'); else if(window.openTabFallback) window.openTabFallback(event, 'Aba2'); else console.error('Nenhuma função openTab disponível');">
                            <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            Itens para cotação
                        </button>
                    @endif
                    <button type="button"
                        class="tablink mb-2 rounded-t-lg bg-gray-200 px-4 py-2 text-gray-700 transition-colors hover:bg-indigo-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        onclick="if(window.openTab) window.openTab(event, 'Aba3'); else if(window.openTabFallback) window.openTabFallback(event, 'Aba3'); else console.error('Nenhuma função openTab disponível');">
                        <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Gerar Cotação
                    </button>
                    <button type="button"
                        class="tablink mb-2 rounded-t-lg bg-gray-200 px-4 py-2 text-gray-700 transition-colors hover:bg-indigo-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        onclick="if(window.openTab) window.openTab(event, 'Aba4'); else if(window.openTabFallback) window.openTabFallback(event, 'Aba4'); else console.error('Nenhuma função openTab disponível');">
                        <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Editar Orçamento Recebido
                    </button>
                    <button type="button"
                        class="tablink mb-2 rounded-t-lg bg-gray-200 px-4 py-2 text-gray-700 transition-colors hover:bg-indigo-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        onclick="if(window.openTab) window.openTab(event, 'Aba5'); else if(window.openTabFallback) window.openTabFallback(event, 'Aba5'); else console.error('Nenhuma função openTab disponível');">
                        <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        Mapa de Cotação
                    </button>
                    <button type="button"
                        class="tablink mb-2 rounded-t-lg bg-gray-200 px-4 py-2 text-gray-700 transition-colors hover:bg-indigo-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        onclick="if(window.openTab) window.openTab(event, 'Aba6'); else if(window.openTabFallback) window.openTabFallback(event, 'Aba6'); else console.error('Nenhuma função openTab disponível');">
                        <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Imprimir Pedidos Aprovados
                    </button>
                </div>
            </section>

            <!-- Conteúdo das abas -->
            <div id="Aba1" class="tabcontent rounded-b-lg rounded-tr-lg border border-gray-200 bg-gray-50 p-6">
                @include('admin.compras.cotacoes.forms._dados')
            </div>

            <div id="Aba2"
                class="tabcontent hidden rounded-b-lg rounded-tr-lg border border-gray-200 bg-gray-50 p-6">
                @include('admin.compras.cotacoes.forms._itens')
            </div>

            <div id="Aba3"
                class="tabcontent hidden rounded-b-lg rounded-tr-lg border border-gray-200 bg-gray-50 p-6">
                @include('admin.compras.cotacoes.forms._gerar')
            </div>

            <div id="Aba4"
                class="tabcontent hidden rounded-b-lg rounded-tr-lg border border-gray-200 bg-gray-50 p-6">
                @include('admin.compras.cotacoes.forms._editar')
            </div>

            <div id="Aba5"
                class="tabcontent hidden rounded-b-lg rounded-tr-lg border border-gray-200 bg-gray-50 p-6">
                @include('admin.compras.cotacoes.forms._mapa')
            </div>

            <div id="Aba6"
                class="tabcontent hidden rounded-b-lg rounded-tr-lg border border-gray-200 bg-gray-50 p-6">
                @include('admin.compras.cotacoes.forms._imprimir')
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-start space-x-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
            @include('admin.compras.cotacoes._buttons')
        </div>
    </div>

    <x-bladewind.modal name="campos-obrigatorios-aba" cancel_button_label="" ok_button_label="Ok" type="error"
        title="Preencher Campos Obrigatórios">
        <b class="dados-aba"></b>
    </x-bladewind.modal>
</form>
@push('scripts')
    @include('admin.compras.cotacoes._scripts')
@endpush
