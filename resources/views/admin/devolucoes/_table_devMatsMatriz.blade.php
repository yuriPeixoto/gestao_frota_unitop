<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10 hidden">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="min-w-[1200px] w-full text-sm text-left text-gray-700 divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Código Transferência
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Código Solicitação
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Filial Solicitação
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Usuário Solicitação
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Departamento</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Recebido</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($devMatsMatriz as $itemDevolucao)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                            <div class="relative inline-block">
                                <button
                                    class="dropdown-button bg-white border px-4 py-2 rounded shadow flex items-center space-x-2">
                                    <x-icons.gear class="w-4 h-4" />
                                    <span>Ações</span>
                                </button>
                                <ul
                                    class="dropdown-menu absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg hidden z-50">

                                    <li>
                                        <a href="{{ route('admin.devolucoes.edit_devMatsMatriz', $itemDevolucao->id_tranferencia) }}"
                                            class="block px-4 py-2 text-blue-600 hover:bg-gray-100 flex items-center">
                                            <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                            Editar
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            onclick="gerarDevolucao({{ $itemDevolucao->id_tranferencia }})"
                                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center">
                                            <x-icons.check class="w-6 h-6 mr-2 text-green-600" />
                                            Gerar Devolução
                                        </a>
                                    </li>
                                    <li>
                                        @php
                                            $permission = [
                                                1,
                                                3,
                                                4,
                                                10,
                                                19,
                                                25,
                                                26,
                                                38,
                                                63,
                                                70,
                                                76,
                                                78,
                                                79,
                                                97,
                                                99,
                                                100,
                                                101,
                                                104,
                                                136,
                                                171,
                                                181,
                                                207,
                                                221,
                                                248,
                                                316,
                                                356,
                                            ];
                                        @endphp

                                        @if (auth()->user()->superuser || (auth()->user()->departamento_id == 9 && in_array(auth()->user()->id, $permission)))
                                            <a href="href="{{ route('admin.devolucoes.edit_devMatsMatriz', $itemDevolucao->id_tranferencia) }}"
                                                class="block px-4 py-2 text-gray-500 hover:bg-gray-100 flex items-center">
                                                <x-icons.check class="w-6 h-6 mr-2 text-green-600" />
                                                Aprovar
                                            </a>
                                        @endif
                                    </li>
                                    <li>
                                        <a href="#"
                                            onclick="openModalTransferencia({{ $itemDevolucao->id_tranferencia }})"
                                            class="block px-4 py-2 text-gray-500 hover:bg-gray-100 flex items-center">
                                            <x-icons.exchange class="w-4 h-4 text-green-600 mr-2" />
                                            Enviar Transferência
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @include('admin.devolucoes._modal_transferencia')

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemDevolucao->id_tranferencia }}
                        </td>
                        <td class="px-6 py-4 wrap text-sm text-gray-900 text-center">
                            {{ $itemDevolucao->id_tranferencia }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemDevolucao->filial->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemDevolucao->usuario->name ?? '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemDevolucao->departamento->descricao_departamento ?? '' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-center">
                            {{ $itemDevolucao->recebido ? 'Sim' : 'Não' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $devMatsMatriz->links() }}
        </div>
    </div>
</div>
