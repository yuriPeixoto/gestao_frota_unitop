<x-tables.row>
    <x-tables.cell>{{ $pedidoNota->id_ordem_servico }}</x-tables.cell>

    <x-tables.cell>
        @foreach($pedidoNota->ordemServico?->servicos ?? [] as $servicoItem)
        {{ $servicoItem->servicos->descricao_servico ?? '' }}<br>
        @endforeach

    </x-tables.cell>

    <x-tables.cell>{{ $pedidoNota->fornecedor->nome_fornecedor }}</x-tables.cell>

    <x-tables.cell>{{ $pedidoNota->ordemServico->departamento?->descricao_departamento ?? '-'}}</x-tables.cell>

    <x-tables.cell>
        @foreach($pedidoNota->ordemServico->servicos ?? [] as $servicoItem)
        {{ $servicoItem->quantidade_servico ?? ''}}<br>
        @endforeach
    </x-tables.cell>

    <x-tables.cell>
        @foreach($pedidoNota->ordemServico->servicos ?? [] as $servicoItem)
        {{ $servicoItem->valor_servico ?? ''}}<br>
        @endforeach
    </x-tables.cell>

    <x-tables.cell>
        @foreach($pedidoNota->ordemServico->servicos ?? [] as $servicoItem)
        {{ $servicoItem->valor_total_com_desconto ?? ''}}<br>
        @endforeach
    </x-tables.cell>
</x-tables.row>