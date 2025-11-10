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
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro Imobilizado') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.cadastroimobilizado.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Imobilizado
                </a>

                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <x-bladewind::notification />


                <!-- Search Form -->
                @include('admin.cadastroimobilizado._search-form')

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.cadastroimobilizado._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @include('admin.cadastroimobilizado._scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
          const problematicContainers = document.querySelectorAll('.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg');
          
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