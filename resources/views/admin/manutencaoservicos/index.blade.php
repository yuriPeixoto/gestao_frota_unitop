<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de Serviços') }}
            </h2>
            <div class="flex items-center space-x-4">

                <a href="{{ route('admin.manutencaoservicos.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Nova Listagem de Serviços
                </a>

                <x-help-icon title="Ajuda - Listagem de Serviços"
                    content="Está tela tem como finalidade exibir os registros de Listagem de Serviços. Os campos abaixo servem para realizar buscas nos registros lançados!" />
            </div>
        </div>
    </x-slot>

    @if ($errors->any())
    <div class="mb-4 rounded bg-red-50 p-4">
        <ul class="list-inside list-disc text-red-600">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.manutencaoservicos._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    @include('admin.manutencaoservicos._table')
                </div>
            </div>
        </div>
    </div>

    {{-- @push('scripts')
    @if (session('notification') && is_array(session('notification')))
    <script>
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}',
                    '{{ session('notification')['type'] }}');
    </script>
    @endif
    @endpush --}}
</x-app-layout>