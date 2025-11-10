@props(['failedNfes' => [], 'title' => 'NFes com Falha'])

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            {{ $title }}
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
            Notas fiscais que apresentaram erro durante o processamento.
        </p>
    </div>

    <div class="overflow-hidden">
        <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700 max-h-80 overflow-y-auto">
            @forelse($failedNfes as $nfe)
                <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ basename($nfe['file']) }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $nfe['error'] }}
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $nfe['failed_at'] ? \Carbon\Carbon::parse($nfe['failed_at'])->format('d/m/Y H:i') : 'Data desconhecida' }}
                            </div>
                            @if(isset($nfe['attempts']) && $nfe['attempts'] > 0)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Tentativas: {{ $nfe['attempts'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-5 sm:px-6 text-center">
                    <span class="text-gray-500 dark:text-gray-400">Nenhuma NFe com falha de processamento</span>
                </li>
            @endforelse
        </ul>
    </div>
</div>