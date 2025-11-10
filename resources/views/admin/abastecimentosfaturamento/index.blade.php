<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Abastecimento para Faturamento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Abastecimento
                                    para Faturamento</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela tem como finalidade exibir os registros de Abastecimentos para
                                    faturamento. Os campos abaixo servem para realizar buscas nos registros lançados!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
    <div class="resolution">
        <div class="max-w-[1600px]">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 bg-white border-b border-gray-200">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <!-- Search Form -->
                        @include('admin.abastecimentosfaturamento._search-form')

                        <!-- Results Table with Loading -->
                        <div class="mt-6 relative max-w-[1600px]">
                            <!-- Loading indicator -->
                            <div id="table-loading"
                                class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                                <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                            </div>

                            <!-- Actual results -->
                            <div id="results-table"
                                class="opacity-0 transition-opacity duration-300 table-responsive relative">
                                @include('admin.abastecimentosfaturamento._table')
                                <a href="#" class="botao-flutuante botao-scroll-left" id="scroll-left-btn"
                                    title="Voltar">
                                    ←
                                </a>
                                <a href="#" class="botao-flutuante botao-scroll-right" id="scroll-right-btn"
                                    title="Avançar">
                                    →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @push('scripts')
    @include('admin.abastecimentosfaturamento._scripts')

    @if (session('limparSelecao'))
    <script>
        localStorage.removeItem('selectedRows');
    </script>
    @endif

    @if (session('notification') && is_array(session('notification')))
    <script>
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}',
                        '{{ session('notification')['type'] }}');
    </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
                    // Checar e corrigir larguras que possam causar overflow
                    document.querySelectorAll('table').forEach(table => {
                        if (table.offsetWidth > table.parentElement.offsetWidth) {
                            table.classList.add('table-responsive');
                        }
                    });

                    const problematicContainers = document.querySelectorAll(
                        '.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg');

                    problematicContainers.forEach(container => {
                        const smartSelects = container.querySelectorAll('[x-data*="simpleSelect"]');

                        smartSelects.forEach(smartSelect => {
                            smartSelect.classList.add('smart-select-container');

                            const dropdownButton = smartSelect.querySelector('[x-ref="button"]');

                            if (dropdownButton) {
                                dropdownButton.addEventListener('click', function() {
                                    container.classList.toggle('dropdown-active');
                                });

                                document.addEventListener('click', function(event) {
                                    if (!smartSelect.contains(event.target)) {
                                        container.classList.remove('dropdown-active');
                                    }
                                });
                            }
                        });
                    });
                });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scrollLeftBtn = document.getElementById('scroll-left-btn');
            const scrollRightBtn = document.getElementById('scroll-right-btn');
            const resultsTable = document.getElementById('results-table');
            
            if (scrollLeftBtn && scrollRightBtn && resultsTable) {
                const tableContainer = resultsTable.querySelector('.table-responsive');
                
                if (tableContainer) {
                    // Botão direito - avança
                    scrollRightBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        tableContainer.scrollLeft += 400;
                    });
                    
                    // Botão esquerdo - volta
                    scrollLeftBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        tableContainer.scrollLeft -= 1400;
                    });
                    
                    // Atualiza visibilidade dos botões baseado no scroll
                    function updateButtonVisibility() {
                        const atStart = tableContainer.scrollLeft === 0;
                        const atEnd = tableContainer.scrollLeft + tableContainer.clientWidth >= tableContainer.scrollWidth - 1;
                        
                        scrollLeftBtn.style.display = atStart ? 'none' : 'flex';
                        scrollRightBtn.style.display = atEnd ? 'none' : 'flex';
                    }
                    
                    tableContainer.addEventListener('scroll', updateButtonVisibility);
                    window.addEventListener('resize', updateButtonVisibility);
                    updateButtonVisibility(); // Inicial
                }
            }
        });
    </script>
    @endpush
</x-app-layout>