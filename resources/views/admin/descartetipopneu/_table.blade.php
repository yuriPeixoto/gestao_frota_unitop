<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo Descarte</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Tipo Descarte</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse($descarte as $index => $tipo)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.descartetipopneu.edit', $tipo->id_tipo_descarte) }}" title="Editar">
                                <x-icons.edit class="w-4 h-4 text-blue-600" />
                            </a>
                            <form action="{{ route('admin.descartetipopneu.destroy', $tipo->id_tipo_descarte) }}"
                                method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Tem certeza que deseja excluir este tipo de descarte?')"
                                    title="Excluir" class="p-1 hover:bg-red-100 rounded">
                                    <x-icons.trash class="w-4 h-4 text-red-600" />
                                </button>
                            </form>

                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $tipo->id_tipo_descarte }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($tipo->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($tipo->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $tipo->descricao_tipo_descarte }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="4" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $descarte->links() }}
    </div>
</div>

<script>
    function excluirDescarte(id) {
        if (!confirm('Tem certeza que deseja excluir este tipo de descarte?')) return;

        console.log('URL:', `/admin/descartetipopneu/descarte/tipo/${id}`); // Corrigido para match com fetch

        fetch(`/admin/descartetipopneu/descarte/tipo/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }

            })
            .then(async response => {
                const data = await response.json().catch(() => null);
                if (!response.ok) throw new Error(data?.message || 'Erro ao excluir');
                return data;
            })
            .then(data => {
                alert(data.message);
                window.location.reload();
            })
            .catch(error => {
                console.error(error);
                alert(error.message || 'Erro desconhecido');
            });
    }
</script>
