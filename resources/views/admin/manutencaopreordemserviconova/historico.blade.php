<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem Últimas Manutenções Do Veículo') }}
            </h2>
        
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    @include('admin.manutencaopreordemserviconova._table-historico')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @if (session('notification') && is_array(session('notification')))
            <script>
                showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}',
                    '{{ session('notification')['type'] }}');
            </script>
        @endif

    @endpush

    @if (session('limparSelecao'))
        <script>
            localStorage.removeItem('selectedRows');
        </script>
    @endif
</x-app-layout>
