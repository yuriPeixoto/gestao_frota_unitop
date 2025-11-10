<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de encerrantes') }}
            </h2>
            <div class="flex items-center space-x-4">
                @can('criar_encerrante')
                <a href="{{ route('admin.encerrantes.create') }}"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    {{ __('Cadastrar') }}
                </a>
                @endcan
                <x-help-icon title="Ajuda - Encerrantes"
                    content="Esta tela exibe os registros de encerrantes das bombas de combustível. Os campos abaixo servem para realizar buscas nos registros lançados!" />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Formulário de Busca -->
                <form action="{{ route('admin.encerrantes.index') }}" method="GET" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="id_encerrante" class="block text-sm font-medium text-gray-700">Cód.
                                Encerrante</label>
                            <input type="number" name="id_encerrante" id="id_encerrante"
                                value="{{ request('id_encerrante') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Digite o código...">
                        </div>

                        <div>
                            <label for="id_bomba" class="block text-sm font-medium text-gray-700">Bomba</label>
                            <select name="id_bomba" id="id_bomba"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach ($bombas as $bomba)
                                <option value="{{ $bomba->id_bomba }}" {{ request('id_bomba')==$bomba->id_bomba ?
                                    'selected' : '' }}>
                                    {{ $bomba->descricao_bomba }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="id_tanque" class="block text-sm font-medium text-gray-700">Tanque</label>
                            <select name="id_tanque" id="id_tanque"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach ($tanques as $tanque)
                                <option value="{{ $tanque->id_tanque }}" {{ request('id_tanque')==$tanque->id_tanque ?
                                    'selected' : '' }}>
                                    {{ $tanque->tanque }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="data_hora_abertura" class="block text-sm font-medium text-gray-700">Data Hora
                                Abertura</label>
                            <input type="datetime-local" name="data_hora_abertura" id="data_hora_abertura"
                                value="{{ request('data_hora_abertura') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label for="data_hora_encerramento" class="block text-sm font-medium text-gray-700">Data
                                Hora Encerramento</label>
                            <input type="datetime-local" name="data_hora_encerramento" id="data_hora_encerramento"
                                value="{{ request('data_hora_encerramento') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="encerrante_abertura" class="block text-sm font-medium text-gray-700">Valor
                                Abertura</label>
                            <input type="number" name="encerrante_abertura" id="encerrante_abertura"
                                value="{{ request('encerrante_abertura') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Digite o valor...">
                        </div>

                        <div>
                            <label for="encerrante_fechamento" class="block text-sm font-medium text-gray-700">Valor
                                Fechamento</label>
                            <input type="number" name="encerrante_fechamento" id="encerrante_fechamento"
                                value="{{ request('encerrante_fechamento') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Digite o valor...">
                        </div>

                        <div>
                            <label for="usuario" class="block text-sm font-medium text-gray-700">Conferente</label>
                            <input type="text" name="usuario" id="usuario" value="{{ request('usuario') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Digite o nome...">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                            Buscar
                        </button>
                        <a href="{{ route('admin.encerrantes.index') }}"
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                            Limpar
                        </a>
                    </div>
                </form>

                <!-- Tabela de Resultados - Estrutura Igual ao Sistema Legado -->
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cód. Encerrante
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bomba
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanque
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Hora Abertura
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Hora Encerramento
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Valor Abertura
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Valor Fechamento
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Conferente
                                </th>
                                {{-- Verificar se tem alguma ação disponível --}}
                                @if(auth()->user()->hasAnyPermission(['editar_encerrante', 'excluir_encerrante']))
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ações
                                </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($encerrantes as $encerrante)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    {{ $encerrante->id_encerrante }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $encerrante->bomba->descricao_bomba ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $encerrante->tanque->tanque ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $encerrante->data_hora_abertura ? $encerrante->data_hora_abertura->format('d/m/Y
                                    H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $encerrante->data_hora_encerramento ?
                                    $encerrante->data_hora_encerramento->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ number_format($encerrante->encerrante_abertura, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ number_format($encerrante->encerrante_fechamento, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ $encerrante->conferente->name ?? '-' }}
                                </td>
                                {{-- Coluna Ações - Só aparece se usuário tem permissões --}}
                                @if(auth()->user()->hasAnyPermission(['editar_encerrante', 'excluir_encerrante']))
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @can('editar_encerrante')
                                        <a href="{{ route('admin.encerrantes.edit', $encerrante) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                            Editar
                                        </a>
                                        @endcan

                                        @can('excluir_encerrante')
                                        <button onclick="deleteEncerrante({{ $encerrante->id_encerrante }})"
                                            class="text-red-600 hover:text-red-900" title="Excluir">
                                            Excluir
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                @php
                                $totalCols = 8; // Colunas básicas (incluindo nova coluna Conferente)
                                if(auth()->user()->hasAnyPermission(['editar_encerrante', 'excluir_encerrante'])) {
                                $totalCols = 9; // + coluna ações
                                }
                                @endphp
                                <td colspan="{{ $totalCols }}" class="px-6 py-4 text-center text-gray-500">
                                    Nenhum encerrante encontrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $encerrantes->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function deleteEncerrante(id) {
            if (confirm('Tem certeza que deseja excluir este encerrante?')) {
                fetch(`/admin/encerrantes/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao excluir o encerrante');
                    });
            }
        }
    </script>
    @endpush
</x-app-layout>