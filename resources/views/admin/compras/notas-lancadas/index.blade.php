<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de Notas Lançadas') }}
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                    Ajuda - Notas Fiscais Lançadas
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela exibe os registros de calibragens realizadas. Use os filtros abaixo para
                                    refinar sua busca.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.compras.notas-lancadas._search-form')

                </br>
                <div class="flex flex-wrap gap-2 justify-center">
                    <label
                        class="flex items-center space-x-2 cursor-pointer px-4 py-2 border rounded-lg transition-all duration-200 hover:bg-gray-100 focus-within:ring-2 focus-within:ring-indigo-500 bg-blue-100 ">
                        <x-forms.checkbox name="solicitacao" class="tipo-compra-checkbox h-5 w-5 text-indigo-600"
                            value="Notas Avulsas" />
                        <span class="text-blue-800 font-medium">Notas Avulsas</span>
                    </label>

                    <label
                        class="flex items-center space-x-2 cursor-pointer px-4 py-2 border rounded-lg transition-all duration-200 hover:bg-gray-100 focus-within:ring-2 focus-within:ring-indigo-500 bg-green-100">
                        <x-forms.checkbox name="solicitacao" class="tipo-compra-checkbox h-5 w-5 text-indigo-600"
                            value="Entrada Estoque" />
                        <span class="text-green-800 font-medium">Entrada Estoque</span>
                    </label>

                    <label
                        class="flex items-center space-x-2 cursor-pointer px-4 py-2 border rounded-lg transition-all duration-200 hover:bg-gray-100 focus-within:ring-2 focus-within:ring-indigo-500 bg-purple-100 ">
                        <x-forms.checkbox name="solicitacao" class="tipo-compra-checkbox h-5 w-5 text-indigo-600"
                            value="NF Compra Serviço" />
                        <span class="text-purple-800 font-medium">NF Compra Serviço</span>
                    </label>

                    <label
                        class="flex items-center space-x-2 cursor-pointer px-4 py-2 border rounded-lg transition-all duration-200 hover:bg-gray-100 focus-within:ring-2 focus-within:ring-indigo-500 bg-yellow-100 ">
                        <x-forms.checkbox name="solicitacao" class="tipo-compra-checkbox h-5 w-5 text-indigo-600"
                            value="Compras pela Ordem" />
                        <span class="text-yellow-800 font-medium">Compras pela Ordem</span>
                    </label>
                </div>

                <!-- Results Table -->

                <div class="mt-6 overflow-x-auto" id="results-table">
                    <div class="mt-6 overflow-x-auto">
                        @include('admin.compras.notas-lancadas._table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    const spinnerHTML = `
        <div class="flex items-center justify-center py-10">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="ml-3 text-gray-600 font-medium">Carregando...</span>
        </div>
    `;

    const checkboxes = document.querySelectorAll('.tipo-compra-checkbox');
    const tabela = document.getElementById('tabela-compras');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const tiposSelecionados = Array.from(checkboxes)
                .filter(c => c.checked)
                .map(c => c.value);

            // Mostrar spinner
            tabela.innerHTML = spinnerHTML;
            tabela.classList.add();

            fetch("{{ route('admin.compras.notas-lancadas.listacompra') }}?" + new URLSearchParams({
                tipo_compra: tiposSelecionados
            }), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => {
                    if (!res.ok) throw new Error('Erro na requisição');
                    return res.text();
                })
                .then(html => {
                    tabela.innerHTML = html;
                    tabela.classList.remove();
                    initCheckboxListeners();
                })
                .catch(err => {
                    console.error(err);
                    tabela.innerHTML = '<p class="text-red-500 text-center py-6">Erro ao carregar dados</p>';
                });
        });
    });


    function initCheckboxListeners() {
        const selectAll = document.querySelector('.select-all-checkbox');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.pedido-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    }

    // Inicializar listeners quando a página carregar
    document.addEventListener('DOMContentLoaded', initCheckboxListeners);
</script>