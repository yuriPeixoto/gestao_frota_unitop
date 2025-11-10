<div class="space-y-6">
    <!-- Informações do Departamento -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Departamento</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Descrição --}}
            <div>
                <label for="descricao_departamento" class="block text-sm font-medium text-gray-700">Descrição <span
                        class="text-red-500">*</span></label>
                <input type="text" name="descricao_departamento" id="descricao_departamento"
                    value="{{ old('descricao_departamento', $departamento->descricao_departamento ?? '') }}"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    required>
                @error('descricao_departamento')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sigla" class="block text-sm font-medium text-gray-700">Sigla <span
                        class="text-red-500">*</span></label>
                <input type="text" name="sigla" id="sigla" value="{{ old('sigla', $departamento->sigla ?? '') }}"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    required maxlength="10">
                @error('sigla')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ativo" class="block text-sm font-medium text-gray-700 mb-2">Status <span
                        class="text-red-500">*</span></label>
                <div class="flex items-center space-x-4">
                    <!-- Opção Ativo -->
                    <label class="inline-flex items-center">
                        <input type="radio" name="ativo" value="1" class="form-radio h-4 w-4 text-indigo-600" {{
                            old('ativo', $departamento->ativo ?? true) ? 'checked' : '' }} required>
                        <span class="ml-2 text-sm text-gray-700">Ativo</span>
                    </label>

                    <!-- Opção Inativo -->
                    <label class="inline-flex items-center">
                        <input type="radio" name="ativo" value="0" class="form-radio h-4 w-4 text-red-600" {{
                            old('ativo', $departamento->ativo ?? true) ? '' : 'checked' }}>
                        <span class="ml-2 text-sm text-gray-700">Inativo</span>
                    </label>
                </div>
                @error('ativo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botões -->
            <div class="col-span-1 sm:col-span-3 flex justify-end space-x-3 w-full mt-4">
                <a href="{{ route('admin.departamentos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancelar
                </a>

                <!-- Botão Enviar (sem Alpine.js) -->
                <button type="submit" id="submitButton"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span id="submitText">{{ isset($departamento) ? 'Atualizar' : 'Salvar' }}</span>
                    <span id="submitSpinner" class="hidden ml-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Script simples para mostrar o spinner
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const submitButton = document.getElementById('submitButton');
        const submitSpinner = document.getElementById('submitSpinner');
        
        if (form && submitButton) {
            form.addEventListener('submit', function() {
                // Mostrar o spinner
                submitSpinner.classList.remove('hidden');
                return true;
            });
        }
    });
</script>