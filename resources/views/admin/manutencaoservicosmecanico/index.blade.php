<x-app-layout>
    @if (session('error'))
        <div class="mb-4 bg-red-50 p-4 rounded">
            <p class="text-red-600">{{ session('error') }}</p>
        </div>
    @elseif(session('info'))
        <div class="mb-4 bg-yellow-50 p-4 rounded">
            <p class="text-yellow-600">{{ session('info') }}</p>
        </div>
    @elseif(session('success'))
        <div class="mb-4 bg-green-50 p-4 rounded">
            <p class="text-green-600">{{ session('success') }}</p>
        </div>
    @endif
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Serviços Mecânico Interno') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Serviços do Mecânico."
                    content="Está tela tem como objetivo apresentar para o Mecânico os serviços que estão vinculados a ele, para assim dar INICIO e FINALIZAÇÃO do serviço." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.manutencaoservicosmecanico._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    @include('admin.manutencaoservicosmecanico._table')
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
