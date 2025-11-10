@if (session('error'))
<div class="mb-4 bg-red-50 p-4 rounded">
    {{ session('error') }}
</div>
@elseif(session('success'))
<div class="mb-4 bg-green-50 p-4 rounded">
    {{ session('success') }}
</div>
@elseif(session('warning'))
<div class="mb-4 bg-yellow-50 p-4 rounded">
    {{ session('warning') }}
</div>
@elseif(session('info'))
<div class="mb-4 bg-blue-50 p-4 rounded">
    {{ session('info') }}
</div>
@elseif(session('status'))
<div class="mb-4 bg-blue-50 p-4 rounded">
    {{ session('status') }}
</div>
@elseif(session('message'))
<div class="mb-4 bg-blue-50 p-4 rounded">
    {{ session('message') }}
</div>
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                <label for="id_precoservicoxfornecedor"
                                    class="block text-sm font-medium text-gray-700">Cód. Preço Serviço X
                                    Fornecedor</label>
                                <input type="text" id="id_precoservicoxfornecedor" name="id_precoservicoxfornecedor"
                                    readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->id_precoservicoxfornecedor ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">


                            <div>
                                <x-forms.smart-select name="id_servico" label="Serviço"
                                    placeholder="Selecione o Serviço..." :options="$servicosFrequentes"
                                    :searchUrl="route('admin.api.servicos.search')"
                                    :selected="old('id_servico', $manutencaoConfig->id_servico ?? '')"
                                    asyncSearch="true" />
                            </div>

                            <div>
                                <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione..."
                                    :options="$fornecedoresFrequentes"
                                    :searchUrl="route('admin.api.fornecedores.search')"
                                    :selected="old('id_fornecedor', $manutencaoConfig->id_fornecedor ?? '')"
                                    asyncSearch="true" />
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="valor_servico_fornecedor"
                                    class="block text-sm font-medium text-gray-700">Valor Serviço Fornecedor</label>
                                <input type="text" id="valor_servico_fornecedor" name="valor_servico_fornecedor"
                                    class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->valor_servico_fornecedor ?? '' }}">
                                @error('valor_servico_fornecedor')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('admin.servicofornecedor.create') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar Formulário
                        </a>

                        <a href="{{ route('admin.servicofornecedor.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
@include('admin.servicofornecedor._scripts')
@endpush
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("valor_servico_fornecedor");

    input.addEventListener("input", function (e) {
        // Remove tudo que não seja número
        let valor = e.target.value.replace(/\D/g, "");

        if (valor === "") {
            e.target.value = "";
            return;
        }

        // Converte para número com duas casas decimais
        let valorNumerico = (parseInt(valor, 10) / 100).toFixed(2);

        // Formata para padrão brasileiro
        let partes = valorNumerico.split(".");
        partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        let valorFormatado = "R$ " + partes.join(",");

        e.target.value = valorFormatado;
    });
});
</script>