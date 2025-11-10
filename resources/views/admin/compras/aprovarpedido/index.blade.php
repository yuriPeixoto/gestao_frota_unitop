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
                {{ 'Aprovar Pedidos' }}
            </h2>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white">
            <div class="bg-white p-6">
                <x-bladewind::notification />

                <!-- Abas de Tipo de Solicitação -->
                <div class="mb-6 border-b border-gray-200 px-4 pt-4">
                    <nav class="flex space-x-8" aria-label="Abas">
                        <a href="{{ route('admin.compras.aprovarpedido.index', array_merge(request()->except(['tipo_solicitacao', 'page']), [])) }}"
                            class="{{ !request('tipo_solicitacao') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                            Todas
                        </a>
                        <a href="{{ route('admin.compras.aprovarpedido.index', array_merge(request()->except(['page']), ['tipo_solicitacao' => '1'])) }}"
                            class="{{ request('tipo_solicitacao') == '1' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                            Produtos
                        </a>
                        <a href="{{ route('admin.compras.aprovarpedido.index', array_merge(request()->except(['page']), ['tipo_solicitacao' => '2'])) }}"
                            class="{{ request('tipo_solicitacao') == '2' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                            Serviços
                        </a>
                    </nav>
                </div>

                <!-- Search Form -->
                @include('admin.compras.aprovarpedido._search-form')

                <!-- Results Table with Loading -->
                <div class="relative mt-6 min-h-[400px] overflow-x-auto">

                    <!-- Actual results -->
                    @include('admin.compras.aprovarpedido._table')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('admin.compras.aprovarpedido._scripts')

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
