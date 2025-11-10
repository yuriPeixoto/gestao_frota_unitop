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
                {{ __('Cadastro Condutores') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.condutores.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Condutor
                </a>

            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <x-bladewind::notification />


                <!-- Search Form -->
                @include('admin.condutores._search-form')

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.condutores._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @include('admin.condutores._scripts')

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