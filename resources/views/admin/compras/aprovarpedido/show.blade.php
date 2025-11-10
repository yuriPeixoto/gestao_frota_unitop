<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Solicitação de Compra #{{ $solicitacao->id_solicitacoes_compras }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.compras.aprovarpedido.index') }}"
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

            <!-- Timeline Horizontal da Solicitação -->
            <div
                class="mb-6 w-full rounded-lg border border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-8 shadow-sm">
                <div class="relative mx-auto max-w-7xl">
                    <div class="mb-6 text-center">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Status da Solicitação - Fluxo Completo
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Acompanhe o progresso da solicitação através das etapas
                        </p>
                    </div>

                    @php
                        $logs = $solicitacao->logs()->orderBy('data_inclusao')->get();
                        $currentStatus = $solicitacao->status;
                        $isCanceled = in_array(strtoupper($currentStatus), [
                            'CANCELADA',
                            'REJEITADA',
                            'REPROVADO GESTOR DEPARTAMENTO',
                            'COTAÇÕES RECUSADAS PELO GESTOR',
                        ]);
                    @endphp

                    @if ($logs->isNotEmpty())
                        <!-- Container dos Steps -->
                        <div class="relative flex items-start justify-between overflow-x-auto pb-4">
                            @foreach ($logs as $index => $log)
                                @php
                                    $isLastLog = $loop->last;
                                @endphp

                                <div class="relative flex min-w-[120px] flex-1 flex-col items-center">
                                    <!-- Círculo do Step -->
                                    <div class="relative mb-4 flex items-center justify-center" style="height: 80px;">
                                        <div
                                            class="@if ($isCanceled && $isLastLog) bg-red-500 border-red-600 text-white
                                            @elseif($isLastLog)
                                                bg-blue-500 border-blue-600 text-white ring-4 ring-blue-200 animate-pulse
                                            @else
                                                bg-green-500 border-green-600 text-white @endif relative z-20 flex h-14 w-14 items-center justify-center rounded-full border-2 shadow-lg transition-all duration-300">

                                            @if ($isCanceled && $isLastLog)
                                                <!-- Ícone de Cancelado -->
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @elseif($isLastLog)
                                                <!-- Ícone de Em Progresso -->
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <!-- Ícone de Concluído -->
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>

                                        <!-- Efeito de brilho para step atual -->
                                        @if ($isLastLog && !$isCanceled)
                                            <div class="absolute inset-0 z-10 animate-ping rounded-full bg-blue-400 opacity-20"
                                                style="width: 56px; height: 56px; margin: auto;"></div>
                                        @endif
                                    </div>

                                    <!-- Nome do Step -->
                                    <div class="mb-2 px-2 text-center" style="min-height: 60px; max-width: 140px;">
                                        <span
                                            class="@if ($isLastLog) text-blue-700 font-semibold
                                            @else text-gray-700 @endif block text-sm font-medium leading-tight transition-colors duration-200">
                                            {{ $log->situacao_compra }}
                                        </span>
                                    </div>

                                    <!-- Informações adicionais -->
                                    <div class="px-2 text-center" style="max-width: 140px;">
                                        <p class="text-xs text-gray-500">
                                            {{ $log->data_inclusao->format('d/m/Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $log->data_inclusao->format('H:i') }}
                                        </p>
                                    </div>

                                    <!-- Linha de Conexão -->
                                    @if (!$loop->last)
                                        <div class="@if ($isCanceled) bg-red-300
                                            @else bg-green-400 @endif absolute z-0 h-1 shadow-sm transition-all duration-500"
                                            style="top: 40px; left: calc(50% + 28px); right: calc(-50% + 28px);">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Status atual -->
                        <div class="mt-8 text-center">
                            <span
                                class="@if ($isCanceled) bg-red-100 text-red-800 border border-red-200
                                @else bg-blue-100 text-blue-800 border border-blue-200 @endif inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold shadow-sm">
                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                Status Atual: {{ $currentStatus }}
                            </span>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum histórico disponível</h3>
                            <p class="mt-1 text-sm text-gray-500">Esta solicitação ainda não possui registros de
                                alterações.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-white p-6">
                    <div class="space-y-6">
                        <!-- Status atual destacado -->
                        <div class="mb-4 flex items-center">
                            <span
                                class="{{ $solicitacao->getStatusClassAttribute() }} mr-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold leading-5">
                                {{ $solicitacao->status }}
                            </span>

                            @if ($solicitacao->aprovado_reprovado === true)
                                <span class="text-sm text-gray-600">
                                    Aprovada por {{ $solicitacao->aprovador->name ?? 'N/A' }} em
                                    {{ $solicitacao->data_aprovacao ? $solicitacao->data_aprovacao->format('d/m/Y H:i') : 'N/A' }}
                                </span>
                            @elseif($solicitacao->aprovado_reprovado === false)
                                <span class="text-sm text-gray-600">
                                    Rejeitada por {{ $solicitacao->aprovador->name ?? 'N/A' }} em
                                    {{ $solicitacao->data_aprovacao ? $solicitacao->data_aprovacao->format('d/m/Y H:i') : 'N/A' }}
                                </span>
                            @endif
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
                                                {{ $solicitacao->id_solicitacoes_compras }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Solicitante
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $solicitacao->solicitante->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Departamento
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $solicitacao->departamento->descricao_departamento ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Data da Solicitação
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $solicitacao->data_inclusao ? $solicitacao->data_inclusao->format('d/m/Y H:i') : 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Prioridade
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                <span
                                                    class="{{ $solicitacao->prioridade == 'alta'
                                                        ? 'bg-red-100 text-red-800'
                                                        : ($solicitacao->prioridade == 'media'
                                                            ? 'bg-yellow-100 text-yellow-800'
                                                            : 'bg-green-100 text-green-800') }} inline-flex rounded-md px-2 text-xs font-semibold leading-5">
                                                    {{ ucfirst($solicitacao->prioridade) }}
                                                </span>
                                            </dd>
                                        </div>
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
                                                {{ $solicitacao->filial->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial de Entrega
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $solicitacao->filialEntrega->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial de Faturamento
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $solicitacao->filialFaturamento->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Fornecedor Preferencial
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $solicitacao->fornecedor->nome_fornecedor ?? 'Não especificado' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Tipo de Solicitação
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                @if ($solicitacao->tipo_solicitacao == 1)
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                                        Produto
                                                    </span>
                                                @elseif ($solicitacao->tipo_solicitacao == 2)
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                                        Serviço
                                                    </span>
                                                @endif
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
                                    Observações
                                </h3>
                            </div>
                            <div class="border-t border-gray-200 p-6">
                                @if ($solicitacao->observacao)
                                    <p class="whitespace-pre-line text-sm text-gray-700">
                                        {{ $solicitacao->observacao }}</p>
                                @else
                                    <p class="text-sm italic text-gray-500">Nenhuma observação.</p>
                                @endif

                                @if ($solicitacao->aprovado_reprovado === false && $solicitacao->observacao_aprovador)
                                    <div class="mt-4 rounded-lg border border-red-100 bg-red-50 p-4">
                                        <h4 class="mb-1 text-sm font-medium text-red-800">Motivo da Rejeição:</h4>
                                        <p class="whitespace-pre-line text-sm text-red-700">
                                            {{ $solicitacao->observacao_aprovador }}</p>
                                    </div>
                                @elseif($solicitacao->aprovado_reprovado === true && $solicitacao->observacao_aprovador)
                                    <div class="mt-4 rounded-lg border border-green-100 bg-green-50 p-4">
                                        <h4 class="mb-1 text-sm font-medium text-green-800">Observação do Aprovador:
                                        </h4>
                                        <p class="whitespace-pre-line text-sm text-green-700">
                                            {{ $solicitacao->observacao_aprovador }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Cotações e Itens -->
                        <div class="overflow-hidden bg-white shadow-lg sm:rounded-xl">
                            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-800">
                                            Cotações Recebidas
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-600">
                                            Clique em uma cotação para visualizar seus itens
                                        </p>
                                    </div>
                                    <div class="rounded-lg bg-white px-4 py-2 shadow-sm">
                                        <span
                                            class="text-lg font-bold text-slate-700">{{ $cotacoesList->count() }}</span>
                                        <span class="block text-xs text-slate-500">Cotações</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 p-6">
                                @php
                                    // Agrupar itens por código de cotação
                                    $cotacoesAgrupadas = $cotacoesItensCompletos->groupBy('id_cotacao');
                                @endphp

                                @forelse($cotacoesAgrupadas as $codigoCotacao => $itens)
                                    <div
                                        class="rounded-xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 shadow-sm transition-all duration-300 hover:shadow-md">

                                        <!-- Cabeçalho da Cotação (Clicável) -->
                                        <div class="cursor-pointer rounded-t-xl border-b border-slate-200 bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-5 transition-colors duration-200 hover:from-indigo-100 hover:to-blue-100"
                                            onclick="toggleCotacao('cotacao-{{ $codigoCotacao }}')">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div>
                                                        <h4 class="text-lg font-semibold text-slate-800">
                                                            Cotação #{{ $codigoCotacao }}
                                                        </h4>
                                                        @if ($itens->first()['fornecedor'])
                                                            <p class="mt-1 flex items-center text-sm text-slate-600">
                                                                {{ $itens->first()['fornecedor'] }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-3">
                                                    <div class="text-right">
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                                            {{ $itens->count() }}
                                                            {{ $itens->count() == 1 ? 'item' : 'itens' }}
                                                        </span>
                                                        <div class="mt-1 text-sm font-semibold text-slate-700">
                                                            Total: R$
                                                            {{ number_format($itens->sum('valor_item'), 2, ',', '.') }}
                                                        </div>
                                                    </div>
                                                    <!-- Ícone de Expand/Collapse -->
                                                    <div class="transform transition-transform duration-200"
                                                        id="icon-cotacao-{{ $codigoCotacao }}">
                                                        <svg class="h-5 w-5 text-slate-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Conteúdo da Cotação (Colapsável) -->
                                        <div id="cotacao-{{ $codigoCotacao }}" class="hidden">
                                            <!-- Tabela de Itens da Cotação -->
                                            <div class="overflow-hidden rounded-b-xl">
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full">
                                                        <thead class="bg-slate-50">
                                                            <tr>
                                                                <th
                                                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                                    Produto
                                                                </th>
                                                                <th
                                                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                                    Descrição
                                                                </th>
                                                                <th
                                                                    class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                                    Qtd. Solicitada
                                                                </th>
                                                                <th
                                                                    class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                                    Qtd. Cotada
                                                                </th>
                                                                <th
                                                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                                    Valor Unit.
                                                                </th>
                                                                <th
                                                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                                    Total Item
                                                                </th>
                                                                <th
                                                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                                    Desconto
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white">
                                                            @foreach ($itens as $item)
                                                                <tr
                                                                    class="hover:bg-slate-25 border-b border-slate-100 transition-colors duration-200">
                                                                    <td class="whitespace-nowrap px-6 py-4">
                                                                        <div
                                                                            class="text-sm font-medium text-slate-900">
                                                                            {{ $item['id_produto'] ?? 'N/A' }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-6 py-4">
                                                                        <div class="max-w-xs text-sm text-slate-700">
                                                                            {{ $item['descricao_produto'] }}
                                                                        </div>
                                                                    </td>
                                                                    <td
                                                                        class="whitespace-nowrap px-6 py-4 text-center">
                                                                        <span
                                                                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                                            {{ number_format($item['quantidade_solicitada'], 2, ',', '.') }}
                                                                        </span>
                                                                    </td>
                                                                    <td
                                                                        class="whitespace-nowrap px-6 py-4 text-center">
                                                                        <span
                                                                            class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                                                            {{ number_format($item['quantidade_fornecedor'], 2, ',', '.') }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                                                        <div
                                                                            class="text-sm font-medium text-slate-900">
                                                                            R$
                                                                            {{ number_format($item['valorunitario'], 2, ',', '.') }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                                                        <div
                                                                            class="text-sm font-semibold text-slate-900">
                                                                            R$
                                                                            {{ number_format($item['valor_item'], 2, ',', '.') }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                                                        <div
                                                                            class="text-sm font-medium text-green-600">
                                                                            R$
                                                                            {{ number_format($item['valor_desconto'], 2, ',', '.') }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <!-- Totais da Cotação -->
                                                        <tfoot>
                                                            <tr class="bg-gradient-to-r from-slate-50 to-slate-100">
                                                                <td colspan="5" class="px-6 py-4 text-right">
                                                                    <span
                                                                        class="text-sm font-semibold text-slate-700">Total
                                                                        da Cotação:</span>
                                                                </td>
                                                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                                                    <div class="text-lg font-bold text-slate-900">
                                                                        R$
                                                                        {{ number_format($itens->sum('valor_item'), 2, ',', '.') }}
                                                                    </div>
                                                                </td>
                                                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                                                    <div class="text-lg font-bold text-green-600">
                                                                        R$
                                                                        {{ number_format($itens->sum('valor_desconto'), 2, ',', '.') }}
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-slate-900">Nenhuma cotação encontrada
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">Ainda não há cotações para esta
                                            solicitação.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Script para controlar o collapse/expand -->
                        <script>
                            function toggleCotacao(cotacaoId) {
                                const content = document.getElementById(cotacaoId);
                                const icon = document.getElementById('icon-' + cotacaoId);

                                if (content.classList.contains('hidden')) {
                                    // Expandir
                                    content.classList.remove('hidden');
                                    content.style.animation = 'slideDown 0.3s ease-out';
                                    icon.style.transform = 'rotate(180deg)';
                                } else {
                                    // Colapsar
                                    content.style.animation = 'slideUp 0.3s ease-out';
                                    setTimeout(() => {
                                        content.classList.add('hidden');
                                    }, 250);
                                    icon.style.transform = 'rotate(0deg)';
                                }
                            }

                            // Adicionar animações CSS
                            const style = document.createElement('style');
                            style.textContent = `
                                @keyframes slideDown {
                                    from {
                                        opacity: 0;
                                        max-height: 0;
                                        transform: translateY(-10px);
                                    }
                                    to {
                                        opacity: 1;
                                        max-height: 1000px;
                                        transform: translateY(0);
                                    }
                                }

                                @keyframes slideUp {
                                    from {
                                        opacity: 1;
                                        max-height: 1000px;
                                        transform: translateY(0);
                                    }
                                    to {
                                        opacity: 0;
                                        max-height: 0;
                                        transform: translateY(-10px);
                                    }
                                }
                            `;
                            document.head.appendChild(style);
                        </script>

                        <!-- Itens da Solicitação Original -->
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="flex items-center justify-between bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Itens da Solicitação Original
                                </h3>
                                <span class="text-sm text-gray-500">{{ $solicitacao->itens->count() }} item(ns)</span>
                            </div>
                            <div class="border-t border-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Tipo
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Código
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Descrição
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Quantidade
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Unidade
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @forelse($solicitacao->itens as $item)
                                                <tr>
                                                    <td
                                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                        {{ ucfirst($item->tipo_item) }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        {{ $item->id_produto ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        {{ $item->nome_item }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        {{ number_format($item->quantidade_solicitada, 2, ',', '.') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        {{ $item->unidadeProduto->descricao_unidade ?? 'N/A' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5"
                                                        class="px-6 py-4 text-center text-sm text-gray-500">
                                                        Nenhum item encontrado.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Histórico Detalhado -->
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Histórico Detalhado
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Todas as ações realizadas nesta solicitação
                                </p>
                            </div>
                            <div class="border-t border-gray-200 p-6">
                                <!-- Histórico das observações -->
                                @if ($solicitacao->observacoes)
                                    <div class="mb-6 rounded-lg bg-gray-50 p-4">
                                        <h4 class="mb-2 text-sm font-medium text-gray-900">Log de Atividades:</h4>
                                        <div class="whitespace-pre-line text-sm text-gray-700">
                                            {{ $solicitacao->observacoes }}</div>
                                    </div>
                                @endif

                                @php
                                    $logs = $solicitacao->logs()->orderBy('data_inclusao', 'desc')->get();

                                    $statusConfig = [
                                        'INCLUIDA' => ['color' => 'bg-blue-500'],
                                        'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO' => ['color' => 'bg-yellow-500'],
                                        'AGUARDANDO INÍCIO DE COMPRAS' => ['color' => 'bg-indigo-500'],
                                        'INICIADA' => ['color' => 'bg-blue-600'],
                                        'AGUARDANDO VALIDAÇÃO DO SOLICITANTE' => ['color' => 'bg-purple-500'],
                                        'SOLICITAÇÃO VALIDADA PELO GESTOR' => ['color' => 'bg-green-500'],
                                        'AGUARDANDO APROVAÇÃO' => ['color' => 'bg-yellow-600'],
                                        'FINALIZADO' => ['color' => 'bg-emerald-500'],
                                        'REPROVADO GESTOR DEPARTAMENTO' => ['color' => 'bg-red-500'],
                                        'CANCELADA' => ['color' => 'bg-red-600'],
                                        'COTAÇÕES RECUSADAS PELO GESTOR' => ['color' => 'bg-orange-500'],
                                        'APROVADA' => ['color' => 'bg-green-600'],
                                        'REJEITADA' => ['color' => 'bg-red-500'],
                                        'COMPRADOR ALTERADO' => ['color' => 'bg-indigo-400'],
                                        'SOLICITAÇÃO ADIADA' => ['color' => 'bg-orange-400'],
                                        'ADIAMENTO REMOVIDO' => ['color' => 'bg-green-400'],
                                    ];
                                @endphp

                                @if ($logs->isNotEmpty())
                                    <ul class="-mb-8">
                                        @foreach ($logs as $index => $log)
                                            <li>
                                                <div class="relative pb-8">
                                                    @if (!$loop->last)
                                                        <span
                                                            class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gray-200"
                                                            aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex items-start space-x-3">
                                                        <div>
                                                            <div class="relative px-1">
                                                                <div
                                                                    class="{{ $statusConfig[$log->situacao_compra]['color'] ?? 'bg-gray-500' }} flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-white">
                                                                    @if ($loop->first)
                                                                        <!-- Ícone de clock para o item mais recente -->
                                                                        <svg class="h-4 w-4 text-white"
                                                                            fill="currentColor" viewBox="0 0 640 640">
                                                                            <path
                                                                                d="M320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64zM296 184L296 320C296 328 300 335.5 306.7 340L402.7 404C413.7 411.4 428.6 408.4 436 397.3C443.4 386.2 440.4 371.4 429.3 364L344 307.2L344 184C344 170.7 333.3 160 320 160C306.7 160 296 170.7 296 184z" />
                                                                        </svg>
                                                                    @else
                                                                        <!-- Ícone de check para itens anteriores -->
                                                                        <svg class="h-4 w-4 text-white"
                                                                            fill="currentColor" viewBox="0 0 448 512">
                                                                            <path
                                                                                d="M434.8 70.1c14.3 10.4 17.5 30.4 7.1 44.7l-256 352c-5.5 7.6-14 12.3-23.4 13.1s-18.5-2.7-25.1-9.3l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l101.5 101.5 234-321.7c10.4-14.3 30.4-17.5 44.7-7.1z" />
                                                                        </svg>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div>
                                                                <div class="text-sm">
                                                                    @if ($log->usuario)
                                                                        <span
                                                                            class="font-medium text-gray-900">{{ $log->usuario->name }}</span>
                                                                    @else
                                                                        <span
                                                                            class="font-medium text-gray-900">Sistema</span>
                                                                    @endif
                                                                    <span class="font-semibold text-indigo-600">alterou
                                                                        o status para</span>
                                                                    <span
                                                                        class="font-semibold text-gray-900">{{ $log->situacao_compra }}</span>
                                                                </div>
                                                                <p class="mt-0.5 text-sm text-gray-500">
                                                                    {{ $log->data_inclusao->format('d/m/Y H:i:s') }}
                                                                </p>
                                                            </div>
                                                            @if ($log->observacao)
                                                                <div class="mt-2 text-sm text-gray-700">
                                                                    <p class="rounded-md bg-gray-50 p-2">
                                                                        {{ $log->observacao }}</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="py-8 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum histórico disponível
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">Esta solicitação ainda não possui
                                            registros de alterações.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
