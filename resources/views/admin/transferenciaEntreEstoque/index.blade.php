<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Transferencia Entre Estoque') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="absolute right-0 mt-2 w-64 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="truncate text-sm font-medium leading-5 text-gray-900">
                                    Ajuda - Transfêrencia
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela exibe as Transfêrencia Entre Estoque realizadas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-4">
            <div class="border-b border-gray-200 bg-white p-6">
                <!-- Search Form -->
                @include('admin.transferenciaEntreEstoque._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    <div class="mt-6 overflow-x-auto">
                        @include('admin.transferenciaEntreEstoque._table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
