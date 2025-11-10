<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Inconsistências de Abastecimento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Inconsistências de Abastecimento"
                    content="Esta tela permite visualizar e corrigir inconsistências encontradas nos abastecimentos via ATS e TruckPag. As inconsistências são problemas identificados durante o processamento dos abastecimentos, como KM incorreto, veículo não encontrado ou estoque insuficiente. Utilize os filtros para localizar as inconsistências e as ações disponíveis para resolvê-las." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 bg-white border-b border-gray-200">
                    <!-- Sistema de abas -->
                    <div x-data="{ activeTab: '{{ request()->query('tab', 'ats') }}' }">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8">
                                <button @click="activeTab = 'ats'"
                                    :class="{'border-indigo-500 text-indigo-600': activeTab === 'ats', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'ats'}"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Inconsistências ATS
                                </button>
                                <button @click="activeTab = 'truckpag'"
                                    :class="{'border-indigo-500 text-indigo-600': activeTab === 'truckpag', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'truckpag'}"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Inconsistências TruckPag
                                </button>
                            </nav>
                        </div>

                        <!-- Conteúdo das abas -->
                        <div class="mt-6">
                            <!-- Tab ATS -->
                            <div x-show="activeTab === 'ats'" x-cloak>
                                @include('admin.inconsistencias._tab_ats')
                            </div>

                            <!-- Tab TruckPag -->
                            <div x-show="activeTab === 'truckpag'" x-cloak>
                                @include('admin.inconsistencias._tab_truckpag')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais de confirmação -->
    <div x-data="{ showModal: false, itemId: null, actionType: null, itemType: null }" x-cloak>
        <div x-show="showModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                                x-show="actionType === 'remover'">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10"
                                x-show="actionType === 'reprocessar'">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    <span x-show="actionType === 'remover'">Remover Inconsistência</span>
                                    <span x-show="actionType === 'reprocessar'">Reprocessar Abastecimento</span>
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" x-show="actionType === 'remover'">
                                        Tem certeza que deseja remover esta inconsistência? Esta ação marcará o item
                                        como tratado sem realmente corrigir o problema.
                                    </p>
                                    <p class="text-sm text-gray-500" x-show="actionType === 'reprocessar'">
                                        Tem certeza que deseja reprocessar este abastecimento? Esta ação tentará
                                        processar o abastecimento novamente usando o estoque atual.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="actionType === 'remover' ? 
                                    (itemType === 'ats' ? '{{ route('admin.inconsistencias.ats.remover', '') }}/' + itemId : '{{ route('admin.inconsistencias.truckpag.remover', '') }}/' + itemId) : 
                                    '{{ route('admin.inconsistencias.ats.reprocessar', '') }}/' + itemId" method="POST"
                            id="action-form">
                            @csrf
                            <button type="submit"
                                x-bind:class="actionType === 'remover' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-show="actionType === 'remover'">Remover</span>
                                <span x-show="actionType === 'reprocessar'">Reprocessar</span>
                            </button>
                        </form>
                        <button type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="showModal = false">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmAction(id, type, action) {
            const modal = document.querySelector('[x-data]').__x.$data;
            modal.itemId = id;
            modal.itemType = type;
            modal.actionType = action;
            modal.showModal = true;
        }
        
        // Atualiza a URL quando a aba for trocada
        document.addEventListener('DOMContentLoaded', function() {
            Alpine.effect(() => {
                const currentTab = document.querySelector('[x-data]').__x.$data.activeTab;
                const url = new URL(window.location);
                url.searchParams.set('tab', currentTab);
                window.history.replaceState({}, '', url);
            });
        });
    </script>
    @endpush
</x-app-layout>