<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód. Franquia Mensal</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Ativo</x-tables.head-cell>
            <x-tables.head-cell>Tipo Equipamento</x-tables.head-cell>
            <x-tables.head-cell>Sub Categoria</x-tables.head-cell>
            <x-tables.head-cell>Operação</x-tables.head-cell>
            <x-tables.head-cell>Step</x-tables.head-cell>
            <x-tables.head-cell>Média</x-tables.head-cell>
            <x-tables.head-cell>0 á 1000</x-tables.head-cell>
            <x-tables.head-cell>1000</x-tables.head-cell>
            <x-tables.head-cell>2000</x-tables.head-cell>
            <x-tables.head-cell>3000</x-tables.head-cell>
            <x-tables.head-cell>4000</x-tables.head-cell>
            <x-tables.head-cell>5000</x-tables.head-cell>
            <x-tables.head-cell>6000</x-tables.head-cell>
            <x-tables.head-cell>7000</x-tables.head-cell>
            <x-tables.head-cell>8000</x-tables.head-cell>
            <x-tables.head-cell>9000</x-tables.head-cell>
            <x-tables.head-cell>10000</x-tables.head-cell>
            <x-tables.head-cell>11000</x-tables.head-cell>
            <x-tables.head-cell>12000</x-tables.head-cell>
            <x-tables.head-cell>13000</x-tables.head-cell>
            <x-tables.head-cell>14000</x-tables.head-cell>
            <x-tables.head-cell>15000</x-tables.head-cell>
            <x-tables.head-cell>16000</x-tables.head-cell>
            <x-tables.head-cell>17000</x-tables.head-cell>
            <x-tables.head-cell>18000</x-tables.head-cell>
            <x-tables.head-cell>19000</x-tables.head-cell>
            <x-tables.head-cell>20000</x-tables.head-cell>

        </x-tables.header>

        <x-tables.body>
            @forelse ($listagem as $franquia)
            <x-tables.row>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <x-tooltip content="Desativar">
                            <form
                                action="{{ route('admin.franquiapremiosmensal.desativar', $franquia->id_franquia_premio_mensal) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja desativar este registro?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    <x-icons.ban class="h-3 w-3" />
                                </button>
                            </form>
                        </x-tooltip>
                        <x-tooltip content="Editar">
                            <a href="{{ route('admin.franquiapremiosmensal.edit', $franquia->id_franquia_premio_mensal) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                        <x-tooltip content="Excluir">
                            <form
                                action="{{ route('admin.franquiapremiosmensal.delete', $franquia->id_franquia_premio_mensal) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja excluir este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            </form>
                        </x-tooltip>
                        <x-tooltip content="Clonar">
                            <form
                                action="{{ route('admin.franquiapremiosmensal.clonar', $franquia->id_franquia_premio_mensal) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja clonar este registro?');">
                                @csrf
                                <button type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-copy" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z" />
                                    </svg>
                                </button>
                            </form>
                        </x-tooltip>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $franquia->id_franquia_premio_mensal }}</x-tables.cell>
                <x-tables.cell>{{ format_date($franquia->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>
                    <span class="{{ $franquia->ativo ? 'text-green-600' : 'text-red-600'}}">
                        {{ $franquia->ativo ? 'Sim' : 'Não'}}
                    </span>
                </x-tables.cell>
                <x-tables.cell>{{ $franquia->equipamento->descricao_tipo ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $franquia->subcategoria->descricao_subcategoria ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $franquia->operacao->descricao_tipo_operacao ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $franquia->step}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->media, 2, '.','.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_0_1000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_1000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_2000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_3000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_4000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_5000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_6000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_7000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_8000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_9000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_10000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_11000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_12000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_13000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_14000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_15000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_16000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_17000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_18000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_19000, 2, '.', '.')}}</x-tables.cell>
                <x-tables.cell>{{ number_format($franquia->_20000, 2, '.', '.')}}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="14" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    {{ $listagem->links() }}
</div>
</div>