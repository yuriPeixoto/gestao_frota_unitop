<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Devolução de Materiais para Matriz') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Tipo de Categoria"
                    content="Nesta tela você pode visualizar todos os tipos de checklist. Utilize o botão 'Novo Tipo Checklist' para adicionar um novo registro. Você pode editar ou excluir tipos de checklist existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>
    <div class="bg-white  overflow-hidden p-6 shadow-sm sm:rounded-lg">
        <div class="w-full flex justify-between">
            <div class="w-10/12">
                <form method="GET" action="{{ route('admin.transferenciaDiretaEstoqueList.index') }}">
                    <div class="flex items-center gap-2">
                        <div class="w-4/12">
                            <x-bladewind::input 
                                label="Pesquisar por filial solicitante"
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
            </div>
            <div>
                <x-bladewind::dropmenu>
                    <x-slot:trigger>
                        <x-bladewind.button type="secondary" size="tiny">
                            <div class="inline-flex items-center p-1 gap-2 text-white font-medium rounded-md transition-colors duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                </svg>
                                    Exportar
                            </div>
                        </x-bladewind.button>
                    </x-slot:trigger>
                    <x-bladewind::dropmenu-item>
                        <a href="{{route('admin.transferenciaDiretaEstoqueList.gerarCSV')}}">
                            <div class="flex gap-4 itens-end p-[5px] font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                                </svg>
                                CSV
                            </div>
                        </a>
                    </x-bladewind::dropmenu-item>
                    <x-bladewind::dropmenu-item>
                        <a href="{{route('admin.transferenciaDiretaEstoqueList.gerarPFD')}}">
                            <div class="flex gap-4 itens-end p-[5px] font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                PDF
                            </div>
                        </a>
                    </x-bladewind::dropmenu-item>
                </x-bladewind::dropmenu>
            </div>
        </div>
       
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cód. Tansferência
                    </th>
    
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cód. Solicitação
                    </th>
    
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Filial Solicitação
                    </th>
    
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Usuário Solicitação
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                       Departamento
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Recebido
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                {{-- @forelse ($results as $result)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $result->id_transferencia_direta_estoque}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->status}}
                    </td>
                    <td class="px-6 py-4  text-sm text-gray-500">
                        {{ $result->name}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->descricao_departamento}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->observação}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->filial->descricao_filial }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->descricao_filial}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->data_inclusao}}
                    </td>
                </tr>
                @empty --}}
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Nenhuma pergunta cadastrado.
                    </td>
                </tr>
                {{-- @endforelse --}}
            </tbody>
        </table>
        {{-- <div class="mt-4">
            {{ $results->links() }}
        </div> --}}
    </div>

</x-app-layout>
