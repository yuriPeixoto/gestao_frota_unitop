<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Subgrupo de Serviços
            </h2>
            @can('criar_grupos')
            <a href="{{ route('admin.subgruposervicos.create') }}"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Novo Subgrupo
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Código
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descrição
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grupo
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Inclusão
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Alteração
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($subgruposervicos as $subgruposervico)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $subgruposervico->id_subgrupo}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $subgruposervico->descricao_subgrupo}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $subgruposervico->grupoServico->descricao_grupo}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ format_date($subgruposervico->data_inclusao)}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ format_date($subgruposervico->data_alteracao)}}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.subgruposervicos.show', $subgruposervico->id_subgrupo) }}"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 rounded-md border border-blue-300"
                                        title="Visualizar">
                                        <x-icons.eye class="mr-2 h-4 w-4 text-blue-600" />
                                        Visualizar
                                    </a>

                                    <a href="{{ route('admin.subgruposervicos.edit', $subgruposervico->id_subgrupo) }}"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-md border border-gray-300"
                                        title="Editar">
                                        <x-icons.pencil class="mr-2 h-4 w-4 text-gray-600" />
                                        Editar
                                    </a>

                                    <form
                                        action="{{ route('admin.subgruposervicos.destroy', $subgruposervico->id_subgrupo) }}"
                                        method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Tem certeza que deseja excluir?')"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-white hover:bg-red-50 rounded-md border border-red-300"
                                            title="Excluir">
                                            <x-icons.trash class="mr-2 h-4 w-4 text-red-600" />
                                            Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Nenhum tipo de subgrupo cadastrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $subgruposervicos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>