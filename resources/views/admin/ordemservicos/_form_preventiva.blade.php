@if (session('error'))
    <div class="mb-4 bg-red-50 p-4 rounded">
        <p class="text-red-600">{{ session('error') }}</p>
    </div>
@endif
@if (session('notification'))
    <x-notification :notification="session('notification')" />
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <form id="OrdemServicoForm" method="POST" action="{{ $action }}" class="space-y-4">
                @csrf
                @if ($method === 'PUT')
                    @method('PUT')
                @endif

                <!-- Progress Stepper -->
                @if (isset($steps) && is_array($steps) && count($steps) > 0)
                    <div
                        class="w-full py-3 px-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-sm border border-blue-100 mb-3">
                        <div class="relative max-w-4xl mx-auto">
                            <!-- Container dos Steps -->
                            <div class="flex items-start justify-between relative">
                                @foreach ($steps as $index => $step)
                                    @php
                                        $currentStatus =
                                            isset($ordem) && $ordem->statusOrdemServico
                                                ? $ordem->statusOrdemServico->situacao_ordem_servico
                                                : $steps[0] ?? 'PRÉ-O.S';

                                        $currentIndex = array_search($currentStatus, $steps);
                                        $isActive = $currentIndex !== false && $currentIndex >= $index;
                                        $isCurrent = $currentIndex === $index;
                                        $isCanceled = strtoupper($currentStatus) === 'CANCELADA';
                                    @endphp

                                    <div class="flex flex-col items-center relative flex-1">
                                        <!-- Círculo do Step -->
                                        <div class="relative mb-1.5 flex items-center justify-center"
                                            style="height: 40px;">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 shadow-md relative z-20
                                                @if ($isCanceled) bg-red-500 border-red-600 text-white
                                                @elseif($isActive)
                                                    @if ($isCurrent)
                                                        bg-blue-500 border-blue-600 text-white ring-2 ring-blue-200 animate-pulse
                                                    @else
                                                        bg-green-500 border-green-600 text-white @endif
@else
bg-white border-gray-300 text-gray-400 hover:border-gray-400
                                                @endif">

                                                @if ($isCanceled)
                                                    <!-- Ícone de Cancelado -->
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @elseif($isActive && !$isCurrent)
                                                    <!-- Ícone de Concluído -->
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @elseif($isCurrent)
                                                    <!-- Ícone de Em Progresso -->
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <!-- Número do Step -->
                                                    <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                                @endif
                                            </div>

                                            <!-- Efeito de brilho para step atual -->
                                            @if ($isCurrent && !$isCanceled)
                                                <div class="absolute inset-0 rounded-full bg-blue-400 opacity-20 animate-ping z-10"
                                                    style="width: 40px; height: 40px; margin: auto;"></div>
                                            @endif
                                        </div>

                                        <!-- Nome do Step -->
                                        <div class="text-center px-0.5" style="min-height: 28px; max-width: 80px;">
                                            <span
                                                class="text-[10px] font-medium leading-tight transition-colors duration-200 block
                                                @if ($isCanceled) text-red-600
                                                @elseif($isActive)
                                                    @if ($isCurrent)
                                                        text-blue-700 font-semibold
                                                    @else
                                                        text-green-700 @endif
@else
text-gray-500
                                                @endif">
                                                {{ $step }}
                                            </span>
                                        </div>

                                        <!-- Linha de Conexão -->
                                        @if ($index < count($steps) - 1)
                                            <div class="absolute h-0.5 z-0 transition-all duration-500
                                                @if ($isCanceled) bg-red-300
                                                @elseif($currentIndex !== false && $currentIndex > $index)
                                                    bg-green-400 shadow-sm
                                                @else
                                                    bg-gray-300 @endif"
                                                style="top: 20px; left: calc(50% + 20px); right: calc(-50% + 20px);">
                                                <!-- Animação de progresso -->
                                                @if ($currentIndex !== false && $currentIndex > $index && !$isCanceled)
                                                    <div
                                                        class="h-full bg-gradient-to-r from-green-400 to-green-500 animate-pulse shadow-sm">
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Status atual -->
                            <div class="mt-3 text-center">
                                @php
                                    $currentOrderStatus =
                                        isset($ordem) && $ordem->statusOrdemServico
                                            ? $ordem->statusOrdemServico->situacao_ordem_servico
                                            : $steps[0] ?? 'PRÉ-O.S';
                                    $isOrderCanceled = strtoupper($currentOrderStatus) === 'CANCELADA';
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold shadow-sm
                                    @if ($isOrderCanceled) bg-red-100 text-red-800 border border-red-200
                                    @else
                                        bg-blue-100 text-blue-800 border border-blue-200 @endif">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Status Atual: {{ $currentOrderStatus }}
                                </span>
                            </div>
                        </div>
                    </div>
                    </br>
                @endif
                <!-- Cabeçalho -->
                <div class="mx-auto">
                    <!-- Botões das abas -->
                    <div class="flex space-x-1">
                        <button type="button"
                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                            onclick="openTab(event, 'Aba1')">
                            Dados O.S.
                        </button>
                        <button type="button"
                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                            onclick="openTab(event, 'Aba2')">
                            Serviços
                        </button>
                        <button type="button"
                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                            onclick="openTab(event, 'Aba3')">
                            Peças
                        </button>
                        <button type="button"
                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                            onclick="openTab(event, 'Aba4')">
                            Reclamação do Veículo
                        </button>
                        {{-- <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba5')">
                                NF
                            </button> --}}
                    </div>
                </div>

                <!-- Conteúdo das abas -->
                <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
                    @include('admin.ordemservicos._dados_preventiva')
                </div>

                <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                    @include('admin.ordemservicos._servicos_preventiva')
                </div>

                <div id="Aba3" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                    @include('admin.ordemservicos._pecas_preventiva')
                </div>

                <div id="Aba4" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                    @include('admin.ordemservicos._reclamacao')
                </div>

                <div id="Aba5" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                    @include('admin.ordemservicos._NF_preventiva')

                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-left space-x-4 mt-6">
                    @include('admin.ordemservicos._buttons')
                </div>

                <x-bladewind.modal name="campos-obrigatorios-aba" cancel_button_label="" ok_button_label="Ok"
                    type="error" title="Preencher Campos Obrigatórios">
                    <b class="dados-aba"></b>
                </x-bladewind.modal>
            </form>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('js/manutencao/ordemservico/socorro.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/manutencao/ordemservico/reclamacoes.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/manutencao/ordemservico/servicos_preventiva.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/manutencao/ordemservico/pecas.js') }}?v={{ time() }}"></script>
    @include('admin.ordemservicos._scripts')
@endpush
