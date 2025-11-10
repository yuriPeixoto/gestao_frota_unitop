<style>
    div[id^="dropdown-"] {
        transition: opacity 0.2s ease, transform 0.2s ease;
        transform-origin: top center;
    }

    div[id^="dropdown-"].hidden {
        opacity: 0;
        transform: scale(0.95);
    }

    div[id^="dropdown-"]:not(.hidden) {
        opacity: 1;
        transform: scale(1);
    }
</style>

<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell></x-tables.head-cell>
            <x-tables.head-cell>Pago</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Prazo Indicação</x-tables.head-cell>
            <x-tables.head-cell>Nome Motorista</x-tables.head-cell>
            <x-tables.head-cell>Data <br> Infração</x-tables.head-cell>
            <x-tables.head-cell>Ait</x-tables.head-cell>
            <x-tables.head-cell>Ait <br> Originária</x-tables.head-cell>
            <x-tables.head-cell>Orgão <br> Autuador</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Valor</x-tables.head-cell>
            <x-tables.head-cell>Vencimento <br> do Boleto</x-tables.head-cell>
            <x-tables.head-cell>Local</x-tables.head-cell>
            <x-tables.head-cell>Gravidade</x-tables.head-cell>
            <x-tables.head-cell>Envio P/ <br> Financeiro</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($listagemnotificacoe as $index => $listagemnotificacoes)
            <x-tables.row :index="$index">
                <x-tables.cell class="relative">
                    @php $dropdownId = 'dropdown-' . $listagemnotificacoes->ait; @endphp

                    <div class="relative inline-block text-left">
                        <!-- Botão -->
                        <button onclick="toggleDropdown(event, '{{ $dropdownId }}')"
                            class="dropdown-toggle bg-white border px-4 py-2 rounded shadow flex items-center space-x-2">
                            <x-icons.gear class="w-4 h-4" />
                            <span>Ações</span>
                        </button>

                        <!-- Dropdown -->
                        <div id="{{ $dropdownId }}"
                            class="fixed z-[9999] mt-1 w-80 bg-white border border-gray-200 rounded-md shadow-lg hidden">
                            <ul class="py-1">
                                <li>
                                    <a onclick="handleDropdownAction('{{ $dropdownId }}', () => abrirCondutorForm({{ $listagemnotificacoes }}))"
                                        class="block px-4 py-3 text-gray-700 hover:bg-gray-100 flex items-center">
                                        <x-icons.circle-up class="w-4 h-4 mr-3 text-blue-600" />
                                        Indicar Motorista
                                    </a>
                                </li>
                                <li>
                                    <a onclick="handleDropdownAction('{{ $dropdownId }}', () => abrirModalPdf({{ $listagemnotificacoes }}, 'notificacao'))"
                                        class="block px-4 py-3 text-gray-700 hover:bg-gray-100 flex items-center">
                                        <x-icons.print class="w-4 h-4 mr-3 text-blue-600" />
                                        Notificação
                                    </a>
                                </li>
                                <li>
                                    <a onclick="handleDropdownAction('{{ $dropdownId }}', () => removerMotorista('{{ $listagemnotificacoes->ait }}'))"
                                        class="block px-4 py-3 text-gray-700 hover:bg-gray-100 flex items-center">
                                        <x-icons.trash class="w-4 h-4 mr-3 text-blue-600" />
                                        Remover Indicação
                                    </a>
                                </li>
                                <li>
                                    <a href="#"
                                        onclick="gerarFici(event, '{{ $listagemnotificacoes->id_smartec_notificacoes_sne_detran }}')"
                                        class="block px-4 py-3 text-gray-700 hover:bg-gray-100 flex items-center">
                                        <x-icons.print class="w-4 h-4 mr-3 text-blue-600" />
                                        Gerar FICI
                                    </a>
                                </li>
                                <li>
                                    <a href="#"
                                        onclick="openDescontoModal({{ $listagemnotificacoes->id_smartec_notificacoes_sne_detran }})"
                                        class="block px-4 py-3 text-gray-700 hover:bg-gray-100 flex items-center">
                                        <x-icons.money-check-dollar class="w-4 h-4 mr-3 text-blue-600" />
                                        Solicitar Desconto<br>40%
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </x-tables.cell>


                <x-tables.cell>
                    @if ($listagemnotificacoes->confirmacao_pagamento == 'true')
                    Sim
                    @else
                    Não
                    @endif
                </x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->placa }}</x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->prazo_indicacao }}</x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->motorista_nome }}</x-tables.cell>
                <x-tables.cell>{{ format_date($listagemnotificacoes->data_infracao, 'd/m/Y') }}</x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->ait }}</x-tables.cell>
                <x-tables.cell>{{$listagemnotificacoes->ait_originaria }}</x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->orgao_autuador }}</x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->descricao }}</x-tables.cell>
                <x-tables.cell>R$ {{ number_format($listagemnotificacoes->valor_a_pagar, 2, ',', '.') }}</x-tables.cell>
                <x-tables.cell>{{ format_date($listagemnotificacoes->boleto_vencimento, 'd/m/Y') }}</x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->local }}</x-tables.cell>
                <x-tables.cell>{{$listagemnotificacoes->gravidade}}</x-tables.cell>
                <x-tables.cell>{{ $listagemnotificacoes->confirmacao_pagamento_manual }}</x-tables.cell>

                <x-bladewind.modal name="condutorForm" cancel_button_label="Cancelar" ok_button_label=""
                    title="Condutor" size="xl">
                    <!-- Informações do Veículo -->
                    <div class="grid grid-cols-4 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Placa:</label>
                            <div class="bg-gray-200 px-3 py-2 rounded text-sm font-mono placa-info"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Renavam:</label>
                            <div class="bg-gray-200 px-3 py-2 rounded text-sm renavam-info"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ait:</label>
                            <div id="ait" class="bg-gray-200 px-3 py-2 rounded text-sm ait-info"></div>
                        </div>
                        <div>
                            {{-- Condutor --}}
                            <label for="condutor">Condutor</label>
                            <select name="condutor" id="condutor" class="form-control">
                                <option value="">Selecione...</option>
                                @foreach($condutor as $item)
                                <option value="{{ $item['value'] }}" {{ old('condutor')==$item['value'] ? 'selected'
                                    : '' }}>
                                    {{ $item['label'] }}
                                </option>
                                @endforeach
                            </select>
                            @error('condutor')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            {{-- Desconto --}}
                            <label for="desconto">Desconto</label>
                            <select name="desconto" id="desconto" class="form-control">
                                <option value="">Selecione...</option>
                                <option value="true" {{ old('desconto')=='true' ? 'selected' : '' }}>Sim</option>
                                <option value="false" {{ old('desconto')=='false' ? 'selected' : '' }}>Não</option>
                            </select>
                        </div>
                    </div>

                    <!-- Link de impressão -->
                    <div class="mb-4">
                        <button onclick="indicarMotorista(getCondutorData())"
                            class="inline-flex items-center text-black-600 hover:text-black-800 text-sm font-medium bg-white border px-4 py-2 rounded shadow">
                            Indicar Motorista
                        </button>
                    </div>
                </x-bladewind.modal>

                <x-bladewind.modal name="solicitarDesconto" cancel_button_label="" ok_button_label=""
                    title="Desconto 40%" size="big">
                    <form id="descontoForm" method="POST">
                        @csrf
                        <input type="hidden" id="multaId" name="id_smartec_notificacoes_sne_detran" value="">

                        <div>
                            <p class="text-xs text-gray-500 mt-1">
                                <b>Atenção: Solicitação de Desconto de 40% na Multa de Trânsito
                                    Ao prosseguir com a solicitação do desconto de 40% nesta multa, você estará
                                    reconhecendo
                                    a infração e abrindo mão do direito de apresentar defesa ou recurso administrativo.
                                    <br><br>
                                    ⚠️ <b>Este processo é irreversível.</b>
                                    Após a confirmação, não será possível cancelar ou alterar a solicitação. <br><br>
                                    📅 O boleto com o valor atualizado poderá levar até 72 horas para ser liberado.
                                    Confirme apenas se estiver ciente e de acordo com as condições estabelecidas pelo
                                    órgão
                                    de trânsito.
                            </p>
                        </div>

                        <!-- Botões de confirmação -->
                        <div class="mb-4 mt-5 gap-4 flex justify-end">
                            <button type="button" onclick="submitDescontoForm()"
                                class="inline-flex items-center text-black-600 hover:text-black-800 text-sm font-medium bg-gray-200 border px-4 py-2 rounded shadow">
                                Sim
                            </button>
                            <button type="button" onclick="hideModal('solicitarDesconto')"
                                class="inline-flex items-center text-black-600 hover:text-black-800 text-sm font-medium bg-gray-200 border px-4 py-2 rounded shadow">
                                Não
                            </button>
                        </div>
                    </form>
                </x-bladewind.modal>


                <div id="pdfModal" class="pdf-modal-overlay">
                    <div class="pdf-modal-container">
                        <!-- Header do Modal -->
                        <div class="pdf-modal-header">
                            <div class="pdf-modal-title">Documento Digital - Visualização de Impressão</div>
                            <div class="pdf-modal-controls">
                                <button class="close-btn" onclick="fecharModalPdf()">&times;</button>
                            </div>
                        </div>

                        <!-- Container do PDF -->
                        <div class="pdf-viewer-container">
                            <div class="pdf-loading" id="pdfLoading">
                                <div class="loading-spinner"></div>
                                Carregando documento...
                            </div>
                            <iframe id="pdfFrame" class="pdf-iframe" style="display: none;"
                                onload="window.pdfCarregado()"></iframe>
                        </div>
                    </div>
                </div>

            </x-tables.row>
            @empty
            <x-tables.empty cols="15" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $listagemnotificacoe->links() }}
    </div>
</div>


<script>
    function closeAllDropdowns() {
        document.querySelectorAll('div[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }

    // Manipula ações do dropdown
    function handleDropdownAction(dropdownId, action) {
        closeAllDropdowns();
        if (action && typeof action === 'function') {
            action();
        }
    }

    // Alterna o dropdown
    function toggleDropdown(event, dropdownId) {
        event.stopPropagation(); // Impede a propagação do evento
        
        closeAllDropdowns();
        
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        
        const button = event.target.closest('button');
        if (!button) return;
        
        const isHidden = dropdown.classList.contains('hidden');
        
        if (isHidden) {
            const buttonRect = button.getBoundingClientRect();
            const dropdownHeight = dropdown.offsetHeight;
            
            // Posicionamento fixo na tela
            dropdown.style.position = 'fixed';
            dropdown.style.left = `${buttonRect.left}px`;
            dropdown.style.width = `${buttonRect.width + 100}px`; // Largura aumentada
            
            // Verifica espaço abaixo do botão
            const spaceBelow = window.innerHeight - buttonRect.bottom;
            if (spaceBelow > dropdownHeight || spaceBelow > buttonRect.top) {
                dropdown.style.top = `${buttonRect.bottom + window.scrollY}px`;
            } else {
                dropdown.style.top = `${buttonRect.top + window.scrollY - dropdownHeight}px`;
            }
            
            dropdown.style.zIndex = '9999';
        }
        
        dropdown.classList.toggle('hidden');
    }

    // Fecha dropdowns ao clicar fora
    document.addEventListener('click', function() {
        closeAllDropdowns();
    });

    // Impede que o dropdown feche ao clicar nele
    document.querySelectorAll('div[id^="dropdown-"]').forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>