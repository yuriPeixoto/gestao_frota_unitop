<div class="flex justify-end mb-4">
    <div class="bg-white border border-gray-300 rounded-2xl px-4 py-3 shadow-md">
        <div class="text-sm font-medium text-gray-700 mb-2">Legenda:</div>
        <div class="space-y-2">
            <div
                class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-yellow-600/20 ring-inset">
                Pré-O.S</div>
            <div
                class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">
                Lançamento O.S. Auxiliar</div>
            <div
                class="inline-flex items-center rounded-md bg-blue-50 text-blue-500 px-2 py-1 text-xs font-medium ring-1 ring-green-600/20 ring-inset">
                Tipo Corretiva</div>
        </div>
    </div>
</div>
<div class="results-table overflow-visible">
    <div class="shadow-sm rounded-lg overflow-x-auto relative" style="overflow-y: visible !important;">
        <table class="min-w-full divide-y divide-gray-200">
            <x-tables.header>
                <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
                <x-tables.head-cell>Cód. O.S.</x-tables.head-cell>
                <x-tables.head-cell>Placa</x-tables.head-cell>
                {{-- <x-tables.head-cell>Filial Veiculo</x-tables.head-cell> --}}
                <x-tables.head-cell>Data Abertura</x-tables.head-cell>
                <x-tables.head-cell>Data Encerramento</x-tables.head-cell>
                <x-tables.head-cell>Tipo O.S.</x-tables.head-cell>
                <x-tables.head-cell>Situação O.S.</x-tables.head-cell>
                <x-tables.head-cell>Recepcionista</x-tables.head-cell>
                <x-tables.head-cell>Local Manutenção</x-tables.head-cell>
                <x-tables.head-cell>Recepcionista Encerramento</x-tables.head-cell>
                <x-tables.head-cell>Código Pré-O.S. / Lançamento O.S. Auxiliar / Tipo Corretiva</x-tables.head-cell>
                <x-tables.head-cell>Grupo Resolvedor</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body>
                @forelse ($ordemServicos as $index => $ordemServico)
                    <x-tables.row :index="$index">
                        <x-tables.cell>
                            <div class="relative">
                                <button
                                    class="dropdown-button bg-white border px-4 py-2 rounded shadow flex items-center space-x-2 hover:bg-gray-50">
                                    <x-icons.gear class="w-4 h-4" />
                                    <span>Ações</span>
                                </button>
                                <ul
                                    class="dropdown-menu absolute left-0 top-full mt-1 w-48 bg-white border rounded-lg shadow-xl hidden overflow-visible">
                                    @if ($ordemServico->id_tipo_ordem_servico)
                                        <li>
                                            <a href="{{ route('admin.ordemservicos.edit' . ($ordemServico->id_tipo_ordem_servico === 1 ? '_preventiva' : ''), $ordemServico->id_ordem_servico) }}"
                                                class="edit-link block px-4 py-2 text-blue-600 hover:bg-gray-100 flex items-center first:rounded-t-lg">
                                                <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                                {{ $ordemServico->id_tipo_ordem_servico === 1
                                                    ? 'Editar Preventiva'
                                                    : ($ordemServico->id_tipo_ordem_servico === 2
                                                        ? 'Editar Corretiva'
                                                        : 'Editar Borracharia') }}
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="#" onclick="imprimirOS('{{ $ordemServico->id_ordem_servico }}')"
                                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100 flex items-center border-t border-gray-100">
                                            <x-icons.pdf-doc class="w-4 h-4 mr-2 text-red-600" />
                                            Imprimir O.S
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            onclick="visualizarServicos({{ $ordemServico->id_ordem_servico }})"
                                            class="block px-4 py-2 text-gray-500 hover:bg-gray-100 flex items-center border-t border-gray-100">
                                            <x-icons.magnifying-glass class="w-4 h-4 mr-2" />
                                            Visualizar Serviços
                                        </a>
                                    </li>
                                    @if (
                                        $ordemServico->id_status_ordem_servico === 4 &&
                                            (auth()->user()->is_superuser || in_array(auth()->user()->id, [271, 361])))
                                        <li>
                                            <a href="#"
                                                onclick="reabrirOrdemServico({{ $ordemServico->id_ordem_servico }})"
                                                class="block px-4 py-2 text-gray-500 hover:bg-gray-100 flex items-center border-t border-gray-100">
                                                <x-icons.refresh class="w-4 h-4 mr-2" />
                                                Reabrir O.S.
                                            </a>
                                        </li>
                                    @endif

                                    @if (auth()->user()->is_superuser || in_array(auth()->user()->id, [271, 361]))
                                        <li>
                                            <a href="#"
                                                onclick="destroyOrdemServico({{ $ordemServico->id_ordem_servico }})"
                                                class="flex items-center px-4 py-2 text-red-600 hover:bg-gray-100 border-t border-gray-100 last:rounded-b-lg">
                                                <x-icons.trash class="h-4 w-4 mr-2 text-red-600" />
                                                Excluir
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </x-tables.cell>
                        <x-tables.cell>{{ $ordemServico->id_ordem_servico }}</x-tables.cell>
                        <x-tables.cell>{{ $ordemServico->veiculo->placa ?? 'Não informado' }}</x-tables.cell>
                        {{-- <x-tables.cell>{{ $ordemServico->filialVeiculo->name ?? 'Não informado' }}</x-tables.cell> --}}
                        <x-tables.cell>{{ format_date($ordemServico->data_abertura, 'd/m/Y H:i') }}</x-tables.cell>
                        <x-tables.cell>{{ format_date($ordemServico->data_encerramento, 'd/m/Y H:i') }}</x-tables.cell>
                        <x-tables.cell>
                            @php
                                $badgeClasses = [
                                    1 => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 ring-inset', // Preventiva - Azul
                                    2 => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20 ring-inset', // Corretiva - Amarelo
                                    3 => 'bg-red-50 text-red-700 ring-1 ring-red-600/20 ring-inset', // Borracharia - Vermelho
                                ];
                            @endphp
                            @if ($ordemServico->tipoOrdemServico)
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $badgeClasses[$ordemServico->id_tipo_ordem_servico] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20 ring-inset' }}">
                                    {{ $ordemServico->tipoOrdemServico->id_tipo_ordem_servico == 1 ? 'O.S. Preventiva' : ($ordemServico->tipoOrdemServico->id_tipo_ordem_servico == 2 ? 'O.S. Corretiva' : 'O.S. Borracharia') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-gray-50 text-gray-700 ring-1 ring-gray-600/20 ring-inset">
                                    Tipo não definido
                                </span>
                            @endif
                        </x-tables.cell>
                        <x-tables.cell>{{ $ordemServico->statusOrdemServico->situacao_ordem_servico ?? 'N/A' }}</x-tables.cell>
                        <x-tables.cell>{{ $ordemServico->usuario->name ?? 'Não Encontrado' }}</x-tables.cell>
                        <x-tables.cell>{{ $ordemServico->local_manutencao }}</x-tables.cell>
                        <x-tables.cell>{{ $ordemServico->usuarioEncerramento->name ?? 'Não Encontrado' }}</x-tables.cell>
                        <x-tables.cell>
                            @if ($ordemServico->id_lancamento_os_auxiliar || $ordemServico->id_pre_os)
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $ordemServico->id_lancamento_os_auxiliar ? 'bg-green-50 text-green-700 ring-1 ring-green-600/20 ring-inset' : 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20 ring-inset' }}">
                                    {{ $ordemServico->id_lancamento_os_auxiliar ?? $ordemServico->id_pre_os }}
                                </span>
                            @endif
                            <br />
                            @php
                                $situacoes = [
                                    1 => 'Investimento',
                                    2 => 'Sinistro',
                                    3 => 'Socorro',
                                    4 => 'Retorno',
                                    5 => 'Programada',
                                    6 => 'Borracharia',
                                ];
                            @endphp

                            @if ($ordemServico->situacao_tipo_os_corretiva)
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium 
        {{ $ordemServico->situacao_tipo_os_corretiva ? 'bg-blue-50 text-blue-500 ring-1 ring-green-600/20 ring-inset' : 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20 ring-inset' }}">
                                    {{ $situacoes[$ordemServico->situacao_tipo_os_corretiva] ?? 'Desconhecido' }}
                                </span>
                            @endif
                        </x-tables.cell>
                        <x-tables.cell>{{ $ordemServico->grupoResolvedor->descricao_grupo_resolvedor ?? '' }}</x-tables.cell>


                    </x-tables.row>
                @empty
                    <x-tables.empty cols="11" message="Nenhum registro encontrado" />
                @endforelse
            </x-tables.body>
        </table>
    </div>
</div>

<x-bladewind.modal name="vizualizar-servicos" size="omg" cancel_button_label="" ok_button_label="Ok"
    title="Serviços">
    <x-tables.table>
        <x-tables.header id="tabelaHeader">
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Manutenção</x-tables.head-cell>
            <x-tables.head-cell>Serviços</x-tables.head-cell>
            <x-tables.head-cell>Quantidade de Serviços</x-tables.head-cell>
            <x-tables.head-cell>Valor Total Serviços</x-tables.head-cell>
            <x-tables.head-cell>Valor Total com Desconto</x-tables.head-cell>
            <x-tables.head-cell>Serviço Finalizado</x-tables.head-cell>
            <x-tables.head-cell>NF Serviço</x-tables.head-cell>
            <x-tables.head-cell>Status Serviços</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body id="tabelaBody"></x-tables.body>

    </x-tables.table>


</x-bladewind.modal>

<div class="mt-4">
    {{ $ordemServicos->links() }}
</div>
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Dropdown script carregado");

            // Manipulador para os botões de dropdown
            const dropdownButtons = document.querySelectorAll('.dropdown-button');
            console.log("Encontrados", dropdownButtons.length, "botões dropdown");

            dropdownButtons.forEach((button, index) => {
                button.addEventListener('click', function(e) {
                    console.log("Botão dropdown clicado", index);
                    e.stopPropagation();

                    // Fecha todos os outros dropdowns
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        if (menu !== this.nextElementSibling) {
                            menu.classList.add('hidden');
                        }
                    });

                    // Alterna a visibilidade do dropdown atual
                    const dropdown = this.nextElementSibling;
                    console.log("Dropdown encontrado:", dropdown);

                    if (dropdown.classList.contains('hidden')) {
                        // Calcular posição do botão
                        const rect = this.getBoundingClientRect();

                        // Posicionar dropdown logo abaixo do botão
                        dropdown.style.position = 'fixed';
                        dropdown.style.top = (rect.bottom + 2) + 'px'; // 2px de espaço
                        dropdown.style.left = rect.left + 'px';
                        dropdown.style.zIndex = '99999';
                        dropdown.style.maxHeight = '300px';
                        dropdown.style.overflowY = 'auto';

                        // Verificar se o dropdown vai sair da tela e ajustar se necessário
                        const dropdownHeight = 160; // altura aproximada do dropdown
                        const windowHeight = window.innerHeight;

                        if (rect.bottom + dropdownHeight > windowHeight) {
                            // Se não couber embaixo, posiciona em cima do botão
                            dropdown.style.top = (rect.top - dropdownHeight) + 'px';
                        }

                        dropdown.classList.remove('hidden');
                        console.log("Dropdown aberto na posição:", dropdown.style.top, dropdown
                            .style.left);
                    } else {
                        dropdown.classList.add('hidden');
                        console.log("Dropdown fechado");
                    }
                });
            });

            // Fecha os dropdowns ao clicar fora deles
            document.addEventListener('click', function() {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            });

            // Fecha dropdown ao rolar a página
            window.addEventListener('scroll', function() {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            });

            // Salva pesquisa no localStorage ao clicar em editar, se houver campos preenchidos
            try {
                const editLinks = document.querySelectorAll('.edit-link');
                if (editLinks.length) {
                    editLinks.forEach(link => {
                        link.addEventListener('click', function() {
                            try {
                                // Seleciona o formulário de pesquisa específico de ordemservicos
                                const searchForm = document.querySelector(
                                    'form[action="{{ route('admin.ordemservicos.index') }}"]');
                                if (!searchForm) return;

                                // Pega inputs, selects e textareas com name
                                const fields = Array.from(searchForm.querySelectorAll(
                                    'input[name], select[name], textarea[name]'));
                                const filled = {};

                                fields.forEach(f => {
                                    const name = f.getAttribute('name');
                                    if (!name) return;

                                    let value = '';
                                    if ((f.type === 'checkbox' || f.type === 'radio') && !f
                                        .checked) {
                                        value = '';
                                    } else {
                                        value = f.value ?? '';
                                    }

                                    if (String(value).trim() !== '') {
                                        filled[name] = value;
                                    }
                                });

                                if (Object.keys(filled).length > 0) {
                                    const payload = {
                                        values: filled,
                                        savedAt: new Date().toISOString()
                                    };
                                    localStorage.setItem('ordemservicos_search', JSON.stringify(
                                        payload));
                                    console.log('Pesquisa O.S. salva no localStorage:', payload);
                                }
                            } catch (err) {
                                console.error('Erro salvando pesquisa no localStorage:', err);
                            }
                        });
                    });
                }
            } catch (err) {
                console.error('Erro ao registrar handlers de editar:', err);
            }
        });
    </script>
    @include('admin.ordemservicos._scripts')
@endpush
