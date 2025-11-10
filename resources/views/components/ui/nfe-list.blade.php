@props(['nfes' => [], 'title' => 'NFes Recentes'])

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            {{ $title }}
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
            Lista das últimas notas fiscais processadas pelo sistema.
        </p>
    </div>

    <div class="overflow-hidden">
        <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($nfes as $nfe)
                <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $nfe->infnfe ?? 'Sem Chave' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Emissor: {{ $nfe->emissor->xnome ?? 'Não informado' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="text-sm text-gray-900 dark:text-white">
                                NF-e #{{ $nfe->nnf ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $nfe->data_inclusao ? $nfe->data_inclusao->format('d/m/Y H:i') : 'Data desconhecida' }}
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-5 sm:px-6 text-center">
                    <span class="text-gray-500 dark:text-gray-400">Nenhuma NFe processada recentemente</span>
                </li>
            @endforelse
        </ul>
    </div>
</div>