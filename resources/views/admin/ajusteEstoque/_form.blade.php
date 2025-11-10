<div class="space-y-6">
    @if ($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="bg-white p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ajuste Estoque</h3>
        <div class="grid md:grid-cols-1 gap-4 sm:grid-cols-1">
            <form id="ajusteEstoqueForm" method="POST" action="{{ $action }}" class="space-y-4"
                enctype="multipart/form-data">
                @csrf
                @if ($method === 'PUT')
                @method('PUT')
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-forms.input label="Cod. Acerto Estoque:" name="id_acerto_estoque" disabled
                        value="{{ old('id_acerto_estoque', $ajuste->id_acerto_estoque ?? '') }}" />

                    <x-forms.input type="date" name="data_acerto" label="Data Acerto:"
                        value="{{ old('data_acerto', isset($ajuste) && $ajuste->data_acerto ? $ajuste->data_acerto->format('Y-m-d') : '') }}"
                        required />


                    <div>
                        <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
                        <select name="id_filial" readonly
                            class="bg-gray-200 h-12 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 mb-4 w-full cursor-not-allowed">
                            <option value="">Selecione uma filial</option>
                            @foreach ($filiais as $filial)
                            <option value="{{ $filial['value'] }}" {{ old('id_filial', $ajuste->id_filial ??
                                GetterFilial()) == $filial['value'] ? 'selected' : '' }}>
                                {{ $filial['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <x-forms.input label="Usuário" name="id_usuario_acerto" disabled="true"
                        value="{{ old('id_usuario_acerto', $ajuste->user->name ?? Auth::user()->name) }}" />
                </div>

                <div class="flex gap-4">
                    <div class="w-full">
                        <x-forms.smart-select name="id_tipo_acerto" required
                            :selected="old('id_tipo_acerto', $ajuste->id_tipo_acerto ?? '')" label="Tipo do Acerto"
                            :options="$tipoAcerto" />
                    </div>
                    <div class="w-full">
                        <x-forms.smart-select name="id_estoque" required
                            :selected="old('id_estoque', $ajuste->id_estoque ?? '')" label="Estoque" :options="[]" />
                    </div>
                </div>
                <div class="w-full">
                    <x-forms.smart-select name="id_produto" :options='[]' label="Produto"
                        :selected="old('id_produto', $ajuste->id_produto ?? '')"
                        :searchUrl="route('admin.api.produtos.search')" />
                </div>
                <div class="flex gap-4">
                    <x-forms.input label="Quantidade Atual:" name="quantidade_atual" readonly
                        value="{{ old('quantidade_atual', $ajuste->quantidade_atual ?? '') }}" />

                    <x-forms.input label="Quantidade Acerto:" name="quantidade_acerto" type="number"
                        value="{{ old('quantidade_acerto', $ajuste->quantidade_acerto ?? '') }}" />

                    <x-forms.input label="Preço Médio:" name="preco_medio" required data-mask="valor"
                        value="{{ old('preco_medio', $ajuste->preco_medio ?? '') }}" />
                </div>
                <div class="flex p-6 justify-end space-x-3 col-span-full">
                    <button type="submit" id="submit-form"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                    </button>
                    <x-forms.button href="{{ route('admin.ajusteEstoque.index') }}" variant="outlined">Cancelar
                    </x-forms.button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
@include('admin.ajusteEstoque._scripts')
@endpush