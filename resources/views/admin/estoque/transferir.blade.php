<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transferência Estoque') }}
            </h2>
            <div class="flex items-center space-x-4 relative z-[9999]">

                <a href="{{ route('admin.estoque.dashboard')}}"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
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
                                    Ajuda - Transferência de Estoque
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela exibe os produtos relacionados ao estoque. Selecione o produto que deseja
                                    enviar para transferência.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <form id="form-transferencia" action="{{ route('admin.estoque.enviartransferencia') }}" method="POST">
            @csrf
            <div class="p-6">
                <x-tables.table>
                    <x-tables.header>
                        <x-tables.head-cell>
                            <x-forms.checkbox name="select_all" class="select-all-checkbox" />
                        </x-tables.head-cell>
                        <x-tables.head-cell>Cód. Estoque</x-tables.head-cell>
                        <x-tables.head-cell>Cód. Produto</x-tables.head-cell>
                        <x-tables.head-cell>Descrição Produto</x-tables.head-cell>
                        <x-tables.head-cell>Quantidade atual</x-tables.head-cell>
                        <x-tables.head-cell>Quantidade Minima</x-tables.head-cell>
                        <x-tables.head-cell>Quantidade Máxima</x-tables.head-cell>

                    </x-tables.header>
                    <x-tables.body>
                        @forelse($estoqueItems as $item)
                        <x-tables.row>
                            <x-tables.cell>
                                <x-forms.checkbox name="id_produto[]" class="pedido-checkbox"
                                    value="{{ $item->id_produto }}" />
                            </x-tables.cell>
                            <x-tables.cell>{{ $item->id_estoque }}</x-tables.cell>
                            <x-tables.cell>{{ $item->id_produto }}</x-tables.cell>
                            <x-tables.cell>{{ $item->produto->descricao_produto }}</x-tables.cell>
                            <x-tables.cell>{{ $item->quantidade_atual }}</x-tables.cell>
                            <x-tables.cell>{{ $item->quantidade_minima }}</x-tables.cell>
                            <x-tables.cell>{{ $item->quantidade_maxima }}</x-tables.cell>

                        </x-tables.row>
                        @empty
                        <x-tables.empty cols="9" message="Nenhum registro encontrado" />
                        @endforelse
                    </x-tables.body>
                </x-tables.table>

                <div class="flex justify-end space-x-3 col-span-full mt-4">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Criar Transferência
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.querySelector('.select-all-checkbox');
        const form = document.getElementById('form-transferencia');

        // Marcar/desmarcar todos
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.pedido-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }

        // Validação antes do envio
        form.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.pedido-checkbox:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Selecione pelo menos um produto para transferir.');
            }
        });
    });
</script>