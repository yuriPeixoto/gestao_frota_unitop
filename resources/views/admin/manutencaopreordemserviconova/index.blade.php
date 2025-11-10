<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem Pré-O.S') }}
            </h2>
            <div class="flex items-center space-x-4">

                <a href="{{ route('admin.manutencaopreordemserviconova.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Nova Pré O.S
                </a>

                <x-help-icon title="Ajuda - Listagem Pré-O.S"
                    content="Está tela tem como finalidade exibir os registros de Listagem de Pré O.S. Os campos abaixo servem para realizar buscas nos registros lançados!" />
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        @if (session('success'))
            <div class="mb-4 p-4 rounded-md bg-green-100 border border-green-300 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-md bg-red-100 border border-red-300 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 bg-white border-b border-gray-200">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search Form -->
                    @include('admin.manutencaopreordemserviconova._search-form')

                    <!-- Results Table -->
                    <div class="mt-6 overflow-x-auto" id="results-table">
                        @include('admin.manutencaopreordemserviconova._table')
                    </div>
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

        @include('admin.manutencaopreordemserviconova._scripts')
    @endpush
</x-app-layout>
