<div class="results-table overflow-x-auto shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left text-black w-8">
                    <x-forms.checkbox name="id[]" class="select-all-checkbox" />
                </th>
                <th class="px-4 py-2 text-left">Número de Fogo</th>
                <th class="px-4 py-2 text-left">Data Inclusão</th>
                <th class="px-4 py-2 text-left">Data Alteração</th>
                <th class="px-4 py-2 text-left">Dias em depósito</th>
                <th class="px-4 py-2 text-left">Local</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Departamento</th>
                <th class="px-4 py-2 text-left">Filial</th>
                <th class="px-4 py-2 text-left">Destinação Solicitada</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 shadow-md rounded-lg">
            {{-- Loop pelos pneus --}}
            @forelse($deposito as $index => $pneu)
                @php
                    // Normaliza a destinação para tomar decisões (fallback para descricao_destino)
                    $dest = strtolower(trim($pneu->destinacao_solicitada ?? ($pneu->descricao_destino ?? '')));
                    $destClass = '';
                    if (
                        str_contains($dest, 'manuten') ||
                        str_contains($dest, 'manutenção') ||
                        str_contains($dest, 'manutencao')
                    ) {
                        $destClass = 'bg-orange-50 text-orange-700';
                    } elseif (str_contains($dest, 'estoque') || str_contains($dest, 'stock')) {
                        $destClass = 'bg-green-50 text-green-700';
                    }
                @endphp

                <tr class="{{ $destClass }}">
                    <td class="px-4 py-2">
                        {{-- Adiciona data-destinacao para que o JS saiba a destinação solicitada da linha --}}
                        <x-forms.checkbox name="id[]" type="checkbox" class="pedido-checkbox"
                            value="{{ $pneu->id_deposito_pneu }}"
                            data-destinacao="{{ $pneu->destinacao_solicitada ?? ($pneu->descricao_destino ?? '') }}" />
                    </td>
                    <td class="px-4 py-2">{{ $pneu->id_pneu ?? '' }}</td>
                    <td class="px-4 py-2">{{ format_date($pneu->data_inclusao) }}</td>
                    <td class="px-4 py-2">{{ format_date($pneu->data_alteracao) }}</td>
                    <td class="px-4 py-2">
                        @php($badge = $pneu->diasEmDepositoBadge())
                        @if ($badge)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $badge }}</span>
                        @else
                            --
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $pneu->descricao_destino ?? 'Não Informado' }}</td>
                    <td class="px-4 py-2">{{ $pneu->pneus->status_pneu ?? 'Não Informado' }}</td>
                    <td class="px-4 py-2">
                        {{ $pneu->pneus->departamentoPneu->descricao_departamento ?? 'Não Informado' }}</td>
                    <td class="px-4 py-2">{{ $pneu->pneus->filialPneu->name ?? 'Não Informado' }}</td>
                    <td class="px-4 py-2">{{ $pneu->destinacao_solicitada ?? 'Não Informado' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-500">Nenhum registro
                        encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginação --}}
    <div class="mt-4">
        {{ $deposito->links() }}
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
