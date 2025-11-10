@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Código Requisição</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Usuário Alteração</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Usuário Estoque</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Documento Autorização</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($requisicaoPneusSaida as $index => $item)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.requisicaopneusvendassaida.edit', $item->id_requisicao_pneu) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->is_superuser)
                                <button type="button" onclick="confirmarExclusao({{ $item->id_requisicao_pneu }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif

                            @php
                                $permissao = [25, 318];
                            @endphp
                            @if (in_array(auth()->user()->id, $permissao) || auth()->user()->is_superuser)
                                <button type="button" onclick="onCancel({{ $item->id_requisicao_pneu }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.disable class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $item->id_requisicao_pneu }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($item->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($item->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $item->usuarioEstoque->name ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->situacao }}</x-tables.cell>
                    <x-tables.cell>{{ $item->usuariosolicitante->name ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->filial->name ?? '-' }}</x-tables.cell>
                    <x-tables.cell class="break-all">
                        @if ($item->documento_autorizacao)
                            @php
                                $arquivoExiste = Storage::disk('public')->exists($item->documento_autorizacao);
                                $urlArquivo = $arquivoExiste
                                    ? route('admin.arquivo.show', ['path' => $item->documento_autorizacao])
                                    : '#';
                            @endphp

                            <a href="{{ $urlArquivo }}" type="button"
                                class="inline-flex items-center p-1 border border-transparent rounded-lg shadow-sm text-green-600 bg-green-200 hover:bg-green-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                title="{{ $arquivoExiste ? 'Visualizar documento' : 'Documento não encontrado' }}"
                                {{ !$arquivoExiste ? 'onclick="event.preventDefault(); alert(\'Arquivo não encontrado no servidor.\')"' : 'target="_blank"' }}>
                                Visualizar Documento
                            </a>
                        @endif
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $requisicaoPneusSaida->links() }}
    </div>
</div>
