<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Solicitação de Envio #{{ $statusEnvio->id_manutencao_pneu }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- Botão ao lado do step --}}
                @if($statusEnvio->situacao_envio === 'Aguardando Aprovação')
                <button
                    onclick="document.getElementById('modal-aprovar-{{ $statusEnvio->id_manutencao_pneu }}').classList.remove('hidden')"
                    class="px-4 py-2 bg-green-500 text-white rounded-lg animate-pulse hover:bg-green-600">
                    Aprovar Saída
                </button>

                <!-- Modal -->
                <div id="modal-aprovar-{{ $statusEnvio->id_manutencao_pneu }}"
                    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 w-96">
                        <form method="POST"
                            action="{{ route('admin.manutencaopneus.aprovar', $statusEnvio->id_manutencao_pneu) }}">
                            @csrf
                            @method('PUT')

                            <label class="block mb-2 text-sm font-medium text-gray-700">Digite sua senha para
                                aprovar:</label>
                            <input type="password" name="senha" class="w-full border rounded-lg p-2 mb-4" required>

                            <div class="flex justify-end gap-2">
                                <button type="button"
                                    onclick="document.getElementById('modal-aprovar-{{ $statusEnvio->id_manutencao_pneu }}').classList.add('hidden')"
                                    class="px-4 py-2 bg-gray-300 rounded-lg">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                                    Confirmar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <a href="{{ route('admin.envioerecebimentopneus.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Mensagens de Sucesso/Erro -->
            @if (session('success'))
            <div class="mb-4 border-l-4 border-green-500 bg-green-100 p-4 text-green-700" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 border-l-4 border-red-500 bg-red-100 p-4 text-red-700" role="alert">
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <!-- Status da Solicitação (Timeline Completo) -->
            <div class="mb-6 overflow-hidden bg-white shadow sm:rounded-lg">
                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                        Status da Solicitação - Fluxo Completo
                    </h3>
                </div>
                <div class="p-6">

                    <div class="flex items-center justify-center space-x-2 overflow-x-auto pb-4">
                        @foreach ($fluxoCompleto as $key => $status)
                        @if (!$loop->first)
                        <!-- Linha conectora -->
                        <div class="{{ $status['ativo'] ? 'bg-green-500' : 'bg-gray-200' }} h-1 w-12 flex-shrink-0">
                        </div>
                        @endif

                        <!-- Step -->
                        <!-- Step -->
                        <div class="flex flex-col items-center relative">
                            <div class="{{ $status['ativo']
                                        ? (isset($status['erro'])
                                            ? 'bg-red-500 text-white'
                                            : (isset($status['warning'])
                                                ? 'bg-orange-500 text-white'
                                                : 'bg-green-500 text-white'))
                                        : 'bg-gray-200 text-gray-400' }}
                                    flex h-10 w-10 items-center justify-center rounded-full">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $status['icon'] }}"></path>
                                </svg>
                            </div>

                            {{-- Nome do status --}}
                            <span class="mt-2 max-w-20 text-center text-xs">{{ $status['label'] }}</span>

                            {{-- Data --}}
                            @if ($status['ativo'] && $status['data'])
                            <span class="mt-1 text-center text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($status['data'])->format('d/m H:i') }}
                            </span>
                            @endif


                        </div>

                        @endforeach
                    </div>


                    <!-- Status atual destacado -->
                    <div class="mt-6 flex items-center justify-center">
                        <span
                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $statusEnvio->situacao_envio ? 'bg-green-50 text-green-700 ring-1 ring-green-600/20 ring-inset' : 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20 ring-inset' }}">
                            {{ $statusEnvio->situacao_envio }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-white p-6">
                    <div class="space-y-6">
                        <!-- Status atual destacado -->
                        <div class="mb-4 flex items-center">
                            <span>

                            </span>

                            <!-- APROVADO POR / REJEITADO POR-->
                        </div>

                        <!-- Informações principais em 2 colunas -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Coluna 1 -->
                            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações da Solicitação
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Número da Solicitação
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $statusEnvio->id_manutencao_pneu }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Solicitante
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $statusEnvio->usuarioSolic->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        {{-- <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Borracheiro
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $solicitacao->departamento->descricao_departamento ?? 'N/A' }}

                                            </dd>
                                        </div> --}}
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Data da Solicitação
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $statusEnvio->data_inclusao ?
                                                $statusEnvio->data_inclusao->format('d/m/Y H:i') : 'N/A' }}
                                            </dd>
                                        </div>
                                        {{-- <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Prioridade
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                <span {{--
                                                    class="{{ $solicitacao->prioridade == 'alta'
                                                        ? 'bg-red-100 text-red-800'
                                                        : ($solicitacao->prioridade == 'media'
                                                            ? 'bg-yellow-100 text-yellow-800'
                                                            : 'bg-green-100 text-green-800') }} inline-flex rounded-md px-2 text-xs font-semibold leading-5">
                                                    {{ ucfirst($solicitacao->prioridade) }} >
                                                </span>
                                            </dd>
                                        </div> --}}

                                    </dl>
                                </div>
                            </div>

                            <!-- Coluna 2 -->
                            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações Adicionais
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $statusEnvio->filial->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        {{-- <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial de Entrega
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{-- {{ $solicitacao->filialEntrega->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial de Faturamento
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{-- {{ $solicitacao->filialFaturamento->name ?? 'N/A' }}
                                            </dd>
                                        </div> --}}
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Fornecedor
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $statusEnvio->fornecedor->nome_fornecedor ?? 'Não especificado'
                                                }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Tipo de Solicitação
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{-- @if ($solicitacao->tipo_solicitacao == 1)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                                    Produto
                                                </span>
                                                @elseif ($solicitacao->tipo_solicitacao == 2) --}}
                                                <span
                                                    class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                                    Serviço
                                                </span>
                                                {{-- @endif --}}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Informações Fiscais
                                </h3>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                <dt class="text-sm font-medium text-gray-500">
                                    Numero Nota Fiscal
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                    <span
                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                        {{ $statusEnvio->nf_envio }}
                                    </span>
                                </dd>
                            </div>
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">

                                <dt class="text-sm font-medium text-gray-500">
                                    Chave Nota Fiscal
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                    <span
                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                        {{ $statusEnvio->chave_nf_envio}}
                                    </span>
                                </dd>
                            </div>

                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                <dt class="text-sm font-medium text-gray-500">
                                    Valor Nota Fiscal
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                    <span
                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                        {{ $statusEnvio->valor_nf !== null
                                        ? 'R$ ' . number_format($statusEnvio->valor_nf, 2, ',', '.')
                                        : '-' }}
                                    </span>
                                </dd>
                            </div>

                        </div>

                        <!-- Itens da Solicitação -->
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="flex items-center justify-between bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Itens da Solicitação
                                </h3>
                                <span class="text-sm text-gray-500">{{ $statusEnvio->manutencaoPneusItens->count() }}
                                    item(ns)</span>
                            </div>
                            <div class="border-t border-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>

                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Fogo
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Modelo Pneu
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Tipo Manutenção
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @forelse($statusEnvio->manutencaoPneusItens as $item)
                                            <tr>
                                                <td
                                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                    {{ $item->id_pneu }}
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                    {{ $item->pneu->modeloPneu->descricao_modelo ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $item->tiporeforma->descricao_tipo_reforma ?? 'N/A' }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Nenhum item encontrado.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if ($statusEnvio->doc_nf || $statusEnvio->doc_extrato)
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Anexos
                                </h3>
                            </div>
                            <div class="border-t border-gray-200 p-6">
                                <ul class="divide-y divide-gray-200">
                                    @if ($statusEnvio->doc_nf)
                                    <li class="flex items-center justify-between py-3">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                            <span class="text-sm text-gray-900">Nota Fiscal (doc_nf)</span>
                                        </div>
                                        <a href="{{ route('admin.manutencaopneus.download', basename($statusEnvio->doc_extrato)) }}"
                                            target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">
                                            Download
                                        </a>
                                    </li>
                                    @endif

                                    @if ($statusEnvio->doc_extrato)
                                    <li class="flex items-center justify-between py-3">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                            <span class="text-sm text-gray-900">Extrato (doc_extrato)</span>
                                        </div>
                                        <a href="{{ route('admin.manutencaopneus.download', basename($statusEnvio->doc_extrato)) }}"
                                            target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">
                                            Download
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        @endif


                        <!-- Histórico de Aprovações e Alterações -->
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg mt-6">
                            <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Histórico</h3>
                            </div>
                            <div class="border-t border-gray-200 p-6">
                                <ul class="-mb-8">
                                    @forelse($historico as $index => $item)
                                    <li>
                                        <div class="relative pb-8">
                                            @if ($index < count($historico) - 1) <span
                                                class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gray-200"
                                                aria-hidden="true"></span>
                                                @endif

                                                <div class="relative flex items-start space-x-3">
                                                    <div>
                                                        <div class="relative px-1">
                                                            <div
                                                                class="{{ $item['color'] }} flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-white">
                                                                <svg class="h-5 w-5 text-white" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="{{ $item['icon'] }}"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div>
                                                            <div class="text-sm">
                                                                <span class="font-medium text-gray-900">{{
                                                                    $item['usuario'] }}</span>
                                                                <span class="font-semibold text-indigo-600">{{
                                                                    $item['acao'] }}</span>
                                                            </div>
                                                            <p class="mt-0.5 text-sm text-gray-500">
                                                                {{ $item['data']->format('d/m/Y H:i') }}
                                                            </p>
                                                        </div>
                                                        @if (!empty($item['observacao']))
                                                        <div class="mt-2 text-sm text-gray-700">
                                                            <p>{{ $item['observacao'] }}</p>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                        </div>
                                    </li>
                                    @empty
                                    <li class="text-center text-gray-500">Nenhum histórico disponível</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</x-app-layout>


<script>
    function verificarSalvar() {
    document.getElementById('modal-aprovar-').classList.remove('hidden');
    }

    function fecharModalNF() {
        document.getElementById('modal-aprovar-').classList.add('hidden');
        //document.getElementById('form-manutencao').submit();
    }
</script>