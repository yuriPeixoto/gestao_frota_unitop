<style>
    .demo-container {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .demo-title {
        margin-bottom: 30px;
        color: #333;
        font-size: 24px;
    }

    /* Modal Overlay - Simula o fundo do Ctrl+P */
    .pdf-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #525659;
        /* Cor exata do Chrome Ctrl+P */
        z-index: 10000;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .pdf-modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    /* Container do Modal */
    .pdf-modal-container {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
    }

    /* Header do Modal - Simula a barra superior do Chrome */
    .pdf-modal-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: #424242;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        z-index: 10001;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .pdf-modal-title {
        color: #ffffff;
        font-size: 16px;
        font-weight: 500;
    }

    .pdf-modal-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .control-btn {
        background: none;
        border: none;
        color: #ffffff;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    .control-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .close-btn {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 24px;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .close-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Viewer do PDF */
    .pdf-viewer-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        width: 90%;
        max-width: 900px;
        height: calc(100% - 100px);
        margin-top: 60px;
        position: relative;
    }

    .pdf-iframe {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 8px;
    }

    /* Loading */
    .pdf-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #666;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #ddd;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsivo */
    @media (max-width: 768px) {
        .pdf-modal-container {
            padding: 10px;
        }

        .pdf-viewer-container {
            width: 95%;
            height: calc(100% - 80px);
        }

        .pdf-modal-header {
            padding: 0 15px;
        }

        .pdf-modal-title {
            font-size: 14px;
        }
    }

    /* Animação de entrada */
    .pdf-viewer-container {
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }

    .pdf-modal-overlay.active .pdf-viewer-container {
        transform: scale(1);
    }
</style>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Restrições e Bloqueios') }}
            </h2>
            <div class="flex items-center space-x-4">
                @if (session('notification'))
                <x-notification :notification="session('notification')" />
                @endif
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Restrições e
                                    Bloqueios
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar todos as restrições,
                                    realizar buscas e gerenciar seus registros.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            @if (session('notification'))
            <x-notification :notification="session('notification')" />
            @endif

            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.restricoesbloqueios._search-form')
                <!-- Results Table with Loading -->
                @include('admin.restricoesbloqueios._table')
            </div>
        </div>
    </div>

    @push('scripts')
    @include('admin.restricoesbloqueios._scripts')
    @endpush
</x-app-layout>