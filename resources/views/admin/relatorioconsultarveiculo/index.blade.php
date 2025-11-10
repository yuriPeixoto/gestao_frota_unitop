<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Consulta de Veiculos') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">


            <!-- Search Form -->
            @include('admin.relatorioconsultarveiculo._search-form')

            <!-- Results Table with Loading -->
            <!-- Loading indicator -->


            <!-- Actual results -->
            @include('admin.relatorioconsultarveiculo._table')
        </div>
    </div>

    @push('scripts')
    @include('admin.relatorioconsultarveiculo._scripts')

    @endpush
</x-app-layout>