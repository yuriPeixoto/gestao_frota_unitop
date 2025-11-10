<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <div id="table-loading"
        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10 hidden">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="min-w-[1200px] w-full text-sm text-left text-gray-700 divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Descrição</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Quantidade</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Valor médio</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Localização</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Cód. Fabricante
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Cód. Alt. 1</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Cód. Alt. 2</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Usuário Edição
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Usuário Cadastro
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($cadastroProdutos as $produto)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <x-tooltip content="Editar">
                            <a href="{{ route('admin.cadastroprodutosestoque.edit', $produto->id_produto_unitop) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                        @if (auth()->user()->is_superuser || in_array(auth()->user()->id, [3, 4, 17, 25]))
                        <x-tooltip content="Excluir">
                            <form
                                action="{{ route('admin.cadastroprodutosestoque.destroy', $produto->id_produto_unitop) }}"
                                method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Tem certeza que deseja excluir o cadastro de produto?')"
                                    title="Excluir"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            </form>
                        </x-tooltip>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->id_produto_unitop }}
                    </td>
                    <td class="px-6 py-4 wrap text-sm text-gray-900">
                        {{ $produto->produto->descricao_produto ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->quantidade_produto }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->valor_medio }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->produto->localizacao_produto ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->produto->cod_fabricante_ ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->produto->cod_alternativo_1_ ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->produto->cod_alternativo_2_ ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->produto->usuarioEdicao->name ?? $produto->produto->id_user_edicao ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $produto->produto->usuarioCadastro->name ?? $produto->produto->id_user_cadastro ?? '' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhum produto encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $cadastroProdutos->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-produto').forEach(function(button) {
                button.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');
                    let nome = this.getAttribute('data-nome');

                    Swal.fire({
                        title: 'Excluir produto?',
                        text: `Tem certeza que deseja excluir "${nome}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, Excluir!',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/admin/cadastroprodutosestoque/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => response
                                    .json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        Swal.fire('Excluído!',
                                                data.message ||
                                                'Produto deletado com sucesso.',
                                                'success')
                                            .then(() => {
                                                location.reload();
                                            });
                                    } else {
                                        // Tratar erro do backend
                                        Swal.fire('Erro!',
                                            data.message ||
                                            'Não foi possível excluir o produto.',
                                            'error');
                                    }
                                })
                                .catch(error => {
                                    console.error('Erro:', error);
                                    Swal.fire('Erro!',
                                        'Erro de comunicação com o servidor.',
                                        'error');
                                });
                        }
                    });
                });
            });
        });
</script>
@endpush