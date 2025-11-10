<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód.</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Usuário Solicitante</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Usuário Estoque</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($requisicoes as $index => $requisicao)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="relative inline-block" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="bg-white border px-4 py-2 rounded shadow flex items-center space-x-2 hover:bg-gray-50">
                                <x-icons.gear class="w-4 h-4" />
                                <span>Ações</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <ul x-show="open" @click.away="open = false" x-transition
                                class="absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg z-50">
                                @php
                                    $canEdit = $requisicao->situacao !== 'FINALIZADA' && $requisicao->is_aprovado;
                                    $canBaixarItens =
                                        $requisicao->is_aprovado &&
                                        ($requisicao->situacao !== 'FINALIZADA' &&
                                            $requisicao->situacao !== 'INICIADA');
                                @endphp

                                @if ($canEdit)
                                    <li>
                                        <a href="{{ route('admin.saidaPneus.edit', $requisicao->id_requisicao_pneu) }}"
                                            class="block px-4 py-2 text-blue-600 hover:bg-gray-100 flex items-center">
                                            <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                            Editar
                                        </a>
                                    </li>
                                @endif

                                <li>
                                    <a href="{{ route('admin.saidaPneus.visualizar', $requisicao->id_requisicao_pneu) }}"
                                        class="block px-4 py-2 text-gray-500 hover:bg-gray-100 flex items-center">
                                        <x-icons.eye class="w-4 h-4 mr-2" />
                                        Visualizar
                                    </a>
                                </li>

                                @if ($canBaixarItens)
                                    <li>
                                        <form
                                            action="{{ route('admin.saidaPneus.assumir-baixa', $requisicao->id_requisicao_pneu) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button
                                                onclick="return confirm('Tem Certeza de que quer continuar com a operação e Assumir a Baixa dos Pneus?')"
                                                class="w-full text-left block px-4 py-2 text-green-600 hover:bg-gray-100 flex items-center">
                                                <x-icons.cubes class="w-4 h-4 mr-2" />
                                                Assumir requisição
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $requisicao->id_requisicao_pneu }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($requisicao->data_inclusao, 'd/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($requisicao->data_alteracao, 'd/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ $requisicao->usuarioSolicitante->name ?? 'Não Encontrado' }}</x-tables.cell>
                    <x-tables.cell>
                        @php
                            $situacaoDisplay =
                                $requisicao->situacao === 'FINALIZADA' ? 'BAIXADA' : $requisicao->situacao;
                            $badgeColor = match ($requisicao->situacao) {
                                'APROVADO' => 'bg-green-50 text-green-700 ring-green-600/20',
                                'INICIADA' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                'FINALIZADA' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                                'AGUARDANDO DOCUMENTO DE VENDA' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
                                'BAIXADO PARCIAL' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                default => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                            };
                        @endphp
                        <span
                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeColor }}">
                            {{ $situacaoDisplay }}
                        </span>
                    </x-tables.cell>
                    <x-tables.cell>{{ $requisicao->usuarioEstoque->name ?? 'Não Encontrado' }}</x-tables.cell>
                    <x-tables.cell>{{ $requisicao->filial->name ?? 'Não Encontrado' }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $requisicoes->links() }}
    </div>
</div>
