<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Placa Sem Login') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{route('admin.manutencaopremio.index')}}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">

                    Voltar
                </a>

                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                    Ajuda - Manutenção Prêmio
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela exibe o dashboard do Prêmio. Use os filtros abaixo para
                                    refinar sua busca.
                                </p>
                            </div>
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
                <form method="GET" action="{{ route('admin.manutencaopremio.show') }}" class="space-y-4"
                    hx-get="{{ route('admin.manutencaopremio.show') }}" hx-target="#results-table"
                    hx-select="#results-table" hx-trigger="change delay:500ms, search">

                    <div class="grid grid-cols-1 sm:grid-cols-5 md:grid-cols-4 gap-4">
                        <div>
                            <x-forms.input type="date" name="data_inclusao" label="Data Inclusao:"
                                value="{{ request('data_inclusao') }}" />
                        </div>
                        <div>
                            <x-forms.input type="date" name="data_final" label="Data Inicial:"
                                value="{{ request('data_final') }}" />
                        </div>
                        <div>
                            <x-forms.smart-select name="placa" label="Placa" placeholder="Selecione a Placa..."
                                :options="$placa" :searchUrl="route('admin.api.manutencaopremio.search')"
                                :selected="request('placa')" asyncSearch="true" />
                        </div>

                    </div>

                    <div class="flex justify-between mt-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.manutencaopremio.show') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.trash class="h-4 w-4 mr-2" />
                                Limpar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>



                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">
                    <div class="mt-6 overflow-x-auto">
                        <div class="results-table">
                            <x-tables.table>
                                <x-tables.header>

                                    <x-tables.head-cell>Ações</x-tables.head-cell>
                                    <x-tables.head-cell>Data</x-tables.head-cell>
                                    <x-tables.head-cell>Placa</x-tables.head-cell>
                                    <x-tables.head-cell>Distância</x-tables.head-cell>

                                </x-tables.header>
                                <x-tables.body>
                                    @forelse($listagem as $mant)
                                    <x-tables.row>
                                        <x-tables.cell>
                                            <div class="flex items-center space-x-2">
                                                <a href="{{route('admin.manutencaopremio.edit', $mant->id_distauxiliar)}}"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <x-icons.pencil class="h-3 w-3" />
                                                </a>
                                                <button type="button"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <x-icons.trash class="h-3 w-3" />
                                                </button>
                                            </div>
                                        </x-tables.cell>
                                        <x-tables.cell>{{format_date($mant->data_)}}</x-tables.cell>
                                        <x-tables.cell>{{$mant->placa}}</x-tables.cell>
                                        <x-tables.cell>{{$mant->distancia}}</x-tables.cell>

                                    </x-tables.row>
                                    @empty
                                    <x-tables.empty cols="9" message="Nenhum registro encontrado" />
                                    @endforelse
                                </x-tables.body>
                            </x-tables.table>
                        </div>
                        <div class="mt-4">
                            {{$listagem->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>