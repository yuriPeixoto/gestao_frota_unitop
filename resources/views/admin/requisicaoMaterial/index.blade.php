<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Requisição de Materiais') }}
            </h2>
            <x-forms.button href="{{ route('admin.requisicaoMaterial.create') }}">
                <x-icons.plus class="h-4 w-4 mr-2" />
                Nova Requisição de Material
            </x-forms.button>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        @if (session('notification'))
            <x-notification :notification="session('notification')" />
        @endif
        <div class="p-4 bg-white border-b border-gray-200">

            <div class="overflow">
                <div class="mb-4">
                    @include('admin.requisicaoMaterial._search-form')
                </div>

                @include('admin.requisicaoMaterial._table')


            </div>
        </div>
    </div>
    @push('scripts')
        @include('admin.requisicaoMaterial._scripts')
    @endpush
</x-app-layout>
