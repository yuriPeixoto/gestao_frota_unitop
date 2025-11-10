<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro de encerrante') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.encerrantes.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    {{ __('Voltar') }}
                </a>
                <x-help-icon title="Ajuda - Cadastro de Encerrante"
                    content="Preencha os dados do encerrante. Selecione primeiro o tanque para carregar as bombas disponíveis. Todos os campos são obrigatórios." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('admin.encerrantes.store') }}" method="POST" id="encerrante-form">
                @csrf

                <!-- Linha 1: Cód.Encerrante (3) | Tanque (5) | Bomba (4) -->
                <div class="grid grid-cols-12 gap-4 mb-4">
                    <div class="col-span-3">
                        <label for="id_encerrante" class="block text-sm font-medium text-gray-700">
                            Cód.Encerrante:
                        </label>
                        <input type="text" id="id_encerrante" name="id_encerrante"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100"
                            value="(Automático)" readonly>
                    </div>

                    <div class="col-span-5">
                        <label for="id_tanque" class="block text-sm font-medium text-gray-700">
                            Tanque: <span class="text-red-500">*</span>
                        </label>
                        <select name="id_tanque" id="id_tanque" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione um tanque...</option>
                            @foreach ($tanques as $tanque)
                            <option value="{{ $tanque->id_tanque }}" {{ old('id_tanque')==$tanque->id_tanque ?
                                'selected' : '' }}>
                                {{ $tanque->tanque }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_tanque')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-4">
                        <label for="id_bomba" class="block text-sm font-medium text-gray-700">
                            Bomba: <span class="text-red-500">*</span>
                        </label>
                        <select name="id_bomba" id="id_bomba" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione primeiro um tanque</option>
                        </select>
                        @error('id_bomba')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Linha 2: Data Hora Abertura (6) | Valor Encerrante Abertura (6) -->
                <div class="grid grid-cols-12 gap-4 mb-4">
                    <div class="col-span-6">
                        <label for="data_hora_abertura" class="block text-sm font-medium text-gray-700">
                            Data Hora Abertura: <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="data_hora_abertura" id="data_hora_abertura" required
                            value="{{ old('data_hora_abertura') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('data_hora_abertura')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-6">
                        <label for="encerrante_abertura" class="block text-sm font-medium text-gray-700">
                            Valor Encerrante Abertura: <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="encerrante_abertura" id="encerrante_abertura" required min="0"
                            value="{{ old('encerrante_abertura') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Digite o valor de abertura">
                        @error('encerrante_abertura')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Linha 3: Data Hora Encerramento (6) | Valor Encerrante Fechamento (6) -->
                <div class="grid grid-cols-12 gap-4 mb-4">
                    <div class="col-span-6">
                        <label for="data_hora_encerramento" class="block text-sm font-medium text-gray-700">
                            Data Hora Encerramento: <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="data_hora_encerramento" id="data_hora_encerramento" required
                            value="{{ old('data_hora_encerramento') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('data_hora_encerramento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-6">
                        <label for="encerrante_fechamento" class="block text-sm font-medium text-gray-700">
                            Valor Encerrante Fechamento: <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="encerrante_fechamento" id="encerrante_fechamento" required min="0"
                            value="{{ old('encerrante_fechamento') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Digite o valor de fechamento">
                        @error('encerrante_fechamento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Linha 4: Filial (6) | Conferente (6) -->
                <div class="grid grid-cols-12 gap-4 mb-6">
                    <div class="col-span-6">
                        <label for="id_filial" class="block text-sm font-medium text-gray-700">
                            Filial:
                        </label>
                        <input type="text" id="id_filial_display"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100"
                            value="{{ Auth::user()->filial->name ?? 'Filial não definida' }}" readonly>
                        <input type="hidden" name="id_filial" value="{{ Auth::user()->filial_id }}">
                    </div>

                    <div class="col-span-6">
                        <label for="usuario" class="block text-sm font-medium text-gray-700">
                            Conferente:
                        </label>
                        <select name="usuario" id="usuario"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="{{ Auth::id() }}" selected>{{ Auth::user()->name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="limparFormulario()"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                        <i class="fas fa-eraser mr-2"></i>Limpar formulário
                    </button>
                    {{-- <a href="{{ route('admin.encerrantes.index') }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a> --}}
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tanqueSelect = document.getElementById('id_tanque');
            const bombaSelect = document.getElementById('id_bomba');
            const dataAberturaInput = document.getElementById('data_hora_abertura');
            const dataEncerramentoInput = document.getElementById('data_hora_encerramento');
            const encerramentoAberturaInput = document.getElementById('encerrante_abertura');
            const encerramentoFechamentoInput = document.getElementById('encerrante_fechamento');
            const form = document.getElementById('encerrante-form');

            // Cascata Tanque → Bomba (igual ao sistema legado)
            tanqueSelect.addEventListener('change', function() {
                const tanqueId = this.value;
                
                // Limpar select de bomba
                bombaSelect.innerHTML = '<option value="">Carregando...</option>';
                bombaSelect.disabled = true;

                if (tanqueId) {
                    fetch(`/admin/encerrantes/bombas-por-tanque/${tanqueId}`)
                        .then(response => response.json())
                        .then(data => {
                            bombaSelect.innerHTML = '<option value="">Selecione uma bomba...</option>';
                            
                            data.forEach(bomba => {
                                const option = document.createElement('option');
                                option.value = bomba.id_bomba;
                                option.textContent = bomba.descricao_bomba;
                                bombaSelect.appendChild(option);
                            });
                            
                            bombaSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Erro ao carregar bombas:', error);
                            bombaSelect.innerHTML = '<option value="">Erro ao carregar bombas</option>';
                            bombaSelect.disabled = true;
                        });
                } else {
                    bombaSelect.innerHTML = '<option value="">Selecione primeiro um tanque</option>';
                    bombaSelect.disabled = true;
                }
            });

            // Validações do formulário (iguais ao legado)
            form.addEventListener('submit', function(e) {
                let hasError = false;
                
                // Validar se data encerramento > data abertura
                if (dataAberturaInput.value && dataEncerramentoInput.value) {
                    const dataAbertura = new Date(dataAberturaInput.value);
                    const dataEncerramento = new Date(dataEncerramentoInput.value);
                    
                    if (dataEncerramento <= dataAbertura) {
                        alert('A data/hora de encerramento deve ser posterior à data/hora de abertura.');
                        hasError = true;
                    }
                }
                
                // Validar se encerrante fechamento > encerrante abertura
                if (encerramentoAberturaInput.value && encerramentoFechamentoInput.value) {
                    const valorAbertura = parseInt(encerramentoAberturaInput.value);
                    const valorFechamento = parseInt(encerramentoFechamentoInput.value);
                    
                    if (valorFechamento <= valorAbertura) {
                        alert('O valor de fechamento deve ser maior que o valor de abertura.');
                        hasError = true;
                    }
                }
                
                if (hasError) {
                    e.preventDefault();
                }
            });
        });

        function limparFormulario() {
            if (confirm('Tem certeza que deseja limpar todos os campos do formulário?')) {
                document.getElementById('encerrante-form').reset();
                document.getElementById('id_bomba').innerHTML = '<option value="">Selecione primeiro um tanque</option>';
                document.getElementById('id_bomba').disabled = true;
            }
        }
    </script>
    @endpush
</x-app-layout>