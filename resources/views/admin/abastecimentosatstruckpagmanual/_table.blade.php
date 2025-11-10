<div class="results-table" id="results-table">
    @if ($abastecimentos && $abastecimentos->count() > 0)
        <x-tables.table>
            <x-tables.header>
                <x-tables.head-cell>Cód. de<br>Abastecimento</x-tables.head-cell>
                <x-tables.head-cell>Origem</x-tables.head-cell>
                <x-tables.head-cell>Bomba/Posto</x-tables.head-cell>
                <x-tables.head-cell>Placa</x-tables.head-cell>
                <x-tables.head-cell>Data<br>Abast.</x-tables.head-cell>
                <x-tables.head-cell>Combustível</x-tables.head-cell>
                <x-tables.head-cell>Volume(L)</x-tables.head-cell>
                <x-tables.head-cell>Km<br>Anterior</x-tables.head-cell>
                <x-tables.head-cell>Km<br>Abast.</x-tables.head-cell>
                <x-tables.head-cell>Km<br>Rodado</x-tables.head-cell>
                <x-tables.head-cell>Média<br>Km/L</x-tables.head-cell>
                <x-tables.head-cell>Valor<br>Litro</x-tables.head-cell>
                <x-tables.head-cell>Valor<br>Total</x-tables.head-cell>
                <x-tables.head-cell>Filial</x-tables.head-cell>
                <x-tables.head-cell>Departamento</x-tables.head-cell>
                <x-tables.head-cell>Categoria</x-tables.head-cell>
                <x-tables.head-cell>Terceiro</x-tables.head-cell>
                <x-tables.head-cell>Tratado</x-tables.head-cell>

                {{-- Coluna Ações - Só aparece se usuário tem permissões para ações --}}
                @php
                    $hasAnyAction = false;
                    // Verificar se pode enviar para inconsistência (ATS/Truckpag)
                    $hasInconsistenciaAction = auth()
                        ->user()
                        ->hasAnyPermission(['editar_inconsistenciaats', 'editar_inconsistenciatruckpag']);
                    // Verificar se pode editar abastecimento manual
                    $hasEditAction = auth()->user()->can('editar_abastecimentomanual');

                    $hasAnyAction = $hasInconsistenciaAction || $hasEditAction;
                @endphp

                @if ($hasAnyAction)
                    <x-tables.head-cell>Ações</x-tables.head-cell>
                @endif
            </x-tables.header>

            <x-tables.body>
                @foreach ($abastecimentos as $index => $abastecimento)
                    <x-tables.row :index="$index">
                        <x-tables.cell>{{ $abastecimento->id }}</x-tables.cell>
                        <x-tables.cell>
                            @if ($abastecimento->tipo == 'ABASTECIMENTO VIA ATS')
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">ATS</span>
                            @elseif($abastecimento->tipo == 'ABASTECIMENTO VIA TRUCKPAG')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">TRUCKPAG</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">MANUAL</span>
                            @endif
                        </x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->descricao_bomba }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->placa }}</x-tables.cell>
                        <x-tables.cell nowrap>{{ $abastecimento->data_inicio?->format('d/m/Y H:i') }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->tipocombustivel }}</x-tables.cell>
                        <x-tables.cell class="text-right">{{ number_format($abastecimento->volume, 2, ',', '.') }}
                        </x-tables.cell>
                        <x-tables.cell class="text-right">{{ number_format($abastecimento->km_anterior, 0, ',', '.') }}
                        </x-tables.cell>
                        <x-tables.cell
                            class="text-right">{{ number_format($abastecimento->km_abastecimento, 0, ',', '.') }}
                        </x-tables.cell>
                        <x-tables.cell class="text-right">{{ number_format($abastecimento->km_rodado, 0, ',', '.') }}
                        </x-tables.cell>
                        <x-tables.cell class="text-right">{{ number_format($abastecimento->media, 2, ',', '.') }}
                        </x-tables.cell>
                        <x-tables.cell class="text-right">R$
                            {{ number_format($abastecimento->valor_litro, 2, ',', '.') }}
                        </x-tables.cell>
                        <x-tables.cell class="text-right">R$
                            {{ number_format($abastecimento->valor_total, 2, ',', '.') }}
                        </x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->nome_filial }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->descricao_departamento }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->descricao_categoria }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->is_terceiro ? 'SIM' : 'NAO' }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->tratado ? 'SIM' : 'NAO' }}</x-tables.cell>

                        {{-- Coluna Ações com Controle de Permissões --}}
                        @if ($hasAnyAction)
                            <x-tables.cell>
                                <div class="flex items-center space-x-2">
                                    {{-- Botão Enviar para Inconsistência - ATS/Truckpag --}}
                                    @if (
                                        $abastecimento->tipo == 'ABASTECIMENTO VIA ATS' ||
                                            $abastecimento->tipo ==
                                                'ABASTECIMENTO
                                                                VIA TRUCKPAG')
                                        @php
                                            $canSendInconsistencia = false;
                                            if ($abastecimento->tipo == 'ABASTECIMENTO VIA ATS') {
                                                $canSendInconsistencia = auth()
                                                    ->user()
                                                    ->can('editar_inconsistenciaats');
                                            } elseif ($abastecimento->tipo == 'ABASTECIMENTO VIA TRUCKPAG') {
                                                $canSendInconsistencia = auth()
                                                    ->user()
                                                    ->can('editar_inconsistenciatruckpag');
                                            }
                                        @endphp

                                        @if ($canSendInconsistencia)
                                            <button type="button"
                                                onclick="enviarParaInconsistencia({{ $abastecimento->id }})"
                                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                title="Enviar para inconsistência">
                                                <x-icons.exclamation class="h-3 w-3" />
                                            </button>
                                        @endif
                                    @endif

                                    {{-- Botão Editar - Apenas para Abastecimentos Manuais --}}
                                    @if ($abastecimento->tipo == 'ABASTECIMENTO MANUAL')
                                        @can('editar_abastecimentomanual')
                                            {{--
                            FIXME: IDs hardcoded - necessário refatorar para usar permissões específicas
                            Usuários autorizados: Antonio(3), Marcos(4), Marcelo(17) + ID 25
                            Verificar relacionamento pessoal_id vs user_id antes de alterar
                            --}}
                                            @if (auth()->user()->isSuperuser() || in_array(auth()->user()->id, [3, 4, 25]))
                                                <a href="{{ route('admin.abastecimentomanual.edit', $abastecimento->id) }}"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    title="Editar">
                                                    <x-icons.pencil class="h-3 w-3" />
                                                </a>
                                            @endif
                                        @endcan
                                    @endif
                                </div>
                            </x-tables.cell>
                        @endif
                    </x-tables.row>
                @endforeach
            </x-tables.body>
        </x-tables.table>

        {{-- Paginação - só aparece quando há dados --}}
        @if ($abastecimentos->hasPages())
            <div class="mt-4">
                {{ $abastecimentos->links() }}
            </div>
        @endif
    @else
        {{-- Verificar se há filtros aplicados --}}
        @php
            $hasFilters = request()->filled([
                'id',
                'data_inicio',
                'data_final',
                'placa',
                'id_tipo_combustivel',
                'id_categoria',
                'id_tipo_equipamento',
                'id_filial',
            ]);
        @endphp

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            @if (!$hasFilters)
                <div class="text-blue-800">
                    <svg class="mx-auto h-12 w-12 text-blue-400 mb-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <h3 class="text-lg font-medium text-blue-900 mb-1">Nenhum filtro aplicado</h3>
                    <p class="text-blue-700">Utilize os filtros acima para visualizar os dados de abastecimento.</p>
                </div>
            @else
                <div class="text-gray-600">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhum resultado encontrado</h3>
                    <p class="text-gray-600">Nenhum abastecimento encontrado para os filtros aplicados.</p>
                </div>
            @endif
        </div>
    @endif
</div>
