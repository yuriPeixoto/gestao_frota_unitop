<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastrar Condutor') }}
            </h2>
        </div>
    </x-slot>

    @include('admin.condutores._form', [
    'action' => route('admin.condutores.store'),
    'method' => 'POST',
    ])
</x-app-layout>