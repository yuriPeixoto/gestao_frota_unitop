@props([
    'modalId',
    'title' => 'Modal',
    'columns' => [],
    'fetchUrl' => null,
    'itemsPerPage' => 2,
    'maxWidth' => '7xl',
    'triggerClass' => 'modal-trigger-' . $modalId,
])

<!-- Modal -->
<div id="modal-{{ $modalId }}" class="fixed inset-0 z-50 flex items-center justify-center hidden"
    data-columns="{{ json_encode($columns) }}" data-fetch-url="{{ $fetchUrl }}" style="background: rgba(0,0,0,0.25);">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-{{ $maxWidth }} p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-700">{{ $title }}</h2>
            <button onclick="closeTableModal('{{ $modalId }}')"
                class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        </div>

        <div class="mb-4 overflow-x-auto rounded-lg shadow-md">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead>
                    <tr class="bg-gray-100">
                        @foreach ($columns as $column)
                            <th class="px-2 py-1">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="modal-table-body-{{ $modalId }}" class="divide-y divide-gray-200 bg-white">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </tbody>
            </table>
            <div id="pagination-{{ $modalId }}" class="pagination-container"></div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        // Namespace único por modal
        const modalId = '{{ $modalId }}';
        const fetchUrl = '{{ $fetchUrl }}';
        const itemsPerPage = {{ $itemsPerPage }};

        // Inicializa o manager global se não existir
        if (typeof window.tableModalManager === 'undefined') {
            window.tableModalManager = {
                paginationData: {},

                open: function(modalId, fetchUrl, columns) {

                    // Sempre limpa dados anteriores
                    delete this.paginationData[modalId];

                    // Mostra loading
                    const tableBody = document.getElementById('modal-table-body-' + modalId);
                    if (tableBody) {
                        tableBody.innerHTML = '<tr><td colspan="' + columns.length +
                            '" class="px-6 py-4 text-center text-gray-500">Carregando...</td></tr>';
                    }

                    // Limpa paginação
                    const pagination = document.getElementById('pagination-' + modalId);
                    if (pagination) pagination.innerHTML = '';

                    fetch(fetchUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                this.paginationData[modalId] = {
                                    items: data.nfItens || data.items || [],
                                    currentPage: 1,
                                    itemsPerPage: itemsPerPage,
                                    columns: columns
                                };

                                this.renderTable(modalId);
                                this.showModal(modalId);
                            } else {
                                throw new Error(data.message || 'Erro ao carregar dados');
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error); // Debug
                            alert('Erro ao carregar dados: ' + error.message);

                            if (tableBody) {
                                tableBody.innerHTML = '<tr><td colspan="' + columns.length +
                                    '" class="px-6 py-4 text-center text-red-500">Erro ao carregar dados</td></tr>';
                            }
                        });
                },

                renderTable: function(modalId) {
                    const paginationData = this.paginationData[modalId];
                    if (!paginationData) {
                        console.error('No pagination data for modal:', modalId);
                        return;
                    }

                    const {
                        items,
                        currentPage,
                        itemsPerPage,
                        columns
                    } = paginationData;

                    const startIndex = (currentPage - 1) * itemsPerPage;
                    const endIndex = startIndex + itemsPerPage;
                    const currentItems = items.slice(startIndex, endIndex);

                    let tableHtml = '';
                    currentItems.forEach(item => {
                        tableHtml += '<tr>';
                        columns.forEach(column => {
                            const value = this.getNestedValue(item, column.field) ?? '-';
                            tableHtml +=
                                `<td class="px-6 py-4 ${column.class || ''}">${value}</td>`;
                        });
                        tableHtml += '</tr>';
                    });

                    const tableBody = document.getElementById('modal-table-body-' + modalId);
                    if (tableBody) {
                        tableBody.innerHTML = tableHtml;
                    } else {
                        console.error('Table body not found for modal:', modalId);
                    }

                    this.renderPagination(modalId);
                },

                getNestedValue: function(obj, path) {
                    return path.split('.').reduce((current, key) => current && current[key], obj);
                },

                renderPagination: function(modalId) {
                    const paginationData = this.paginationData[modalId];
                    if (!paginationData) return;

                    const {
                        items,
                        currentPage,
                        itemsPerPage
                    } = paginationData;
                    const totalPages = Math.ceil(items.length / itemsPerPage);
                    const paginationContainer = document.getElementById('pagination-' + modalId);

                    if (!paginationContainer || totalPages <= 1) {
                        if (paginationContainer) paginationContainer.innerHTML = '';
                        return;
                    }

                    let paginationHtml = `
                    <div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center text-sm text-gray-500">
                            Mostrando ${((currentPage - 1) * itemsPerPage) + 1} até ${Math.min(currentPage * itemsPerPage, items.length)} de ${items.length} registros
                        </div>
                        <div class="flex items-center space-x-2">
                `;

                    // Botão Anterior
                    paginationHtml += `
                    <button 
                        onclick="tableModalManager.changePage('${modalId}', ${currentPage - 1})"
                        ${currentPage === 1 ? 'disabled' : ''}
                        class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Anterior
                    </button>
                `;

                    // Números das páginas
                    for (let i = 1; i <= totalPages; i++) {
                        const isActive = i === currentPage;
                        paginationHtml += `
                        <button 
                            onclick="tableModalManager.changePage('${modalId}', ${i})"
                            class="px-3 py-1 text-sm font-medium ${isActive ? 'text-white bg-blue-600 border-blue-600' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50'} border rounded-md"
                        >
                            ${i}
                        </button>
                    `;
                    }

                    // Botão Próximo
                    paginationHtml += `
                    <button 
                        onclick="tableModalManager.changePage('${modalId}', ${currentPage + 1})"
                        ${currentPage === totalPages ? 'disabled' : ''}
                        class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Próximo
                    </button>
                `;

                    paginationHtml += '</div></div>';
                    paginationContainer.innerHTML = paginationHtml;
                },

                changePage: function(modalId, newPage) {
                    const paginationData = this.paginationData[modalId];
                    if (!paginationData) return;

                    const totalPages = Math.ceil(paginationData.items.length / paginationData.itemsPerPage);

                    if (newPage < 1 || newPage > totalPages) return;

                    this.paginationData[modalId].currentPage = newPage;
                    this.renderTable(modalId);
                },

                showModal: function(modalId) {
                    const modal = document.getElementById('modal-' + modalId);
                    if (modal) {
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                },

                close: function(modalId) {
                    const modal = document.getElementById('modal-' + modalId);
                    if (modal) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';

                        // Limpa dados
                        delete this.paginationData[modalId];

                        // Limpa visual
                        const tableBody = document.getElementById('modal-table-body-' + modalId);
                        const pagination = document.getElementById('pagination-' + modalId);

                        if (tableBody) tableBody.innerHTML = '';
                        if (pagination) pagination.innerHTML = '';
                    }
                }
            };

            // Event listeners globais (só uma vez)
            if (!window.tableModalGlobalListenersAdded) {
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        // Busca especificamente por modais table-modal que estejam abertos
                        const openModal = document.querySelector('[id^="modal-"]:not(.hidden)');

                        if (openModal && tableModalManager.paginationData) {
                            const modalId = openModal.id.replace('modal-', '');

                            // Verifica se este modal existe nos dados de paginação (confirma que é um table-modal)
                            if (tableModalManager.paginationData[modalId] ||
                                document.getElementById('modal-table-body-' + modalId)) {

                                tableModalManager.close(modalId);
                            }
                        }
                    }
                });

                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('fixed') &&
                        e.target.classList.contains('inset-0') &&
                        e.target.id.startsWith('modal-')) {
                        const modalId = e.target.id.replace('modal-', '');
                        tableModalManager.close(modalId);
                    }
                });

                window.tableModalGlobalListenersAdded = true;
            }
        }

        // Inicialização específica deste modal
        function initModal() {
            if (!fetchUrl) return;

            const modal = document.getElementById('modal-' + modalId);
            if (!modal) return;

            const columns = JSON.parse(modal.dataset.columns);
            const triggerClass = 'modal-trigger-' + modalId;

            // Remove listeners antigos se existirem
            const existingTriggers = document.querySelectorAll('.' + triggerClass);
            existingTriggers.forEach(trigger => {
                // Clona o elemento para remover todos os listeners
                const newTrigger = trigger.cloneNode(true);
                trigger.parentNode.replaceChild(newTrigger, trigger);
            });

            // Adiciona novos listeners
            document.querySelectorAll('.' + triggerClass).forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    tableModalManager.open(modalId, fetchUrl, columns);
                });
            });
        }

        // Executa quando DOM estiver pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initModal);
        } else {
            initModal();
        }

        // Re-inicializa após um pequeno delay (para garantir que elementos existam)
        setTimeout(initModal, 100);
    })();

    // Função global para compatibilidade
    function closeTableModal(modalId) {
        if (window.tableModalManager) {
            window.tableModalManager.close(modalId);
        }
    }
</script>
