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

    {{-- Comentário apenas pra forçar o merge --}}

    <h3 class="text-lg font-medium text-gray-900 mb-4">Sinistro</h3>

    <x-bladewind::tab-group name="sinistros-tab">
        <x-slot name="headings">
            <x-bladewind::tab-heading name="dados_sinistro" active="true" icon="truck" label="Dados Sinistro"
                icon_type="outline" />
            <x-bladewind::tab-heading name="dados_do_processo" label="Dados do Processo" icon="square-3-stack-3d"
                icon_type="outline" />
            <x-bladewind::tab-heading name="documentos" label="Documentos" icon="document-text" icon_type="outline" />
            <x-bladewind::tab-heading name="dados_dos_envolvidos" label="Dados dos Envolvidos" icon="document-text"
                icon_type="outline" />
        </x-slot>

        <x-bladewind::tab-body>

            <x-bladewind::tab-content name="dados_sinistro" active="true">
                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações do Sinistro</h3>
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

                    <div class="col-span-1">
                        <select name="id_veiculo" id="placa_select" onchange="updateCategoria(this.value)"
                            class="w-full mb-3 rounded-md flex justify-between text-sm items-center rounded-md bg-white text-slate-600 border-2 border-slate-300/50 hover:border-slate-300
         dark:text-dark-300 dark:border-dark-600 dark:hover:border-dark-500/50 dark:bg-transparent py-[10px] pl-4 pr-2 clickable
          enabled">
                            <option value="">Selecione a Placa</option>
                            @foreach ($formOptions['placas'] as $placa)
                                <option value="{{ $placa['value'] }}">{{ $placa['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-1">
                        <select name="id_categoria_veiculo" id="categoria_select"
                            class="w-full mb-3 rounded-md flex justify-between text-sm items-center rounded-md bg-white text-slate-600 border-2 border-slate-300/50 hover:border-slate-300
         dark:text-dark-300 dark:border-dark-600 dark:hover:border-dark-500/50 dark:bg-transparent py-[10px] pl-4 pr-2 clickable
          enabled">
                            <option value="">Selecione a Categoria</option>
                        </select>
                    </div>

                    @php
                        $statuslist = [
                            ['label' => 'Fechado', 'value' => 'Fechado'],
                            ['label' => 'Em Andamento', 'value' => 'Em Andamento'],
                        ];
                    @endphp

                    <div class="col-span-1">
                        <x-bladewind::select name="situacao_sinistro_processo" placeholder="Status" searchable="true"
                            label="Status" :data="$statuslist"
                            selected_value="{{ old('situacao_sinistro_processo', $sinistro->situacao_sinistro_processo ?? '') }}" />
                    </div>

                    @php
                        $processolist = [
                            ['label' => 'CGB', 'value' => 'CGB'],
                            ['label' => 'CGR', 'value' => 'CGR'],
                            ['label' => 'FROTA', 'value' => 'FROTA'],
                            ['label' => 'JVE', 'value' => 'JVE'],
                            ['label' => 'RH', 'value' => 'RH'],
                            ['label' => 'ROO', 'value' => 'ROO'],
                            ['label' => 'SAO', 'value' => 'SAO'],
                            ['label' => 'SEGURADORA', 'value' => 'SEGURADORA'],
                            ['label' => 'TERCEIRO', 'value' => 'TERCEIRO'],
                            ['label' => 'TRAFEGO', 'value' => 'TRAFEGO'],
                            ['label' => 'COLETA/ENTREGA', 'value' => 'COLETA/ENTREGA'],
                        ];
                    @endphp

                    <div class="col-span-1">
                        <x-bladewind::select name="setor" placeholder="Processo Alocado" searchable="true"
                            label="Processo Alocado" selected_value="{{ old('setor', $sinistro->setor ?? '') }}"
                            :data="$processolist" />
                    </div>

                    @php
                        $prazoindica = [
                            ['label' => '30', 'value' => '30'],
                            ['label' => '60', 'value' => '60'],
                            ['label' => '90', 'value' => '90'],
                        ];
                    @endphp

                    <div class="col-span-2">
                        <x-bladewind::select name="prazo_em_dias" placeholder="Prazo" searchable="true" label="Prazo"
                            selected_value="{{ old('prazo_em_dias', $sinistro->prazo_em_dias ?? '') }}"
                            :data="$prazoindica" />
                    </div>

                    <div class="col-span-2">
                        <x-bladewind::select name="id_filial" placeholder="Filial" searchable="true" label="Filial"
                            selected_value="{{ old('id_filial', $sinistro->id_filial ?? '') }}" :data="$formOptions['filiais']" />
                    </div>

                    <div class="col-span-full">
                        <x-bladewind::select name="id_motorista" placeholder="Motorista" searchable="true"
                            label="Motorista" selected_value="{{ old('id_motorista', $sinistro->id_motorista ?? '') }}"
                            :data="$formOptions['pessoas']" />
                    </div>

                </div>

                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados da Ocorrência</h3>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 ">

                    <div class="col-span-2">
                        <x-bladewind::select name="id_tipo_ocorrencia" placeholder="Tipo Ocorrência" searchable="true"
                            label="Tipo Ocorrência"
                            selected_value="{{ old('id_tipo_ocorrencia', $sinistro->id_tipo_ocorrencia ?? '') }}"
                            :data="$formOptions['tipoocorrencias']" />
                    </div>

                    <div class="col-span-2">
                        <x-bladewind::select name="id_motivo" placeholder="Motivo Ocorrência" searchable="true"
                            label="Motivo Ocorrência"
                            selected_value="{{ old('id_motivo', $sinistro->id_motivo ?? '') }}" :data="$formOptions['tipomotivos']" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="data_sinistro" type="date" label="Data Ocorrência"
                            selected_value="{{ old('data_sinistro', $sinistro->data_sinistro ?? '') }}"
                            style="--icon-color: black; --icon-opacity: 1;" />
                    </div>

                    <div class="col-span-3">
                        <x-bladewind::textarea name="responsabilidade_sinistro" placeholder="Responsabilidade"
                            label="Responsabilidade"
                            selected_value="{{ old('responsabilidade_sinistro', $sinistro->responsabilidade_sinistro ?? '') }}" />
                    </div>

                </div>


                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados do Local</h3>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center align-items-center">

                    @php
                        $situacaopista = [
                            ['label' => 'seca', 'value' => 'Seca'],
                            ['label' => 'molhada', 'value' => 'Molhada'],
                            ['label' => 'neblina', 'value' => 'Com Neblina'],
                        ];
                    @endphp

                    <div class="col-span-1">
                        <x-bladewind::select name="situacao_pista" placeholder="Situação Pista" searchable="true"
                            label="Situação Pista"
                            selected_value="{{ old('situacao_pista', $sinistro->situacao_pista ?? '') }}"
                            :data="$situacaopista" />
                    </div>

                    @php
                        $estadopista = [
                            ['label' => 'bom', 'value' => 'Bom'],
                            ['label' => 'regular', 'value' => 'Regular'],
                            ['label' => 'ruim', 'value' => 'Ruim'],
                            ['label' => 'obras', 'value' => 'Em Obras'],
                        ];
                    @endphp

                    <div class="col-span-1">
                        <x-bladewind::select name="estados_pista" placeholder="Estado Pista" searchable="true"
                            label="Estado Pista"
                            selected_value="{{ old('estados_pista', $sinistro->estados_pista ?? '') }}"
                            :data="$estadopista" />
                    </div>

                    @php
                        $topografia = [
                            ['label' => 'Reta', 'value' => 'Reta'],
                            ['label' => 'Curva Direita', 'value' => 'Curva Direita'],
                            ['label' => 'Curva Esquerda', 'value' => 'Curva Esquerda'],
                            ['label' => 'Cruzamento', 'value' => 'Cruzamento'],
                            ['label' => 'Ponte', 'value' => 'Ponte'],
                            ['label' => 'Trevo', 'value' => 'Trevo'],
                        ];
                    @endphp

                    <div class="col-span-1">
                        <x-bladewind::select name="topografica" placeholder="Topografia" searchable="true"
                            label="Topografia"
                            selected_value="{{ old('topografica', $sinistro->topografica ?? '') }}"
                            :data="$topografia" />
                    </div>

                    <div class="col-span-1 flex flex-col items-start">
                        <label for="sinalizacao" class="mb-1 text-sm font-medium">Há Sinalização?</label>
                        <div class="flex items-center space-x-4">
                            <x-bladewind::radio-button label="Sim" name="sinalizacao" value="1"
                                checked="{{ old('sinalizacao', $sinistro->sinalizacao ?? '') == '1' ? 'true' : 'false' }}"
                                id="sinalizacao_sim" />
                            <x-bladewind::radio-button label="Não" name="sinalizacao" value="0"
                                checked="{{ old('sinalizacao', $sinistro->sinalizacao ?? '') == '0' ? 'true' : 'false' }}"
                                id="sinalizacao_nao" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <x-bladewind::textarea name="local_ocorrencia" placeholder="Observação do Local da Ocorrência"
                            label="Observação do Local da Ocorrência"
                            selected_value="{{ old('local_ocorrencia', $sinistro->local_ocorrencia ?? '') }}" />
                    </div>

                </div>

                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações da Ocorrência</h3>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center align-items-center">

                    <div class="col-span-full">
                        <x-bladewind::textarea name="descricao_ocorrencia" placeholder="Descrição da Ocorrência"
                            label="Descrição da Ocorrência"
                            selected_value="{{ old('descricao_ocorrencia', $sinistro->descricao_ocorrencia ?? '') }}" />
                    </div>

                    <div class="col-span-full">
                        <x-bladewind::textarea name="observacao_ocorrencia"
                            placeholder="Descrição da Ocorrência pelo Motorista"
                            label="Descrição da Ocorrência pelo Motorista"
                            selected_value="{{ old('observacao_ocorrencia', $sinistro->observacao_ocorrencia ?? '') }}" />
                    </div>

                </div>

                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Histórico do Registro</h3>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center align-items-center">

                    <div class="col-span-2">
                        <x-bladewind::datepicker name="data_evento" placeholder="Data do Evento" />
                    </div>

                    @php
                        $situacao = [
                            ['label' => 'Abertura processo sinistro', 'value' => 'Abertura processo sinistro'],
                            ['label' => 'Vistoria realizada', 'value' => 'Vistoria realizada'],
                            ['label' => 'Fase de orçamento', 'value' => 'Fase de orçamento'],
                            ['label' => 'Orçamento aprovado', 'value' => 'Orçamento aprovado'],
                            ['label' => 'Serviço em andamento', 'value' => 'Serviço em andamento'],
                            ['label' => 'Finalizado', 'value' => 'Finalizado'],
                            ['label' => 'Processo de ressarcimento', 'value' => 'Processo de ressarcimento'],
                            ['label' => 'Em atraso', 'value' => 'Em atraso'],
                            ['label' => 'Envio departamento responsável', 'value' => 'Envio departamento responsável'],
                        ];
                    @endphp
                    <div class="col-span-2">
                        <x-bladewind::select name="descricao_situacao" placeholder="Situação" searchable="true"
                            :data="$situacao" />
                    </div>

                    <div class="col-span-3">
                        <x-bladewind::textarea name="observacao" placeholder="Observação" />
                    </div>

                    <div class="flex justify-center items-center">
                        <button type="button" onclick="adicionarHistorico()"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Adicionar Histórico
                        </button>
                    </div>

                    <!-- Campo hidden para armazenar os históricos -->
                    <input type="hidden" name="historicos" id="historicos_json" value="[]">

                    @php
                        $actionIcons = [
                            'icon:pencil | tip:Editar | click:editSinistro({id})',
                            "icon:trash | tip:Excluir | color:red | click:destroySinistro({id}, '{id}')",
                        ];

                        $column_historico = [
                            'datainclusao' => 'Data Inclusão',
                            'data_alteracao' => 'Data Alteração',
                            'data_evento' => 'Data do Evento',
                            'id_usuario' => 'Usuário',
                            'descricao_situacao' => 'Situação',
                            'observacao' => 'Observação',
                        ];
                    @endphp

                    <div class="col-span-full">
                        <table class="min-w-full divide-y divide-gray-200 tabelaHistorico">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data do Evento
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Situação
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Observação
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
                </div>
            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="dados_do_processo" active="true">
                {{-- <h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações do Sinistro</h3> --}}
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

                    <div class="col-span-2">
                        <x-bladewind::select name="id_tipo_orgao" placeholder="Orgão de Registro" searchable="true"
                            label="Orgão de Registro"
                            selected_value="{{ old('id_tipo_orgao', $sinistro->id_tipo_orgao ?? '') }}"
                            :data="$formOptions['tiposOrgaos']" />
                    </div>

                    <div class="col-span-2">
                        <x-bladewind::input name="numero_processo" placeholder="N° do Processo/B.O"
                            label="N° do Processo/B.O"
                            selected_value="{{ old('numero_processo', $sinistro->numero_processo ?? '') }}" />
                    </div>

                </div>

                <hr class="mt-10 mb-10">

                <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados de Pagamento</h3>

                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 ">

                    <div class="col-span-2">
                        <x-bladewind::input name="valor_apagar" placeholder="Valor a Pagar" class="monetario" />
                    </div>

                    <div class="col-span-2">
                        <x-bladewind::input name="valor_pago" placeholder="Custo Carvalima" class="monetario"
                            label="Custo Carvalima"
                            selected_value="{{ old('valor_pago', $sinistro->valor_pago ?? '') }}" />
                    </div>

                    <div class="col-span-2">
                        <x-bladewind::input name="valorpagoseguradora" placeholder="Valor Pago Seguradora"
                            class="monetario" label="Valor Pago Seguradora"
                            selected_value="{{ old('valorpagoseguradora', $sinistro->valorpagoseguradora ?? '') }}" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="valorpagofrota" placeholder="Custo Colaborador"
                            class="monetario" />
                    </div>

                    <div class="col-span-1">
                        <x-bladewind::input name="valor_pago_terceiro" placeholder="Valor Pago Terceiro"
                            class="monetario" />
                    </div>

                </div>

            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="documentos" active="true">
                {{-- <h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações do Sinistro</h3> --}}
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

                    <div class="col-span-3">
                        <x-bladewind::filepicker name="documento" placeholder="Documento" label="Documento" />
                    </div>

                    <div class="flex justify-center items-center">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ isset($empresa) ? 'Editar' : 'Adicionar' }}
                        </button>
                    </div>

                    @php
                        $actionIcons = [
                            'icon:pencil | tip:Editar | click:editSinistro({id})',
                            "icon:trash | tip:Excluir | color:red | click:destroySinistro({id}, '{id}')",
                        ];

                        $column_historico = [
                            'data_inclusao' => 'Data Inclusão',
                            'data_alteracao' => 'Data Alteração',
                            'documento' => 'Documento',
                        ];
                    @endphp

                    <div class="col-span-full">

                        <x-bladewind::table layout="custom" :paginated="true" page_size="10" :default_page="$default_page = 6">

                            <x-slot:header>
                                <th>Data Inclusão</th>
                                <th>Data Alteração</th>
                                <th>Documento</th>
                            </x-slot:header>

                        </x-bladewind::table>
                    </div>

                </div>

            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="dados_dos_envolvidos" active="true">
                {{-- <h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações do Sinistro</h3> --}}
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

                    <div class="col-span-2">
                        <x-bladewind::input name="nome" placeholder="Nome" />
                    </div>

                    <div class="col-span-2">
                        <x-bladewind::input id="telefone" name="telefone" placeholder="Telefone" />
                    </div>

                    <div class="col-span-2">
                        <x-bladewind::input id="cpf" name="cpf" placeholder="CPF" />
                    </div>

                    <div class="flex justify-center items-center">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ isset($empresa) ? 'Editar' : 'Adicionar' }}
                        </button>
                    </div>

                    @php
                        $actionIcons = [
                            'icon:pencil | tip:Editar | click:editSinistro({id})',
                            "icon:trash | tip:Excluir | color:red | click:destroySinistro({id}, '{id}')",
                        ];

                        $column_historico = [
                            'data_inclusao' => 'Data Inclusão',
                            'nome' => 'Nome',
                            'telefone' => 'Telefone',
                            'cpf' => 'CPF',
                        ];
                    @endphp

                    <div class="col-span-full">

                        <x-bladewind::table layout="custom" :paginated="true" page_size="10" :default_page="$default_page = 6">

                            <x-slot:header>
                                <th>Data Inclusão</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>CPF</th>
                            </x-slot:header>

                        </x-bladewind::table>
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

@push('scripts')
    @vite('resources/js/historico-sinistro.js')

    
@endpush
