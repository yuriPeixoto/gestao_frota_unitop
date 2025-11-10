<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. Produto</x-tables.head-cell>
            <x-tables.head-cell>Descrição Produto</x-tables.head-cell>
            <x-tables.head-cell>Estoque Mínimo</x-tables.head-cell>
            <x-tables.head-cell>Estoque Máximo</x-tables.head-cell>
            <x-tables.head-cell>Localização do Produto</x-tables.head-cell>
            <x-tables.head-cell>Quantidade em Estoque</x-tables.head-cell>
            <x-tables.head-cell>Valor Médio do Produto</x-tables.head-cell>
            <x-tables.head-cell>Código Produto</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse ($listagem as $index => $produto)
            <x-tables.row :index="$index">
                <x-tables.cell>{{$produto->id_produto}}</x-tables.cell>
                <x-tables.cell>{{$produto->descricao_produto}}</x-tables.cell>
                <x-tables.cell>{{$produto->estoque_minimo}}</x-tables.cell>
                <x-tables.cell>{{$produto->estoque_maximo}}</x-tables.cell>
                <x-tables.cell>{{$produto->localizacao_produto}}</x-tables.cell>
                <x-tables.cell>{{$produto->quantidade_atual_produto}}</x-tables.cell>
                <x-tables.cell>{{$produto->valor_medio}}</x-tables.cell>
                <x-tables.cell>{{$produto->codigo_produto}}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    <div class="mt-4">
        {{ $listagem->links()}}
    </div>
</div>
{{-- <div id="modalTransferencia"
    class="hidden fixed inset-0 z-40 bg-black/40 backdrop-blur-md flex items-center justify-center">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 relative">
        <h2 class="text-xl font-semibold mb-4">Consultar Veiculo</h2>

        <div id="conteudoModalTransferencia" class="overflow-y-auto max-h-[70vh]">
            <p class="text-gray-500">Carregando...</p>
        </div>

        <button onclick="fecharModalTransferencia()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
            Fechar
        </button>
    </div>
</div> --}}
{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.querySelector('.select-all-checkbox');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.pedido-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });
</script> --}}

{{-- <script>
    function abrirModalTransferencia(id) {
        const modal = document.getElementById('modalTransferencia');
        const conteudo = document.getElementById('conteudoModalTransferencia');

        modal.classList.remove('hidden');
        conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

        fetch(`/admin/consultaprodutostransferencia/abrirModal/${id}`)
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

</script> --}}