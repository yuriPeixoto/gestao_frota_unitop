<div class="space-y-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <div x-data="abastecimentoForm()">
            <form id="abastecimentoForm" method="POST" action="{{ $action }}" class="space-y-4">
                @csrf
                @if ($method === 'PUT')
                    @method('PUT')
                @endif

                <!-- Cabeçalho -->
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo de Equipamento</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-1">
                    <div>
                        <x-forms.input label="Descrição" name="descricao_tipo" error_message="descricao_tipo"
                            value="{{ old('descricao_tipo', $tipoequipamentos->descricao_tipo ?? '') }}" />
                        @error('descricao_tipo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-forms.input label="Número de Eixos" name="numero_eixos" id="numero_eixos" numeric="true"
                            max="4" error_message="numero_eixos"
                            value="{{ old('numero_eixos', $tipoequipamentos->numero_eixos ?? '') }}" />
                        @error('numero_eixos')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Campos de pneus nos eixos -->
                    @for ($i = 1; $i <= 4; $i++)
                        <div id="eixo_{{ $i }}" style="display: none;">
                            <x-forms.input label="Número de Pneus no Eixo {{ $i }}"
                                name="numero_pneus_eixo_{{ $i }}" numeric="true" max="4"
                                error_message="numero_pneus_eixo_{{ $i }}"
                                value="{{ old('numero_pneus_eixo_' . $i, $tipoequipamentos->{'numero_pneus_eixo_' . $i} ?? '') }}" />
                            @error('numero_pneus_eixo_{{ $i }}')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endfor


                    <!-- Botões -->
                    <div class="flex justify-end space-x-3 col-span-2">
                        <x-bladewind::button tag="a" href="{{ route('admin.tipoequipamentos.index') }}" outline>
                            Cancelar
                        </x-bladewind::button>
                        <!-- Botão Enviar -->
                        <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                            <span>{{ isset($tipoequipamento) ? 'Atualizar' : 'Salvar' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let numeroEixosInput = document.getElementById("numero_eixos");

            numeroEixosInput.addEventListener("input", function() {
                let numeroEixos = parseInt(numeroEixosInput.value) || 0;

                for (let i = 1; i <= 4; i++) {
                    let eixoDiv = document.getElementById(`eixo_${i}`);
                    eixoDiv.style.display = i <= numeroEixos ? "block" : "none";
                }
            });

            // Aciona o evento manualmente para exibir os eixos corretos caso haja valor pré-preenchido
            numeroEixosInput.dispatchEvent(new Event("input"));
        });
    </script>
@endpush
