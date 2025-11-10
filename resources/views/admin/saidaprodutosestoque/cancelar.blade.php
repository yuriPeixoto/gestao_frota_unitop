<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cancelamento Requisição Peças
            </h2>
        </div>
    </x-slot>


    <form action="{{ route('admin.saidaprodutosestoque.cancelarrequisicao', $vRequisicaoProduto->id_solicitacao_pecas) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        

        <div class="space-y-6">
            @if($errors->any())
                <div class="mb-4 bg-red-50 p-4 rounded">
                    <ul class="list-disc list-inside text-red-600">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-bladewind::tab-group name="tab-icon">
            
                <x-bladewind::tab-body>

                    <x-bladewind::tab-content  active="true">

                        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-5 items-center ">

                            <x-bladewind::input name="id_solicitacao_pecas" type="text" label="Cód." readonly="true"
                                selected_value="{{ old('id_solicitacao_pecas', $vRequisicaoProduto->id_solicitacao_pecas ?? '')}}" />

                        </div>

                        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-1 items-center ">

                            <x-bladewind::textarea label="Observação de Cancelamento" name="observacao_cancelado" 
                                selected_value="{{ old('observacao_cancelado', $vRequisicaoProduto->observacao_cancelado ?? '') }}" />

                        </div>


                    </x-bladewind::tab-content>
                
                </x-bladewind::tab-body>
            </x-bladewind::tab-group>
            <!-- Botões -->
            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.saidaprodutosestoque.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($vRequisicaoProduto) ? 'Finalizar' : 'Salvar' }}
                </button>
                
            </div>
        </div>
    </form>
</x-app-layout>