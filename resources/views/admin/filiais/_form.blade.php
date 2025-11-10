<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form action="{{ isset($filial) ? route('admin.filiais.update', ['branch' => $filial->id]) : route('admin.filiais.store') }}"
                      method="POST"
                      x-data="filiaisForm()">
                    @csrf
                    @if(isset($filial))
                        @method('PUT')
                    @endif

                    <div class="space-y-6">
                        <!-- Informações da Filial -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações da Filial</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           value="{{ old('name', $filial->name ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700">Código</label>
                                    <input type="text"
                                           name="code"
                                           id="code"
                                           value="{{ old('code', $filial->code ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox"
                                           name="is_headquarter"
                                           x-model="filiaisForm().isHeadquarter"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">Esta é a matriz</span>
                                </label>
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Endereço</h3>
                            <p class="text-sm text-gray-500 mb-4">Digite o CEP para preenchimento automático do endereço</p>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="relative">
                                    <label for="zip_code" class="block text-sm font-medium text-gray-700">CEP</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input type="text"
                                               name="zip_code"
                                               id="zip_code"
                                               x-on:input="filiaisForm().formatCep($event.target)"
                                               maxlength="9"
                                               value="{{ old('zip_code', $filial->address->zip_code ?? '') }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <!-- Loading spinner -->
                                        <div x-show="filiaisForm().loading"
                                             class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('zip_code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700">Estado</label>
                                    <select name="state"
                                            id="state"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Selecione...</option>
                                        @foreach(['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'] as $uf)
                                            <option value="{{ $uf }}" {{ old('state', $filial->address->state ?? '') == $uf ? 'selected' : '' }}>
                                                {{ $uf }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700">Cidade</label>
                                    <input type="text"
                                           name="city"
                                           id="city"
                                           value="{{ old('city', $filial->address->city ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('city')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="district" class="block text-sm font-medium text-gray-700">Bairro</label>
                                    <input type="text"
                                           name="district"
                                           id="district"
                                           value="{{ old('district', $filial->address->district ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('district')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="street" class="block text-sm font-medium text-gray-700">Rua</label>
                                    <input type="text"
                                           name="street"
                                           id="street"
                                           value="{{ old('street', $filial->address->street ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('street')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="number" class="block text-sm font-medium text-gray-700">Número</label>
                                    <input type="text"
                                           name="number"
                                           id="number"
                                           value="{{ old('number', $filial->address->number ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="complement" class="block text-sm font-medium text-gray-700">Complemento</label>
                                    <input type="text"
                                           name="complement"
                                           id="complement"
                                           value="{{ old('complement', $filial->address->complement ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.filiais.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ isset($filial) ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>


    document.querySelector('input[name="zip_code"]').addEventListener('input', function (e) {
        let value = e.target.value;

        value = value.replace(/\D/g, '');

        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }

        e.target.value = value;
    })


    function filiaisForm() {
        return {
            isHeadquarter: {{ isset($filial) && $filial->is_headquarter ? 'true' : 'false' }},
            loading: false,
            formatCep: (el) => {
                let value = el.value.replace(/\D/g, '');
                if (value.length === 8) {
                    filiaisForm().searchCep(value);
                }
            },
            searchCep(cep) {
                console.log(cep);
                this.loading = true;
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('street').value = data.logradouro;
                            document.getElementById('district').value = data.bairro;
                            document.getElementById('city').value = data.localidade;
                            document.getElementById('state').value = data.uf;
                            document.getElementById('number').focus();
                        } else {
                            alert('CEP não encontrado');
                        }
                    })
                    .catch(() => alert('Erro ao buscar CEP'))
                    .finally(() => this.loading = false);
            }
        }
    }
</script>
