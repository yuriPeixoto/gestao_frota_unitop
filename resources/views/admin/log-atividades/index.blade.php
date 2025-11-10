<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Log de Atividades</h2>
            <div class="flex items-center space-x-4">
                @if(auth()->user()->can('ver_dashboard_atividades'))
                    <a href="{{ route('admin.log-atividades.dashboard') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Dashboard
                    </a>
                @endif
                @if(auth()->user()->can('ver_auditoria_completa'))
                    <div class="relative" x-data="{ showForm: false }">
                        <button
                            @click="showForm = !showForm"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Exportar Dados Completos
                        </button>

                        <div
                            x-show="showForm"
                            x-transition
                            @click.away="showForm = false"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-10"
                        >
                            <form action="{{ route('admin.log-atividades.export') }}" method="GET" class="p-4">
                                <h3 class="text-sm font-medium mb-4">Filtros para Exportação</h3>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Data Inicial</label>
                                        <input type="date" name="start_date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Data Final</label>
                                        <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Usuário</label>
                                        <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                            <option value="">Todos os usuários</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Modelo</label>
                                        <select name="model" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                            <option value="">Todos os modelos</option>
                                            <option value="User">Usuário</option>
                                            <option value="Veiculo">Veículo</option>
                                            <option value="OrdemServico">Ordem de Serviço</option>
                                            <option value="Fornecedor">Fornecedor</option>
                                            <option value="Produto">Produto</option>
                                            <option value="Pneu">Pneu</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-4 pt-3 border-t">
                                    <button
                                        type="button"
                                        @click="showForm = false"
                                        class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
                                    >
                                        Exportar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                <x-help-icon
                    title="Ajuda - Log de Atividades"
                    content="Esta tela apresenta um registro cronológico das ações realizadas no sistema, exibindo apenas as informações relevantes para o usuário final. Cada registro mostra o usuário responsável, a ação executada e as alterações realizadas em português. O ponto colorido indica o tipo de ação: verde para criação, azul para atualização e vermelho para exclusão. Para auditoria completa, usuários autorizados podem ver dados técnicos ou exportar relatórios detalhados."
                />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="space-y-4">
                @foreach($activities as $activity)
                    <div class="bg-white border rounded-lg shadow-sm p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div @class([
                                    'w-2 h-2 rounded-full',
                                    'bg-green-500' => $activity->action === 'created',
                                    'bg-blue-500' => $activity->action === 'updated',
                                    'bg-red-500' => $activity->action === 'deleted',
                                ])></div>
                                <div>
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium text-gray-900">
                                            {{ $activity->user->name ?? 'Sistema' }}
                                        </span>
                                        {{ formatActivityAction($activity->action) }}
                                        {{ formatActivityModel($activity->model) }}
                                        #{{ $activity->model_id }}
                                    </div>
                                    <div class="text-xs text-gray-500 space-x-2">
                                        <span>{{ $activity->created_at->format('d/m/Y H:i:s') }}</span>
                                        @if($activity->ip_address)
                                            <span>•</span>
                                            <span>IP: {{ $activity->ip_address }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="$refs.changes{{ $activity->id }}.classList.toggle('hidden')"
                                class="text-sm text-blue-600 hover:text-blue-800">
                                Ver alterações
                            </button>
                        </div>

                        <div x-ref="changes{{ $activity->id }}" class="hidden mt-4">
                            @include('admin.log-atividades._changes', [
                                'activity' => $activity,
                                'users' => $users
                            ])
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
