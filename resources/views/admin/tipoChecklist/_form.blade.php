<div class="space-y-6 ">
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo Checklist</h3>
    <div class="grid md:grid-cols-1 gap-4 sm:grid-cols-1">
    <div>
        <x-bladewind::input 
            label="Nome" 
            name="nome"
            required
            error_message="nome"
            selected_value="{{ old('nome', $checkList->nome ?? '') }}" 
        />

        <x-bladewind::input 
            label="Descrição" 
            name="descricao"
            required
            error_message="descricao"
            selected_value="{{ old('descricao', $checkList->descricao ?? '') }}"
        />

        <x-bladewind::select
            name="filtro"
            selected_value="{{ old('filtro', $checkList->filtro ?? '') }}"
            required
            error_message="filtro"
            id="filtro"
            label="Filtro"
            :data="$filtro" 
        />

        <div class="hidden" id="departamento_id">
            <x-bladewind::select
                name="departamento_id"
                selected_value="{{ old('departamento_id', $checkList->departamento_id ?? '') }}"
                required
                error_message="departamento_id"
                label="Departamento"
                :data="$departamentos"
            />
        </div>

        <div class="hidden" id="cargo_id">
            <x-bladewind::select
                name="cargo_id"
                selected_value="{{ old('cargo_id', $checkList->cargo_id ?? '') }}"
                required
                error_message="cargo_id"
                label="Cargo"
                :data="$cargos"  
            />
        </div>

        <x-bladewind::select
            name="multiplas_etapas"
            selected_value="{{ old('multiplas_etapas', $checkList->multiplas_etapas ?? '') }}"
            required
            error_message="multiplas_etapas"
            label="Segunda Etapa"
            :data="$multiplas_etapas"  
        />

        @error('descricao_tipo_borracha')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">
        <x-bladewind::button tag="a" href="{{ route('admin.tipoChecklist.index') }}" outline>
            Cancelar
        </x-bladewind::button>
        <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
            class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
            <template x-if="!isSubmitting">
                <span>{{ isset($checkList) ? 'Atualizar' : 'Salvar' }}</span>
            </template>
            <template x-if="isSubmitting">
                <span>{{ isset($checkList) ? 'Atualizando...' : 'Salvando...' }}</span>
            </template>
        </button>
    </div>
</div>

<script>

    function mudaDepartamentoOuCargo(e) {
        const value = e.target.value;
        if(value != null){
            const departamento = document.querySelector('[id="departamento_id"]');
            const cargo = document.querySelector('[id="cargo_id"]');
            if(value === 'Departamento'){
                departamento.classList.remove('hidden');
            }else{
                departamento.classList.add('hidden');

            }
            if(value === 'Cargo'){
                cargo.classList.remove('hidden');
            }else{
                cargo.classList.add('hidden');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', mudaDepartamentoOuCargo);
    const filtro = document.querySelector('[name="filtro"]').addEventListener('change', mudaDepartamentoOuCargo);

</script>
