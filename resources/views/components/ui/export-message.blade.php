{{-- Exibe mensagem de erro se tentar exportar sem filtros --}}
@if (session('error') && session('export_error'))
<div id="export-error" class="relative px-4 py-3 my-4 bg-red-100 border-l-4 border-red-500 text-red-700">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="font-bold">Erro na exportação</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
        <div>
            <button type="button" onclick="document.getElementById('export-error').remove()" 
                    class="text-red-700 hover:text-red-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Exibe confirmação para grande volume de dados --}}
@if (session('warning') && session('export_confirmation'))
<div id="export-warning" class="relative px-4 py-3 my-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="font-bold">Atenção</p>
                <p class="text-sm">{{ session('warning') }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ session('export_url') }}" 
               class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded text-sm">
                Continuar Exportação
            </a>
            <button type="button" onclick="document.getElementById('export-warning').remove()" 
                    class="text-yellow-700 hover:text-yellow-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endif