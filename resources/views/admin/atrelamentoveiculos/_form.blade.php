<div class="space-y-6">
    @if($errors->any())
        <div class="mb-4 bg-red-50 p-4 rounded">
            <ul class="list-disc list-inside text-red-600">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
        <h3 class="text-lg font-medium text-gray-900 mb-4">Veículo</h3>
    
        {{-- Componente para criaçao das TAB de navegaçao --}}
        {{-- O código é grande, mas apenas por causa do HTML --}}
        {{-- AS tabs de heading sao responsaveis por se conectarem a tab content pelo name --}}
        {{-- Verifica se estamos na rota de criação caso contrario estamos na rota de ediçao --}}
        @php
            $isCreate = Route::currentRouteName() == 'admin.atrelamentoveiculos.create' ? true : false;
        @endphp

    <x-bladewind::tab-group name="tab-icon">
        <x-slot name="headings">
            <x-bladewind::tab-heading name="atrelamento" active="true" icon="truck" label="Atrelamento"
                icon_type="outline" />
            @if (!$isCreate)
                <x-bladewind::tab-heading name="desatrelamento" icon="car" label="Desatrelamento" icon="square-3-stack-3d" icon_type="outline" />
            @endif
        </x-slot>

        <x-bladewind::tab-body>
            <x-bladewind::tab-content name="atrelamento" active="true">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-bladewind::input name="data_atrelamento" type="date" label="Data Atrelamento" required
                                value="{{ old('data_atrelamento', format_date($registroAtrelamento->data_atrelamento, 'd/m/Y') ?? '')}}" />
                        </div>
                        <div>
                            <x-bladewind::select name="id_cavalo" id="id_cavalo" class="block w-full mt-1"  label="Placa do Cavalo" required
                                :data="$veiculos" selected_value="{{ old('id_cavalo', $veiculos->id_veiculo ?? '') }}"/>
                        </div>
                        <div>
                            <x-bladewind::input name="km_hr_inicial_cavalo" type="number" label="KM Inicial do cavalo" required
                                selected_value="{{ old('km_hr_inicial_cavalo', $registroAtrelamento->km_hr_inicial_cavalo ?? '')}}" />
                        </div>
                        <div>
                            <x-bladewind::input name="id_usuario" type="text" label="Usuário" disabled="true"
                                selected_value="{{ old('id_usuario', $nomeUser->name?? '-')}}" />
                        </div>
                        <div>
                            <x-bladewind::input name="id_filial" type="text" label="Filial" disabled="true"
                                selected_value="{{ old('id_filial', $veiculos->filialVeiculo->name?? '-')}}" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Carreta</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-bladewind::select name="id_carreta" id="id_carreta" class="block w-full mt-1"  label="Placa Carreta" required
                            :data="$carretas" />
                        </div>
                        <div>
                            <x-bladewind::input name="atrelamento_itens_atrelamento_km_hr_inicial_carreta" type="number" label="KM/Horas Inicial Carreta" disabled="true"
                                selected_value="{{ old('atrelamento_itens_atrelamento_km_hr_inicial_carreta', $atrelamento->atrelamento_itens_atrelamento_km_hr_inicial_carreta ?? '')}}" />
                        </div>
                        <div>
                            <x-bladewind::input name="atrelamento_itens_atrelamento_km_hr_final_carreta" type="number" label="KM/Horas Final Carreta" disabled="true"
                                selected_value="{{ old('atrelamento_itens_atrelamento_km_hr_final_carreta', $atrelamento->atrelamento_itens_atrelamento_km_hr_final_carreta ?? '')}}" />
                        </div>
                        <div>
                            <x-bladewind::input name="atrelamento_itens_atrelamento_km_hr_rodados" type="number" label="KM/Horas Rodadas" disabled="true"
                                selected_value="{{ old('atrelamento_itens_atrelamento_km_hr_rodados', $atrelamento->atrelamento_itens_atrelamento_km_hr_rodados ?? '')}}" />
                        </div>
                    </div>
                </div>
            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="desatrelamento">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-bladewind::input name="data_desatrelamento" type="date" label="Data Desatrelamento" numeric="true" required
                                selected_value="{{ old('data_desatrelamento', $atrelamento->data_desatrelamento ?? '')}}" />
                        </div>
                        <div>
                            <x-bladewind::input name="km_hr_final_cavalo" type="number" label="KM Final do cavalo" required
                                value="{{ old('km_hr_final_cavalo', $atrelamento->km_hr_final_cavalo ?? '')}}" />
                        </div>
                    </div>
                </div>
            </x-bladewind::tab-content>

        </x-bladewind::tab-body>
    </x-bladewind::tab-group>

    
    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">

        <a href="{{ route('admin.atrelamentoveiculos.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit" id="submit-form"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            {{ isset($empresa) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>
@push('scripts')
     <script>
        document.addEventListener('DOMContentLoaded', () => {
            const renvavamInput = document.querySelector('[name="km_hr_inicial_cavalo"]');

            // Atualiza os campos ocultos ao trocar o veículo
            document.querySelector('[name="id_cavalo"]').addEventListener('change', function() {
                const placaId = this.value;

                if (placaId) {
                    fetch('/admin/atrelamentoveiculos/get-kmhrinicialcavalo-data', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                placa: placaId
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.error) {
                                // Atualiza os spans visíveis
                                renvavamInput.value = data.km_hr_inicial_cavalo;

                            } else {
                                alert(data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar dados do veículo:', error);
                        });
                }
            });
        });
    </script>
@endpush
