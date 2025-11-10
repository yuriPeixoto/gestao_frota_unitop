<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Dashboard de Atividades</h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.log-atividades.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ver Todas as Atividades
                </a>
                <x-help-icon
                    title="Ajuda - Dashboard de Atividades"
                    content="Painel de controle com estatísticas e alertas sobre as atividades do sistema. Mostra resumos em tempo real, gráficos de atividades e alertas críticos que requerem atenção."
                />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">

                    <!-- Estatísticas principais -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium">Atividades Hoje</h3>
                                    <p class="text-3xl font-bold">{{ $stats['today_count'] ?? 0 }}</p>
                                </div>
                                <div class="text-blue-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium">Esta Semana</h3>
                                    <p class="text-3xl font-bold">{{ $stats['week_count'] ?? 0 }}</p>
                                </div>
                                <div class="text-green-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-6 text-white">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium">Críticas (24h)</h3>
                                    <p class="text-3xl font-bold">{{ $stats['critical_24h'] ?? 0 }}</p>
                                </div>
                                <div class="text-red-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-6 text-white">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium">Usuários Ativos</h3>
                                    <p class="text-3xl font-bold">{{ $stats['active_users_today'] ?? 0 }}</p>
                                </div>
                                <div class="text-yellow-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alertas críticos -->
                    @if($criticalAlerts->count() > 0)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
                            <div class="flex items-center mb-4">
                                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <h3 class="text-lg font-semibold text-red-800">Alertas Críticos ({{ $criticalAlerts->count() }})</h3>
                            </div>
                            <div class="space-y-3">
                                @foreach($criticalAlerts as $alert)
                                    <div class="bg-white rounded-lg p-4 border border-red-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-red-600">{{ $alert->getCriticalityIcon() }}</span>
                                                    <span class="font-medium text-gray-900">
                                                        {{ $alert->user->name ?? 'Sistema' }}
                                                    </span>
                                                    <span class="text-sm text-gray-600">
                                                        {{ $alert->getFormattedSummary() }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $alert->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.log-atividades.show', $alert) }}"
                                               class="text-sm text-blue-600 hover:text-blue-800">
                                                Ver detalhes
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Gráficos e estatísticas -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Gráfico de atividades (últimos 7 dias) -->
                        <div class="bg-white border rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Atividades dos Últimos 7 Dias</h3>
                            @if(count($activityChart) > 0)
                                <div class="space-y-3">
                                    @php
                                        $maxCount = max($activityChart->toArray());
                                    @endphp
                                    @foreach($activityChart as $date => $count)
                                        <div class="flex items-center">
                                            <div class="w-20 text-xs text-gray-600">
                                                {{ \Carbon\Carbon::parse($date)->format('d/m') }}
                                            </div>
                                            <div class="flex-1 mx-3">
                                                <div class="bg-gray-200 rounded-full h-4">
                                                    <div class="bg-blue-500 h-4 rounded-full"
                                                         style="width: {{ $maxCount > 0 ? ($count / $maxCount * 100) : 0 }}%"></div>
                                                </div>
                                            </div>
                                            <div class="w-12 text-xs text-gray-600 text-right">
                                                {{ $count }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-8">Nenhuma atividade nos últimos 7 dias</p>
                            @endif
                        </div>

                        <!-- Top usuários mais ativos -->
                        <div class="bg-white border rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Usuários Mais Ativos Hoje</h3>
                            @if($topUsers->count() > 0)
                                <div class="space-y-3">
                                    @foreach($topUsers as $userActivity)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-blue-600">
                                                        {{ substr($userActivity->user->name ?? 'S', 0, 1) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-medium">
                                                    {{ $userActivity->user->name ?? 'Sistema' }}
                                                </span>
                                            </div>
                                            <span class="text-sm text-gray-600">
                                                {{ $userActivity->activity_count }} atividades
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-8">Nenhuma atividade hoje</p>
                            @endif
                        </div>
                    </div>

                    <!-- Estatísticas por categoria e ação -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                        <!-- Top ações -->
                        <div class="bg-white border rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Principais Ações Hoje</h3>
                            @if(isset($stats['top_actions_today']) && count($stats['top_actions_today']) > 0)
                                <div class="space-y-3">
                                    @foreach($stats['top_actions_today'] as $action => $count)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium">
                                                {{ formatActivityAction($action) }}
                                            </span>
                                            <span class="text-sm text-gray-600">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-8">Nenhuma ação registrada hoje</p>
                            @endif
                        </div>

                        <!-- Top modelos -->
                        <div class="bg-white border rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Modelos Mais Alterados Hoje</h3>
                            @if(isset($stats['top_models_today']) && count($stats['top_models_today']) > 0)
                                <div class="space-y-3">
                                    @foreach($stats['top_models_today'] as $model => $count)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium">
                                                {{ formatActivityModel($model) }}
                                            </span>
                                            <span class="text-sm text-gray-600">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-center py-8">Nenhum modelo alterado hoje</p>
                            @endif
                        </div>
                    </div>

                    <!-- Categorias de atividades -->
                    @if(isset($stats['categories_today']) && count($stats['categories_today']) > 0)
                        <div class="bg-white border rounded-lg p-6 mt-8">
                            <h3 class="text-lg font-semibold mb-4">Atividades por Categoria Hoje</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($stats['categories_today'] as $category => $count)
                                    <div class="text-center p-4 @switch($category)
                                        @case('security') bg-red-50 @break
                                        @case('financial') bg-green-50 @break
                                        @case('operational') bg-blue-50 @break
                                        @case('administrative') bg-yellow-50 @break
                                        @default bg-gray-50
                                    @endswitch rounded-lg">
                                        <div class="text-2xl mb-2">
                                            @switch($category)
                                                @case('security') 🔒 @break
                                                @case('financial') 💰 @break
                                                @case('operational') ⚙️ @break
                                                @case('administrative') 📋 @break
                                                @default 📄
                                            @endswitch
                                        </div>
                                        <div class="text-xs font-medium text-gray-700 uppercase">
                                            @switch($category)
                                                @case('security') Segurança @break
                                                @case('financial') Financeiro @break
                                                @case('operational') Operacional @break
                                                @case('administrative') Administrativo @break
                                                @default {{ $category }}
                                            @endswitch
                                        </div>
                                        <div class="text-lg font-bold text-gray-900">{{ $count }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
        </div>
    </div>

    <script>
        // Auto-atualizar alertas críticos a cada 5 minutos
        setInterval(function() {
            fetch('{{ route('admin.log-atividades.critical-alerts') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.count > 0) {
                        // Aqui você pode atualizar a interface ou mostrar uma notificação
                        console.log(`${data.count} alertas críticos encontrados`);
                    }
                })
                .catch(error => console.error('Erro ao buscar alertas:', error));
        }, 300000); // 5 minutos
    </script>
</x-app-layout>