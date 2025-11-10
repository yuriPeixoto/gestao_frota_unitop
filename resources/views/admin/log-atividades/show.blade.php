<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.log-atividades.index') }}"
                   class="text-gray-600 hover:text-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h2 class="text-2xl font-bold">Detalhes da Atividade #{{ $log->id }}</h2>
            </div>
            <x-help-icon
                title="Ajuda - Detalhes da Atividade"
                content="Visualização detalhada de uma atividade específica do sistema, incluindo todas as alterações realizadas, dados técnicos para auditoria e atividades relacionadas ao mesmo registro."
            />
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">

                    <!-- Informações principais -->
                    <div class="bg-white border rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Usuário</label>
                                    <div class="mt-1 flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-600">
                                                {{ substr($log->user->name ?? 'S', 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-sm font-medium">{{ $log->user->name ?? 'Sistema' }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ação</label>
                                    <div class="mt-1 flex items-center space-x-2">
                                        <div @class([
                                            'w-3 h-3 rounded-full',
                                            'bg-green-500' => $log->action === 'created',
                                            'bg-blue-500' => $log->action === 'updated',
                                            'bg-red-500' => $log->action === 'deleted',
                                        ])></div>
                                        <span class="text-sm">{{ formatActivityAction($log->action) }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Modelo</label>
                                    <p class="mt-1 text-sm">{{ formatActivityModel($log->model) }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ID do Registro</label>
                                    <p class="mt-1 text-sm font-mono">#{{ $log->model_id }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data/Hora</label>
                                    <p class="mt-1 text-sm">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                                    <p class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                                </div>

                                @if($log->criticality)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Criticidade</label>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <span class="text-lg">{{ $log->getCriticalityIcon() }}</span>
                                            <span class="text-sm capitalize @switch($log->criticality)
                                                @case('critical') text-red-600 @break
                                                @case('high') text-orange-600 @break
                                                @case('medium') text-yellow-600 @break
                                                @case('low') text-green-600 @break
                                                @default text-gray-600
                                            @endswitch">
                                                @switch($log->criticality)
                                                    @case('critical') Crítica @break
                                                    @case('high') Alta @break
                                                    @case('medium') Média @break
                                                    @case('low') Baixa @break
                                                    @default {{ $log->criticality }}
                                                @endswitch
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                @if($log->category)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Categoria</label>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <span class="text-lg">{{ $log->getCategoryIcon() }}</span>
                                            <span class="text-sm">
                                                @switch($log->category)
                                                    @case('security') Segurança @break
                                                    @case('financial') Financeiro @break
                                                    @case('operational') Operacional @break
                                                    @case('administrative') Administrativo @break
                                                    @default {{ $log->category }}
                                                @endswitch
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                @if($log->summary)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Resumo</label>
                                        <p class="mt-1 text-sm">{{ $log->summary }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($log->tags && count($log->tags) > 0)
                            <div class="mt-6 pt-6 border-t">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($log->tags as $tag)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Alterações -->
                    <div class="bg-white border rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Alterações Realizadas</h3>
                        @include('admin.log-atividades._changes', [
                            'activity' => $log,
                            'users' => collect()
                        ])
                    </div>

                    <!-- Informações técnicas (apenas para auditoria) -->
                    @if(auth()->user()->can('ver_auditoria_completa'))
                        <div class="bg-gray-50 border rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold mb-4">Informações Técnicas</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @if($log->ip_address)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Endereço IP</label>
                                        <p class="mt-1 text-sm font-mono">{{ $log->ip_address }}</p>
                                    </div>
                                @endif

                                @if($log->user_agent)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">User Agent</label>
                                        <p class="mt-1 text-xs text-gray-600 break-all">{{ $log->user_agent }}</p>
                                    </div>
                                @endif

                                @if($log->affected_users && count($log->affected_users) > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Usuários Afetados</label>
                                        <p class="mt-1 text-sm">{{ implode(', ', $log->affected_users) }}</p>
                                    </div>
                                @endif

                                @if($log->retention_days)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Retenção</label>
                                        <p class="mt-1 text-sm">{{ $log->retention_days }} dias</p>
                                    </div>
                                @endif
                            </div>

                            <!-- JSON completo dos valores -->
                            <div class="mt-6 space-y-4">
                                @if($log->old_values)
                                    <div>
                                        <button
                                            onclick="toggleJsonView('old-values')"
                                            class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            Ver valores anteriores (JSON completo)
                                        </button>
                                        <div id="old-values" class="hidden mt-2 bg-white p-4 rounded border">
                                            <pre class="text-xs overflow-auto max-h-64">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                @endif

                                @if($log->new_values)
                                    <div>
                                        <button
                                            onclick="toggleJsonView('new-values')"
                                            class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            Ver novos valores (JSON completo)
                                        </button>
                                        <div id="new-values" class="hidden mt-2 bg-white p-4 rounded border">
                                            <pre class="text-xs overflow-auto max-h-64">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Logs relacionados -->
                    @if($relatedLogs->count() > 0)
                        <div class="bg-white border rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">
                                Atividades Relacionadas ({{ $relatedLogs->count() }})
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Outras atividades realizadas no mesmo registro ({{ formatActivityModel($log->model) }} #{{ $log->model_id }})
                            </p>
                            <div class="space-y-3">
                                @foreach($relatedLogs as $relatedLog)
                                    <div class="border-l-4 @switch($relatedLog->action)
                                        @case('created') border-green-500 @break
                                        @case('updated') border-blue-500 @break
                                        @case('deleted') border-red-500 @break
                                        @default border-gray-500
                                    @endswitch pl-4 py-2">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium">{{ $relatedLog->user->name ?? 'Sistema' }}</span>
                                                    {{ formatActivityAction($relatedLog->action) }}
                                                    @if($relatedLog->summary)
                                                        - {{ $relatedLog->summary }}
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $relatedLog->created_at->format('d/m/Y H:i:s') }}
                                                    ({{ $relatedLog->created_at->diffForHumans() }})
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.log-atividades.show', $relatedLog) }}"
                                               class="text-sm text-blue-600 hover:text-blue-800">
                                                Ver detalhes
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
        </div>
    </div>

    <script>
        function toggleJsonView(elementId) {
            const element = document.getElementById(elementId);
            const button = element.previousElementSibling;

            element.classList.toggle('hidden');

            if (element.classList.contains('hidden')) {
                button.textContent = button.textContent.replace('Ocultar', 'Ver');
            } else {
                button.textContent = button.textContent.replace('Ver', 'Ocultar');
            }
        }
    </script>
</x-app-layout>