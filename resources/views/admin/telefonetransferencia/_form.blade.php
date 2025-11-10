<div class="space-y-6">
    <div class="rounded-lg bg-gray-50 p-4">
        <h3 class="mb-4 text-lg font-medium text-gray-900">Status Cadastro Imobilizado</h3>
        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2">
            <div>
                {{-- Cod. Telefone Transferência --}}
                <label for="id_telefone_transferencia" class="block text-sm font-medium text-gray-700">Código</label>
                <input type="text" id="id_telefone_transferencia" name="id_telefone_transferencia" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $telefoneTransferencia->id_telefone_transferencia ?? '' }}">
            </div>

            <div>
                <x-forms.input label="Nome" name="nome"
                    value="{{ old('nome', $telefoneTransferencia->nome ?? '') }}" />
            </div>

            <div>
                <x-forms.input label="Telefone" name="telefone" id="telefone"
                    value="{{ old('telefone', $telefoneTransferencia->telefone ?? '') }}" />
            </div>

            <div>
                {{-- Modelo do veiculo --}}
                <x-forms.smart-select name="departamento" label="Medelo do Veiculo" class="border-gray-300 bg-gray-100"
                    placeholder="Selecione o departamento..." :options="$departamentoTransferencia" :disabled="isset($telefoneTransferencia->departamento)" :selected="old('departamento', $telefoneTransferencia->departamento ?? '')"
                    asyncSearch="true" />
            </div>

            <!-- Botões -->
            <div class="col-span-full flex justify-end space-x-3">
                <x-forms.button href="{{ route('admin.telefonetransferencia.index') }}" type="secondary"
                    variant="outlined">
                    Cancelar
                </x-forms.button>
                <!-- Botão Enviar -->
                <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                    class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                    <template x-if="!isSubmitting">
                        <span>{{ isset($telefoneTransferencia) ? 'Atualizar' : 'Salvar' }}</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span>{{ isset($telefoneTransferencia) ? 'Atualizando...' : 'Salvando...' }}</span>
                    </template>
                </button>
            </div>
        </div>

        @push('scripts')
            <script src="https://unpkg.com/imask"></script>
            <script>
                var element = document.getElementById('telefone');
                var maskOptions = {
                    mask: '(00) 0 0000-0000'
                };
                var mask = IMask(element, maskOptions);
            </script>
        @endpush
