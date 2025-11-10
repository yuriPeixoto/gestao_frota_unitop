@if (session('notification'))
<x-notification :notification="session('notification')" />
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800">Listagem de Pneus em Manutenção</h2>
            <x-button-link href="{{ route('admin.manutencaopneus.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Adicionar Pneu para Manutenção
            </x-button-link>
        </div>

        @include('admin.manutencaopneus._search')
        @include('admin.manutencaopneus._table')
    </div>
</div>