<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. O.S.</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Data Abertura</x-tables.head-cell>
            <x-tables.head-cell>Tipo O.S.</x-tables.head-cell>
            <x-tables.head-cell>Situação O.S.</x-tables.head-cell>
            <x-tables.head-cell>Data Encerramento</x-tables.head-cell>
            <x-tables.head-cell>Recepcionista</x-tables.head-cell>
            <x-tables.head-cell>Local Manutenção</x-tables.head-cell>
            <x-tables.head-cell>Recepcionista Encerramento</x-tables.head-cell>
            <x-tables.head-cell>Cód. Lcto. O.S. Auxiliar</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @php
                $permissao = [
                    'usuarios_reabertura' => [1, 25, 291],
                    'usuarios_exclusao' => [1, 3, 4, 25, 28, 50, 78],
                ];
            @endphp
            @forelse ($ordemServicoCanceladas as $index => $ordemServicoCancelada)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            {{-- Regra de Exibição --}}
                            @if (in_array(Auth::user()->id, $permissao['usuarios_reabertura']))
                                <a href="#"
                                    onclick="confirmarExclusao({{ $ordemServicoCancelada->id_ordem_servico }})" /><x-icons.trash
                                    class="w-4 h-4 text-red-600" /></a>
                            @endif

                            <a
                                href="{{ route('admin.ordemservicocanceladas.exportPdf') }}?{{ http_build_query(['id' => $ordemServicoCancelada->id_ordem_servico]) }}">
                                <x-icons.pdf-doc class="w-4 h-4 text-red-600" />
                            </a>

                            {{-- Regra de Exibição --}}
                            @if (in_array(Auth::user()->id, $permissao['usuarios_exclusao']))
                                <a
                                    href="{{ route('admin.ordemservicocanceladas.retornaros', $ordemServicoCancelada->id_ordem_servico) }}" /><x-icons.refresh
                                    class="w-4 h-4 text-red-600" /></a>
                            @endif
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->id_ordem_servico }}</x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->veiculo->placa ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($ordemServicoCancelada->data_abertura, 'd/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->tipoOrdemServico->descricao_tipo_ordem }}</x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->statusOrdemServico->situacao_ordem_servico }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($ordemServicoCancelada->data_encerramento, 'd/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->usuario->name ?? 'Não Encontrado' }}</x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->local_manutencao }}</x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->usuarioEncerramento->name ?? 'Não Encontrado' }}</x-tables.cell>
                    <x-tables.cell>{{ $ordemServicoCancelada->id_lancamento_os_auxiliar }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $ordemServicoCanceladas->links() }}
    </div>
</div>
