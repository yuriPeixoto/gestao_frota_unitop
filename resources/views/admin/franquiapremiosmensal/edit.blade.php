<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Franquia Mensal') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">



                </div>
            </div>
        </div>
    </x-slot>

    @include('admin.franquiapremiosmensal._form', [
    'action' => route('admin.franquiapremiosmensal.update', $franquia->id_franquia_premio_mensal),
    'method' => 'PUT',
    'franquia' => $franquia,
    ])
</x-app-layout>