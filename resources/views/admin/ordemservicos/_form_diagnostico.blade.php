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
            <div x-data="OrdemServicoForm()">
                <form id="OrdemServicoForm" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif
                    <!------- conteudo status ------->

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
                            <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba5')">
                                NF
                            </button>
                        </div>
                    </div>

                    <!-- Conteúdo das abas -->
                    <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicos._dados_diagnostico')
                    </div>

                    <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicos._servicos_diagnostico')
                    </div>

                    <div id="Aba3" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicos._pecas_diagnostico')
                    </div>

                    <div id="Aba4" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicos._reclamacao')
                    </div>

                    <div id="Aba5" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicos._NF_diagnostico')

                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-left space-x-4 mt-6">
                        @include('admin.ordemservicos._buttons')
                    </div>

                    <x-bladewind.modal name="campos-obrigatorios-aba" cancel_button_label="" ok_button_label="Ok"
                        type="error" title="Preencher Campos Obrigatórios">
                        <b class="dados-aba"></b>
                    </x-bladewind.modal>

                    <!-- Campo Hidden para Itens - Alpine.js removido -->
                    {{-- <input type="hidden" name="items_servicos" :value="JSON.stringify(itemsServicos)">
                    <input type="hidden" name="items_pecas" :value="JSON.stringify(itemsPecas)">
                    <input type="hidden" name="items_reclamacoes" :value="JSON.stringify(itemsReclamacoes)">
                    <input type="hidden" name="items_nf" :value="JSON.stringify(itemsNF)"> --}}
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    @include('admin.ordemservicos._scripts')
@endpush
