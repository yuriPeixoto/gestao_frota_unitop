<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de Saída de Pneus Para Venda') }}
            </h2>
        </div>
    </x-slot>

    <!-- Envolver tudo em um contexto Alpine.js -->
    <div x-data="modalComponent()" @requisicao-atualizada.window="handleRequisicaoAtualizada($event.detail)"
        @bloquear-fechamento-modal.window="bloqueiaFechamento = true;">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 bg-white border-b border-gray-200">
                <x-bladewind::notification />

                <div class="overflow-x-auto">
                    @include('admin.requisicaopneusvendas._search-form')
                    <div class="mt-4">
                        @include('admin.requisicaopneusvendas._table')
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal aqui, no mesmo contexto -->
        @include('admin.requisicaopneusvendas.modal-visualizar')
    </div>

    @push('scripts')
        @include('admin.requisicaopneusvendas._scripts')
    @endpush
</x-app-layout>
