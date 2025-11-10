<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <!-- Loading indicator -->
    <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <!-- Container com scroll horizontal -->
    <div class="overflow-x-auto">
        <div id="results-table" class="opacity-0 transition-opacity duration-300 min-w-[1200px]">
            <x-tables.table>
                <x-tables.header>
                    <x-tables.head-cell>Placa</x-tables.head-cell>
                    <x-tables.head-cell>Restrições</x-tables.head-cell>
                    <x-tables.head-cell class="hidden md:table-cell">Comunicação</x-tables.head-cell>
                    <x-tables.head-cell class="hidden md:table-cell">Agente</x-tables.head-cell>
                    <x-tables.head-cell>Data</x-tables.head-cell>
                    <x-tables.head-cell>Detran Restrições</x-tables.head-cell>
                    <x-tables.head-cell>Renajud Restrições</x-tables.head-cell>
                    <x-tables.head-cell>Tipo Bloqueio</x-tables.head-cell>
                    <x-tables.head-cell>Protocolo</x-tables.head-cell>
                    <x-tables.head-cell>Processo</x-tables.head-cell>
                    <x-tables.head-cell>Município Bloqueio</x-tables.head-cell>
                    <x-tables.head-cell>Motivo</x-tables.head-cell>
                    <x-tables.head-cell>Data Renajud</x-tables.head-cell>
                    <x-tables.head-cell>Hora Renajud</x-tables.head-cell>
                    <x-tables.head-cell>Tipo Renajud</x-tables.head-cell>
                    <x-tables.head-cell>Processo Restrição</x-tables.head-cell>
                </x-tables.header>

                <x-tables.body>
                    @forelse($restricoes as $item)
                    <x-tables.row>
                        <x-tables.cell>{{$item->placa ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->restricoes ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->comunicacaovenda ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->agentefinanceiro ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->datainclusao ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->detranrestricoes ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->renajudrestricoes ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->tipobloqueio ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->protocolo ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->processo ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->mucipio_bloqueio ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->motivo ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->data_renajud ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->hora_renajud ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->tipo_renajud ?? '-'}}</x-tables.cell>
                        <x-tables.cell>{{$item->processo_restricao ?? '-'}}</x-tables.cell>


                    </x-tables.row>
                    @empty
                    <x-tables.empty cols="16" message="Nenhum registro encontrado"
                        class="text-center text-gray-500 py-6 col-span-full flex justify-center items-center" />
                    @endforelse
                </x-tables.body>
            </x-tables.table>

            <div class="mt-4">
                {{ $restricoes->withQueryString()->links() }}
            </div>
        </div>
    </div>