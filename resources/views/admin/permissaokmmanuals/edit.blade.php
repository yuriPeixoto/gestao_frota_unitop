<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Permissão km Manual Veículo Código') }}: {{ $permissaokmmanual->id_permissao_km_manual }}
            </h2>
            <x-help-icon title="Ajuda - Editar permissão km manual"
                content="Nesta tela você pode editar a permissão selecionada. Preencha os campos e clique em salvar." />
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.permissaokmmanuals.update', $permissaokmmanual) }}" method="POST"
                        x-data="{ isSubmitting: false }"
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                        @csrf
                        @method('PUT')
                        @include('admin.permissaokmmanuals._form')

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>