<div id="results-table"
    class="opacity-0 transition-opacity duration-300 overflow-x-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
    <x-tables.table class="min-w-full text-xs">
        <x-tables.header>
            <x-tables.head-cell></x-tables.head-cell>
            <x-tables.head-cell>Cód</br>Pedido</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Número NF</x-tables.head-cell>
            <x-tables.head-cell>Chave</br> Nota</x-tables.head-cell>
            <x-tables.head-cell>Pedido Geral</x-tables.head-cell>
            <x-tables.head-cell>Valor Total</x-tables.head-cell>
            <x-tables.head-cell>Valor Total com Desconto</x-tables.head-cell>
            <x-tables.head-cell>Data Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Cód. Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Solicitante</x-tables.head-cell>
            <x-tables.head-cell>Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Tipo Pedido</x-tables.head-cell>
            <x-tables.head-cell>XML Integrado</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Usuário</br>Lançamento</x-tables.head-cell>
            <x-tables.head-cell>OS</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Valor Total Nota</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($pedidosNotas as $index => $pedidoNota)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex gap-2">
                        <x-forms.checkbox name="id_pedido_compras[]" value="{{ $pedidoNota->id_pedido_compras }}"
                            class="pedido-checkbox" />
                        <x-tooltip content="Visualizar" placement="bottom">
                            <button onclick="abrirModalTransferencia({{ $pedidoNota->id_pedido_compras }})">
                                <x-icons.eye class="w-5 h-5 text-black-500 hover:text-black-700" />
                            </button>
                        </x-tooltip>
                        <x-tooltip content="Desvincular" placement="bottom">
                            <button type="button" onclick="excluirNota({{ $pedidoNota->id_pedido_compras }})">
                                <x-icons.document-text class="w-5 h-5 text-red-500 hover:text-red-700" />
                            </button>
                        </x-tooltip>

                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->id_pedido_compras }}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->nome_fornecedor }}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->numero_nf }}</x-tables.cell>
                @php
                $chave = $pedidoNota->chave_nf;
                $chaveFormatada = substr($chave, 0, 10) . '...' . substr($chave, -5);
                @endphp
                <x-tables.cell>
                    <a href="#" title="{{$chave}}"> {{ $chaveFormatada }}</a>
                </x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->id_pedido_geral}}</x-tables.cell>
                <x-tables.cell>{{ number_format($pedidoNota->pedidoCompra->valor_total, 2, ',', '.') }}</x-tables.cell>
                <x-tables.cell>{{ number_format($pedidoNota->pedidoCompra->valor_total_desconto, 2, ',', '.') }}
                </x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->data_solicitacao ?? '-'}}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->id_solicitacao }}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->solicitante ?? '-'}}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->solicitacao}}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->tipo_pedido}}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->xml_integrado }}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->data_inclusao ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->user->name ?? '-'}}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->os}}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->filial ?? '-'}}</x-tables.cell>
                <x-tables.cell>{{ $pedidoNota->placa ?? '-'}}</x-tables.cell>
                <x-tables.cell>{{ number_format($pedidoNota->valor_nota_fiscal, 2, ',', '.') }}
                </x-tables.cell>

            </x-tables.row>
            @empty
            <x-tables.empty cols="20" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $pedidosNotas->links() }}
    </div>
</div>
<!-- Modal -->
<div id="modalTransferencia" class="hidden fixed inset-0 z-40 bg-opacity-80 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 relative">
        <h2 class="text-xl font-semibold mb-4">Visualizar Transferência</h2>

        <div id="conteudoModalTransferencia" class="overflow-y-auto max-h-[70vh]">
            <p class="text-gray-500">Carregando...</p>
        </div>

        <button onclick="fecharModalTransferencia()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
            Fechar
        </button>
    </div>
</div>

<script>
    function abrirModalTransferencia(id) {
            if (!id || isNaN(Number(id))) {
                alert('ID inválido para abrir modal: ' + id);
                return;
            }
            const modal = document.getElementById('modalTransferencia');
            const conteudo = document.getElementById('conteudoModalTransferencia');

            modal.classList.remove('hidden');
            conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

            fetch(`/admin/compras/pedidos-notas/${id}`)
                .then(response => {
                    if (!response.ok) throw new Error('Erro ao carregar dados');
                    return response.text();
                })
                .then(html => {
                    conteudo.innerHTML = html;
                })
                .catch(() => {
                    conteudo.innerHTML = '<p class="text-red-500">Erro ao carregar a transferência.</p>';
                });
        }

        function fecharModalTransferencia() {
            const modal = document.getElementById('modalTransferencia');
            modal.classList.add('hidden');
        }
</script>

<script>
    function excluirNota(id) {
        if (confirm('Tem certeza que deseja desvincular esta nota?')) {
        // Usando o helper route() do Laravel (recomendado)
        const url = new URL(`{{ route('admin.compras.pedidos-notas.excluir-nota', '') }}/${id}`);
        
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin' // Importante para sessões/cookies
            })
            .then(data => {
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            })
            .catch(error => {
                const message =
                    error?.notification?.message ||
                    error?.message ||
                    'Ocorreu um erro ao finalizar a requisição';

                if (typeof showNotification === 'function') {
                    showNotification('Erro', message, 'error');
                } else {
                    alert(message);
                }
            });
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.querySelector('.select-all-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                // Seleciona todos os checkboxes de pedidos na página atual
                document.querySelectorAll('.pedido-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });
</script>