<div class="space-y-6">
    {{-- @if ($errors->any())
    <div class="mb-4 bg-red-50 p-4 rounded">
        <ul class="list-disc list-inside text-red-600">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif --}}

    <x-bladewind::tab-group name="tab-icon">
        <x-slot name="headings">
            <x-bladewind::tab-heading name="cadastro_abastecimento" active="true" icon="truck"
                label="Cadastro Abastecimento" icon_type="outline" />
        </x-slot>

        <x-bladewind::tab-body>

            <x-bladewind::tab-content name="cadastro_abastecimento" active="true">
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

                    <div class="col-span-2">
                        <x-bladewind::select name="id_fornecedor" placeholder="Posto" searchable="true" label="Posto"
                            selected_value="{{ old('id_fornecedor', $abastecimento->id_fornecedor ?? '') }}"
                            :data="$formOptions['fornecedores']" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::select name="id_veiculo" placeholder="Placa" searchable="true" label="Placa"
                            filter="capacidade_tanque"
                            selected_value="{{ old('id_veiculo', $abastecimento->id_veiculo ?? '') }}"
                            :data="$formOptions['placas']" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="capacidade_tanque" placeholder="Capacidade do Tanque"
                            searchable="true" label="Capacidade do Tanque"
                            selected_value="{{ old('capacidade_tanque', $placas->capacidade_tanque_principal ?? '') }}" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="numero_nota_fiscal" placeholder="N° Nota Fiscal" searchable="true"
                            label="N° Nota Fiscal"
                            selected_value="{{ old('numero_nota_fiscal', $abastecimento->numero_nota_fiscal ?? '') }}" />
                    </div>

                    <div class="col-span-3">
                        <x-bladewind::input name="chave_nf" placeholder="Chave NF" searchable="true" label="Chave NF"
                            selected_value="{{ old('chave_nf', $abastecimento->chave_nf ?? '') }}" />
                    </div>

                    <div class="col-span-3">
                        <x-bladewind::select name="id_motorista" placeholder="Motorista" searchable="true"
                            label="Motorista"
                            selected_value="{{ old('id_motorista', $abastecimento->id_motorista ?? '') }}"
                            :data="$formOptions['pessoas']" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::select name="id_departamento" placeholder="Departamento" searchable="true"
                            label="Departamento"
                            selected_value="{{ old('id_departamento', $abastecimento->id_departamento ?? '') }}"
                            :data="$formOptions['departamentos']" />
                    </div>

                </div>

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Abastecimentos</h3>
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

                    <div class="col-span-1">
                        <x-bladewind::input type="date" name="data_abastecimento" placeholder="Data Abastecimento"
                            label="Data Abastecimento" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::select name="id_combustivel" placeholder="Tipo de Combustível" searchable="true"
                            label="Tipo de Combustível"
                            selected_value="{{ old('id_combustivel', $abastecimento->id_combustivel ?? '') }}"
                            :data="$formOptions['tipocombustiveis']" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::select name="id_bomba" placeholder="Nome Bomba(Bico)" searchable="true"
                            label="Nome Bomba(Bico)"
                            selected_value="{{ old('id_bomba', $abastecimento->id_bomba ?? '') }}" :data="$formOptions['bombas']" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="litros_abastecido" placeholder="Litros(m³)" label="Litros(m³)"
                            selected_value="{{ old('litros_abastecido', $abastecimento->litros_abastecido ?? '') }}" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="km_veiculo" placeholder="Km Veículo" label="Km Veículo"
                            selected_value="{{ old('km_veiculo', $abastecimento->km_veiculo ?? '') }}" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="km_anterior" placeholder="Km Anterior" label="Km Anterior"
                            selected_value="{{ old('km_anterior', $abastecimento->km_anterior ?? '') }}" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="valor_unitario" placeholder="Valor Unitário" class="monetario"
                            label="Valor Unitário"
                            selected_value="{{ old('valor_unitario', $abastecimento->valor_unitario ?? '') }}" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="valor_total" placeholder="Valor Total" class="monetario"
                            label="Valor Total"
                            selected_value="{{ old('valor_total', $abastecimento->valor_total ?? '') }}" />
                    </div>

                </div>

            </x-bladewind::tab-content>
    </x-bladewind::tab-group>

    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">
        <a href="{{ route('admin.sinistros.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            {{ isset($empresa) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>
</x-adminlte-card>

<script>
    const currencyList = document.querySelectorAll('.monetario');
    const currencyInputs = Array.from(currencyList);

    currencyInputs.forEach(input => {
        input.addEventListener('input', () => {
            // Remove o formato de moeda para manipular o valor
            let valor = input.value.replace(/[^\d-]/g, ''); // Mantém apenas números e o sinal de menos

            // Verifica se o valor é negativo
            const isNegative = valor.startsWith('-');

            // Remove o sinal de menos para o cálculo, se presente
            valor = valor.replace('-', '');

            // Ajusta os centavos
            valor = (parseInt(valor || '0', 10) / 100).toFixed(2);

            // Adiciona o sinal de menos de volta, se for o caso
            if (isNegative) {
                valor = '-' + valor;
            }

            // Formata o valor para BRL
            input.value = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            }).format(valor);
        });
    });
</script>
