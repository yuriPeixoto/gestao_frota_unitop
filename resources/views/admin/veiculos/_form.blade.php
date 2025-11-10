<div class="space-y-6">
    @if (session('error'))
        <div class="alert-danger alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 p-4">
            <ul class="list-inside list-disc text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Componente para criaçao das TAB de navegaçao --}}
    {{-- O código é grande, mas apenas por causa do HTML --}}
    {{-- AS tabs de heading sao responsaveis por se conectarem a tab content pelo name --}}
    {{-- Verifica se estamos na rota de criação caso contrario estamos na rota de ediçao --}}
    @php
        $isCreate = Route::currentRouteName() == 'admin.veiculos.create' ? true : false;
    @endphp

    <x-bladewind::tab-group name="tab-icon">
        <x-slot name="headings">
            <x-bladewind::tab-heading name="dados_veiculo" active="true" icon="truck" label="Dados Veículo"
                icon_type="outline" />
            <x-bladewind::tab-heading name="controles_veiculo" label="Controles" icon="square-3-stack-3d"
                icon_type="outline" />
            <x-bladewind::tab-heading name="registro_compra" label="Registro de Compra" icon="square-3-stack-3d"
                icon_type="outline" />

            <x-bladewind::tab-heading name="dados_nao_tracionado" label="Dados não Tracionado" icon="square-3-stack-3d"
                icon_type="outline" />

            <x-bladewind::tab-heading name="km_comodato" label="KM para Veículo em Comodato" icon="calendar"
                icon_type="outline" />

            @if (!$isCreate && isset($formOptions))
                <x-bladewind::tab-heading name="historico_transferencia" label="Histórico de Transferência"
                    icon="document-text" icon_type="outline" />
            @endif

            @if (!$isCreate)
                {{-- Aba de pneus aplicados aparece apenas na edição --}}
                <x-bladewind::tab-heading name="pneus_aplicados" label="Pneus Aplicados" icon="lifebuoy"
                    icon_type="outline" />
            @endif


        </x-slot>

        <x-bladewind::tab-body>

            <x-bladewind::tab-content name="dados_veiculo" active="true">

                <div class="flex items-center justify-between mb-10">
                    <h3 class="font-semibold text-gray-800 uppercase tracking-wide text-lg mb-2 md:mb-0">
                        Informações do Veículo
                    </h3>
                    <div>
                        <label for="situacao_veiculo" class="block text-sm font-medium text-gray-700 mb-2">
                            Situação do Veículo
                        </label>
                        <div class="inline-flex rounded-md shadow-sm overflow-hidden border border-gray-300">
                            <input type="radio" name="situacao_veiculo" id="situacao_veiculo_1" value="1"
                                class="sr-only" @if (old('situacao_veiculo', $veiculo->situacao_veiculo ?? '') == '1') checked @endif>
                            <label for="situacao_veiculo_1" id="label_situacao_veiculo_1"
                                class="px-6 py-2 cursor-pointer text-sm font-medium transition-colors duration-150">
                                Ativo
                            </label>
                            <input type="radio" name="situacao_veiculo" id="situacao_veiculo_0" value="0"
                                class="sr-only" @if (old('situacao_veiculo', $veiculo->situacao_veiculo ?? '') == '0') checked @endif>
                            <label for="situacao_veiculo_0" id="label_situacao_veiculo_0"
                                class="px-6 py-2 cursor-pointer text-sm font-medium transition-colors duration-150 border-l border-gray-300">
                                Inativo
                            </label>

                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-5 items-center">
                    {{-- setar o height maximo 60 px da imagem e com overflow none --}}
                    <div class=" overflow-hidden">
                        <x-bladewind::filepicker name="imagem_veiculo" required="false" placeholder="Foto do veículo"
                            accepted_file_types="image/*" selected_value_class="h-full w-full" base64="false"
                            url="{{ old('imagem_veiculo', isset($veiculo) && $veiculo->imagem_veiculo ? url('storage/' . $veiculo->imagem_veiculo) : '') }}"
                            selected_value="{{ old('imagem_veiculo', $veiculo->imagem_veiculo ?? '') }}" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-3 items-center mt-4">
                    <x-forms.input name="placa" type="text" label="Placa"
                        value="{{ old('placa', $veiculo->placa ?? '') }}" />

                    <x-forms.smart-select name="id_uf" label="UF" placeholder="UF" :options="$formOptions['ufs']"
                        value="{{ old('id_uf', $veiculo->id_uf ?? ($veiculo->uf ?? '')) }}" />

                    <x-forms.smart-select name="id_municipio" label="Municipio" placeholder="Selecione o Municipio..."
                        :options="$formOptions['municipios']" :searchUrl="route('admin.api.municipio.search')" :selected="old('id_municipio', $veiculo->municipioVeiculo->nome_municipio ?? '')" asyncSearch="true" />

                </div>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 items-center mt-4">
                    {{-- CAMPO: FORNECEDOR COMODATO --}}
                    @can('ver_fornecedor_comodato')
                        <div class="campo-permissao-wrapper">
                            @can('editar_fornecedor_comodato')
                                <x-forms.smart-select name="id_fornecedor_comodato" label="Fornecedor Comodato"
                                    :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedor.search')" :selected="old('id_fornecedor_comodato', $veiculo->id_fornecedor_comodato ?? '')" />
                            @else
                                <x-forms.smart-select name="id_fornecedor_comodato" label="Fornecedor Comodato 🔒"
                                    :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedor.search')" :selected="old('id_fornecedor_comodato', $veiculo->id_fornecedor_comodato ?? '')" disabled="true" />
                                <small class="text-gray-500 text-xs mt-1">🔒 Você não tem permissão para editar este
                                    campo</small>
                            @endcan
                        </div>
                    @else
                        {{-- Campo oculto se não tem permissão para ver --}}
                        <input type="hidden" name="id_fornecedor_comodato"
                            value="{{ old('id_fornecedor_comodato', $veiculo->id_fornecedor_comodato ?? '') }}">

                        {{-- Placeholder visual --}}
                        <div class="campo-permissao-wrapper">
                            <x-forms.input name="id_fornecedor_comodato_fake" type="text" label="Fornecedor Comodato 🚫"
                                value="*** ACESSO RESTRITO ***" readonly="true" class="bg-gray-100 text-gray-400" />
                            <small class="text-red-500 text-xs mt-1">🚫 Você não tem permissão para visualizar este
                                campo</small>
                        </div>
                    @endcan

                    {{-- CAMPO: DATA FIM COMODATO --}}
                    @can('ver_data_comodato')
                        <div class="campo-permissao-wrapper">
                            @can('editar_data_comodato')
                                <x-forms.input type="date" name="data_comodato" label="Data Fim Comodato"
                                    value="{{ old('data_comodato', $veiculo->data_comodato ?? '') }}" />
                            @else
                                <x-forms.input type="date" name="data_comodato" label="Data Fim Comodato 🔒"
                                    value="{{ old('data_comodato', $veiculo->data_comodato ?? '') }}" readonly="true" />
                                <small class="text-gray-500 text-xs mt-1">🔒 Você não tem permissão para editar este
                                    campo</small>
                            @endcan
                        </div>
                    @else
                        {{-- Campo oculto se não tem permissão para ver --}}
                        <input type="hidden" name="data_comodato"
                            value="{{ old('data_comodato', $veiculo->data_comodato ?? '') }}">

                        {{-- Placeholder visual --}}
                        <div class="campo-permissao-wrapper">
                            <x-forms.input name="data_comodato_fake" type="text" label="Data Fim Comodato 🚫"
                                value="*** ACESSO RESTRITO ***" readonly="true" class="bg-gray-100 text-gray-400" />
                            <small class="text-red-500 text-xs mt-1">🚫 Você não tem permissão para visualizar este
                                campo</small>
                        </div>
                    @endcan

                    <x-forms.smart-select name="id_categoria" label="Categoria" placeholder="Categoria"
                        :options="$formOptions['categorias']" :selected="old('id_categoria', $veiculo->id_categoria ?? '')" />

                    <x-forms.smart-select name="id_modelo_veiculo" label="Modelo" placeholder="Modelo"
                        :options="$formOptions['modelos']" :selected="old('id_modelo_veiculo', $veiculo->id_modelo_veiculo ?? '')" />

                    <x-forms.smart-select name="id_pessoal" label="Motorista fixo" placeholder="Motorista fixo"
                        :options="$formOptions['motoristas']" :selected="old('id_pessoal', $veiculo->id_pessoal ?? '')" />

                    <x-forms.input name="ano_modelo" type="number" label="Ano Modelo"
                        value="{{ old('ano_modelo', $veiculo->ano_modelo ?? '') }}" />

                    <x-forms.input name="renavam" type="text" label="Renavam"
                        value="{{ old('renavam', $veiculo->renavam ?? '') }}" />

                    <x-forms.input name="chassi" type="text" label="Chassi"
                        value="{{ old('chassi', $veiculo->chassi ?? '') }}" />

                    <x-forms.input name="marca_veiculo" type="text" label="Marca Veiculo"
                        value="{{ old('marca_veiculo', $veiculo->marca_veiculo ?? '') }}" />

                    <x-forms.input name="ano_fabricacao" type="text" label="Ano Fabricação"
                        value="{{ old('ano_fabricacao', $veiculo->ano_fabricacao ?? '') }}" />


                    <x-forms.input name="capacidade_tanque_principal" type="number"
                        label="Capacidade Tanque Principal"
                        value="{{ old('capacidade_tanque_principal', $veiculo->capacidade_tanque_principal ?? '') }}" />

                    <x-forms.input name="capacidade_tanque_secundario" type="number"
                        label="Capacidade Tanque Secundario"
                        value="{{ old('capacidade_tanque_secundario', $veiculo->capacidade_tanque_secundario ?? '') }}" />


                    <x-forms.input name="capacidade_arla" type="number" label="Capacidade Arla"
                        value="{{ old('capacidade_arla', $veiculo->capacidade_arla ?? '') }}" />

                    <x-forms.input name="cor_veiculo" type="text" label="Cor"
                        value="{{ old('cor_veiculo', $veiculo->cor_veiculo ?? '') }}" />

                    <x-forms.input name="valor_venal" type="text" label="Valor Venal"
                        oninput="formatarMoedaBrasileira(this)"
                        value="{{ old('valor_venal', $veiculo->valor_venal ?? '') }}" />

                    <x-forms.input name="numero_frota" type="number" label="Numero Frota"
                        value="{{ old('numero_frota', $veiculo->numero_frota ?? '') }}" />

                    <x-forms.input name="km_inicial" type="text" label="KM Inicial"
                        value="{{ old('km_inicial', $veiculo->km_inicial ?? '') }}" />

                    <x-forms.input name="horas_iniciais" type="number" label="Horas Iniciais"
                        value="{{ old('horas_iniciais', $veiculo->horas_iniciais ?? '') }}" />

                    <x-forms.smart-select name="id_tipo_combustivel" label="Tipo Combustível"
                        placeholder="Tipo Combustível" :options="$formOptions['tipoCombustiveis']" :selected="old('id_tipo_combustivel', $veiculo->id_tipo_combustivel ?? '')" />

                    <x-forms.smart-select name="id_tipo_veiculo" label="Tipo Veículo" placeholder="Tipo Veículo"
                        :options="$formOptions['tipoVeiculo']" :selected="old('id_tipo_veiculo', $veiculo->id_tipo_veiculo ?? '')" />

                    <x-forms.input name="rota_1" type="number" label="Rota Fixa"
                        value="{{ old('rota_1', $veiculo->rota_1 ?? '') }}" />

                    <x-forms.input name="capacidade_carregamento_m3" type="number"
                        label="Capacidade Carregamento M3"
                        value="{{ old('capacidade_carregamento_m3', $veiculo->capacidade_carregamento_m3 ?? '') }}" />

                    <x-forms.input name="capacidade_carregamento_cubado" type="number"
                        label="Capacidade Carregamento Cubado"
                        value="{{ old('capacidade_carregamento_cubado', $veiculo->capacidade_carregamento_cubado ?? '') }}" />

                    <x-forms.input name="capacidade_carregamento_real" type="number"
                        label="Capacidade Carregamento Real"
                        value="{{ old('capacidade_carregamento_real', $veiculo->capacidade_carregamento_real ?? '') }}" />


                </div>

                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase">Configuração do Veículo</h3>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-5 items-center align-items-center">

                    <x-forms.input name="descricao_equipamento" type="number" label="Horas Iniciais"
                        value="{{ old('descricao_equipamento', $veiculo->descricao_equipamento ?? '') }}" />

                    <x-forms.input name="empresa" type="text" label="Descrição Rastreador"
                        value="{{ old('empresa', $veiculo->empresa ?? '') }}" />

                    <x-forms.smart-select name="id_tipo_equipamento" label="Tipo Equipamento"
                        placeholder="Tipo Equipamento" :options="$formOptions['tipoEquipamentos']" :selected="old('id_tipo_equipamento', $veiculo->id_tipo_equipamento ?? '')" />


                    <x-forms.smart-select name="id_filial" label="Filial" placeholder="Filial" :options="$formOptions['filiais']"
                        :selected="old('id_filial', $veiculo->id_filial ?? '')" :disabled="isset($veiculo)" />

                    <x-forms.smart-select name="id_base_veiculo" label="Base" placeholder="Base" :options="$formOptions['bases']"
                        :selected="old('id_base_veiculo', $veiculo->id_base_veiculo ?? '')" />

                    <x-forms.smart-select name="id_departamento" label="Departamento" placeholder="Departamento"
                        :options="$formOptions['departamentos']" :selected="old('id_departamento', $veiculo->id_departamento ?? '')" />

                    <div class="hidden" id="fornecedor">
                        <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione..."
                            :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')" :selected="old('id_fornecedor', $veiculo->id_fornecedor ?? '')" asyncSearch="true" />
                    </div>

                    <div>
                        <label for="is_terceiro" class="block">Veículo Terceiro</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_terceiro" id="is_terceiro_true" value="true"
                                    {{ old('is_terceiro', $veiculo->is_terceiro ?? null) == true ? 'checked' : '' }}>
                                <label for="is_terceiro_true">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_terceiro" id="is_terceiro_false" value="false"
                                    {{ old('is_terceiro', $veiculo->is_terceiro ?? null) == false ? 'checked' : '' }}>
                                <label for="is_terceiro_false">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="telemetria" class="block">Telemetria</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="telemetria" id="telemetria_sim" value="1"
                                    {{ old('telemetria', $veiculo->telemetria ?? '0') == '1' ? 'checked' : '' }}>
                                <label for="telemetria_sim" class="block">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="telemetria" id="telemetria_nao" value="0"
                                    {{ old('telemetria', $veiculo->telemetria ?? '0') == '0' ? 'checked' : '' }}>
                                <label for="telemetria_nao" class="block">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="contrato_manutencao" class="block">Contrato Manutenção</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="contrato_manutencao" id="contrato_manutencao_sim"
                                    value="1"
                                    {{ old('contrato_manutencao', $veiculo->contrato_manutencao ?? '0') == '1' ? 'checked' : '' }}>
                                <label for="contrato_manutencao_sim" class="block">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="contrato_manutencao" id="contrato_manutencao_nao"
                                    value="0"
                                    {{ old('contrato_manutencao', $veiculo->contrato_manutencao ?? '0') == '0' ? 'checked' : '' }}>
                                <label for="contrato_manutencao_nao" class="block">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_possui_tracao" class="block">Possui Tração</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_possui_tracao" id="is_possui_tracao_sim"
                                    value="1"
                                    {{ old('is_possui_tracao', $veiculo->is_possui_tracao ?? '0') == '1' ? 'checked' : '' }}>
                                <label for="is_possui_tracao_sim" class="block">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_possui_tracao" id="is_possui_tracao_nao"
                                    value="0"
                                    {{ old('is_possui_tracao', $veiculo->is_possui_tracao ?? '0') == '0' ? 'checked' : '' }}>
                                <label for="is_possui_tracao_nao" class="block">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_marcador_quilometragem" class="block">Marcador Quilometragem</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_marcador_quilometragem"
                                    id="is_marcador_quilometragem_sim" value="1"
                                    {{ old('is_marcador_quilometragem', $veiculo->is_marcador_quilometragem ?? '0') == '1' ? 'checked' : '' }}>
                                <label for="is_marcador_quilometragem_sim" class="block">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_marcador_quilometragem"
                                    id="is_marcador_quilometragem_nao" value="0"
                                    {{ old('is_marcador_quilometragem', $veiculo->is_marcador_quilometragem ?? '0') == '0' ? 'checked' : '' }}>
                                <label for="is_marcador_quilometragem_nao" class="block">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_horas" class="block">Marcador Horas</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_horas" id="is_horas_sim" value="1"
                                    {{ old('is_horas', $veiculo->is_horas ?? '0') == '1' ? 'checked' : '' }}>
                                <label for="is_horas_sim" class="block">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_horas" id="is_horas_nao" value="0"
                                    {{ old('is_horas', $veiculo->is_horas ?? '0') == '0' ? 'checked' : '' }}>
                                <label for="is_horas_nao" class="block">Não</label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Configuração do Prêmio</h3>


                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-5 items-center align-items-center">

                    <x-forms.smart-select name="id_subcategoria_veiculo" label="Sub-Categoria"
                        placeholder="Sub-Categoria" :options="$formOptions['subCategorias']" :selected="old('id_subcategoria_veiculo', $veiculo->id_subcategoria_veiculo ?? null)" />

                    <x-forms.smart-select name="id_operacao" label="Tipo Operação" placeholder="Tipo Operação"
                        :options="$formOptions['tipoOperacoes']" :selected="old('id_operacao', $veiculo->id_operacao ?? null)" />

                </div>
            </x-bladewind::tab-content>
            <x-bladewind::tab-content name="controles_veiculo">

                <h3 class="font-medium text-gray-800 mb-10 uppercase">Controles</h3>
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-2 items-center">
                    <div>
                        <label for="is_controle_manutencao" class="block">Controla Manutenção</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controle_manutencao" id="is_controle_manutencao_true"
                                    value="true"
                                    {{ old('is_controle_manutencao', $controlesveiculo->is_controle_manutencao ?? null) == true ? 'checked' : '' }}>
                                <label for="is_controle_manutencao_true">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controle_manutencao" id="is_controle_manutencao_false"
                                    value="false"
                                    {{ old('is_controle_manutencao', $controlesveiculo->is_controle_manutencao ?? null) == false ? 'checked' : '' }}>
                                <label for="is_controle_manutencao_false">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_controla_seguro_obrigatorio" class="block">Controla Seguro Obrigatório</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_seguro_obrigatorio"
                                    id="is_controla_seguro_obrigatorio_true" value="true"
                                    {{ old('is_controla_seguro_obrigatorio', $controlesveiculo->is_controla_seguro_obrigatorio ?? null) == true
                                        ? 'checked'
                                        : '' }}>
                                <label for="is_controla_seguro_obrigatorio_true">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_seguro_obrigatorio"
                                    id="is_controla_seguro_obrigatorio_false" value="false"
                                    {{ old('is_controla_seguro_obrigatorio', $controlesveiculo->is_controla_seguro_obrigatorio ?? null) == false
                                        ? 'checked'
                                        : '' }}>
                                <label for="is_controla_seguro_obrigatorio_false">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_controla_pneu" class="block">Controla Pneu</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_pneu" id="is_controla_pneu_true"
                                    value="true"
                                    {{ old('is_controla_pneu', $controlesveiculo->is_controla_pneu ?? null) == true ? 'checked' : '' }}>
                                <label for="is_controla_pneu_true">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_pneu" id="is_controla_pneu_false"
                                    value="false"
                                    {{ old('is_controla_pneu', $controlesveiculo->is_controla_pneu ?? null) == false ? 'checked' : '' }}>
                                <label for="is_controla_pneu_false">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_controla_licenciamento" class="block">Controla Licenciamento</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_licenciamento"
                                    id="is_controla_licenciamento_true" value="true"
                                    {{ old('is_controla_licenciamento', $controlesveiculo->is_controla_licenciamento ?? null) == true
                                        ? 'checked'
                                        : '' }}>
                                <label for="is_controla_licenciamento_true">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_licenciamento"
                                    id="is_controla_licenciamento_false" value="false"
                                    {{ old('is_controla_licenciamento', $controlesveiculo->is_controla_licenciamento ?? null) == false
                                        ? 'checked'
                                        : '' }}>
                                <label for="is_controla_licenciamento_false">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_controla_ipva" class="block">Controla IPVA</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_ipva" id="is_controla_ipva_true"
                                    value="true"
                                    {{ old('is_controla_ipva', $controlesveiculo->is_controla_ipva ?? null) == true ? 'checked' : '' }}>
                                <label for="is_controla_ipva_true">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_controla_ipva" id="is_controla_ipva_false"
                                    value="false"
                                    {{ old('is_controla_ipva', $controlesveiculo->is_controla_ipva ?? null) == false ? 'checked' : '' }}>
                                <label for="is_controla_ipva_false">Não</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="is_considera_para_rateio" class="block">Considerar para Rateio</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_considera_para_rateio"
                                    id="is_considera_para_rateio_true" value="true"
                                    {{ old('is_considera_para_rateio', $controlesveiculo->is_considera_para_rateio ?? null) == true
                                        ? 'checked'
                                        : '' }}>
                                <label for="is_considera_para_rateio_true">Sim</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="is_considera_para_rateio"
                                    id="is_considera_para_rateio_false" value="false"
                                    {{ old('is_considera_para_rateio', $controlesveiculo->is_considera_para_rateio ?? null) == false
                                        ? 'checked'
                                        : '' }}>
                                <label for="is_considera_para_rateio_false">Não</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-start items-center">
                        <button type="button" onclick="adicionarHistorico()"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Adicionar Controle
                        </button>
                    </div>
                </div>

                <hr class="mt-10 mb-10">

                <!-- Campo hidden para armazenar os históricos - Note que o nome foi alterado para 'historicos' -->
                <input type="hidden" name="historicosControle" id="historicos_json"
                    value="{{ isset($controlesveiculo) ? json_encode($controlesveiculo) : '[]' }}">

                <div class="col-span-full">
                    <table class="min-w-full divide-y divide-gray-200 tabelaHistorico">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data Inclusão
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Controla <br> Manutenção
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Controla <br> Licenciamento
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Controla <br> Seguro <br> Obrigatorio
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Controla <br> IPVA
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Controla <br> Pneu
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Considerar <br> para <br> Rateio
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tabelaHistoricoBody" class="bg-white divide-y divide-gray-200">
                            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                        </tbody>
                    </table>
                </div>


            </x-bladewind::tab-content>
            <x-bladewind::tab-content name="registro_compra">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados da Compra</h3>
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-5 items-center ">

                    <x-forms.input readonly={true} name="id_usuario_cadastro" type="text" label="Usuário"
                        value="{{ old('id_usuario_cadastro', $registroCompra->id_usuario_cadastro ?? Auth::user()->name) }}" />

                    <div class="col-span-4"></div>

                    {{-- Lembrar que o ID fornecedor vem do fornecedor selecionado anteriormente --}}

                    <x-forms.input name="financiador" type="text" label="Financiador" readonly={true}
                        value="{{ old('financiador', $registroCompra->financiador ?? Auth::user()->name) }}" />

                    <x-forms.input name="data_inicio_financiamento" type="date" label="Data de Início"
                        readonly={true}
                        value="{{ old('data_inicio_financiamento', $registroCompra->data_inicio_financiamento ?? '') }}" />

                    <x-forms.input name="data_compra" type="date" label="Data Compra" readonly={true}
                        value="{{ old('data_compra', $registroCompra->data_compra ?? '') }}" />

                    <x-forms.input name="valor_do_bem" type="text" label="Valor Financiamento" readonly={true}
                        oninput="formatarMoedaBrasileira(this)"
                        value="{{ old('valor_do_bem', $registroCompra->valor_do_bem ?? '') }}" />

                    <x-forms.input name="numero_de_parcelas" type="number" label="Nº de Parcelas" readonly={true}
                        value="{{ old('numero_de_parcelas', $registroCompra->numero_de_parcelas ?? '') }}" />

                    <x-forms.input name="valor_parcela" type="text" label="Valor Parcelas" readonly={true}
                        oninput="formatarMoedaBrasileira(this)"
                        value="{{ old('valor_parcela', $registroCompra->valor_parcela ?? '') }}" />

                    <x-forms.input name="numero_processo" type="number" label="Nº Processo" readonly={true}
                        value="{{ old('numero_processo', $registroCompra->numero_processo ?? '') }}" />

                    <x-forms.input name="reclamante_nome" type="text" label="Reclamante" readonly={true}
                        value="{{ old('reclamante_nome', $registroCompra->reclamante_nome ?? '') }}" />

                    <x-forms.input name="valor_processo" type="text" label="Valor Processo" readonly={true}
                        oninput="formatarMoedaBrasileira(this)"
                        value="{{ old('valor_processo', $registroCompra->valor_processo ?? '') }}" />

                    <x-forms.input name="valor_da_compra" type="text" label="Valor do Veículo" readonly={true}
                        oninput="formatarMoedaBrasileira(this)"
                        value="{{ old('valor_da_compra', $registroCompra->valor_da_compra ?? '') }}" />

                    <x-forms.input name="numero_patrimonio" type="text" label="Nº Patrimonio" readonly={true}
                        value="{{ old('numero_patrimonio', $registroCompra->numero_patrimonio ?? '') }}" />

                </div>

                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados da Venda</h3>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-6 items-center ">

                    <x-forms.smart-select name="id_fornecedor_comprador" label="Fornecedor Comprador"
                        placeholder="Selecione..." :options="$formOptions['fornecedores']" :searchUrl="route('admin.api.fornecedores.search')" :selected="old('id_fornecedor_comprador', $veiculo->id_fornecedor_comprador ?? '')"
                        asyncSearch="true" />

                    <x-forms.input name="data_venda" type="date" label="Data Venda"
                        value="{{ old('data_venda', $registroCompra->data_venda ?? '') }}" />

                    <x-forms.input name="valor_da_venda" type="text" label="Valor da Venda"
                        oninput="formatarMoedaBrasileira(this)"
                        value="{{ old('valor_da_venda', $registroCompra->valor_da_venda ?? '') }}" />

                    <x-forms.input name="km_final" type="text" label="KM Venda"
                        value="{{ old('km_final', $registroCompra->km_final ?? '') }}" />

                    <x-forms.input name="hora_final" type="number" label="Hora Final"
                        value="{{ old('hora_final', $registroCompra->hora_final ?? '') }}" />

                    <div class="col-span-full">
                        <label for="observacao" class="block text-sm font-medium text-gray-700">Motivo Venda</label>
                        <textarea name="motivo_venda"
                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400">
                            {{ old('observacao', $registroCompra->observacao ?? '') }}</textarea>
                    </div>

                </div>

            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="dados_nao_tracionado">
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-6 lg:grid-cols-2 items-center ">

                    <x-bladewind::input name="modelo_carroceria" type="text" label="Modelo Carroceria"
                        selected_value="{{ old('modelo_carroceria', $veiculonaotracionado->modelo_carroceria ?? '') }}" />

                    <x-bladewind::input name="marca_carroceria" type="text" label="Marca Carroceria"
                        selected_value="{{ old('marca_carroceria', $veiculonaotracionado->marca_carroceria ?? '') }}" />

                    <x-bladewind::input name="tara_nao_tracionado" type="number" label="Tara Kg" numeric="true"
                        min="0"
                        selected_value="{{ old('tara_nao_tracionado', $veiculonaotracionado->tara_nao_tracionado ?? '') }}" />

                    <x-bladewind::input name="lotacao_nao_tracionado" type="number" label="Lotação Kg"
                        numeric="true" min="0"
                        selected_value="{{ old('lotacao_nao_tracionado', $veiculonaotracionado->lotacao_nao_tracionado ?? '') }}" />

                    <x-bladewind::input name="ano_carroceria" type="number" label="Ano" numeric="true"
                        min="0"
                        selected_value="{{ old('ano_carroceria', $veiculonaotracionado->ano_carroceria ?? '') }}" />

                    <x-bladewind::input name="refrigeracao_carroceria" type="text" label="Refrigeração"
                        selected_value="{{ old('refrigeracao_carroceria', $veiculonaotracionado->refrigeracao_carroceria ?? '') }}" />

                    <x-bladewind::input name="comprimento_carroceria" type="number" label="Comprimento"
                        numeric="true" min="0"
                        selected_value="{{ old('comprimento_carroceria', $veiculonaotracionado->comprimento_carroceria ?? '') }}" />

                    <x-bladewind::input name="largura_carroceria" type="number" label="Largura" numeric="true"
                        min="0"
                        selected_value="{{ old('largura_carroceria', $veiculonaotracionado->largura_carroceria ?? '') }}" />

                    <x-bladewind::input name="altura_carroceria" type="number" label="Altura" numeric="true"
                        min="0"
                        selected_value="{{ old('altura_carroceria', $veiculonaotracionado->altura_carroceria ?? '') }}" />

                    <x-bladewind::input name="capacidade_volumetrica_1" type="number"
                        label="Capacidade Volumetrica 1" numeric="true" min="0"
                        selected_value="{{ old('capacidade_volumetrica_1', $veiculonaotracionado->capacidade_volumetrica_1 ?? '') }}" />

                    <x-bladewind::input name="capacidade_volumetrica_2" type="number"
                        label="Capacidade Volumetrica 2" numeric="true" min="0"
                        selected_value="{{ old('capacidade_volumetrica_2', $veiculonaotracionado->capacidade_volumetrica_2 ?? '') }}" />

                    <x-bladewind::input name="capacidade_volumetrica_3" type="number"
                        label="Capacidade Volumetrica 3" numeric="true" min="0"
                        selected_value="{{ old('capacidade_volumetrica_3', $veiculonaotracionado->capacidade_volumetrica_3 ?? '') }}" />

                    <x-bladewind::input name="capacidade_volumetrica_4" type="number"
                        label="Capacidade Volumetrica 4" numeric="true" min="0"
                        selected_value="{{ old('capacidade_volumetrica_4', $veiculonaotracionado->capacidade_volumetrica_4 ?? '') }}" />

                    <x-bladewind::input name="capacidade_volumetrica_5" type="number"
                        label="Capacidade Volumetrica 5" numeric="true" min="0"
                        selected_value="{{ old('capacidade_volumetrica_5', $veiculonaotracionado->capacidade_volumetrica_5 ?? '') }}" />

                    <x-bladewind::input name="capacidade_volumetrica_6" type="number"
                        label="Capacidade Volumetrica 6" numeric="true" min="0"
                        selected_value="{{ old('capacidade_volumetrica_6', $veiculonaotracionado->capacidade_volumetrica_6 ?? '') }}" />

                    <x-bladewind::input name="capacidade_volumetrica_7" type="number"
                        label="Capacidade Volumetrica 7" numeric="true" min="0"
                        selected_value="{{ old('capacidade_volumetrica_7', $veiculonaotracionado->capacidade_volumetrica_7 ?? '') }}" />

                </div>

                <div class="flex justify-left items-center mb-4">
                    <button type="button" onclick="adicionarHistoricoNaoTracionado()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Adicionar
                    </button>
                </div>

                <!-- Campo hidden para armazenar os históricos de historicos_nao_tracionado -->
                <input type="hidden" name="historicos_nao_tracionado" id="historicos_nao_tracionado_json"
                    value="{{ isset($veiculonaotracionado) ? json_encode($veiculonaotracionado) : '[]' }}">
                @php
                    $veiculonaotracionado = $veiculonaotracionado ?? null;
                @endphp

                <div id="tabela-nao-tracionado">

                    <x-bladewind::table layout="custom" :paginated="true" :data="$veiculonaotracionado">
                        <x-slot:header>
                            <th>Data</th>
                            <th>Carroceria</th>
                            <th>Especificações</th>
                            <th>Dimensões</th>
                            <th>Capacidade Volumétrica</th>
                            <th>Ações</th>
                        </x-slot:header>

                        <tbody id="tabelaNaoTracionadoBody" class="bg-white divide-y divide-gray-200">
                            {{-- Carregado via JS --}}
                        </tbody>
                    </x-bladewind::table>
                </div>
            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="historico_transferencia">
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">
                    <x-bladewind::select name="id_filial_origem" placeholder="Filial Origem" searchable="true"
                        selected_value="{{ old('id_filial_origem', $transferenciaVeiculo->id_filial_origem ?? '') }}"
                        :data="$formOptions['filiais']" />

                    <x-bladewind::select name="id_filial_destino" placeholder="Filial Destino" searchable="true"
                        selected_value="{{ old('id_filial_destino', $transferenciaVeiculo->id_filial_destino ?? '') }}"
                        :data="$formOptions['filiais']" />

                    <x-bladewind::input name="km_transferencia" type="number" label="KM Transferência"
                        numeric="true" min="0"
                        selected_value="{{ old('km_transferencia', $transferenciaVeiculo->km_transferencia ?? '') }}" />

                    <x-bladewind::input name="data_transferencia" type="date" label="Data Transferência"
                        selected_value="{{ old('data_transferencia', $transferenciaVeiculo->data_transferencia ?? '') }}" />
                </div>

                <div class="flex justify-left items-center mb-4">
                    <button type="button" onclick="adicionarHistoricoTransferencia()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Adicionar Histórico
                    </button>
                </div>

                <!-- Campo hidden para armazenar os históricos de transferência -->
                <input type="hidden" name="historicos_transferencia" id="historicos_transferencia_json"
                    value="{{ isset($transferencias) ? json_encode($transferencias) : '[]' }}">
                @php

                    $actionIcons = [
                        "icon:pencil | click:redirect('/user/{id}')",
                        "icon:trash | color:red | click:deleteUser({id}')",
                    ];

                    $transferencias = $transferencias ?? null;
                    $kmComodato = $kmComodato ?? null;
                @endphp

                <div id="tabela-transferencia">

                    <x-bladewind::table layout="custom" :paginated="true" :actionIcons="$actionIcons" :data="$transferencias">

                        <x-slot:header>
                            <th>Data Inclusão</th>
                            <th>Data Alteração</th>
                            <th>Data Transfêrencia</th>
                            <th>Filial Origem</th>
                            <th>Flial Destino</th>
                            <th>KM Transferência</th>
                            <th>Checklist</th>
                            <th>Ações</th>
                        </x-slot:header>

                        <tbody id="tabelaTransferenciaBody" class="bg-white divide-y divide-gray-200">
                            {{-- Carregado via JS --}}
                        </tbody>

                    </x-bladewind::table>
                </div>
            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="km_comodato">
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

                    <x-bladewind::input name="data_realizacao" type="date" label="Data Realização" numeric="true"
                        min="0"
                        selected_value="{{ old('data_realizacao', $kmComotado->data_realizacao ?? '') }}" />

                    <x-bladewind::input name="km_realizacao" type="number" label="KM Realização"
                        selected_value="{{ old('km_realizacao', $kmComotado->km_realizacao ?? '') }}" />

                    <x-bladewind::input name="horimetro" type="number" label="Horimetro"
                        selected_value="{{ old('horimetro', $kmComotado->horimetro ?? '') }}" />
                </div>

                <div class="flex justify-left items-center mb-4">
                    <button type="button" onclick="adicionarHistoricoComodatoKm()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Adicionar KM
                    </button>
                </div>

                <!-- Campo hidden para armazenar os históricos -->
                <input type="hidden" name="historicosKm" id="historicos_json_km"
                    value="{{ isset($kmComodato) ? json_encode($kmComodato) : '[]' }}">

                <div id="tabela-transferencia">

                    <x-bladewind::table layout="custom" :paginated="true">

                        <x-slot:header>
                            <th>Data Inclusão</th>
                            <th>Data Alteração</th>
                            <th>Data Transfêrencia</th>
                            <th>Km Realizado</th>
                            <th>Horimetro</th>
                            <th>Ações</th>
                        </x-slot:header>

                        <tbody id="tabelaHistoricoBodyKm" class="bg-white divide-y divide-gray-200">
                            {{-- Carregado via JS --}}
                        </tbody>

                    </x-bladewind::table>
                </div>


            </x-bladewind::tab-content>
            <x-bladewind::tab-content name="pneus_aplicados">

                <!-- Mensagem quando não há pneus aplicados -->
                <div id="mensagemSemPneus" class="hidden bg-blue-50 border-l-4 border-blue-400 p-6 mb-6 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-lg font-medium text-blue-800">Nenhum pneu aplicado neste veículo</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Este veículo ainda não possui pneus aplicados no sistema.</p>
                                <p class="mt-1">Para aplicar pneus, utilize o módulo de gestão de pneus.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="mostarDiv" class="flex flex-col items-center justify-center p-4">

                    <!-- Cabeçalho informativo -->
                    <div id="headerPneus" class="w-full max-w-4xl mb-4 hidden">
                        <div
                            class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4 shadow-sm border border-indigo-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">Pneus Aplicados</h3>
                                        <p class="text-sm text-gray-600">Visualização das posições dos pneus no veículo
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-lg">
                                        <span class="text-2xl font-bold text-white" id="contadorPneus">0</span>
                                        <span class="ml-2 text-sm text-indigo-100">pneu(s)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Container Principal -->
                    <div class="flex flex-col items-center justify-center w-full max-w-4xl">

                        <!-- SVG Responsivo -->
                        <div class="flex-shrink-0 text-center mb-4">
                            <svg id="caminhao" class="w-full h-auto max-w-md mx-auto min-w-[700px] min-h-[100px]"
                                viewBox="0 0 500 600"></svg>
                        </div>

                        <!-- Rodapé/Legenda compacta -->
                        <div class="w-full max-w-lg mx-auto px-4 py-3 bg-gray-50 rounded-lg text-center">
                            <div class="inline-flex flex-wrap justify-center items-center gap-x-4 gap-y-2 text-xs">
                                <span class="font-medium text-gray-500 mr-2">Legenda:</span>
                                <span class="flex items-center"><span
                                        class="inline-block w-3 h-3 bg-black mr-1"></span>
                                    >24mm</span>
                                <span class="flex items-center"><span
                                        class="inline-block w-3 h-3 bg-green-500 mr-1"></span> 21-24mm</span>
                                <span class="flex items-center"><span
                                        class="inline-block w-3 h-3 bg-blue-500 mr-1"></span> 16-20mm</span>
                                <span class="flex items-center"><span
                                        class="inline-block w-3 h-3 bg-yellow-400 mr-1"></span> 10-15mm</span>
                                <span class="flex items-center"><span
                                        class="inline-block w-3 h-3 bg-red-500 mr-1"></span> 0-9mm</span>
                                <span class="flex items-center"><span
                                        class="inline-block w-3 h-3 bg-gray-300 mr-1"></span> Sem dados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-bladewind::tab-content>
            {{-- </x-bladewind::tab-content> --}}


        </x-bladewind::tab-body>
    </x-bladewind::tab-group>

    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">

        <!-- Botão principal -->
        @if (!$isCreate)
            <button type="button" id="open-modal"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Baixar Veículo
            </button>
        @endif

        <!-- Modal de confirmação (escondido por padrão) -->
        <div id="confirmation-modal"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Confirmação</h2>
                <p class="text-gray-600 mb-6">Tem certeza de que deseja dar baixa no veículo?</p>
                <div class="flex justify-end space-x-4">
                    <!-- Botão de cancelar -->
                    <button type="button" id="cancel-button"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none">
                        Cancelar
                    </button>
                    <!-- Botão de confirmar -->
                    <button type="button" id="confirm-button"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.veiculos.index') }}"
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
        function toggleFornecedor() {
            const selected = document.querySelector('input[name="is_terceiro"]:checked');
            if (selected && selected.value === "true") {
                document.getElementById('fornecedor').classList.remove('hidden');
            } else {
                document.getElementById('fornecedor').classList.add('hidden');
            }
        }

        // Executa ao carregar a página
        document.addEventListener('DOMContentLoaded', () => {
            toggleFornecedor();

            // Adiciona o evento de mudança nos radio buttons
            document.querySelectorAll('input[name="is_terceiro"]').forEach((el) => {
                el.addEventListener('change', toggleFornecedor);
            });
        });
    </script>
    @include('admin.veiculos._scripts')
@endpush
