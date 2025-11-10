<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Criar Serviço') }}
            </h2>
            
        </div>
    </x-slot>

    <form action="{{ route('admin.manutencoes.store') }}" method="POST"
        x-data="{ isSubmitting: false }"
        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
        @csrf
        @include('admin.servicos._form')
    </form>

</x-app-layout>
