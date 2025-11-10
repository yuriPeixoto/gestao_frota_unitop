<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Devolução de Requisição de Peças') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Tipo de Categoria"
                    content="Nesta tela você pode visualizar todos os tipos de checklist. Utilize o botão 'Novo Tipo Checklist' para adicionar um novo registro. Você pode editar ou excluir tipos de checklist existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>
    <div class="bg-white  overflow-hidden p-6 shadow-sm sm:rounded-lg">
        <form method="GET" action="{{ route('admin.devolucaoTransferenciaEntreEstoque.index') }}">
            <div class="flex items-center gap-2">
                <div class="w-4/12">
                    <x-bladewind::input 
                        label="Pesquisar"
                        name="search"
                        error_message="search"
                        selected_value="{{ old('search', $checklist->search ?? '') }}" 
                    />
                </div>
                <button type="submit" class="px-4 py-2 mb-4 h-12 text-white bg-blue-500 rounded hover:bg-blue-600">
                    Buscar
                </button>
            </div>
        </form>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Código Solicitação
                    </th>
    
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Situação
                    </th>
    
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status Devolução
                    </th>
    
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Filial
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Departamento
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Filial Solicitante
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Inclusão
                    </th>
                    {{-- <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Ações</span>
                    </th> --}}
                </tr>
            </thead>
            {{-- <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($devolucao as $result)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $result->id_devolucao_transferencia_estoque_requisicao}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->nome}}
                    </td>
                    <td class="px-6 py-4  text-sm text-gray-500">
                        {{ $result->descricao}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    </td>
    
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Nenhuma pergunta cadastrado.
                    </td>
                </tr>
                @endforelse
            </tbody> --}}
        </table>
        <div class="mt-4">
            {{-- {{ $devolucao->links() }} --}}
        </div>
    </div>

</x-app-layout>
