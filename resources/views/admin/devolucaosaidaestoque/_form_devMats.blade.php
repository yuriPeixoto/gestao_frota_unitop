<div class="space-y-6">
    @if (session('notification'))
        <x-notification :notification="session('notification')" />
    @endif

    <div class="p-6 bg-white border-b border-gray-200">
        <form id="devolucaoMateriais" method="POST" action="{{ $action }}" class="space-y-4"
            enctype="multipart/form-data">
            @csrf
            @if ($method === 'PUT')
                @method('PUT')
            @endif

            <div class="flex gap-4">
                <div class="w-32 flex-none">
                    <x-forms.input name="id_devolucao_materiais" label="Código Devolução" readonly />
                </div>
                <div class="w-32 flex-1">
                    <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                        :options="$filiais" asyncSearch="false" />
                </div>
                <div class="w-32 flex-auto">
                    <x-forms.smart-select name="id_solicitacao_pecas" label="Código Solicitação de Materiais"
                        placeholder="Selecione a requisicão..." :options="$requisicoes" asyncSearch="true" />
                </div>
                <div class="w-32 flex-auto">
                    <x-forms.smart-select name="id_produto" label="Descrição Produto" :options="[]"
                        asyncSearch="true" />
                </div>
                <div class="w-32 flex-auto">
                    <x-forms.input name="quantidade" label="Quantidade" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label class="block text-sm font-medium text-gray-700">Justificativa</label>
                <textarea name="justificativa" rows="6" class="border border-gray-300 rounded-md shadow-sm">{{ old('observacao', $devulucaoProdutos->observacao ?? '') }}</textarea>
            </div>

            <!-- Botões -->
            <div class="flex justify-right space-x-3 col-span-full">
                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($devolucaosaidaestoque) ? 'Atualizar' : 'Salvar' }}
                </button>

                <a href="{{ route('admin.devolucaosaidaestoque.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    @include('admin.devolucaosaidaestoque._scripts')
@endpush
