@php
use Illuminate\Support\Facades\Storage;
@endphp

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="multasForm()">
                <form id="multasForm" method="POST" action="{{ $action }}" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf
                    @if (isset($multas))
                    @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados da Multa</h3>

                        <!-- Linha 1 -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Placa - Convertido para smart-select -->
                            <div>
                                <x-forms.smart-select name="id_veiculo" label="Placa"
                                    placeholder="Selecione o veículo..." :options="$placasData"
                                    :selected="isset($multas) ? $multas->id_veiculo : null" required="true"
                                    asyncSearch="true" searchUrl="{{ route('admin.veiculos.search') }}"
                                    minSearchLength="2" onSelectCallback="atualizarDadosVeiculo" />
                            </div>

                            <!-- Departamento -->
                            <div>
                                <label for="departamento"
                                    class="block text-sm font-medium text-gray-700">Departamento</label>
                                <input type="text" id="departamento" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-bind:value="departamento">
                                <input type="hidden" id="hidden_departamento" name="id_departamento"
                                    x-bind:value="idDepartamento">
                            </div>

                            <!-- Filial -->
                            <div>
                                <label for="filial" class="block text-sm font-medium text-gray-700">Filial</label>
                                <input type="text" id="filial" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-bind:value="filial">
                                <input type="hidden" id="hidden_filial" name="id_filial" x-bind:value="idFilial">
                            </div>
                        </div>

                        <!-- Linha 2 -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Locação do Veículo -->
                            <div>
                                <label for="locacao" class="block text-sm font-medium text-gray-700">Locação do
                                    Veículo</label>
                                <input type="text" id="locacao" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-bind:value="locacao">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status_multa" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status_multa" name="status_multa" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    <option value="Em Andamento" {{ isset($multas) && $multas->status_multa == 'Em
                                        Andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="Finalizada" {{ isset($multas) && $multas->status_multa ==
                                        'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                                </select>
                            </div>

                            <!-- Situação -->
                            <div>
                                <label for="situacao" class="block text-sm font-medium text-gray-700">Situação</label>
                                <select id="situacao" name="situacao" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    <option value="Financeiro" {{ isset($multas) && $multas->situacao == 'Financeiro' ?
                                        'selected' : '' }}>Financeiro</option>
                                    <option value="Notificação" {{ isset($multas) && $multas->situacao == 'Notificação'
                                        ? 'selected' : '' }}>Notificação</option>
                                    <option value="Recurso" {{ isset($multas) && $multas->situacao == 'Recurso' ?
                                        'selected' : '' }}>Recurso</option>
                                    <option value="Embarcadora" {{ isset($multas) && $multas->situacao == 'Embarcadora'
                                        ? 'selected' : '' }}>Embarcadora</option>
                                </select>
                            </div>
                        </div>

                        <!-- Linha 3 -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Pontuação -->
                            <div>
                                <label for="id_classificacao_multa"
                                    class="block text-sm font-medium text-gray-700">Pontuação</label>
                                <select id="id_classificacao_multa" name="id_classificacao_multa" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($classificacaoMultaData as $classificacao)
                                    <option value="{{ $classificacao['value'] }}" {{ isset($multas) && $multas->
                                        id_classificacao_multa == $classificacao['value'] ? 'selected' : '' }}>
                                        {{ $classificacao['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Órgão -->
                            <div>
                                <label for="id_orgao" class="block text-sm font-medium text-gray-700">Órgão</label>
                                <select id="id_orgao" name="id_orgao" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($tipoOrgaoSinistro as $orgao)
                                    <option value="{{ $orgao['value'] }}" {{ isset($multas) && $multas->id_orgao ==
                                        $orgao['value'] ? 'selected' : '' }}>
                                        {{ $orgao['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Valor da Multa - Com formatação de moeda -->
                            <div>
                                <label for="valor_multa" class="block text-sm font-medium text-gray-700">Valor da
                                    Multa</label>
                                <input type="text" id="valor_multa" name="valor_multa" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm money-mask"
                                    value="{{ isset($multas) ? number_format($multas->valor_multa, 2, ',', '.') : '' }}">
                            </div>
                        </div>

                        <!-- Linha 4 -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Auto da Infração -->
                            <div>
                                <label for="auto_infracao" class="block text-sm font-medium text-gray-700">Auto da
                                    Infração</label>
                                <input type="text" id="auto_infracao" name="auto_infracao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) ? $multas->auto_infracao : '' }}">
                            </div>

                            <!-- Notificação -->
                            <div>
                                <label for="notificacao"
                                    class="block text-sm font-medium text-gray-700">Notificação</label>
                                <input type="text" id="notificacao" name="notificacao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) ? $multas->notificacao : '' }}">
                            </div>

                            <!-- Quantidade de Parcelas -->
                            <div>
                                <label for="parcelas" class="block text-sm font-medium text-gray-700">Quantidade de
                                    Parcelas</label>
                                <input type="number" id="parcelas" name="parcelas"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) ? $multas->parcelas : '' }}">
                            </div>
                        </div>

                        <!-- Linha 5 -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Data da Infração -->
                            <div>
                                <label for="data_infracao" class="block text-sm font-medium text-gray-700">Data da
                                    Infração</label>
                                <input type="datetime-local" id="data_infracao" name="data_infracao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_infracao ? date('Y-m-d\TH:i', strtotime($multas->data_infracao)) : '' }}">
                            </div>

                            <!-- Vencimento da Multa -->
                            <div>
                                <label for="vencimento_multa" class="block text-sm font-medium text-gray-700">Vencimento
                                    da Multa</label>
                                <input type="datetime-local" id="vencimento_multa" name="vencimento_multa"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->vencimento_multa ? date('Y-m-d\TH:i', strtotime($multas->vencimento_multa)) : '' }}">
                            </div>

                            <!-- Responsabilidade -->
                            <div>
                                <label for="responsabilidade"
                                    class="block text-sm font-medium text-gray-700">Responsabilidade</label>
                                <select id="responsabilidade" name="responsabilidade"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-on:change="toggleResponsabilidadeFields()">
                                    <option value="">Selecione...</option>
                                    <option value="Condutor" {{ isset($multas) && $multas->responsabilidade ==
                                        'Condutor' ? 'selected' : '' }}>Condutor</option>
                                    <option value="Locadora" {{ isset($multas) && $multas->responsabilidade ==
                                        'Locadora' ? 'selected' : '' }}>Locadora</option>
                                    <option value="Falha-Locado" {{ isset($multas) && $multas->responsabilidade ==
                                        'Falha-Locado' ? 'selected' : '' }}>Falha-Locado</option>
                                    <option value="Falha-Veiculo" {{ isset($multas) && $multas->responsabilidade ==
                                        'Falha-Veiculo' ? 'selected' : '' }}>Falha-Veiculo</option>
                                    <option value="Terceiros" {{ isset($multas) && $multas->responsabilidade ==
                                        'Terceiros' ? 'selected' : '' }}>Terceiros</option>
                                    <option value="Outros" {{ isset($multas) && $multas->responsabilidade == 'Outros' ?
                                        'selected' : '' }}>Outros</option>
                                    <option value="Apuração" {{ isset($multas) && $multas->responsabilidade ==
                                        'Apuração' ? 'selected' : '' }}>Apuração</option>
                                    <option value="Carvalima" {{ isset($multas) && $multas->responsabilidade ==
                                        'Carvalima' ? 'selected' : '' }}>Carvalima</option>
                                </select>
                            </div>
                        </div>

                        <!-- Linha 6 - Campos de responsabilidade condicionais -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Condutor -->
                            <div x-show="showCondutor">
                                <x-forms.smart-select name="id_condutor" label="Condutor"
                                    placeholder="Selecione o condutor..." :options="$condutorData"
                                    :selected="isset($multas) ? $multas->id_condutor : null" asyncSearch="true"
                                    searchUrl="{{ route('admin.condutores.search') }}" minSearchLength="3" />
                            </div>

                            <!-- Filial Responsável -->
                            <div x-show="showFilialResponsavel">
                                <label for="id_filial_responsaval"
                                    class="block text-sm font-medium text-gray-700">Filial Responsável</label>
                                <select id="id_filial_responsaval" name="id_filial_responsaval"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($filialData as $filial)
                                    <option value="{{ $filial['value'] }}" {{ isset($multas) && $multas->
                                        id_filial_responsaval == $filial['value'] ? 'selected' : '' }}>
                                        {{ $filial['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Departamento Responsável -->
                            <div x-show="showDepartamentoResponsavel">
                                <label for="id_departamento_responsaval"
                                    class="block text-sm font-medium text-gray-700">Departamento Responsável</label>
                                <select id="id_departamento_responsaval" name="id_departamento_responsaval"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($departamentoData as $departamento)
                                    <option value="{{ $departamento['value'] }}" {{ isset($multas) && $multas->
                                        id_departamento_responsaval == $departamento['value'] ? 'selected' : '' }}>
                                        {{ $departamento['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Linha 7 - Campos condicionais para Condutor -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Prazo Identificação Condutor -->
                            <div x-show="showCondutorFields">
                                <label for="data_prazo_ident" class="block text-sm font-medium text-gray-700">Prazo
                                    Identificação Condutor</label>
                                <input type="datetime-local" id="data_prazo_ident" name="data_prazo_ident"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_prazo_ident ? date('Y-m-d\TH:i', strtotime($multas->data_prazo_ident)) : '' }}">
                            </div>

                            <!-- Pendente Assinatura Condutor -->
                            <div x-show="showCondutorFields">
                                <label class="block text-sm font-medium text-gray-700">Pendente Assinatura
                                    Condutor</label>
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="is_assinado" value="1" class="form-radio" {{
                                            isset($multas) && isset($multas->is_assinado) && $multas->is_assinado == 1 ?
                                        'checked' : '' }}>
                                        <span class="ml-2">Sim</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="is_assinado" value="0" class="form-radio" {{
                                            isset($multas) && isset($multas->is_assinado) && $multas->is_assinado == 0 ?
                                        'checked' : (isset($multas) && !isset($multas->is_assinado) ? 'checked' :
                                        (!isset($multas) ? 'checked' : '')) }}>
                                        <span class="ml-2">Não</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Debitar do Condutor -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Debitar do Condutor?</label>
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="debitar_condutor" value="1" class="form-radio" {{
                                            isset($multas) && isset($multas->debitar_condutor) &&
                                        $multas->debitar_condutor == 1 ? 'checked' : '' }}>
                                        <span class="ml-2">Sim</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="debitar_condutor" value="0" class="form-radio" {{
                                            isset($multas) && isset($multas->debitar_condutor) &&
                                        $multas->debitar_condutor == 0 ? 'checked' : (isset($multas) &&
                                        !isset($multas->debitar_condutor) ? 'checked' : (!isset($multas) ? 'checked' :
                                        '')) }}>
                                        <span class="ml-2">Não</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Linha 8 - Assinatura Condutor (MODIFICADO) -->
                        <div class="mt-4" x-show="showCondutorFields">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assinatura Condutor</label>
                            <div class="border-2 border-gray-300 rounded-md relative">
                                <div class="absolute inset-0 flex items-center justify-center" id="signature-loading"
                                    style="background: rgba(255,255,255,0.8); z-index: 5; display: none;">
                                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="ml-2 text-sm text-indigo-500">Carregando assinatura...</span>
                                </div>
                                <canvas id="assinaturaPad" class="w-full h-40 bg-white"></canvas>
                            </div>
                            <div class="flex justify-end mt-2 space-x-2">
                                <button type="button" id="clear-button"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Limpar
                                </button>
                            </div>
                            <input type="hidden" id="signature-data" name="assinatura">
                            <input type="hidden" id="existing-signature-url"
                                value="{{ isset($multas) && $multas->assinatura ? Storage::url($multas->assinatura) : '' }}">
                            <p class="mt-1 text-xs text-gray-500">Desenhe a assinatura do condutor no campo acima.
                                Clique em "Limpar" para começar novamente.</p>
                        </div>

                        <!-- Linha 9 - Tratativa e Localização -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <!-- Localização da ocorrência -->
                            <div>
                                <label for="localizacao" class="block text-sm font-medium text-gray-700">Localização da
                                    Ocorrência</label>
                                <textarea id="localizacao" name="localizacao" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ isset($multas) ? $multas->localizacao : '' }}</textarea>
                            </div>

                            <!-- Tratativa -->
                            <div>
                                <label for="descricao" class="block text-sm font-medium text-gray-700">Tratativa</label>
                                <textarea id="descricao" name="descricao" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ isset($multas) ? $multas->descricao : '' }}</textarea>
                            </div>
                        </div>

                        <!-- Linha 10 - Município e Upload -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Município-->
                            <div>
                                <x-forms.smart-select name="id_municipio" label="Município"
                                    placeholder="Selecione o município..." :options="$municipiosData"
                                    :searchUrl="route('admin.municipios.search')"
                                    :selected="old('id_municipio', $multas->id_municipio ?? '')" asyncSearch="true"
                                    minSearchLength="2" />
                            </div>

                            <!-- Arquivo da Multa -->
                            <div>
                                <label for="arquivo_multa" class="block text-sm font-medium text-gray-700">Arquivo da
                                    Multa</label>
                                <input type="file" id="arquivo_multa" name="arquivo_multa"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @if(isset($multas) && $multas->aquivo_multa)
                                <p class="mt-1 text-sm text-gray-500">Arquivo atual:
                                    <a href="{{ Storage::url($multas->aquivo_multa) }}" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800">
                                        Visualizar arquivo
                                    </a>
                                </p>
                                @endif
                            </div>

                            <!-- Arquivo Boleto -->
                            <div>
                                <label for="arquivo_boleto" class="block text-sm font-medium text-gray-700">Arquivo
                                    Boleto</label>
                                <input type="file" id="arquivo_boleto" name="arquivo_boleto"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @if(isset($multas) && $multas->arquivo_boleto)
                                <p class="mt-1 text-sm text-gray-500">Arquivo atual:
                                    <a href="{{ Storage::url($multas->arquivo_boleto) }}" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800">
                                        Visualizar arquivo
                                    </a>
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes da Multa -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Detalhes da Multa</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Notificação Detalhe -->
                            <div>
                                <label for="notificacao_detalhe"
                                    class="block text-sm font-medium text-gray-700">Notificação Detalhe</label>
                                <select id="notificacao_detalhe" x-model="notificacaoDetalhe"
                                    x-on:change="toggleDetailFields()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    <option value="Envio para o Financeiro">Envio para o Financeiro</option>
                                    <option value="Envio para o RH">Envio para o RH</option>
                                    <option value="Identificação de Condutor">Identificação de Condutor</option>
                                    <option value="Notificação Recebida">Notificação Recebida</option>
                                    <option value="Pagamento">Pagamento</option>
                                </select>
                            </div>

                            <!-- Prazo Indicação Condutor -->
                            <div>
                                <label for="prazo_indicacao_condutor"
                                    class="block text-sm font-medium text-gray-700">Indicação do Condutor</label>
                                <input type="text" id="prazo_indicacao_condutor" name="prazo_indicacao_condutor"
                                    x-model="prazoIndicacaoCondutor" :disabled="!enablePrazoIndicacaoCondutor"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Data Envio Financeiro -->
                            <div>
                                <label for="data_envio_financeiro" class="block text-sm font-medium text-gray-700">Data
                                    Envio Financeiro</label>
                                <input type="datetime-local" id="data_envio_financeiro" name="data_envio_financeiro"
                                    x-model="dataEnvioFinanceiro" :disabled="!enableDataEnvioFinanceiro"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_envio_financeiro ? date('Y-m-d\TH:i', strtotime($multas->data_envio_financeiro)) : '' }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Data Pagamento -->
                            <div>
                                <label for="data_pagamento" class="block text-sm font-medium text-gray-700">Data
                                    Pagamento</label>
                                <input type="datetime-local" id="data_pagamento" name="data_pagamento"
                                    x-model="dataPagamento" :disabled="!enableDataPagamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_pagamento ? date('Y-m-d\TH:i', strtotime($multas->data_pagamento)) : '' }}">
                            </div>

                            <!-- Data Recebimento Notificação -->
                            <div>
                                <label for="data_recebimento_notificacao"
                                    class="block text-sm font-medium text-gray-700">Data Recebimento Notificação</label>
                                <input type="datetime-local" id="data_recebimento_notificacao"
                                    name="data_recebimento_notificacao" x-model="dataRecebimentoNotificacao"
                                    :disabled="!enableDataRecebimentoNotificacao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_recebimento_notificacao ? date('Y-m-d\TH:i', strtotime($multas->data_recebimento_notificacao)) : '' }}">
                            </div>

                            <!-- Data Envio Departamento -->
                            <div>
                                <label for="data_envio_departamento"
                                    class="block text-sm font-medium text-gray-700">Data Envio Departamento</label>
                                <input type="datetime-local" id="data_envio_departamento" name="data_envio_departamento"
                                    x-model="dataEnvioDepartamento" :disabled="!enableDataEnvioDepartamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_envio_departamento ? date('Y-m-d\TH:i', strtotime($multas->data_envio_departamento)) : '' }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <!-- Data Indeferimento Recurso -->
                            <div>
                                <label for="data_indeferimento_recurso"
                                    class="block text-sm font-medium text-gray-700">Data Indeferimento Recurso</label>
                                <input type="datetime-local" id="data_indeferimento_recurso"
                                    name="data_indeferimento_recurso" x-model="dataIndeferimentoRecurso"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_indeferimento_recurso ? date('Y-m-d\TH:i', strtotime($multas->data_indeferimento_recurso)) : '' }}">
                            </div>

                            <!-- Data Início Recurso -->
                            <div>
                                <label for="data_inicio_recurso" class="block text-sm font-medium text-gray-700">Data
                                    Início Recurso</label>
                                <input type="datetime-local" id="data_inicio_recurso" name="data_inicio_recurso"
                                    x-model="dataInicioRecurso"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->data_inicio_recurso ? date('Y-m-d\TH:i', strtotime($multas->data_inicio_recurso)) : '' }}">
                            </div>

                            <!-- Responsável Recurso -->
                            <div>
                                <label for="responsavel_recurso"
                                    class="block text-sm font-medium text-gray-700">Responsável Recurso</label>
                                <input type="text" id="responsavel_recurso" name="responsavel_recurso"
                                    x-model="responsavelRecurso"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($multas) && $multas->responsavel_recurso ? $multas->responsavel_recurso : '' }}">
                            </div>
                        </div>

                        <div class="flex justify-left items-center mt-4">
                            <button type="button" x-on:click="adicionarDetalhe()"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Adicionar
                            </button>
                        </div>

                        <!-- Tabela de Detalhes -->
                        <div class="mt-6">
                            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-700">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                        <tr>
                                            <th scope="col" class="py-3 px-6">Notificação Detalhe</th>
                                            <th scope="col" class="py-3 px-6">Indicação Condutor</th>
                                            <th scope="col" class="py-3 px-6">Envio Financeiro</th>
                                            <th scope="col" class="py-3 px-6">Pagamento</th>
                                            <th scope="col" class="py-3 px-6">Recebimento Notificação</th>
                                            <th scope="col" class="py-3 px-6">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaDetalheMultaBody">
                                        <template x-for="(item, index) in detalhes" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="py-3 px-6"
                                                    x-text="item.data_envio_financeiro ? 'Envio para o Financeiro' : 
                                                                           (item.data_pagamento ? 'Pagamento' : 
                                                                           (item.data_recebimento_notificacao ? 'Notificação Recebida' : 
                                                                           (item.data_envio_departamento ? 'Envio para o RH' : 
                                                                           (item.prazo_indicacao_condutor ? 'Identificação de Condutor' : 'Outro'))))">
                                                </td>
                                                <td class="py-3 px-6" x-text="item.prazo_indicacao_condutor || '-'">
                                                </td>
                                                <td class="py-3 px-6"
                                                    x-text="item.data_envio_financeiro ? formatDate(item.data_envio_financeiro) : '-'">
                                                </td>
                                                <td class="py-3 px-6"
                                                    x-text="item.data_pagamento ? formatDate(item.data_pagamento) : '-'">
                                                </td>
                                                <td class="py-3 px-6"
                                                    x-text="item.data_recebimento_notificacao ? formatDate(item.data_recebimento_notificacao) : '-'">
                                                </td>
                                                <td class="py-3 px-6">
                                                    <div class="flex items-center space-x-2">
                                                        <button type="button" x-on:click="editarDetalhe(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" x-on:click="removerDetalhe(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="detalhes.length === 0" class="bg-white border-b">
                                            <td colspan="6" class="py-3 px-6 text-center text-gray-500">Nenhum detalhe
                                                adicionado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Campo Hidden para armazenar os detalhes -->
                        <input type="hidden" name="detalheMultainput" id="detalheMulta_json"
                            x-model="JSON.stringify(detalhes)">
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('admin.multas.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>

                        <button type="submit" id="submit-button"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ isset($multas) ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.7/dist/inputmask.min.js"></script>
<script src="{{ asset('js/multas/signature-pad.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Aplicar máscaras aos campos monetários
        Inputmask("currency", {
            radixPoint: ",",
            groupSeparator: ".",
            digits: 2,
            autoGroup: true,
            prefix: 'R$ ',
            suffix: '',
            placeholder: "0,00"
        }).mask(document.querySelectorAll(".money-mask"));
    });

    function multasForm() {
        return {
            departamento: "{{ isset($multas) && $multas->veiculo && $multas->veiculo->departamentoVeiculo ? $multas->veiculo->departamentoVeiculo->descricao_departamento : 'Selecionar uma placa...' }}",
            filial: "{{ isset($multas) && $multas->veiculo && $multas->veiculo->filial ? $multas->veiculo->filial->name : 'Selecionar uma placa...' }}",
            locacao: "{{ isset($multas) && $multas->veiculo && $multas->veiculo->baseVeiculo ? $multas->veiculo->baseVeiculo->descricao_base : 'Selecionar uma placa...' }}",
            idDepartamento: "{{ isset($multas) ? $multas->id_departamento : '' }}",
            idFilial: "{{ isset($multas) ? $multas->id_filial : '' }}",
            
            // Status dos campos de responsabilidade
            showCondutor: {{ isset($multas) && $multas->responsabilidade == 'Condutor' ? 'true' : 'false' }},
            showFilialResponsavel: {{ isset($multas) && $multas->responsabilidade == 'Carvalima' ? 'true' : 'false' }},
            showDepartamentoResponsavel: {{ isset($multas) && $multas->responsabilidade == 'Carvalima' ? 'true' : 'false' }},
            showCondutorFields: {{ isset($multas) && $multas->responsabilidade == 'Condutor' ? 'true' : 'false' }},
            
            // Campos de detalhe
            notificacaoDetalhe: '',
            prazoIndicacaoCondutor: '',
            dataEnvioFinanceiro: '',
            dataPagamento: '',
            dataRecebimentoNotificacao: '',
            dataEnvioDepartamento: '',
            dataIndeferimentoRecurso: '',
            dataInicioRecurso: '',
            responsavelRecurso: '',
            
            // Estados de habilitação dos campos
            enablePrazoIndicacaoCondutor: false,
            enableDataEnvioFinanceiro: false,
            enableDataPagamento: false,
            enableDataRecebimentoNotificacao: false,
            enableDataEnvioDepartamento: false,
            
            // Lista de detalhes - inicializada com os dados existentes
            detalhes: @json(isset($detalheMultaTab) ? $detalheMultaTab : []),
            
            // Índice para edição
            editIndex: -1,
            
            init() {
                // Inicializar a interface com delay para não impactar o carregamento inicial
                setTimeout(() => this.initializeInterface(), 100);
                
                // Adicionar listener para eventos de seleção do smart-select
                window.addEventListener('id_veiculo:selected', (event) => {
                    if (event.detail && event.detail.value) {
                        this.atualizarDadosVeiculo(event.detail.value);
                    }
                });
            },
            
            initializeInterface() {
                this.updateFormVisibility();
            },
            
            atualizarDadosVeiculo(id) {
                // Mostrar valores temporários enquanto carrega
                this.departamento = 'Carregando...';
                this.filial = 'Carregando...';
                this.locacao = 'Carregando...';
                
                fetch('/admin/multas/get-vehicle-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ placa: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        // Garantir que estamos atribuindo strings
                        this.departamento = String(data.departamento || '');
                        this.filial = String(data.filial || '');
                        this.locacao = String(data.locacao || '');
                        this.idDepartamento = String(data.id_departamento || '');
                        this.idFilial = String(data.id_filial || '');
                        console.log('Dados atualizados:', this.departamento, this.filial, this.locacao);
                    } else {
                        console.error('Erro ao buscar dados do veículo:', data.error);
                        this.departamento = 'Erro ao buscar dados';
                        this.filial = 'Erro ao buscar dados';
                        this.locacao = 'Erro ao buscar dados';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar dados do veículo:', error);
                    this.departamento = 'Erro ao buscar dados';
                    this.filial = 'Erro ao buscar dados';
                    this.locacao = 'Erro ao buscar dados';
                });
            },
            
            toggleResponsabilidadeFields() {
                this.updateFormVisibility();
                
                // Evento personalizado para notificar mudança na visibilidade do pad de assinatura
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('responsabilidadeChanged', {
                        detail: { showCondutorFields: this.showCondutorFields }
                    }));
                }, 100);
            },
            
            updateFormVisibility() {
                const responsabilidade = document.getElementById('responsabilidade').value;
                
                // Resetar todos os campos
                this.showCondutor = false;
                this.showFilialResponsavel = false;
                this.showDepartamentoResponsavel = false;
                this.showCondutorFields = false;
                
                // Definir visibilidade com base na responsabilidade
                if (responsabilidade === 'Condutor') {
                    this.showCondutor = true;
                    this.showCondutorFields = true;
                } else if (responsabilidade === 'Carvalima') {
                    this.showFilialResponsavel = true;
                    this.showDepartamentoResponsavel = true;
                }
            },
            
            toggleDetailFields() {
                // Resetar todos os campos
                this.enablePrazoIndicacaoCondutor = false;
                this.enableDataEnvioFinanceiro = false;
                this.enableDataPagamento = false;
                this.enableDataRecebimentoNotificacao = false;
                this.enableDataEnvioDepartamento = false;
                
                // Habilitar campos específicos com base na notificação selecionada
                switch (this.notificacaoDetalhe) {
                    case 'Identificação de Condutor':
                        this.enablePrazoIndicacaoCondutor = true;
                        break;
                    case 'Envio para o Financeiro':
                        this.enableDataEnvioFinanceiro = true;
                        break;
                    case 'Pagamento':
                        this.enableDataPagamento = true;
                        break;
                    case 'Notificação Recebida':
                        this.enableDataRecebimentoNotificacao = true;
                        break;
                    case 'Envio para o RH':
                        this.enableDataEnvioDepartamento = true;
                        break;
                }
            },
            
            adicionarDetalhe() {
                // Validações
                if (!this.notificacaoDetalhe) {
                    alert('Selecione um tipo de notificação.');
                    return;
                }
                
                // Verificar se pelo menos um campo está preenchido
                if (!this.prazoIndicacaoCondutor && 
                    !this.dataEnvioFinanceiro && 
                    !this.dataPagamento && 
                    !this.dataRecebimentoNotificacao && 
                    !this.dataEnvioDepartamento && 
                    !this.dataIndeferimentoRecurso && 
                    !this.dataInicioRecurso && 
                    !this.responsavelRecurso) {
                    alert('Preencha pelo menos um campo de detalhe.');
                    return;
                }
                
                const detalhe = {
                    datainclusao: new Date().toISOString(),
                    data_alteracao: new Date().toISOString(),
                    prazo_indicacao_condutor: this.prazoIndicacaoCondutor || null,
                    data_envio_financeiro: this.dataEnvioFinanceiro || null,
                    data_pagamento: this.dataPagamento || null,
                    data_recebimento_notificacao: this.dataRecebimentoNotificacao || null,
                    data_envio_departamento: this.dataEnvioDepartamento || null,
                    data_indeferimento_recurso: this.dataIndeferimentoRecurso || null,
                    data_inicio_recurso: this.dataInicioRecurso || null,
                    responsavel_recurso: this.responsavelRecurso || null
                };
                
                if (this.editIndex >= 0) {
                    // Atualizando item existente
                    this.detalhes[this.editIndex] = detalhe;
                    this.editIndex = -1;
                } else {
                    // Adicionando novo item
                    this.detalhes.push(detalhe);
                }
                
                // Limpar os campos
                this.limparCamposDetalhe();
            },
            
            editarDetalhe(index) {
                const detalhe = this.detalhes[index];
                
                // Determinar o tipo de notificação baseado nos campos preenchidos
                if (detalhe.data_envio_financeiro) {
                    this.notificacaoDetalhe = 'Envio para o Financeiro';
                    this.enableDataEnvioFinanceiro = true;
                } else if (detalhe.data_pagamento) {
                    this.notificacaoDetalhe = 'Pagamento';
                    this.enableDataPagamento = true;
                } else if (detalhe.data_recebimento_notificacao) {
                    this.notificacaoDetalhe = 'Notificação Recebida';
                    this.enableDataRecebimentoNotificacao = true;
                } else if (detalhe.data_envio_departamento) {
                    this.notificacaoDetalhe = 'Envio para o RH';
                    this.enableDataEnvioDepartamento = true;
                } else if (detalhe.prazo_indicacao_condutor) {
                    this.notificacaoDetalhe = 'Identificação de Condutor';
                    this.enablePrazoIndicacaoCondutor = true;
                } else {
                    this.notificacaoDetalhe = '';
                }
                
                this.prazoIndicacaoCondutor = detalhe.prazo_indicacao_condutor || '';
                this.dataEnvioFinanceiro = detalhe.data_envio_financeiro || '';
                this.dataPagamento = detalhe.data_pagamento || '';
                this.dataRecebimentoNotificacao = detalhe.data_recebimento_notificacao || '';
                this.dataEnvioDepartamento = detalhe.data_envio_departamento || '';
                this.dataIndeferimentoRecurso = detalhe.data_indeferimento_recurso || '';
                this.dataInicioRecurso = detalhe.data_inicio_recurso || '';
                this.responsavelRecurso = detalhe.responsavel_recurso || '';
                
                this.editIndex = index;
            },
            
            removerDetalhe(index) {
                if (confirm('Tem certeza que deseja remover este detalhe?')) {
                    this.detalhes.splice(index, 1);
                    
                    if (this.editIndex === index) {
                        this.limparCamposDetalhe();
                        this.editIndex = -1;
                    }
                }
            },
            
            limparCamposDetalhe() {
                this.notificacaoDetalhe = '';
                this.prazoIndicacaoCondutor = '';
                this.dataEnvioFinanceiro = '';
                this.dataPagamento = '';
                this.dataRecebimentoNotificacao = '';
                this.dataEnvioDepartamento = '';
                this.dataIndeferimentoRecurso = '';
                this.dataInicioRecurso = '';
                this.responsavelRecurso = '';
                
                this.enablePrazoIndicacaoCondutor = false;
                this.enableDataEnvioFinanceiro = false;
                this.enableDataPagamento = false;
                this.enableDataRecebimentoNotificacao = false;
                this.enableDataEnvioDepartamento = false;
            },
            
            formatDate(dateString) {
                if (!dateString) return '';
                try {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('pt-BR', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    console.error('Erro ao formatar data:', e);
                    return dateString;
                }
            }
        };
    }
</script>
@endpush