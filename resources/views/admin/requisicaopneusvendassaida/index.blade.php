<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de Saída de Pneus Para Venda') }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        @if (session('notification'))
            <x-notification :notification="session('notification')" />
        @endif
        <div class="p-4 bg-white border-b border-gray-200">

            <div class="overflow">
                <div class="mb-4">
                    @include('admin.requisicaopneusvendassaida._search-form')
                </div>

                @include('admin.requisicaopneusvendassaida._table')


            </div>
        </div>
    </div>
    @push('scripts')
        @include('admin.requisicaopneusvendassaida._scripts')
    @endpush
</x-app-layout>
