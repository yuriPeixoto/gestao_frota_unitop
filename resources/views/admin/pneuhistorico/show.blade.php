<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Histórico de Vida do Pneu') }} - ID: {{ $pneu->id_pneu }}
            </h2>
        </div>
    </x-slot>
<div class="container mx-auto px-4 py-6">
    <!-- Cabeçalho -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('admin.pneuhistorico.index') }}"
                   class="inline-flex items-center text-indigo-600 hover:text-indigo-800 no-print">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar
                </a>
                <span class="text-gray-300 no-print">|</span>
                <h1 class="text-2xl font-bold text-gray-900">Histórico de Vida do Pneu</h1>
            </div>
            <p class="text-gray-600">Todas as movimentações e atividades registradas</p>
        </div>

        <div class="flex space-x-2 no-print">
            <x-ui.export-buttons route="admin.pneuhistorico" :formats="['pdf']" :route-params="['id' => $pneu->id_pneu]" />
        </div>
    </div>

    <!-- Informações do Pneu -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Informações do Pneu</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">N° de Fogo</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $pneu->id_pneu }}</dd>
            </div>
            @if($pneu->cod_antigo)
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">Código Antigo</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $pneu->cod_antigo }}</dd>
            </div>
            @endif
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">Modelo</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $pneu->getModeloPneuFromHistorico()?->descricao_modelo ?? 'N/A' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">Status Atual</dt>
                <dd>
                    @php
                        $statusColors = [
                            'APLICADO' => 'bg-green-100 text-green-800',
                            'ESTOQUE' => 'bg-blue-100 text-blue-800',
                            'MANUTENCAO' => 'bg-orange-100 text-orange-800',
                            'DESCARTE' => 'bg-red-100 text-red-800',
                            'VENDA' => 'bg-purple-100 text-purple-800'
                        ];
                        $statusColor = $statusColors[$pneu->status_pneu] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColor }}">
                        {{ $pneu->status_pneu }}
                    </span>
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">Filial</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $pneu->filialPneu->name ?? 'N/A' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">Departamento</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $pneu->departamentoPneu->descricao_departamento ?? 'N/A' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">Data de Inclusão</dt>
                <dd class="text-lg font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($pneu->data_inclusao)->format('d/m/Y H:i') }}
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-sm font-medium text-gray-500">Total de Eventos</dt>
                <dd class="text-lg font-semibold text-indigo-600">{{ count($historico) }}</dd>
            </div>
        </div>
    </div>

    <!-- Timeline do Histórico -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-6">Timeline de Movimentações</h2>

        @if(count($historico) > 0)
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($historico as $index => $evento)
                    <li>
                        <div class="relative pb-8">
                            @if($index < count($historico) - 1)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    @php
                                        $colorClass = match($evento['cor']) {
                                            'green' => 'bg-green-500',
                                            'blue' => 'bg-blue-500',
                                            'orange' => 'bg-orange-500',
                                            'red' => 'bg-red-500',
                                            'purple' => 'bg-purple-500',
                                            'indigo' => 'bg-indigo-500',
                                            'emerald' => 'bg-emerald-500',
                                            'gray' => 'bg-gray-500',
                                            default => 'bg-gray-500'
                                        };
                                    @endphp
                                    <div class="h-8 w-8 rounded-full {{ $colorClass }} flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @switch($evento['icone'])
                                                @case('document-plus')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    @break
                                                @case('truck')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                    @break
                                                @case('archive-box')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                    @break
                                                @case('wrench-screwdriver')
                                                @case('wrench')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    @break
                                                @case('check-circle')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    @break
                                                @case('adjustments-horizontal')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                                    @break
                                                @case('arrow-right-circle')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    @break
                                                @case('banknotes')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    @break
                                                @case('trash')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    @break
                                                @case('calculator')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    @break
                                                @case('arrow-path')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    @break
                                                @default
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            @endswitch
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div>
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-900">{{ $evento['descricao'] }}</p>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">
                                            <time datetime="{{ $evento['data'] }}">
                                                {{ \Carbon\Carbon::parse($evento['data'])->format('d/m/Y H:i') }}
                                            </time>
                                        </div>
                                    </div>

                                    @if(!empty($evento['detalhes']))
                                    <div class="mt-3 bg-gray-50 rounded-lg p-3">
                                        <dl class="space-y-1">
                                            @foreach($evento['detalhes'] as $campo => $valor)
                                                @if(!empty($valor))
                                                <div class="flex justify-between text-sm">
                                                    <dt class="font-medium text-gray-600 capitalize">
                                                        {{ str_replace('_', ' ', $campo) }}:
                                                    </dt>
                                                    <dd class="text-gray-900 text-right ml-2">
                                                        @if($campo === 'valor_unitario' || $campo === 'valor_venda')
                                                            R$ {{ number_format($valor, 2, ',', '.') }}
                                                        @elseif($campo === 'km_rodados' && $valor > 0)
                                                            {{ number_format($valor, 0, ',', '.') }} km
                                                        @elseif($campo === 'diferenca')
                                                            @if($valor > 0)
                                                                <span class="text-green-600">+{{ $valor }}</span>
                                                            @elseif($valor < 0)
                                                                <span class="text-red-600">{{ $valor }}</span>
                                                            @else
                                                                <span class="text-gray-600">0</span>
                                                            @endif
                                                        @elseif(str_contains($campo, 'data_') || str_ends_with($campo, '_data') || $campo === 'data_inclusao' || $campo === 'data_recebimento' || $campo === 'data_prevista_retorno')
                                                            @php
                                                                try {
                                                                    echo \Carbon\Carbon::parse($valor)->format('d/m/Y');
                                                                } catch (\Exception $e) {
                                                                    echo $valor;
                                                                }
                                                            @endphp
                                                        @else
                                                            {{ $valor }}
                                                        @endif
                                                    </dd>
                                                </div>
                                                @endif
                                            @endforeach
                                        </dl>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum histórico encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">Este pneu ainda não possui movimentações registradas.</p>
            </div>
        @endif
    </div>

    <!-- Resumo Estatístico -->
    @if(count($historico) > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Resumo de Atividades</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @php
                $resumo = collect($historico)->groupBy('tipo')->map->count();
                $tiposCount = [
                    'ENTRADA_SISTEMA' => 'Entradas',
                    'APLICADO' => 'Aplicações',
                    'ESTOQUE' => 'Estoques',
                    'MANUTENCAO' => 'Manutenções',
                    'CALIBRAGEM' => 'Calibragens',
                    'TRANSFERENCIA' => 'Transferências',
                    'VENDA' => 'Vendas',
                    'DESCARTE' => 'Descartes'
                ];
            @endphp
            @foreach($tiposCount as $tipo => $label)
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $resumo[$tipo] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Botão Voltar ao Topo -->
<button id="back-to-top"
        class="fixed bottom-6 right-6 bg-indigo-600 text-white p-3 rounded-full shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-110 opacity-0 invisible"
        onclick="scrollToTop()">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
    </svg>
</button>

<style>
    @media print {
        .no-print { display: none !important; }
        body { print-color-adjust: exact; }
        #back-to-top { display: none !important; }
    }

    #back-to-top.show {
        opacity: 1;
        visibility: visible;
    }
</style>

<script>
// Função para mostrar/ocultar o botão baseado na posição do scroll
window.addEventListener('scroll', function() {
    const backToTopButton = document.getElementById('back-to-top');

    if (window.pageYOffset > 300) {
        backToTopButton.classList.add('show');
    } else {
        backToTopButton.classList.remove('show');
    }
});

// Função para voltar ao topo suavemente
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>

</x-app-layout>
