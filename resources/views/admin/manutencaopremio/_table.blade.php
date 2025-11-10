<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód. Prêmio</x-tables.head-cell>
            <x-tables.head-cell>Cód. Motorista</x-tables.head-cell>
            <x-tables.head-cell>Nome</x-tables.head-cell>
            <x-tables.head-cell>Data Inicial</x-tables.head-cell>
            <x-tables.head-cell>Data Final</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Distância</x-tables.head-cell>
            <x-tables.head-cell>Valor Prêmio</x-tables.head-cell>
            <x-tables.head-cell>Qtd placas</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse($listagem as $mant)
            <x-tables.row>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <x-tooltip content="Editar">
                            <a href="{{ route('admin.manutencaopremio.editarmotorista', $mant->id_mot_unitop) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{$mant->cod_premio}}</x-tables.cell>
                <x-tables.cell>{{$mant->id_mot_unitop}}</x-tables.cell>
                <x-tables.cell>{{$mant->nome_motorista ?? ''}}</x-tables.cell>
                <x-tables.cell>{{$mant->filial ?? ''}}</x-tables.cell>
                <x-tables.cell>{{format_date($mant->data_inicial)}}</x-tables.cell>
                <x-tables.cell>{{format_date($mant->data_final)}}</x-tables.cell>
                <x-tables.cell>{{$mant->distancia}}</x-tables.cell>
                <x-tables.cell>R$ {{number_format($mant->valor_premio, 2, ',', '.')}}</x-tables.cell>
                <x-tables.cell>{{$mant->qtd_placas}}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
</div>
<div class="mt-4">
    {{$listagem->links()}}
</div>