<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Monitoramento das Manutenções') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Tipo de Categoria"
                    content="Nesta tela você pode visualizar todos os tipos de checklist. Utilize o botão 'Novo Tipo Checklist' para adicionar um novo registro. Você pode editar ou excluir tipos de checklist existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

   
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <form method="GET" action="{{ route('admin.monitoramentoDasManutencoes.index') }}">
            <div class="flex p-4 gap-4">
                <div class="w-full ">
                    <div>
                        <label class="block text-xl font-medium text-gray-700" for="filial">Filial</label>
                        <select class="w-full" id="filial" name="filial">
                            @foreach ($filiais as $filial)
                                <option value="{{ $filial->value }}" {{ request('filial') == $filial->value ? 'selected' : '' }}>{{ $filial->label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 mt-4 h-12 text-white bg-blue-500 rounded hover:bg-blue-600">
                            Buscar
                        </button>
                    </div>
                </div>

                <div class="w-full">
                    <div>
                        <label class="block text-xl font-medium text-gray-700" for="categoria_veiculo">Categoria de veículos</label>
                        <select class="w-full" id="categoria_veiculo" name="categoria_veiculo">
                            @foreach ($tipoVeiculo as $tipo)
                                <option value="{{ $tipo->value }}" {{ request('categoria_veiculo') == $tipo->value ? 'selected' : '' }}>{{ $tipo->label }}</option>
                            @endforeach
                        </select>
                    </div>
                
                </div>

                <div class="w-full">
                    <div>
                        <label class="block text-xl font-medium text-gray-700" for="status_os">Status O.S</label>
                        <select class="w-full" id="status_os" name="status_os">
                            @foreach ($statusOS as $status)
                                <option value="{{ $status->value }}" {{ request('status_os') == $status->value ? 'selected' : '' }}>{{ $status->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="w-full">
                    <div>
                        <label class="block text-xl font-medium text-gray-700" for="tipo_os">Tipo O.S</label>
                        <select class="w-full" id="tipo_os" name="tipo_os">
                            @foreach ($tipoOS as $tipo)
                                <option value="{{ $tipo->value }}" {{ request('tipo_os') == $tipo->value ? 'selected' : '' }}>{{ $tipo->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>  
        </form>
        <div class="flex px-4 my-4 w-full gap-4">

            <div class="flex w-full  bg-sky-500">
                <div class="bg-sky-600 p-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24"><g fill="none">
                        <path fill="white" d="M2 3V1.75C1.31 1.75.75 2.31.75 3zm11 0h1.25c0-.69-.56-1.25-1.25-1.25zm0 6V7.75A1.25 1.25 0 0 0 11.75 9zM2 4.25h11v-2.5H2zM11.75 3v16h2.5V3zm-8.5 14V3H.75v14zM13 10.25h5v-2.5h-5zM20.75 13v4h2.5v-4zm-6.5 6V9h-2.5v10zm4.28.53a.75.75 0 0 1-1.06 0l-1.768 1.768a3.25 3.25 0 0 0 4.596 0zm-1.06-1.06a.75.75 0 0 1 1.06 0l1.768-1.768a3.25 3.25 0 0 0-4.596 0zM6.53 19.53a.75.75 0 0 1-1.06 0l-1.768 1.768a3.25 3.25 0 0 0 4.596 0zm-1.06-1.06a.75.75 0 0 1 1.06 0l1.768-1.768a3.25 3.25 0 0 0-4.596 0zm13.06 0a.74.74 0 0 1 .22.53h2.5a3.24 3.24 0 0 0-.952-2.298zm.22.53a.74.74 0 0 1-.22.53l1.768 1.768A3.24 3.24 0 0 0 21.25 19zM16 17.75h-3v2.5h3zm1.47 1.78a.74.74 0 0 1-.22-.53h-2.5c0 .83.318 1.664.952 2.298zm-.22-.53a.74.74 0 0 1 .22-.53l-1.768-1.768A3.24 3.24 0 0 0 14.75 19zm-11.78.53a.74.74 0 0 1-.22-.53h-2.5c0 .83.318 1.664.952 2.298zM5.25 19a.74.74 0 0 1 .22-.53l-1.768-1.768A3.24 3.24 0 0 0 2.75 19zM13 17.75H8v2.5h5zm-6.47.72a.74.74 0 0 1 .22.53h2.5c0-.83-.318-1.664-.952-2.298zm.22.53a.74.74 0 0 1-.22.53l1.768 1.768A3.24 3.24 0 0 0 9.25 19zm14-2a.75.75 0 0 1-.75.75v2.5A3.25 3.25 0 0 0 23.25 17zM18 10.25A2.75 2.75 0 0 1 20.75 13h2.5c0-2.9-2.35-5.25-5.25-5.25zM.75 17A3.25 3.25 0 0 0 4 20.25v-2.5a.75.75 0 0 1-.75-.75z"/>
                        <path stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2 8h3m-3 4h5"/></g>
                    </svg>
                </div>
                <div class="p-4 mx-auto text-white">
                    <h1 class="font-bold text-md">VEÍCULOS EM PREVENTIVA</h1>
                    <span class="font-extrabold text-md">{{ $preventivaCount }}</span>
                </div>
            </div>

            <div class="flex w-full  bg-zinc-500">
                <div class="bg-zinc-600 p-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24"><path fill="white" fill-rule="evenodd" d="M1 3a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v5h4a5 5 0 0 1 5 5v4a3 3 0 0 1-2.129 2.872a3 3 0 0 1-5.7.128H8.83a3 3 0 0 1-5.7-.128A3 3 0 0 1 1 17v-4h6a1 1 0 1 0 0-2H1V9h4a1 1 0 0 0 0-2H1zm13 15h1.171a3 3 0 0 1 5.536-.293A1 1 0 0 0 21 17v-4a3 3 0 0 0-3-3h-4zm-7 1a1 1 0 1 0-2 0a1 1 0 0 0 2 0m10.293-.707A1 1 0 0 0 17 19a1 1 0 1 0 .293-.707" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="p-4 mx-auto text-white">
                    <h1 class="font-bold text-md">VEÍCULOS EM CORRENTIVA</h1>
                    <span class="font-extrabold text-md">{{ $corretivaCount }}</span>
                </div>
            </div>

            <div class="flex w-full bg-red-500">
                <div class="bg-red-600 p-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24"><g fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="currentColor"><path d="M19.5 19.5a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0m-10 0a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0"/>
                        <path d="M2 12v5c0 .935 0 1.402.201 1.75a1.5 1.5 0 0 0 .549.549c.348.201.815.201 1.75.201m10 0h-5m5.5-2V9c0-1.414 0-2.121-.44-2.56C14.122 6 13.415 6 12 6h-1m4.5 2.5h1.801c.83 0 1.245 0 1.589.195c.344.194.557.55.984 1.262l1.699 2.83c.212.354.318.532.373.728c.054.197.054.403.054.816V17c0 .935 0 1.402-.201 1.75a1.5 1.5 0 0 1-.549.549c-.348.201-.815.201-1.75.201M7.85 7.85l-1.35-.9V4.7M2 6.5a4.5 4.5 0 1 0 9 0a4.5 4.5 0 0 0-9 0"/>
                    </svg>
                </div>
                <div class="py-4 px-2 text-white">
                    <h1 class="font-bold text-md">ATRASADAS</h1>
                    <span class="font-extrabold text-md">{{ $atrasadasCount }}</span>
                </div>
            </div>

            <div class="flex w-full bg-green-500">
                <div class="bg-green-600 p-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 32 32">
                        <path fill="white" d="M12 6H8V2H6v4H2v2h4v4h2V8h4z"/>
                        <path fill="white" d="m29.919 16.606l-3-7A1 1 0 0 0 26 9h-3V7a1 1 0 0 0-1-1h-7v2h6v12.556A4 4 0 0 0 19.142 23h-6.284a3.98 3.98 0 0 0-7.716 0H4v-9H2v10a1 1 0 0 0 1 1h2.142a3.98 3.98 0 0 0 7.716 0h6.284a3.98 3.98 0 0 0 7.716 0H29a1 1 0 0 0 1-1v-7a1 1 0 0 0-.081-.394M9 26a2 2 0 1 1 2-2a2.003 2.003 0 0 1-2 2m14-15h2.34l2.144 5H23Zm0 15a2 2 0 1 1 2-2a2 2 0 0 1-2 2m5-3h-1.142A3.995 3.995 0 0 0 23 20v-2h5Z"/>
                    </svg>
                </div>
                <div class="p-4 px-2 text-white">
                    <h1 class="font-bold text-md">DENTRO DO PRAZO</h1>
                    <span class="font-extrabold text-md">0</span>
                </div>
            </div>
        </div>
        
        <div class="w-full px-4 gap-4 bg-white md:flex">
            <div class="w-full border-2 border-gray-300">
                <div class="p-4 bg-gray-100 border-b text-gray-600 border-gray-300">
                    Manutenções por Tipo de Equipamento
                </div>
                <div class="p-4">
                    {!! $manutecaoChart->container() !!} 
                    {!! $manutecaoChart->script() !!}
                </div>
            </div>
            
            <div class="w-full border-2 border-gray-300">
                <div class="p-4 bg-gray-100 border-b text-gray-600 border-gray-300">
                    Manutenções Situações
                </div>
                <div>
                    {!! $manutecaoSituacaoChart->container() !!} 
                    {!! $manutecaoSituacaoChart->script() !!}
                </div>
            </div>
        </div>
      
        <div class="mx-4 w-full ">
            <div class="w-10/12 mt-10">
                <form method="GET" action="{{ route('admin.monitoramentoDasManutencoes.index') }}">
                    <div class="flex items-center gap-2">
                        <div class="w-4/12">
                            <x-bladewind::input 
                                label="Pesquisar por Placa"
                                name="search"
                                error_message="search"
                                selected_value="{{ old($request->search ?? '') }}" 
                            />
                        </div>
                        <button type="submit" class="px-4 py-2 mb-4 h-12 text-white bg-blue-500 rounded hover:bg-blue-600">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
            <table class="divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            OS
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            PLACA
                        </th>

                        <th scope="col"
                            class="py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Equipamento
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo OS
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                           Situação OS
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Observação
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            DATA ABERTURA
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            previsão saída
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            tempo previsão
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            tempo atraso
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            recepcionista
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            km
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            filial
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($list as $result)
                    <tr>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->id_ordem_servico }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->placa }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->descricao_tipo }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->descricao_tipo_ordem }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->situacao_ordem_servico }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->observacao }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->data_abertura }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->data_previsao_saida }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->previsao }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->atraso }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->recepcionista }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->km_manutencao }}
                        </td>
                        <td class="py-4 text-sm font-medium text-gray-900">
                            {{ $result->filial }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Nenhuma pergunta cadastrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $list->links() }}
            </div>
        </div>
    </div>

</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

