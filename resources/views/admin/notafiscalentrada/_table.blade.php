@php
    $user = auth()->user()->id;
    $permission = [5, 25, 291, 318, 433, 473, 476, 477, 483];

    $columns = [
        ['field' => 'id_nota_fiscal_entrada', 'label' => 'Código Nota Fiscal'],
        ['field' => 'nota_fiscal_entrada.numero_nota_fiscal', 'label' => 'Número Nota Fiscal'],
        ['field' => 'nota_fiscal_entrada.id_fornecedor', 'label' => 'Código Fornecedor'],
        ['field' => 'nota_fiscal_entrada.nome_empresa', 'label' => 'Nome Fornecedor'],
        ['field' => 'numero_item', 'label' => 'Número Item'],
        ['field' => 'cod_produto', 'label' => 'Código Produto'],
        ['field' => 'nome_produto', 'label' => 'Nome Produto'],
        ['field' => 'ncm', 'label' => 'Código NCM'],
        ['field' => 'quantidade_produtos', 'label' => 'Quantidade'],
        ['field' => 'qtde_devolucao', 'label' => 'Quantidade Devolução'],
        ['field' => 'unidade', 'label' => 'Unidade'],
        ['field' => 'valor_unitario_formatado', 'label' => 'Valor Unitário', 'class' => 'whitespace-nowrap'],
        [
            'field' => 'valor_unitario_desconto_formatado',
            'label' => 'Valor Unitário com Desconto',
            'class' => 'whitespace-nowrap',
        ],
        ['field' => 'valor_total_formatado', 'label' => 'Valor Total', 'class' => 'whitespace-nowrap'],
        [
            'field' => 'valor_unitario_desconto_formatado',
            'label' => 'Valor com Desconto',
            'class' => 'whitespace-nowrap',
        ],
    ];
@endphp
<div class="results-table">
    <div class="overflow-hidden rounded-lg shadow-md bg-white">
        <table class="w-full table-auto">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Ações</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Código Nota Fiscal</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Código Pedido</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Código Nota Fiscal</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Número Nota Fiscal</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">CNPJ</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Nome Empresa</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Número</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Natureza da Operação</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Data de Emissão</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Valor Nota Fiscal</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Valor do Frete</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Valor do Desconto</th>
                    <th class="px-6 py-4 text-left text-sm text-gray-900">Apuração de Saldo</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse ($nfEntrada as $index => $item)
                    <tr
                        class="{{ $item->valores_diferentes ? 'bg-red-200 hover:bg-red-300' : 'hover:bg-gray-50' }} {{ $item->apuracao_saldo ? '' : 'bg-yellow-200 hover:bg-yellow-300' }} transition-colors duration-200">
                        <td class="px-6 py-4">
                            <x-dropdown-menu button-text="Ações">
                                @if (!$item->apuracao_saldo && (in_array($user, $permission) || auth()->user()->is_superuser))
                                    <x-tooltip content="Editar Nota Fiscal">
                                        <li>
                                            <a href="{{ route('admin.notafiscalentrada.edit', $item->id_nota_fiscal_entrada) }}"
                                                class="flex items-center px-4 py-2 text-blue-600 hover:bg-blue-100">
                                                <x-icons.edit class="h-4 w-4 mr-2" />
                                                Editar
                                            </a>
                                        </li>
                                    </x-tooltip>
                                @endif
                                @if (
                                    $item->apuracao_saldo &&
                                        (in_array($user, $permission) || auth()->user()->is_superuser) &&
                                        $item->validarDevolucaoParcial() &&
                                        $item->validarDevolucao())
                                    <li>
                                        <a href="#"
                                            onclick="confirmarExclusao({{ $item->id_nota_fiscal_entrada }})"
                                            class="flex items-center px-4 py-2 text-red-600 hover:bg-red-100">
                                            <x-icons.trash class="h-4 w-4 mr-2" />
                                            Excluir Nota Fiscal
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a href="#"
                                        class="modal-trigger-notafiscalentrada-{{ $item->id_nota_fiscal_entrada }} flex items-center px-4 py-2 text-blue-600 hover:bg-blue-100">
                                        <x-icons.heroicon-s-magnifying-glass class="h-4 w-4 mr-2" />
                                        Visualizar
                                    </a>
                                </li>
                                @if (
                                    $item->apuracao_saldo &&
                                        in_array($user, $permission) &&
                                        ($item->validarDevolucaoParcial() && $item->validarDevolucao()))
                                    <li>
                                        <a href="{{ route('admin.notafiscalentrada.devolucao', $item->id_nota_fiscal_entrada) }}"
                                            class="flex items-center px-4 py-2 text-red-600 hover:bg-red-100">
                                            <x-icons.box class="h-4 w-4 mr-2" />
                                            Devolução Parcial
                                        </a>
                                    </li>
                                @endif
                                @if (
                                    $item->apuracao_saldo &&
                                        in_array($user, $permission) &&
                                        ($item->validarDevolucaoParcial() && $item->validarDevolucao()))
                                    <li>
                                        <a href="#"
                                            class="flex items-center px-4 py-2 text-red-600 hover:bg-red-100">
                                            <x-icons.box class="h-4 w-4 mr-2" />
                                            Devolução Total
                                        </a>
                                    </li>
                                @endif
                            </x-dropdown-menu>

                            <x-table-modal modal-id="notafiscalentrada-{{ $item->id_nota_fiscal_entrada }}"
                                title="Visualizando Itens" :columns="$columns"
                                fetch-url="/admin/notafiscalentrada/{{ $item->id_nota_fiscal_entrada }}/dados"
                                :items-per-page="2" max-width="7xl" />

                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->id_nota_fiscal_entrada }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->id_pedido_compras }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->cod_nota_fiscal }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->numero_nota_fiscal }}</td>
                        <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $item->cnpj }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->nome_empresa }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->numero }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->natureza_operacao }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ format_date($item->data_emissao) }}</td>
                        <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $item->valor_nota_fiscal_formatado }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $item->valor_frete_formatado }}</td>
                        <td class="px-6 py-4 text-gray-700 whitespace-nowrap">{{ $item->valor_desconto_formatado }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $item->apuracao_saldo ? 'Sim' : 'Não' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">Nenhum registro encontrado</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>



    <div class="mt-4">
        {{ $nfEntrada->links() }}
    </div>
    @push('scripts')
        @include('admin.notafiscalentrada._scripts')
    @endpush
</div>
