<div class="results-table">
    @can('criar_abastecimentosfaturamento')
    <div x-data="notasFaturamentoSelecionaveis()" x-init="init()">
        <div class="p-3 bg-gray-100/50 rounded-lg mb-4" x-show="selectedRows.length > 0">
            <div class="flex items-center justify-between">
                <button @click="confirmarRows()"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.clipboard-document-check class="w-4 h-4 mr-2" />
                    Faturar
                </button>
                <div class="text-sm font-medium text-gray-700"
                    x-text="`${selectedRows.length} item(s) selecionado(s) no total`"></div>
            </div>
        </div>

        <x-tables.table :checkable="true">
            <x-tables.header>
                <x-tables.checkbox-head-cell />
                <x-tables.head-cell class="text-center">Cód. Transação</x-tables.head-cell>
                <x-tables.head-cell>Chave NF</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Número NF</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Data<br>Vencimento NF</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Valor NF</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Posto<br>Abastecimento</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Nome<br>Fantasia</x-tables.head-cell>
                <x-tables.head-cell class="text-center">CNPJ</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Placa</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Tipo Combustível</x-tables.head-cell>
                <x-tables.head-cell class="text-center">Data<br>Abastecimento</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body>
                @forelse ($abastecimentos as $index => $abastecimento)
                <x-tables.row :index="$index" data-id="{{ $abastecimento->cod_transacao }}" class="cursor-pointer"
                    @click="toggleRow($event.currentTarget.getAttribute('data-id'))">
                    <x-tables.checkbox-cell :id="$abastecimento->cod_transacao" />
                    <x-tables.cell class="text-center">{{ $abastecimento->cod_transacao }}</x-tables.cell>
                    <x-tables.cell>{{ $abastecimento->chave_nf }}</x-tables.cell>
                    <x-tables.cell>{{ $abastecimento->numero_nf }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($abastecimento->data_vencimento_nf, 'd/m/Y') }}</x-tables.cell>
                    <x-tables.cell nowrap>R$ {{ number_format($abastecimento->valor_nf, 2, ',', '.') }}</x-tables.cell>
                    <x-tables.cell>{{ $abastecimento->posto_abastecimento }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $abastecimento->nomefatasiaposto }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $abastecimento->cnpj }}</x-tables.cell>
                    <x-tables.cell>{{ $abastecimento->placa }}</x-tables.cell>
                    <x-tables.cell>{{ $abastecimento->tipo_combustivel }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($abastecimento->data_abastecimento) }}</x-tables.cell>
                </x-tables.row>
                @empty
                <x-tables.empty cols="12" message="Nenhum registro encontrado" />
                @endforelse
            </x-tables.body>
        </x-tables.table>

        <div class="flex justify-between items-center mt-2">
            <div class="font-bold">
                Total: R$ {{ number_format($totalGeral, 2, ',', '.') }}
            </div>
            <div class="text-sm text-gray-600">
                Página atual: {{ $abastecimentos->currentPage() }} de {{ $abastecimentos->lastPage() }}
            </div>
        </div>

        <input type="hidden" name="notasFaturamentoSelecionadas" :value="selectedRows.join(',')" />
    </div>
    @else
    {{-- Versão somente leitura para usuários sem permissão --}}
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell class="text-center">Cód. Transação</x-tables.head-cell>
            <x-tables.head-cell>Chave NF</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Número NF</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Data<br>Vencimento NF</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Valor NF</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Posto<br>Abastecimento</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Nome<br>Fantasia</x-tables.head-cell>
            <x-tables.head-cell class="text-center">CNPJ</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Placa</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Tipo Combustível</x-tables.head-cell>
            <x-tables.head-cell class="text-center">Data<br>Abastecimento</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($abastecimentos as $index => $abastecimento)
            <x-tables.row :index="$index">
                <x-tables.cell class="text-center">{{ $abastecimento->cod_transacao }}</x-tables.cell>
                <x-tables.cell>{{ $abastecimento->chave_nf }}</x-tables.cell>
                <x-tables.cell>{{ $abastecimento->numero_nf }}</x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($abastecimento->data_vencimento_nf, 'd/m/Y') }}</x-tables.cell>
                <x-tables.cell nowrap>R$ {{ number_format($abastecimento->valor_nf, 2, ',', '.') }}</x-tables.cell>
                <x-tables.cell>{{ $abastecimento->posto_abastecimento }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $abastecimento->nomefatasiaposto }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $abastecimento->cnpj }}</x-tables.cell>
                <x-tables.cell>{{ $abastecimento->placa }}</x-tables.cell>
                <x-tables.cell>{{ $abastecimento->tipo_combustivel }}</x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($abastecimento->data_abastecimento) }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="11" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="flex justify-between items-center mt-2">
        <div class="font-bold">
            Total: R$ {{ number_format($totalGeral, 2, ',', '.') }}
        </div>
        <div class="text-sm text-gray-600">
            Página atual: {{ $abastecimentos->currentPage() }} de {{ $abastecimentos->lastPage() }}
        </div>
    </div>
    @endcan

    <div class="mt-4">
        {{ $abastecimentos->links() }}
    </div>
</div>

{{-- Lógica para manter selecionado os itens da tabela ao trocar de página --}}
@can('criar_abastecimentosfaturamento')
<script>
    function notasFaturamentoSelecionaveis() {
        return {
            selectedRows: JSON.parse(localStorage.getItem('selectedRows')) || [],

            toggleRow(id) {
                if (!id || !/^\d+$/.test(id)) return; // Só aceita IDs numéricos

                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
                } else {
                    this.selectedRows.push(id);
                }

                this.atualizarLocalStorage();
                this.sincronizarLinhaCheckbox(id);
            },

            toggleSelecionarTodos(checked) {
                const idsNaPaginaAtual = Array.from(document.querySelectorAll('tr[data-id]'))
                    .map(row => row.getAttribute('data-id'))
                    .filter(id => id && /^\d+$/.test(id));

                if (checked) {
                    // Adiciona IDs que ainda não estão selecionados
                    idsNaPaginaAtual.forEach(id => {
                        if (!this.selectedRows.includes(id)) {
                            this.selectedRows.push(id);
                        }
                    });
                } else {
                    // Remove apenas os IDs da página atual
                    this.selectedRows = this.selectedRows.filter(id => !idsNaPaginaAtual.includes(id));
                }

                this.atualizarLocalStorage();
                this.init();
            },

            confirmarRows() {
                if (this.selectedRows.length === 0) {
                    showAlert({
                        title: 'Atenção!',
                        message: 'Nenhum item selecionado para faturamento.',
                        type: 'warning'
                    });
                    return;
                }
                
                const ids = this.selectedRows.join(',');
                window.location.href = `/admin/abastecimentosfaturamento/create?ids=${ids}`;
            },

            init() {
                this.$nextTick(() => {
                    // Atualizar rows baseado na seleção atual
                    this.atualizarRowsStatus();
                    
                    // Obter IDs na página atual para uso posteriormente
                    const idsNaPaginaAtual = Array.from(document.querySelectorAll('tr[data-id]'))
                        .map(row => row.getAttribute('data-id'))
                        .filter(id => id && /^\d+$/.test(id));

                    const checkboxSelecionarTodos = document.querySelector('thead input[type="checkbox"]');
                    if (checkboxSelecionarTodos) {
                        // Somente marque "todos" se todos os IDs da página atual estiverem selecionados
                        const todosSelecionados = idsNaPaginaAtual.length > 0 && idsNaPaginaAtual.every(id =>
                            this.selectedRows.includes(id));
                        checkboxSelecionarTodos.checked = todosSelecionados;
                        
                        // Adicionar indicador visual para seleção parcial
                        checkboxSelecionarTodos.indeterminate = !todosSelecionados && 
                                                              idsNaPaginaAtual.some(id => this.selectedRows.includes(id));
                    }

                    this.observarCheckboxSelecionarTodos();
                });
            },

            // Atualizar o status visual das linhas
            atualizarRowsStatus() {
                document.querySelectorAll('tr[data-id]').forEach(row => {
                    const id = row.getAttribute('data-id');
                    const checkbox = row.querySelector('input[type="checkbox"]');

                    if (this.selectedRows.includes(id)) {
                        row.classList.add('bg-gray-100');
                        if (checkbox) checkbox.checked = true;
                    } else {
                        row.classList.remove('bg-gray-100');
                        if (checkbox) checkbox.checked = false;
                    }
                });
            },
            
            sincronizarLinhaCheckbox(id) {
                // Atualizar todas as linhas para garantir consistência
                this.atualizarRowsStatus();
                
                // Atualizar também o "Selecionar Todos" se necessário
                this.init();
            },

            observarCheckboxSelecionarTodos() {
                const checkboxSelecionarTodos = document.querySelector('thead input[type="checkbox"]');
                if (checkboxSelecionarTodos && !checkboxSelecionarTodos.hasAttribute('data-listener')) {
                    checkboxSelecionarTodos.setAttribute('data-listener', 'true');
                    checkboxSelecionarTodos.addEventListener('change', (e) => {
                        this.toggleSelecionarTodos(e.target.checked);
                    });
                }
            },

            atualizarLocalStorage() {
                if (this.selectedRows.length === 0) {
                    localStorage.removeItem('selectedRows');
                } else {
                    localStorage.setItem('selectedRows', JSON.stringify(this.selectedRows));
                }
            }
        }
    }
</script>
@endcan