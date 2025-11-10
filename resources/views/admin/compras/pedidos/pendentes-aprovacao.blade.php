<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pedidos Pendentes de Aprovação') }}
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Pedidos
                                    Pendentes</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela lista todos os pedidos de compra que estão pendentes de sua aprovação.
                                    Você pode aprovar ou rejeitar os pedidos conforme sua alçada de aprovação.
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
                <!-- Filtros de busca -->
                <form action="{{ route('admin.compras.pedidos.pendentes-aprovacao') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-forms.input name="numero" label="Número do Pedido" value="{{ request('numero') }}" />
                    </div>

                    <div>
                        <x-forms.input type="date" name="data_inicial" label="Data Inicial"
                            value="{{ request('data_inicial') }}" />
                    </div>

                    <div>
                        <x-forms.input type="date" name="data_final" label="Data Final"
                            value="{{ request('data_final') }}" />
                    </div>

                    <div>
                        <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                            placeholder="Selecione o fornecedor..." :options="$fornecedoresFrequentes ?? []"
                            :searchUrl="route('admin.api.fornecedores.search')" :selected="request('id_fornecedor')"
                            asyncSearch="true" />
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                            Buscar
                        </button>
                    </div>
                </form>

                <!-- Ações em Lote -->
                <div class="mt-6 bg-gray-50 p-4 rounded-lg"
                    x-data="{ itemsSelecionados: [], selecionarTodos: false, modalAberto: false, justificativa: '' }">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ações em Lote</h3>

                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <input type="checkbox" id="selecionar-todos" x-model="selecionarTodos" @change="marcarTodos"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="selecionar-todos" class="ml-2 text-sm text-gray-700">Selecionar Todos</label>
                        </div>

                        <div class="flex space-x-2">
                            <button type="button" @click="abrirModalAprovacao"
                                :disabled="itemsSelecionados.length === 0"
                                :class="itemsSelecionados.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Aprovar Selecionados
                            </button>
                        </div>
                    </div>

                    <!-- Modal de Aprovação em Lote -->
                    <div x-show="modalAberto" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
                        <div
                            class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="modalAberto" @click="modalAberto = false"
                                class="fixed inset-0 transition-opacity" aria-hidden="true">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>

                            <div x-show="modalAberto"
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                @click.away="modalAberto = false">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                                Aprovar Pedidos Selecionados
                                            </h3>

                                            <p class="text-sm text-gray-500 mb-4">
                                                Você está prestes a aprovar <span
                                                    x-text="itemsSelecionados.length"></span> pedidos. Esta ação não
                                                pode ser desfeita.
                                            </p>

                                            <div class="space-y-4">
                                                <div>
                                                    <label for="justificativa"
                                                        class="block text-sm font-medium text-gray-700">Justificativa
                                                        (opcional)</label>
                                                    <textarea id="justificativa" x-model="justificativa" rows="3"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="button" @click="aprovarSelecionados"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        Confirmar Aprovação
                                    </button>
                                    <button type="button" @click="modalAberto = false"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Pedidos -->
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        width="5%">
                                        Selecionar
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Número
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fornecedor
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Solicitante
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($pedidos as $pedido)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" x-model="itemsSelecionados"
                                            :value="{{ $pedido->id_pedido_compras }}"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.compras.pedidos.show', $pedido->id_pedido_compras) }}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            {{ $pedido->numero }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $pedido->fornecedor->nome_fornecedor ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $pedido->data_inclusao?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $pedido->solicitacaoCompra->solicitante->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.compras.pedidos.show', $pedido->id_pedido_compras) }}"
                                                class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            <button type="button"
                                                @click="aprovarPedido({{ $pedido->id_pedido_compras }})"
                                                class="text-green-600 hover:text-green-900" title="Aprovar">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>

                                            <button type="button"
                                                @click="rejeitarPedido({{ $pedido->id_pedido_compras }})"
                                                class="text-red-600 hover:text-red-900" title="Rejeitar">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Nenhum pedido pendente de aprovação encontrado
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pedidos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
                Alpine.data('pedidosAprovacao', () => ({
                    itemsSelecionados: [],
                    selecionarTodos: false,
                    modalAberto: false,
                    justificativa: '',
                    
                    marcarTodos() {
                        if (this.selecionarTodos) {
                            // Selecionar todos os checkboxes
                            this.itemsSelecionados = Array.from(document.querySelectorAll('input[type="checkbox"]:not(#selecionar-todos)
                            ')).map(checkbox => parseInt(checkbox.value));
                        } else {
                            // Desmarcar todos
                            this.itemsSelecionados = [];
                        }
                    },
                    
                    abrirModalAprovacao() {
                        if (this.itemsSelecionados.length === 0) {
                            alert('Selecione pelo menos um pedido para aprovar.');
                            return;
                        }
                        
                        this.modalAberto = true;
                        this.justificativa = '';
                    },
                    
                    aprovarSelecionados() {
                        if (this.itemsSelecionados.length === 0) return;
                        
                        // Criar formulário dinamicamente e enviar
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("admin.compras.pedidos.aprovar-lote") }}';
                        
                        // CSRF Token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                        
                        // IDs dos pedidos selecionados
                        this.itemsSelecionados.forEach(id => {
                            const pedidoInput = document.createElement('input');
                            pedidoInput.type = 'hidden';
                            pedidoInput.name = 'pedidos[]';
                            pedidoInput.value = id;
                            form.appendChild(pedidoInput);
                        });
                        
                        // Justificativa
                        const justificativaInput = document.createElement('input');
                        justificativaInput.type = 'hidden';
                        justificativaInput.name = 'justificativa';
                        justificativaInput.value = this.justificativa;
                        form.appendChild(justificativaInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    },
                    
                    aprovarPedido(id) {
                        const confirmacao = confirm('Tem certeza que deseja aprovar este pedido?');
                        if (!confirmacao) return;
                        
                        // Criar formulário dinamicamente e enviar
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/compras/pedidos/${id}/aprovar`;
                        
                        // CSRF Token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    },
                    
                    rejeitarPedido(id) {
                        const motivo = prompt('Informe o motivo da rejeição:');
                        if (!motivo) return;
                        
                        // Criar formulário dinamicamente e enviar
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/compras/pedidos/${id}/rejeitar`;
                        
                        // CSRF Token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);
                        
                        // Motivo da rejeição
                        const motivoInput = document.createElement('input');
                        motivoInput.type = 'hidden';
                        motivoInput.name = 'motivo_rejeicao';
                        motivoInput.value = motivo;
                        form.appendChild(motivoInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    }
                }));
            });
    </script>
    @endpush
</x-app-layout>