<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <form id="bombaForm" method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method === 'PUT')
            @method('PUT')
            @endif

            <!-- Cabeçalho -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium mb-6 text-gray-800 border-b pb-2">Dados da Bomba</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @if(isset($bomba->id_bomba))
                    <div>
                        <label for="id_bomba" class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                        <input type="text" id="id_bomba" name="id_bomba" readonly
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ $bomba->id_bomba ?? '' }}">
                    </div>
                    @endif

                    <div class="@if(!isset($bomba->id_bomba)) md:col-span-2 @endif">
                        <label for="descricao_bomba" class="block text-sm font-medium text-gray-700 mb-1">Descrição
                            <span class="text-red-500">*</span></label>
                        <input type="text" id="descricao_bomba" name="descricao_bomba" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('descricao_bomba', $bomba->descricao_bomba ?? '') }}">
                        @error('descricao_bomba')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tamanho_maximo_encerrante"
                            class="block text-sm font-medium text-gray-700 mb-1">Tamanho Máximo Encerrante <span
                                class="text-red-500">*</span></label>
                        <input type="number" id="tamanho_maximo_encerrante" name="tamanho_maximo_encerrante" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('tamanho_maximo_encerrante', $bomba->tamanho_maximo_encerrante ?? '') }}">
                        @error('tamanho_maximo_encerrante')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="bomba_ctf" class="block text-sm font-medium text-gray-700 mb-1">Bico 1</label>
                        <input type="text" id="bomba_ctf" name="bomba_ctf"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('bomba_ctf', $bomba->bomba_ctf ?? '') }}">
                        @error('bomba_ctf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bomba_ctf_2_bico" class="block text-sm font-medium text-gray-700 mb-1">Bico
                            2</label>
                        <input type="text" id="bomba_ctf_2_bico" name="bomba_ctf_2_bico"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('bomba_ctf_2_bico', $bomba->boma_ctf_2_bico ?? '') }}">
                        @error('bomba_ctf_2_bico')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-1">Filial:<span
                                class="text-red-500">*</span></label>
                        <select name="id_filial" id="id_filial" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecione...</option>
                            @foreach ($filial as $filiais)
                            <option value="{{ $filiais['value'] }}" {{ (old('id_filial', $bomba->id_filial ?? '') ==
                                $filiais['value']) ? 'selected' : '' }}>
                                {{ $filiais['label'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_filial')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_tanque" class="block text-sm font-medium text-gray-700 mb-1">Tanque:<span
                                class="text-red-500">*</span></label>
                        <select name="id_tanque" id="id_tanque" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecione...</option>
                            @foreach ($tanque as $tanques)
                            <option value="{{ $tanques['value'] }}" {{ (old('id_tanque', $bomba->id_tanque ?? '') ==
                                $tanques['value']) ? 'selected' : '' }}>
                                {{ $tanques['label'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_tanque')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-4 mt-8 pb-4">
                <a href="{{ route('admin.bombas.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Voltar
                </a>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bombaForm');
        
        if (form) {
            console.log('Formulário de bombas encontrado');
            
            // Adicionar validação de formulário e debug
            form.addEventListener('submit', function(e) {
                let hasError = false;
                
                // Validar campos obrigatórios
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value) {
                        e.preventDefault();
                        hasError = true;
                        field.classList.add('border-red-500');
                        
                        // Adicionar mensagem de erro se não existir
                        const errorMsg = field.parentNode.querySelector('.text-red-600');
                        if (!errorMsg) {
                            const newError = document.createElement('p');
                            newError.className = 'mt-1 text-sm text-red-600';
                            newError.textContent = 'Este campo é obrigatório';
                            field.parentNode.appendChild(newError);
                        }
                    } else {
                        field.classList.remove('border-red-500');
                        const errorMsg = field.parentNode.querySelector('.text-red-600');
                        if (errorMsg) {
                            errorMsg.remove();
                        }
                    }
                });
                
                if (hasError) {
                    return false;
                }
                
                // Log para debug - remover em produção
                console.log('Formulário válido, enviando...');
                console.log('Action:', form.action);
                console.log('Method:', form.method);
                
                // Registrar valores dos campos para debug
                const formData = new FormData(form);
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
            });
            
            // Remover classe de erro ao digitar/selecionar
            form.querySelectorAll('input, select').forEach(element => {
                element.addEventListener('input', function() {
                    if (this.value) {
                        this.classList.remove('border-red-500');
                        const errorMsg = this.parentNode.querySelector('.text-red-600');
                        if (errorMsg) {
                            errorMsg.remove();
                        }
                    }
                });
            });
        } else {
            console.error('Formulário de bombas não encontrado!');
        }
    });
</script>
@endpush