<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form id="descarteImobilizado" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif


                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Descarte Imobilizado</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                {{-- Cod. Descarte Imobilizado --}}
                                <label for="id_descarte_imobilizados"
                                    class="block text-sm font-medium text-gray-700">Código Descarte
                                    Imobilizado</label>
                                <input type="text" id="id_descarte_imobilizados" name="id_descarte_imobilizados"
                                    readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $descarteImobilizado->id_descarte_imobilizados ?? '' }}">
                            </div>
                            {{-- {{dd($descarteImobilizado->id_produtos_imobilizados)}} --}}
                            <div>
                                {{-- Produto Imobilizados --}}
                                <x-forms.smart-select name="id_produtos_imobilizados" label="Produtos Imobilizados"
                                    placeholder="Selecione o produto..." :options="$produtosImobilizados"
                                    required="true" :searchUrl="route('admin.api.produtosimobilizados.search')"
                                    asyncSearch="true"
                                    :selected="old('id_produtos_imobilizados', $descarteImobilizado->id_produtos_imobilizados ?? '')" />
                            </div>

                            <div>
                                {{-- Obsercação Descarte --}}
                                <label for="motivo_descarte" class="block text-sm font-medium text-gray-700">Obsercação
                                    Descarte</label>
                                <input type="text" id="motivo_descarte" name="motivo_descarte"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $descarteImobilizado->motivo_descarte ?? '' }}">

                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                {{-- Usuario --}}
                                <label for="id_usuario" class="block text-sm font-medium text-gray-700">Usuário</label>

                                <!-- Input visível com o nome do usuário (somente leitura) -->
                                <input type="text" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $descarteImobilizado->user->name ?? auth()->user()->name }}">

                                <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                <input type="hidden" name="id_usuario"
                                    value="{{ $descarteImobilizado->user->id ?? auth()->user()->id }}">
                            </div>

                            <div>
                                {{-- Filial --}}
                                <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>

                                <!-- Input visível com o nome do usuário (somente leitura) -->
                                <input type="text" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $descarteImobilizado->filial->name ?? auth()->user()->filial->name }}">

                                <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                <input type="hidden" name="id_filial"
                                    value="{{ $descarteImobilizado->user->id_filial ?? auth()->user()->filial_id }}">
                            </div>

                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('admin.descarteimobilizado.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Voltar
                            </a>

                            <button type="submit" id="submit-button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@include('admin.descarteimobilizado._scripts')
@endpush