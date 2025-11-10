<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pneus em Deposito') }}
            </h2>
            <div class="flex items-center space-x-4">

                <div x-show="helpOpen" @click.away="helpOpen = false"
                    class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                    <div class="py-1">
                        <div class="px-4 py-2">
                            <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                Ajuda - Pneus em Deposito
                            </p>
                            <p class="mt-1 text-xs leading-5 text-gray-500">
                                Esta tela exibe os registros de pneus em Depositos. Use os filtros abaixo para
                                refinar sua busca.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.pneusdeposito._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    <div class="mt-6 overflow-x-auto">
                        @include('admin.pneusdeposito._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('admin.pneusdeposito._scripts')
    @endpush
</x-app-layout>
