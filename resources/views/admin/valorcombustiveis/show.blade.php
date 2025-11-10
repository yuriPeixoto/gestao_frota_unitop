<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Visualizar Valor de Combustível por Bomba') }}
            </h2>
            <div class="flex items-center space-x-4">
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Visualização de
                                    Valor de Combustível</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar os detalhes de um valor de combustível cadastrado.
                                    Os campos estão em modo somente leitura.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.valorcombustiveis.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    @include('admin.valorcombustiveis._form', [
    'action' => '#',
    'method' => 'GET',
    'valorCombustiveis' => $valorCombustiveis,
    'habilitarEdicaoUsuario' => 'true',
    ])

    @push('scripts')
    <script>
        // Desabilitar todos os campos do formulário para garantir que estejam somente leitura
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('valorCombustivelForm');
            if (form) {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.setAttribute('disabled', 'disabled');
                });
                
                // Remover os botões de ação desnecessários
                const submitButtons = form.querySelectorAll('button[type="submit"]');
                submitButtons.forEach(button => {
                    button.style.display = 'none';
                });
            }
        });
    </script>
    @endpush
</x-app-layout>