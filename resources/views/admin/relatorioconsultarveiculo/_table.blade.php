<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>
                {{-- Checkbox "select all" com name definido --}}
                <x-forms.checkbox name="select_all" class="select-all-checkbox" />
            </x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód. Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Base Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Categoria</x-tables.head-cell>
            <x-tables.head-cell>Ano de Fabricação</x-tables.head-cell>
            <x-tables.head-cell>Ano Modelo</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse ($listagem as $index => $veiculo)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <x-forms.checkbox name="id[]" value="{{ $veiculo->id_veiculo }}" class="pedido-checkbox" />
                    </x-tables.cell>
                    <x-tables.cell>
                        <button type="button" onclick="abrirModalTransferencia({{ $veiculo->id_veiculo }})">
                            <x-icons.eye class="w-5 h-5" />
                        </button>
                    </x-tables.cell>
                    <x-tables.cell>{{ $veiculo->id_veiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $veiculo->placa }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($veiculo->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($veiculo->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $veiculo->filial->name ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $veiculo->baseVeiculo->descricao_base ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $veiculo->departamento->descricao_departamento ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $veiculo->categoriaVeiculo->descricao_categoria ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $veiculo->ano_fabricacao }}</x-tables.cell>
                    <x-tables.cell>{{ $veiculo->ano_modelo }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="15" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    <div class="mt-4">
        {{ $listagem->links() }}
    </div>
</div>
<div id="modalTransferencia"
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
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.querySelector('.select-all-checkbox');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.pedido-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });
</script>

<script>
    function abrirModalTransferencia(id) {
        const modal = document.getElementById('modalTransferencia');
        const conteudo = document.getElementById('conteudoModalTransferencia');

        modal.classList.remove('hidden');
        conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

        fetch(`/admin/relatorioconsultarveiculo/abrirModal/${id}`)
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
