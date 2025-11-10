<style>
    .dropdown-active {
        overflow: visible !important;
        z-index: 30;
    }

    [x-data*="simpleSelect"] [role="listbox"] {
        z-index: 50;
    }

    .smart-select-container {
        position: relative;
        z-index: 40;
    }

    /* Estilos para o loading */
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Relatório Geral de Checklist') }}
            </h2>

        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <x-bladewind::notification />
                @include('admin.relatoriogeralchecklist._search-form')
            </div>
        </div>
    </div>

    @push('scripts')
    @include('admin.relatoriogeralchecklist._scripts')

    @endpush
</x-app-layout>