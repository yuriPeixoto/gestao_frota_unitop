<style>
    .dropdown-active {
        overflow: visible !important;
        z-index: 30;
    }

    [x-data*="simpleSelect"] [role="listbox"] {
        z-index: 50;
    }

    .smart-select-container {
        position: relative;
        z-index: 40;
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Cadastro de Contratos') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.contratos.create') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Contrato
                </a>

                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-4">
            <div class="border-b border-gray-200 bg-white p-6">
                <x-bladewind::notification />

                <!-- Search Form -->
                @include('admin.contratos._search-form')

                <!-- Results Table with Loading -->
                <div class="relative mt-6 min-h-[400px] overflow-x-auto">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-white bg-opacity-80">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.contratos._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('admin.contratos._scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
    @endpush
</x-app-layout>
