<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Card de Boas-vindas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 text-gray-900">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">
                            Bem-vindo, {{ $user->name }}! 👋
                        </h1>

                        <div class="text-lg text-gray-600 mb-4">
                            <p>{{ $current_date }}</p>
                            <p class="text-sm text-gray-500" id="current-time">
                                {{ $current_time }}
                            </p>
                        </div>

                        <div class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Sistema de Gestão de Frota Carvalima
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulos do Sistema -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-semibold text-gray-700 mb-2">
                            Módulos do Sistema
                        </h3>
                        <p class="text-gray-500">
                            Acesse os módulos disponíveis para gerenciar sua frota
                        </p>
                        <div class="mt-4 text-sm text-gray-400">
                            Total de {{ $total_modules }} módulos disponíveis
                        </div>
                    </div>

                    <!-- Módulos em Produção -->
                    @if(count($modules_by_status['production']) > 0)
                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <h4 class="text-lg font-semibold text-gray-700">Em Produção</h4>
                            <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                {{ count($modules_by_status['production']) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($modules_by_status['production'] as $module)
                            <x-dashboard.module-card :module="$module" :status-config="$status_config" />
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Módulos em Homologação -->
                    @if(count($modules_by_status['staging']) > 0)
                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <div class="w-3 h-3 bg-gray-500 rounded-full mr-3"></div>
                            <h4 class="text-lg font-semibold text-gray-700">Em Homologação</h4>
                            <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full">
                                {{ count($modules_by_status['staging']) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($modules_by_status['staging'] as $module)
                            <x-dashboard.module-card :module="$module" :status-config="$status_config" />
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Módulos em Desenvolvimento -->
                    @if(count($modules_by_status['development']) > 0)
                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                            <h4 class="text-lg font-semibold text-gray-700">Em Desenvolvimento</h4>
                            <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
                                {{ count($modules_by_status['development']) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($modules_by_status['development'] as $module)
                            <x-dashboard.module-card :module="$module" :status-config="$status_config" />
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Mensagem caso não tenha módulos (não deve acontecer) -->
                    @if($total_modules == 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum módulo disponível</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Entre em contato com o administrador do sistema.
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Script para atualizar horário em tempo real -->
    <script>
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Atualiza o horário a cada segundo
        setInterval(updateTime, 1000);

        // Atualiza imediatamente quando a página carrega
        document.addEventListener('DOMContentLoaded', updateTime);

        // Opcional: Sincronizar com o servidor a cada 5 minutos
        setInterval(function() {
            fetch('/dashboard/current-time')
                .then(response => response.json())
                .then(data => {
                    const timeElement = document.getElementById('current-time');
                    if (timeElement && data.time) {
                        timeElement.textContent = data.time;
                    }
                })
                .catch(error => {
                    console.log('Erro ao sincronizar horário:', error);
                });
        }, 300000); // 5 minutos
    </script>
</x-app-layout>