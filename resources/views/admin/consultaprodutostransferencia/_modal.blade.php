{{-- 🔹 Dados principais --}}
<h3 class="text-lg font-semibold mt-4 mb-2">Dados principais</h3>
<div class="grid grid-cols-3 gap-6 mb-6">
    <x-forms.input name="id_veiculo" label="Cód. Veiculo:" value="{{ $veiculo->id_veiculo}}" readonly />
    <x-forms.input name="placa" label="Placa:" value="{{ $veiculo->placa}}" readonly />
    <x-forms.input name="id_municipio" label="Município" value="{{ $veiculo->municipioVeiculo->nome_municipio}}"
        readonly />
    <x-forms.input name="id_filial" label="Filial:" value="{{ $veiculo->filial->name}}" readonly />
    <x-forms.input name="id_fornecedor" label="Fornecedor:" value="{{ $veiculo->id_fornecedor}}" readonly />
</div>

{{-- 🔹 Características --}}
<h3 class="text-lg font-semibold mt-4 mb-2">Características</h3>
<div class="grid grid-cols-3 gap-6 mb-6">
    <x-forms.input name="departamento" label="Departamento:"
        value="{{ $veiculo->departamentoVeiculo->descricao_departamento}}" readonly />
    <x-forms.input name="tipo_combustivel" label="Tipo Combustível:"
        value="{{ $veiculo->combustivelVeiculo->descricao}}" readonly />
    <x-forms.input name="categoria" label="Categoria:" value="{{ $veiculo->categoriaVeiculo->descricao_categoria}}"
        readonly />
    <x-forms.input name="cor_veiculo" label="Cor:" value="{{ $veiculo->cor_veiculo}}" readonly />
    <x-forms.input name="marca_veiculo" label="Marca Veículo:" value="{{ $veiculo->marca_veiculo}}" readonly />
    <x-forms.input name="modelo_veiculo" label="Modelo:" value="{{ $veiculo->modeloVeiculo->descricao_modelo_veiculo}}"
        readonly />
</div>

{{-- 🔹 Identificação --}}
<h3 class="text-lg font-semibold mt-4 mb-2">Identificação</h3>
<div class="grid grid-cols-3 gap-6 mb-6">
    <x-forms.input name="chassi" label="Chassi:" value="{{ $veiculo->chassi}}" readonly />
    <x-forms.input name="renavam" label="Renavam:" value="{{ $veiculo->renavam}}" readonly />
    <x-forms.input name="ano_fabricacao" label="Ano de Fabricação:" value="{{ $veiculo->ano_fabricacao}}" readonly />
    <x-forms.input name="ano_modelo" label="Ano Modelo:" value="{{ $veiculo->ano_modelo}}" readonly />
    <x-forms.input type="date" name="data_compra" label="Data Compra:" value="{{ $veiculo->data_compra}}" readonly />
</div>

{{-- 🔹 Informações técnicas --}}
<h3 class="text-lg font-semibold mt-4 mb-2">Informações técnicas</h3>
<div class="grid grid-cols-3 gap-6 mb-6">
    <x-forms.input name="km_inicial" label="KM Inicial:" value="{{ $veiculo->km_inicial}}" readonly />
    <x-forms.input name="horas_iniciais" label="Hora Inicial:" value="{{ $veiculo->horas_iniciais}}" readonly />
    <x-forms.input name="valor_venal" label="Valor Venal:" value="{{ $veiculo->valor_venal}}" readonly />
    <x-forms.input name="capacidade_tanque_principal" label="Capacidade Tanque Principal:"
        value="{{ $veiculo->capacidade_tanque_principal}}" readonly />
    <x-forms.input name="capacidade_tanque_secundario" label="Capacidade Tanque Secundário:"
        value="{{ $veiculo->capacidade_tanque_secundario}}" readonly />
    <x-forms.input name="capacidade_arla" label="Capacidade Arla:" value="{{ $veiculo->capacidade_arla}}" readonly />
</div>

{{-- 🔹 Controle --}}
<h3 class="text-lg font-semibold mt-4 mb-2">Controle</h3>
<div class="grid grid-cols-3 gap-6 mb-6">
    <x-forms.input name="is_terceiro" label="Veículo de Terceiro:" value="{{ $veiculo->is_terceiro}}" readonly />
    <x-forms.input name="is_possui_tracao" label="Possui Tração:" value="{{ $veiculo->is_possui_tracao}}" readonly />
    <x-forms.input name="is_marcador_quilometragem" label="Possui Marcador de KM:"
        value="{{ $veiculo->is_marcador_quilometragem}}" readonly />
    <x-forms.input name="is_horas" label="Possui Marcador de Horas:" value="{{ $veiculo->is_horas}}" readonly />
</div>

{{-- 🔹 Cadastro --}}
<h3 class="text-lg font-semibold mt-4 mb-2">Cadastro</h3>
<div class="grid grid-cols-3 gap-6">
    <x-forms.input type="date" name="data_inclusao" label="Data Inclusão:" value="{{ $veiculo->data_inclusao}}"
        readonly />
    <x-forms.input type="date" name="data_alteracao" label="Data Alteração:" value="{{ $veiculo->data_alteracao}}"
        readonly />
    <x-forms.input name="id_base_veiculo" label="Base:" value="{{ $veiculo->baseVeiculo->descricao_base}}" readonly />
    <x-forms.input name="contrato_manutencao" label="Contrato de Manutenção:" value="{{ $veiculo->contrato_manutencao}}"
        readonly />
    <x-forms.input name="id_sascar" label="Cód. Sascar:" value="{{ $veiculo->id_sascar}}" readonly />
    <x-forms.input name="tipo_equipamento" label="Tipo Equipamento:"
        value="{{ $veiculo->tipoEquipamento->descricao_tipo ?? ''}}" readonly />
</div>