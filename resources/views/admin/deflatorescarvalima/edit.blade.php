<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Deflator') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">



                </div>
            </div>
        </div>
    </x-slot>

    @include('admin.deflatorescarvalima._form', [
    'action' => route('admin.deflatorescarvalima.update', $evento->id_deflatores),
    'method' => 'PUT',
    'evento' => $evento,
    ])
</x-app-layout>