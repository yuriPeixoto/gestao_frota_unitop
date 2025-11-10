<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro de Meta por Tipo de Equipamento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.metatipoequipamentos.create') }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + Cadastrar
                </a>
                <x-help-icon title="Ajuda - Cadastro de Meta por Tipo de Equipamento"
                    content="Está tela tem como finalidade exibir os registros de Meta por Tipo de Equipamento. Os campos abaixo servem para realizar buscas nos registros lançados!" />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.metatipoequipamentos._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    @include('admin.metatipoequipamentos._table')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('admin.metatipoequipamentos._scripts')
    @endpush
</x-app-layout>
