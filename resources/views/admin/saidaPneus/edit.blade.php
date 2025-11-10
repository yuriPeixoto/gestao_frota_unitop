<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro Saída de Pneus') }}
            </h2>
        </div>
    </x-slot>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <x-bladewind::notification />

            <form id="form_RequisicaoPneuForm" method="POST"
                action="{{ route('admin.saidaPneus.update', $requisicao->id_requisicao_pneu) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Row 1: Informações Básicas -->
                <div class="grid grid-cols-12 gap-4 mb-4">
                    <!-- Cód. -->
                    <div class="col-span-1">
                        <label for="id_requisicao_pneu"
                            class="block text-sm font-medium text-gray-700 mb-1">Cód.</label>
                        <input type="text" id="id_requisicao_pneu" name="id_requisicao_pneu" readonly
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm"
                            value="{{ $requisicao->id_requisicao_pneu }}">
                    </div>

                    <!-- Usuário Solicitante -->
                    <div class="col-span-2">
                        <label for="id_usuario_solicitante" class="block text-sm font-medium text-gray-700 mb-1">Usuário
                            Solicitante:</label>
                        <input type="text" id="id_usuario_solicitante" readonly
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm"
                            value="{{ $requisicao->usuarioSolicitante->name ?? 'Não encontrado' }}">
                    </div>

                    <!-- Filial -->
                    <div class="col-span-2">
                        <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-1">Filial:</label>
                        <input type="text" id="id_filial" readonly
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm"
                            value="{{ $requisicao->filial->name ?? 'Não encontrada' }}">
                    </div>

                    <!-- Situação -->
                    <div class="col-span-2">
                        <label for="situacao" class="block text-sm font-medium text-gray-700 mb-1">Situação:</label>
                        <input type="text" id="situacao" name="situacao" readonly
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm"
                            value="{{ $requisicao->situacao === 'FINALIZADA' ? 'BAIXADA' : $requisicao->situacao }}">
                    </div>

                    <!-- Transferência Entre Filiais -->
                    <div class="col-span-1">
                        <label for="transferencia_entre_filiais"
                            class="block text-sm font-medium text-gray-700 mb-1">Transferência Entre Filiais:</label>
                        <div class="mt-1 flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="transferencia_entre_filiais" value="1"
                                    {{ $requisicao->transferencia_entre_filiais ? 'checked' : '' }}
                                    id="transferencia_sim" class="form-radio text-indigo-600">
                                <span class="ml-2 text-sm">Sim</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="transferencia_entre_filiais" value="0"
                                    {{ !$requisicao->transferencia_entre_filiais ? 'checked' : '' }}
                                    id="transferencia_nao" class="form-radio text-indigo-600">
                                <span class="ml-2 text-sm">Não</span>
                            </label>
                        </div>
                    </div>

                    <!-- Filial Destino -->
                    <div class="col-span-2" id="filial_destino_container" style="display: none;">
                        <label for="id_filial_destino" class="block text-sm font-medium text-gray-700 mb-1">Filial
                            Destino:</label>
                        <select id="id_filial_destino" name="id_filial_destino"
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach ($filiais as $filial)
                                <option value="{{ $filial->id }}"
                                    {{ old('id_filial_destino', $requisicao->id_filial_destino ?? '') == $filial->id ? 'selected' : '' }}>
                                    {{ $filial->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Terceiro -->
                    <div class="col-span-2">
                        <label for="id_terceiro" class="block text-sm font-medium text-gray-700 mb-1">Terceiro:</label>
                        <select id="id_terceiro" name="id_terceiro" readonly disabled
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm"
                            onchange="onRequisicaoTerceiro()">
                            <option value="">Selecione...</option>
                            @foreach ($terceiros as $terceiro)
                                <option value="{{ $terceiro->id_fornecedor }}"
                                    {{ $requisicao->id_terceiro == $terceiro->id_fornecedor ? 'selected' : '' }}>
                                    {{ $terceiro->nome_fornecedor }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2: Observação Solicitante -->
                <div class="grid grid-cols-12 gap-4 mb-4">
                    <div class="col-span-12">
                        <label for="observacao_solicitante"
                            class="block text-sm font-medium text-gray-700 mb-1">Observação Solicitante:</label>
                        <textarea id="observacao_solicitante" name="observacao_solicitante" rows="3" readonly
                            class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm resize-none">{{ $requisicao->observacao_solicitante }}</textarea>
                    </div>
                </div>

                <!-- Row 3: Justificativa e Observação -->
                <div class="grid grid-cols-12 gap-4 mb-6">
                    <div class="col-span-6">
                        <label for="justificativa_de_finalizacao"
                            class="block text-sm font-medium text-gray-700 mb-1">Justificativa de Finalização:</label>
                        <textarea id="justificativa_de_finalizacao" name="justificativa_de_finalizacao" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('justificativa_de_finalizacao', $requisicao->justificativa_de_finalizacao) }}</textarea>
                    </div>
                    <div class="col-span-6">
                        <label for="observacao" class="block text-sm font-medium text-gray-700 mb-1">Observação:</label>
                        <textarea id="observacao" name="observacao" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('observacao', $requisicao->observacao) }}</textarea>
                    </div>
                </div>

                <!-- Seção: Dados do Pedido -->
                <div class="border-t border-gray-200 pt-6 mb-6">
                    <div class="bg-gray-100 px-4 py-2 mb-4 border-l-4 border-gray-400">
                        <h3 class="text-lg font-medium text-gray-800">Dados do Pedido</h3>
                    </div>

                    <!-- Detail Form para adicionar itens -->
                    <div class="border border-gray-300 rounded-md p-4 mb-4 bg-white">
                        <div class="grid grid-cols-12 gap-4">
                            <!-- Modelo de Pneu Requisitado -->
                            <div class="col-span-3">
                                <label for="requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu"
                                    class="block text-sm font-medium text-gray-700 mb-1">Modelo de Pneu
                                    Requisitado:</label>
                                <select id="requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu"
                                    name="requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    {{ $requisicao->situacao === 'FINALIZADA' ? 'disabled' : '' }}
                                    onchange="carregarPneus(this.value)">
                                    <option value="">Selecione...</option>
                                </select>
                                <input type="hidden"
                                    id="requisicao_pneu_modelos_requisicao_pneu_id_requisicao_pneu_modelos"
                                    name="requisicao_pneu_modelos_requisicao_pneu_id_requisicao_pneu_modelos">
                            </div>

                            <!-- Quantidade -->
                            <div class="col-span-1">
                                <label for="requisicao_pneu_modelos_requisicao_pneu_quantidade"
                                    class="block text-sm font-medium text-gray-700 mb-1">Quantidade:</label>
                                <input type="number" id="requisicao_pneu_modelos_requisicao_pneu_quantidade"
                                    name="requisicao_pneu_modelos_requisicao_pneu_quantidade" readonly
                                    class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm"
                                    min="1">
                            </div>

                            <!-- Pneus Baixados -->
                            <div class="col-span-6">
                                <label for="id_pneus" class="block text-sm font-medium text-gray-700 mb-1">
                                    <span id="pneus_label">Pneus Baixados:</span>
                                </label>
                                <div id="pneus_container"
                                    class="mt-1 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2 bg-white">
                                    <div id="no_pneus_message" class="text-gray-500 text-sm">Selecione um modelo
                                        primeiro</div>
                                </div>
                                <input type="hidden" id="requisicao_pneu_modelos_requisicao_pneu_quantidade_baixa"
                                    name="requisicao_pneu_modelos_requisicao_pneu_quantidade_baixa">
                            </div>

                            <!-- Data Baixa e Botão -->
                            <div class="col-span-2">
                                <label for="requisicao_pneu_modelos_requisicao_pneu_data_baixa"
                                    class="block text-sm font-medium text-gray-700 mb-1">Data Baixa:</label>
                                <input type="date" id="requisicao_pneu_modelos_requisicao_pneu_data_baixa"
                                    name="requisicao_pneu_modelos_requisicao_pneu_data_baixa" readonly
                                    class="mt-1 block w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2 text-sm mb-2"
                                    value="{{ date('Y-m-d') }}">

                                <button type="button" id="button_adicionar_requisicao_pneu_modelos_requisicao_pneu"
                                    onclick="onAddDetailRequisicaoPneuModelosRequisicaoPneu()"
                                    {{ $requisicao->situacao === 'FINALIZADA' ? 'disabled' : '' }}
                                    class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50">
                                    <i class="fas fa-save mr-1 text-blue-300"></i>
                                    <span id="button_text">Adicionar</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Detalhes -->
                    <div class="table-responsive">
                        <table id="requisicao_pneu_modelos_requisicao_pneu_list"
                            class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Cód. Requisição Pneus</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Data Inclusão</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Data Alteração</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Modelo dos Pneus Requisitados</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Quantidade</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Quantidade Baixa</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Data Baixa</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody id="requisicao_pneu_modelos_requisicao_pneu_list_body"
                                class="bg-white divide-y divide-gray-200">
                                @foreach ($requisicao->requisicaoPneuModelos as $modelo)
                                    <tr id="row_{{ $modelo->id_requisicao_pneu_modelos }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">
                                            {{ $modelo->id_requisicao_pneu_modelos }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">
                                            {{ $modelo->data_inclusao ? $modelo->data_inclusao->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">
                                            {{ $modelo->data_alteracao ? $modelo->data_alteracao->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 border">
                                            Cód.{{ $modelo->id_modelo_pneu }} -
                                            {{ $modelo->modeloPneu->descricao_modelo ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">
                                            {{ $modelo->quantidade }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">
                                            {{ $modelo->quantidade_baixa ?? 0 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">
                                            {{ $modelo->data_baixa ? date('d/m/Y', strtotime($modelo->data_baixa)) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">
                                            <div class="flex space-x-2">
                                                @if ($requisicao->situacao !== 'FINALIZADA')
                                                    <button type="button"
                                                        onclick="onEditDetailRequisicaoPneuModelos({{ $modelo->id_requisicao_pneu_modelos }})"
                                                        class="inline-flex items-center px-2 py-1 bg-blue-600 border border-transparent rounded text-xs text-white hover:bg-blue-700">
                                                        <i class="fas fa-check-circle mr-1"></i>Selecionar
                                                    </button>
                                                    <button type="button"
                                                        onclick="onEstorno({{ $modelo->id_requisicao_pneu_modelos }})"
                                                        class="inline-flex items-center px-2 py-1 bg-orange-600 border border-transparent rounded text-xs text-white hover:bg-orange-700">
                                                        <i class="fas fa-caret-square-right mr-1"></i>Estornar
                                                        Requisição
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-between items-center space-x-2 pt-6 border-t border-gray-200">
                    <div class="flex space-x-2">
                        <!-- Voltar -->
                        <a href="{{ route('admin.saidaPneus.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-arrow-left mr-2"></i>Voltar
                        </a>
                    </div>

                    <div class="flex space-x-2">
                        <!-- Salvar -->
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <i class="fas fa-save mr-2"></i>Salvar
                        </button>

                        <!-- Limpar formulário -->
                        <button type="button" onclick="onClear()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <i class="fas fa-eraser mr-2"></i>Limpar formulário
                        </button>

                        <!-- Finalizar Saída de Pneu -->
                        @if ($requisicao->situacao !== 'FINALIZADA')
                            <button type="button" onclick="onFinalizarSaida()"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <i class="fas fa-box mr-2"></i>Finalizar Saída de Pneu
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Variáveis globais para controle de estado
            let pneusDisponiveis = [];
            let currentModeloId = null;
            let currentRequisicaoModeloId = null;
            let isEditMode = false;
            let editingRowId = null;

            // Estado da requisição
            const requisicaoId = {{ $requisicao->id_requisicao_pneu }};
            const requisicaoSituacao = '{{ $requisicao->situacao }}';

            // CSRF Token para requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '{{ csrf_token() }}';

            // Headers padrão para requests
            const defaultHeaders = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            };

            // Função para validar terceiro (replica onRequisicaoTerceiro)
            async function onRequisicaoTerceiro() {
                const idTerceiro = document.getElementById('id_terceiro').value;

                if (idTerceiro) {
                    // Desabilitar transferência entre filiais
                    document.getElementById('transferencia_sim').disabled = true;
                    document.getElementById('transferencia_nao').disabled = true;
                    document.getElementById('transferencia_nao').checked = true;
                } else {
                    // Habilitar transferência entre filiais
                    document.getElementById('transferencia_sim').disabled = false;
                    document.getElementById('transferencia_nao').disabled = false;
                }
            }

            // Função para carregar pneus por modelo (replica carregarPneus)
            async function carregarPneus(modeloId) {
                currentModeloId = modeloId;
                const container = document.getElementById('pneus_container');
                const labelElement = document.getElementById('pneus_label');

                if (!modeloId) {
                    container.innerHTML =
                        '<div id="no_pneus_message" class="text-gray-500 text-sm">Selecione um modelo primeiro</div>';
                    labelElement.textContent = 'Pneus Baixados:';
                    return;
                }

                try {
                    // Mostrar loading
                    container.innerHTML = '<div class="text-blue-500 text-sm">Carregando pneus...</div>';

                    const response = await fetch('{{ route('admin.saidaPneus.ajax.carregar-pneus') }}', {
                        method: 'POST',
                        headers: defaultHeaders,
                        body: JSON.stringify({
                            id_modelo_pneu: modeloId,
                            id_requisicao_pneu_modelos: currentRequisicaoModeloId || 0
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    if (data.sem_estoque) {
                        container.innerHTML = '<div class="text-red-500 text-sm font-bold">Sem estoque</div>';
                        labelElement.innerHTML = 'Pneus Baixados: <span style="font-weight: bold;">Sem estoque</span>';
                        return;
                    }

                    // Criar checkboxes para os pneus
                    renderPneusCheckboxes(data);
                    labelElement.textContent = 'Pneus Baixados:';

                } catch (error) {
                    console.error('Erro ao carregar pneus:', error);
                    container.innerHTML = '<div class="text-red-500 text-sm">Erro ao carregar pneus: ' + error.message +
                        '</div>';
                    labelElement.textContent = 'Pneus Baixados:';
                }
            }

            // Renderizar checkboxes de pneus
            function renderPneusCheckboxes(data) {
                const container = document.getElementById('pneus_container');
                let html = '';

                const modeloSelect = document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu');
                if (modeloSelect) {
                    // Se não existir option com esse value, adiciona uma (marca como selecionada)
                    if (![...modeloSelect.options].some(o => o.value == data.id_modelo)) {
                        const optionLabel = data.descricao_modelo; // ajuste conforme o que o backend retorna
                        const opt = new Option(optionLabel, data.id_modelo, true, true);
                        modeloSelect.add(opt);
                    } else {
                        modeloSelect.value = data.id_modelo;
                    }
                }

                data.pneus.forEach((pneu, index) => {
                    const isDisabled = data.desabilitados.includes(pneu.id);
                    // Verificar se está selecionado tanto na lista geral quanto específica do modelo em edição
                    const isSelectedGeral = data.selecionados.includes(pneu.id);
                    const isSelectedModelo = data.selecionados_modelo && data.selecionados_modelo.includes(pneu.id);
                    const isSelected = isSelectedGeral || isSelectedModelo;
                    const checkboxId = `pneu_${pneu.id}`;

                    // Debug log para acompanhar a marcação
                    if (isSelected && currentRequisicaoModeloId) {
                        console.log(
                            `Pneu ${pneu.id} marcado como selecionado para o modelo ${currentRequisicaoModeloId}`);
                    }

                    html += `
                    <div class="flex items-center mb-1 ${index % 2 === 0 ? 'mr-4' : ''}" style="width: calc(50% - 8px); display: inline-block;">
                        <input type="checkbox"
                               id="${checkboxId}"
                               name="id_pneus[]"
                               value="${pneu.id}"
                               ${isDisabled ? 'disabled' : ''}
                               ${isSelected ? 'checked' : ''}
                               onchange="updateQuantidadeBaixa()"
                               class="form-checkbox text-indigo-600">
                        <label for="${checkboxId}" class="ml-2 text-xs ${isDisabled ? 'text-gray-400' : 'text-gray-700'} cursor-pointer">
                            ${pneu.label}
                        </label>
                        ${isDisabled ? `<input type="hidden" name="id_pneus[]" value="${pneu.id}">` : ''}
                    </div>
                `;
                });

                container.innerHTML = html;
                updateQuantidadeBaixa();
            }

            // Atualizar quantidade baixa
            function updateQuantidadeBaixa() {
                const checkboxes = document.querySelectorAll('input[name="id_pneus[]"]:checked:not([disabled])');
                const hiddenInputs = document.querySelectorAll('input[type="hidden"][name="id_pneus[]"]');
                const quantidadeBaixa = checkboxes.length + hiddenInputs.length;

                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_quantidade_baixa').value = quantidadeBaixa;
                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_quantidade').value = quantidadeBaixa;
            }

            // Adicionar item ao detalhe (replica onAddDetailRequisicaoPneuModelosRequisicaoPneu)
            async function onAddDetailRequisicaoPneuModelosRequisicaoPneu() {
                const modeloId = document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu').value;
                const quantidade = document.getElementById('requisicao_pneu_modelos_requisicao_pneu_quantidade').value;
                const checkboxes = document.querySelectorAll('input[name="id_pneus[]"]:checked:not([disabled])');
                const hiddenInputs = document.querySelectorAll('input[type="hidden"][name="id_pneus[]"]');

                const pneusSelecionados = [
                    ...Array.from(checkboxes).map(cb => cb.value),
                    ...Array.from(hiddenInputs).map(input => input.value)
                ];

                // Validações do legado
                if (!modeloId) {
                    alert('Selecione um modelo para continuar.');
                    return;
                }

                if (pneusSelecionados.length === 0) {
                    alert('Selecione ao menos um número de fogo para continuar.');
                    return;
                }

                if (quantidade && pneusSelecionados.length > parseInt(quantidade)) {
                    alert('A quantidade selecionada não pode ser maior que a solicitada');
                    return;
                }

                // Capturar o ID do modelo se estiver em modo de edição
                const idRequisicaoModelo = isEditMode ?
                    document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_requisicao_pneu_modelos').value :
                    null;

                try {
                    const response = await fetch('{{ route('admin.saidaPneus.ajax.adicionar-item') }}', {
                        method: 'POST',
                        headers: defaultHeaders,
                        body: JSON.stringify({
                            id_requisicao_pneu: requisicaoId,
                            id_modelo_pneu: modeloId,
                            quantidade: quantidade || pneusSelecionados.length,
                            pneus_selecionados: pneusSelecionados,
                            id_requisicao_pneu_modelos: idRequisicaoModelo, // Enviar ID se for edição
                            is_edit_mode: isEditMode // Flag para indicar se é edição
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        const successMessage = isEditMode ? 'Item editado com sucesso!' : 'Item adicionado com sucesso!';
                        alert(successMessage);

                        // Resetar modo de edição
                        isEditMode = false;
                        editingRowId = null;
                        currentRequisicaoModeloId = null;

                        clearForm();
                        location.reload(); // Recarregar para mostrar o item atualizado na tabela
                    } else {
                        alert(data.error || 'Erro ao adicionar item');
                    }

                } catch (error) {
                    console.error('Erro ao adicionar item:', error);
                    alert('Erro ao adicionar item: ' + error.message);
                }
            }

            // Editar detalhe (replica onEditDetailRequisicaoPneuModelos)
            async function onEditDetailRequisicaoPneuModelos(idRequisicaoModelo) {
                try {
                    const response = await fetch('{{ route('admin.saidaPneus.ajax.editar-detalhe') }}', {
                        method: 'POST',
                        headers: defaultHeaders,
                        body: JSON.stringify({
                            id_requisicao_modelo: idRequisicaoModelo
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Entrar em modo de edição
                        isEditMode = true;
                        editingRowId = idRequisicaoModelo;
                        currentRequisicaoModeloId = data.data.id_requisicao_pneu_modelos;

                        // Preencher formulário com dados do item
                        document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu').value = data.data
                            .id_modelo_pneu;
                        document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_requisicao_pneu_modelos')
                            .value = data.data.id_requisicao_pneu_modelos;
                        document.getElementById('requisicao_pneu_modelos_requisicao_pneu_quantidade').value = data.data
                            .quantidade;
                        document.getElementById('requisicao_pneu_modelos_requisicao_pneu_quantidade_baixa').value = data
                            .data.quantidade_baixa;
                        document.getElementById('requisicao_pneu_modelos_requisicao_pneu_data_baixa').value = data.data
                            .data_baixa;

                        // Carregar pneus para o modelo
                        await carregarPneus(data.data.id_modelo_pneu);

                        // Alterar botão para modo edição
                        const button = document.getElementById('button_adicionar_requisicao_pneu_modelos_requisicao_pneu');
                        const buttonText = document.getElementById('button_text');
                        button.className = button.className.replace('bg-blue-600', 'bg-orange-600').replace(
                            'hover:bg-blue-700', 'hover:bg-orange-700');
                        button.innerHTML = '<i class="far fa-edit mr-1"></i><span id="button_text">Editar</span>';

                    } else {
                        alert(data.error || 'Erro ao carregar dados para edição');
                    }

                } catch (error) {
                    console.error('Erro ao editar item:', error);
                    alert('Erro ao editar item: ' + error.message);
                }
            }

            // Estornar requisição (replica onEstorno)
            async function onEstorno(idRequisicaoModelo) {
                if (!confirm('Tem Certeza de que quer continuar com estorno e Finalizar a Baixa dos Pneus?')) {
                    return;
                }

                try {
                    const response = await fetch(
                        `{{ url('/admin/pneus/saida-pneus/estornar-modelo') }}/${idRequisicaoModelo}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.error || 'Erro ao estornar');
                    }

                } catch (error) {
                    console.error('Erro ao estornar:', error);
                    alert('Erro ao estornar requisição: ' + error.message);
                }
            }

            // Limpar formulário (replica onClear)
            function onClear() {
                if (!confirm('Deseja limpar o formulário?')) {
                    return;
                }

                clearForm();
            }

            // Função auxiliar para limpar formulário
            function clearForm() {
                // Resetar modo de edição
                isEditMode = false;
                editingRowId = null;
                currentRequisicaoModeloId = null;
                currentModeloId = null;

                // Limpar campos
                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu').value = '';
                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_requisicao_pneu_modelos').value = '';
                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_quantidade').value = '';
                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_quantidade_baixa').value = '';
                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_data_baixa').value = '{{ date('Y-m-d') }}';

                // Limpar container de pneus
                const container = document.getElementById('pneus_container');
                container.innerHTML =
                    '<div id="no_pneus_message" class="text-gray-500 text-sm">Selecione um modelo primeiro</div>';

                // Resetar label
                document.getElementById('pneus_label').textContent = 'Pneus Baixados:';

                // Resetar botão para modo adicionar
                const button = document.getElementById('button_adicionar_requisicao_pneu_modelos_requisicao_pneu');
                button.className = button.className.replace('bg-orange-600', 'bg-blue-600').replace('hover:bg-orange-700',
                    'hover:bg-blue-700');
                button.innerHTML = '<i class="fas fa-save mr-1 text-blue-300"></i><span id="button_text">Adicionar</span>';
            }

            // Finalizar saída (replica onFinalizarSaida)
            function onFinalizarSaida() {
                // Verificar se todas as quantidades estão corretas
                const tabela = document.getElementById('requisicao_pneu_modelos_requisicao_pneu_list_body');
                const rows = tabela.querySelectorAll('tr');

                let totalRequisitado = 0;
                let totalBaixado = 0;

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 6) {
                        const quantidade = parseInt(cells[4].textContent) || 0;
                        const quantidadeBaixa = parseInt(cells[5].textContent) || 0;
                        totalRequisitado += quantidade;
                        totalBaixado += quantidadeBaixa;
                    }
                });

                if (totalRequisitado !== totalBaixado) {
                    alert('A quantidade selecionada não atende a quantidade requisitada!');
                    return;
                }

                if (confirm('Tem Certeza de que quer continuar com a operação e Finalizar a Baixa dos Pneus?')) {
                    window.location.href = `{{ route('admin.saidaPneus.finalizar', $requisicao->id_requisicao_pneu) }}`;
                }
            }

            // Função para controlar estado dos campos baseado na situação
            function configureFormByStatus() {
                const situacao = requisicaoSituacao;
                const isFinalized = situacao === 'FINALIZADA';

                if (isFinalized) {
                    // Desabilitar todos os campos editáveis se finalizada
                    document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu').disabled = true;
                    document.getElementById('button_adicionar_requisicao_pneu_modelos_requisicao_pneu').disabled = true;

                    // Esconder botões de ação da tabela
                    const actionButtons = document.querySelectorAll(
                        'button[onclick*="onEditDetailRequisicaoPneuModelos"], button[onclick*="onEstorno"]');
                    actionButtons.forEach(button => button.style.display = 'none');
                }
            }

            // Validações em tempo real
            function setupRealTimeValidations() {
                // Validação ao alterar modelo
                document.getElementById('requisicao_pneu_modelos_requisicao_pneu_id_modelo_pneu').addEventListener('change',
                    function() {
                        if (!isEditMode) {
                            clearForm();
                        }
                    });

                // Validação de quantidade máxima
                document.addEventListener('change', function(e) {
                    if (e.target.name === 'id_pneus[]' && e.target.type === 'checkbox') {
                        const quantidadeInput = document.getElementById(
                            'requisicao_pneu_modelos_requisicao_pneu_quantidade');
                        const maxQuantidade = parseInt(quantidadeInput.value) || 0;
                        const checkboxes = document.querySelectorAll(
                            'input[name="id_pneus[]"]:checked:not([disabled])');

                        if (maxQuantidade > 0 && checkboxes.length > maxQuantidade) {
                            e.target.checked = false;
                            alert('A quantidade selecionada não pode ser maior que a solicitada (' + maxQuantidade +
                                ')');
                            return;
                        }

                        updateQuantidadeBaixa();
                    }
                });
            }

            // Auto-save temporário para evitar perda de dados
            function setupAutoSave() {
                const autoSaveFields = [
                    'observacao',
                    'justificativa_de_finalizacao'
                ];

                autoSaveFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', function() {
                            // Salvar no localStorage temporariamente
                            localStorage.setItem(`saida_pneu_${requisicaoId}_${fieldId}`, this.value);
                        });

                        // Restaurar valor salvo
                        const savedValue = localStorage.getItem(`saida_pneu_${requisicaoId}_${fieldId}`);
                        if (savedValue && !field.value) {
                            field.value = savedValue;
                        }
                    }
                });
            }

            // Função para limpar auto-save quando formulário é enviado
            function clearAutoSave() {
                const autoSaveFields = [
                    'observacao',
                    'justificativa_de_finalizacao'
                ];

                autoSaveFields.forEach(fieldId => {
                    localStorage.removeItem(`saida_pneu_${requisicaoId}_${fieldId}`);
                });
            }

            // Interceptar submit do formulário para limpar auto-save
            document.getElementById('form_RequisicaoPneuForm').addEventListener('submit', function() {
                clearAutoSave();
            });

            // Função para controlar a exibição do campo filial destino
            function toggleFilialDestino() {
                const transferenciaSimRadio = document.getElementById('transferencia_sim');
                const filialDestinoContainer = document.getElementById('filial_destino_container');

                if (transferenciaSimRadio && filialDestinoContainer) {
                    if (transferenciaSimRadio.checked) {
                        filialDestinoContainer.style.display = 'block';
                    } else {
                        filialDestinoContainer.style.display = 'none';
                        // Limpar seleção quando esconder o campo
                        const filialDestinoSelect = document.getElementById('id_filial_destino');
                        if (filialDestinoSelect) {
                            filialDestinoSelect.value = '';
                        }
                    }
                }
            }

            // Inicialização quando a página carrega
            document.addEventListener('DOMContentLoaded', function() {
                configureFormByStatus();
                setupRealTimeValidations();
                setupAutoSave();

                // Configurar estado inicial do campo filial destino
                toggleFilialDestino();

                // Adicionar event listeners para os radio buttons de transferência entre filiais
                const transferenciaRadios = document.querySelectorAll('input[name="transferencia_entre_filiais"]');
                transferenciaRadios.forEach(radio => {
                    radio.addEventListener('change', toggleFilialDestino);
                });

                // Configurar estado inicial baseado na situação
                if (requisicaoSituacao === 'FINALIZADA') {
                    // Mostrar mensagem de requisição finalizada
                    const container = document.getElementById('pneus_container');
                    if (container.innerHTML.includes('Selecione um modelo primeiro')) {
                        container.innerHTML = '<div class="text-gray-500 text-sm">Requisição finalizada</div>';
                    }
                }
            });

            // Exposar funções globais para uso em onclick
            window.carregarPneus = carregarPneus;
            window.onAddDetailRequisicaoPneuModelosRequisicaoPneu = onAddDetailRequisicaoPneuModelosRequisicaoPneu;
            window.onEditDetailRequisicaoPneuModelos = onEditDetailRequisicaoPneuModelos;
            window.onEstorno = onEstorno;
            window.onClear = onClear;
            window.onFinalizarSaida = onFinalizarSaida;
            window.onRequisicaoTerceiro = onRequisicaoTerceiro;
            window.updateQuantidadeBaixa = updateQuantidadeBaixa;
            window.toggleFilialDestino = toggleFilialDestino;
        </script>
    @endpush
</x-app-layout>
