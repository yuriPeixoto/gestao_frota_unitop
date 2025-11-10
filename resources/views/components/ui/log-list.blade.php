@props(['logs' => [], 'title' => 'Logs de Processamento'])

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            {{ $title }}
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
            Últimas entradas do log de processamento de NFe.
        </p>
    </div>

    <div class="overflow-hidden">
        <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700 max-h-80 overflow-y-auto">
            @forelse($logs as $log)
                @php
                    $typeColor = match($log['tipo']) {
                        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'error' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                    };
                @endphp
                
                <li class="px-4 py-3 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-lg">
                                {{ $log['mensagem'] }}
                            </div>
                            <div class="ml-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                                    {{ ucfirst($log['tipo']) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $log['data'] }}
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-5 sm:px-6 text-center">
                    <span class="text-gray-500 dark:text-gray-400">Nenhum log de processamento disponível</span>
                </li>
            @endforelse
        </ul>
    </div>
    
    @if(count($logs) > 0)
        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-4 sm:px-6 border-t border-gray-200 dark:border-gray-600">
            <div class="flex justify-center">
                <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    Ver todos os logs
                </a>
            </div>
        </div>
    @endif
</div>