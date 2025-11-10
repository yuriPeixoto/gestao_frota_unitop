<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div x-data="{ tab: 'envio' }" class="w-full">
                {{-- Cabeçalho com abas --}}
                <div class="flex justify-between items-center border-b border-gray-300">
                    <div class="flex space-x-6">
                        <button @click="tab = 'envio'" :class="tab === 'envio' 
                    ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' 
                    : 'text-gray-600 hover:text-gray-800'" class="pb-2 text-lg focus:outline-none">
                            Envio para Manutenção
                        </button>
                        <button @click="tab = 'recebimento'" :class="tab === 'recebimento' 
                    ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' 
                    : 'text-gray-600 hover:text-gray-800'" class="pb-2 text-lg focus:outline-none">
                            Recebimento Manutenção
                        </button>
                    </div>

                    {{-- Botão ajuda --}}
                    <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                        <button @click="helpOpen = !helpOpen" type="button"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>

                        <div x-show="helpOpen" @click.away="helpOpen = false"
                            class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <div class="px-4 py-2">
                                    <p class="text-sm font-medium text-gray-900">
                                        Ajuda - Controle de Pneus
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Navegue entre as abas para visualizar os envios e recebimentos de manutenção.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conteúdo das abas --}}
                <div class="mt-1">
                    <div x-show="tab === 'envio'">
                        {{-- Aqui entra a view de Envio --}}

                        @include('admin.manutencaopneus.index', ['envios' => $envios])
                    </div>

                    <div x-show="tab === 'recebimento'" x-cloak>
                        {{-- Aqui entra a view de Recebimento --}}

                        @include('admin.manutencaopneusentrada.index', ['recebimentos' => $recebimento])
                    </div>
                </div>
            </div>

        </div>
    </x-slot>



</x-app-layout>