<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="produtoImobilizadoForm()">
                <form id="produtoImobilizado" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif


                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Produto Imobilizado</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                {{-- Cod. Imobilizado --}}
                                <label for="id_produtos_imobilizados"
                                    class="block text-sm font-medium text-gray-700">Código
                                    Imobilizado</label>
                                <input type="text" id="id_produtos_imobilizados" name="id_produtos_imobilizados"
                                    readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $produtoImobilizado->id_produtos_imobilizados ?? '' }}">
                            </div>

                            <div>
                                {{-- Cod. Patrimonio --}}
                                <label for="cod_patrimonio" class="block text-sm font-medium text-gray-700">Código
                                    Patrimonio</label>
                                <input type="number" id="cod_patrimonio" name="cod_patrimonio" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $produtoImobilizado->cod_patrimonio ?? '' }}">
                            </div>

                            <div>
                                {{-- Tipo Imobilizado --}}
                                <x-forms.smart-select name="id_tipo_imobilizados" label="Tipo Imobilizado"
                                    placeholder="Selecione o Tipo de Imobilizado..." :options="$tipoImobilizados"
                                    required="true"
                                    :selected="old('id_tipo_imobilizados', $produtoImobilizado->id_tipo_imobilizados ?? '')"
                                    asyncSearch="true" required="true" />
                            </div>

                            <div>
                                {{-- Produto --}}
                                <x-forms.smart-select name="id_produto" label="Produto"
                                    placeholder="Selecione o produto..." :options="$produto" required="true"
                                    :searchUrl="route('admin.api.produto.search')" asyncSearch="true"
                                    :selected="old('id_produto', $produtoImobilizado->produto ?? '')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                {{-- Número Nota Fiscal --}}
                                <label for="numero_nf" class="block text-sm font-medium text-gray-700">Nª
                                    Nota Fiscal</label>
                                <input type="number" id="numero_nf" name="numero_nf" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $produtoImobilizado->numero_nf ?? '' }}">
                            </div>

                            <div>
                                {{-- Responsável --}}
                                <x-forms.smart-select name="id_responsavel_imobilizado" label="Responsável"
                                    placeholder="Selecione o responsável..." :options="$pessoal"
                                    :searchUrl="route('admin.api.pessoal.search')"
                                    :selected="old('id_responsavel_imobilizado', $produtoImobilizado->id_responsavel_imobilizado ?? '')"
                                    asyncSearch="false" />
                            </div>

                            <div>
                                {{-- Lider --}}
                                <x-forms.smart-select name="id_lider_setor" label="Lider"
                                    placeholder="Selecione a lider..." :options="$pessoal"
                                    :searchUrl="route('admin.api.pessoal.search')"
                                    :selected="old('id_lider_setor', $produtoImobilizado->id_lider_setor ?? '')"
                                    asyncSearch="false" />
                            </div>

                            <div>
                                {{-- Valor --}}
                                <label for="valor" class="block text-sm font-medium text-gray-700">Valor</label>
                                <input type="text" id="valor" name="valor" required
                                    oninput="formatarMoedaBrasileira(this)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $produtoImobilizado->valor ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                {{-- Departamento--}}
                                <x-forms.smart-select name="id_departamento" label="Departamento"
                                    placeholder="Selecione o departamento..." :options="$departamento"
                                    :searchUrl="route('admin.api.departamento.search')"
                                    :selected="old('id_departamento', $produtoImobilizado->id_departamento ?? '')"
                                    asyncSearch="true" required="true" />
                            </div>

                            <div>
                                {{-- Placa --}}
                                <x-forms.smart-select name="id_veiculo" label="Placa"
                                    placeholder="Selecione o veículo..." :options="$veiculosFrequentes"
                                    :searchUrl="route('admin.api.veiculos.search')"
                                    :selected="old('id_veiculo', $produtoImobilizado->id_veiculo ?? '')"
                                    asyncSearch="true" required="true"
                                    onSelectCallback="atualizarDadosVeiculoCallback" />
                            </div>

                            <div>
                                {{-- Filial Veiculo --}}
                                <label for="filial" class="block text-sm font-medium text-gray-700">Filial
                                    veiculo</label>
                                <input type="text" id="filial" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-bind:value="filial">
                                <input type="hidden" id="hidden_filial" name="id_filial">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                {{-- Filial --}}
                                <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>

                                <!-- Input visível com o nome do usuário (somente leitura) -->
                                <input type="text" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $produtoImobilizado->filial->name ?? auth()->user()->filial->name }}">

                                <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                <input type="hidden" name="id_filial"
                                    value="{{ $produtoImobilizado->user->id_filial ?? auth()->user()->filial_id }}">
                            </div>

                            <div>
                                {{-- Usuario --}}
                                <label for="id_usuario" class="block text-sm font-medium text-gray-700">Usuário</label>

                                <!-- Input visível com o nome do usuário (somente leitura) -->
                                <input type="text" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $produtoImobilizado->user->name ?? auth()->user()->name }}">

                                <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                <input type="hidden" name="id_usuario"
                                    value="{{ $produtoImobilizado->user->id ?? auth()->user()->id }}">
                            </div>
                        </div>
                    </div>


                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('admin.produtosimobilizados.index') }}"
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
<script>
    function produtoImobilizadoForm() {
        return {
            filial: "{{ isset($licenciamentoveiculos) && $licenciamentoveiculos->veiculo && $licenciamentoveiculos->veiculo->filial ? $licenciamentoveiculos->veiculo->filial->name : 'Selecionar uma placa...' }}",

            init() {
                // Listener para atualizar a filial quando um veículo for selecionado
                window.addEventListener('id_veiculo:selected', (event) => {
                    if (event.detail && event.detail.value) {
                        this.atualizarDadosVeiculo(event.detail.value);
                    }
                });
            },

            atualizarDadosVeiculo(id) {
                this.filial = 'Carregando...'; // Mensagem temporária

                fetch('/admin/licenciamentoveiculos/get-vehicle-data', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            placa: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            this.filial = String(data.filial || 'Selecionar uma placa...');
                        } else {
                            console.error('Erro ao buscar dados do veículo:', data.error);
                            this.filial = 'Erro ao carregar';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar dados do veículo:', error);
                        this.filial = 'Erro ao carregar';
                    });
            }
        };
    }
</script>
@include('admin.produtosimobilizados._scripts')
@endpush