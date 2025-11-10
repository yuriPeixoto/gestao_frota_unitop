<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <!-- Adicione esta coluna para o checkbox -->
            <x-tables.head-cell>
                <input type="checkbox" id="select-all"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            </x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Serviço<br>Mecãnico</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Mecânico</x-tables.head-cell>
            <x-tables.head-cell>Serviço</x-tables.head-cell>
            <x-tables.head-cell>Cód. O.S</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse($manutancaoServicoMec as $index => $controle)
                <x-tables.row :index="$index">
                    <!-- Adicione esta célula com o checkbox -->
                    <x-tables.cell>
                        <input type="checkbox" name="selected_rows[]" value="{{ $controle->id_servico_mecanico }}"
                            class="row-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </x-tables.cell>
                    <x-tables.cell>
                        @if ($controle->status_servico != 'INICIADO' && $controle->status_servico != 'FINALIZADO')
                            <div>
                                <a href="{{ route('admin.manutencaoservicosmecanico.edit', $controle->id_servico_mecanico) }}"
                                    class="inline-flex items-center py-2 px-3 font-semibold rounded-lg shadow-md text-white bg-green-500 hover:bg-green-700 transition-colors duration-200 whitespace-nowrap">
                                    Iniciar Serviço
                                </a>
                            </div>
                        @else
                            <div>
                                <button type="button" class="btn btn-primary btn-sm w-full mb-2 whitespace-nowrap"
                                    onclick="confirmarRows()">Finalizar Serviço</button>
                            </div>
                        @endif
                    </x-tables.cell>
                    <x-tables.cell>{{ $controle->id_servico_mecanico }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($controle->data_inclusao, 'd/m/Y') }}</x-tables.cell>
                    <x-tables.cell>{{ $controle->id_mecanico ? $controle->fornecedor->nome_fornecedor : '' }}</x-tables.cell>
                    <x-tables.cell>{{ $controle->pessoal ? optional($controle->pessoal)->nome : '' }}</x-tables.cell>
                    <x-tables.cell>{{ $controle->servico->descricao_servico ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $controle->id_os }}</x-tables.cell>
                    <x-tables.cell>{{ $controle->veiculo ? $controle->veiculo->placa : '' }}</x-tables.cell>
                    <x-tables.cell>{{ $controle->status_servico }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="10" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    <div class="mt-4">
        {{ $manutancaoServicoMec->links() }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.row-checkbox');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = [...checkboxes].every(cb => cb.checked);
                    selectAll.checked = allChecked;
                }
            });
        });
    });

    function confirmarRows() {
        // Coletar todos os checkboxes marcados
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');

        // Extrair os valores (IDs) dos checkboxes marcados
        const ids = Array.from(selectedCheckboxes).map(checkbox => checkbox.value).join(',');

        if (ids.length === 0) {
            alert('Por favor, selecione pelo menos um serviço para finalizar.');
            return;
        }

        // Redirecionar para a rota com os IDs
        window.location.href = `/admin/manutencaoservicosmecanico/finalizartodos?ids=${ids}`;
    }
</script>
