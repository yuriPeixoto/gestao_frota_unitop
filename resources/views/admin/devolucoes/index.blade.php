<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Devoluções') }}
            </h2>

        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            @include('admin.devolucoes._tabs')
        </div>
    </div>

    @push('scripts')
        @include('admin.devolucoes._scripts')
    @endpush
</x-app-layout>
