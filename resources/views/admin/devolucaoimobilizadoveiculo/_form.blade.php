@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
    <div>

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-50 p-4">
                <ul class="list-inside list-disc text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="border-b border-gray-200 bg-white p-4">

            <div class="border-b border-gray-200 bg-white p-6">
                <div>
                    <form id="devolucaoImobilizadoVeiculo" method="POST" action="{{ $action }}" class="space-y-4"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($method === 'PUT')
                            @method('PUT')
                        @endif

                        <!-- Cabeçalho -->
                        <h3 class="mb-10 font-medium uppercase text-gray-800">Dados Devolução Veiculo</h3>
                        <div class="mb-6 rounded-lg p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div>
                                    {{-- Cod. Trasferência Imobilizado --}}
                                    <label for="id_devolucao_imobilizado_veiculo"
                                        class="block text-sm font-medium text-gray-700">Código Trasferência
                                        do veiculo</label>
                                    <input type="text" id="id_devolucao_imobilizado_veiculo"
                                        name="id_devolucao_imobilizado_veiculo" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $devolucaoImobilizadoVeiculo->id_devolucao_imobilizado_veiculo ?? '' }}">
                                </div>

                                <div>
                                    {{-- Usuario --}}
                                    <label for="id_usuario"
                                        class="block text-sm font-medium text-gray-700">Usuário</label>

                                    <!-- Input visível com o nome do usuário (somente leitura) -->
                                    <input type="text" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $devolucaoImobilizadoVeiculo->user->name ?? auth()->user()->name }}">

                                    <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                    <input type="hidden" name="id_usuario"
                                        value="{{ $devolucaoImobilizadoVeiculo->user->id ?? auth()->user()->id }}">
                                </div>

                                <div>
                                    {{-- Departamento --}}
                                    <label for="id_departamento"
                                        class="block text-sm font-medium text-gray-700">Departamento</label>

                                    <!-- Input visível com o nome do usuário (somente leitura) -->
                                    <input type="text" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $devolucaoImobilizadoVeiculo->departamento->descricao_departamento ?? auth()->user()->departamento->descricao_departamento }}">

                                    <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                    <input type="hidden" name="id_departamento"
                                        value="{{ $devolucaoImobilizadoVeiculo->user->id_departamento ?? auth()->user()->departamento_id }}">
                                </div>

                                <div>
                                    {{-- Filial --}}
                                    <label for="id_filial_usuario"
                                        class="block text-sm font-medium text-gray-700">Filial Usuário</label>

                                    <!-- Input visível com o nome do usuário (somente leitura) -->
                                    <input type="text" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ auth()->user()->filial->name }}">

                                    <!-- Input oculto com o ID do usuário, que será enviado no form -->
                                    <input type="hidden" name="id_filial_usuario"
                                        value="{{ auth()->user()->filial_id }}">
                                </div>

                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div>
                                    <label for="is_tipo" class="block text-sm font-medium text-gray-700">Comodato ou
                                        Filial?</label>

                                    <div class="grid grid-cols-1 gap-1">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="tipo" id="is_tipo_comodato" value="COMODATO"
                                                @if (isset($devolucaoImobilizadoVeiculo)) disabled @endif
                                                @if (old('tipo', $devolucaoImobilizadoVeiculo->tipo ?? null) == 'COMODATO') checked @endif>
                                            <label for="is_tipo_comodato"
                                                @if (isset($devolucaoImobilizadoVeiculo)) class="text-gray-500" @endif>Comodato</label>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="tipo" id="is_tipo_filial" value="FILIAL"
                                                @if (isset($devolucaoImobilizadoVeiculo)) disabled @endif
                                                @if (old('tipo', $devolucaoImobilizadoVeiculo->tipo ?? null) == 'FILIAL') checked @endif>
                                            <label for="is_tipo_filial"
                                                @if (isset($devolucaoImobilizadoVeiculo)) class="text-gray-500" @endif>Filial</label>
                                        </div>
                                    </div>

                                    @if (isset($devolucaoImobilizadoVeiculo))
                                        <!-- Input hidden para garantir que o valor seja enviado quando disabled -->
                                        <input type="hidden" name="tipo"
                                            value="{{ $devolucaoImobilizadoVeiculo->tipo }}">
                                    @endif
                                </div>

                                {{-- filial Origem --}}
                                <div id="filialOrigem">
                                    <x-forms.smart-select name="id_filial_origem" label="Filial Origem"
                                        placeholder="Selecione a Filial de Origem" :disabled="isset($devolucaoImobilizadoVeiculo)" :options="$filial"
                                        :selected="old(
                                            'id_filial_origem',
                                            $devolucaoImobilizadoVeiculo->id_filial_origem ?? '',
                                        )" asyncSearch="false" />
                                </div>

                                {{-- filial Destino --}}
                                <div class="hidden" id="filialDestino">
                                    <x-forms.smart-select name="id_filial_destino" label="Filial Destino"
                                        placeholder="Selecione a Filial de Destino" :disabled="isset($devolucaoImobilizadoVeiculo)" :options="$filial"
                                        :selected="old(
                                            'id_filial_destino',
                                            $devolucaoImobilizadoVeiculo->id_filial_destino ?? '',
                                        )" asyncSearch="false" />
                                </div>

                                {{-- Fornecedor --}}
                                <div class="hidden" id="fornecedor">
                                    <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                                        placeholder="Selecione o fornecedor..." :options="$fornecedor" :disabled="isset($devolucaoImobilizadoVeiculo)"
                                        :searchUrl="route('admin.api.fornecedores.search')" asyncSearch="false" :selected="old(
                                            'id_fornecedor',
                                            $devolucaoImobilizadoVeiculo->id_fornecedor ?? '',
                                        )" asyncSearch="true" />
                                </div>

                                {{-- Departamento --}}
                                <div class="hidden" id="departamento">
                                    <x-forms.smart-select name="id_departamento" label="Departamento"
                                        placeholder="Selecione o departamento..." :options="$departamento" asyncSearch="false"
                                        :disabled="isset($devolucaoImobilizadoVeiculo)" :selected="old(
                                            'id_departamento',
                                            $devolucaoImobilizadoVeiculo->id_departamento ?? '',
                                        )" asyncSearch="true" />
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div>
                                    {{-- tipo equipamento do veiculo --}}
                                    <x-forms.smart-select name="id_tipo_equipamento" label="Tipo do Veiculo"
                                        class="border-gray-300 bg-gray-100"
                                        placeholder="Selecione o modelo do veiculo..." :options="$tipoEquipamento"
                                        :disabled="isset($devolucaoImobilizadoVeiculo->id_tipo_equipamento)" :selected="old(
                                            'id_tipo_equipamento',
                                            $devolucaoImobilizadoVeiculo->id_tipo_equipamento ?? '',
                                        )" asyncSearch="true" />
                                </div>

                                <div>
                                    <x-forms.smart-select name="id_veiculo" label="Veiculo" :options="[]"
                                        :selected="old('id_veiculo', $devolucaoImobilizadoVeiculo->id_veiculo ?? '')" :disabled="isset($devolucaoImobilizadoVeiculo->id_veiculo)" asyncSearch="false" />
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div>
                                    {{-- Data de inicio --}}
                                    @if (isset($devolucaoImobilizadoVeiculo->data_inicio) && !empty($devolucaoImobilizadoVeiculo->data_inicio))
                                        {{-- Campo readonly quando já tem data --}}
                                        <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data
                                            de Início</label>
                                        <input type="text" readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            value="{{ date('d/m/Y', strtotime($devolucaoImobilizadoVeiculo->data_inicio)) }}">
                                        {{-- Input hidden para preservar o valor --}}
                                        <input type="hidden" name="data_inicio"
                                            value="{{ date('Y-m-d', strtotime($devolucaoImobilizadoVeiculo->data_inicio)) }}">
                                    @else
                                        {{-- Campo normal quando não há data --}}
                                        <x-forms.input name="data_inicio" type="date" label="Data de Início"
                                            value="{{ old('data_inicio', '') }}" />
                                    @endif
                                </div>

                                <div>
                                    {{-- Data de fim --}}
                                    @if (isset($devolucaoImobilizadoVeiculo->data_fim) && !empty($devolucaoImobilizadoVeiculo->data_fim))
                                        {{-- Campo readonly quando já tem data --}}
                                        <label for="data_fim" class="block text-sm font-medium text-gray-700">Data de
                                            Fim</label>
                                        <input type="text" readonly
                                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            value="{{ date('d/m/Y', strtotime($devolucaoImobilizadoVeiculo->data_fim)) }}">
                                        {{-- Input hidden para preservar o valor --}}
                                        <input type="hidden" name="data_fim"
                                            value="{{ date('Y-m-d', strtotime($devolucaoImobilizadoVeiculo->data_fim)) }}">
                                    @else
                                        {{-- Campo normal quando não há data --}}
                                        <x-forms.input name="data_fim" type="date" label="Data de Fim"
                                            value="{{ old('data_fim', '') }}" />
                                    @endif
                                </div>
                            </div>

                            @if (isset($devolucaoImobilizadoVeiculo) && $devolucaoImobilizadoVeiculo->status === 3)
                                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
                                    <!-- Anexo Documento -->
                                    <div>
                                        <label for="anexo_documento"
                                            class="block text-sm font-medium text-gray-700">Anexo
                                            Documento
                                            (PDF)</label>
                                        <input type="file" id="anexo_documento" name="anexo_documento"
                                            accept=".pdf"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

                                        @if (isset($devolucaoImobilizadoVeiculo) && $devolucaoImobilizadoVeiculo->anexo_documento)
                                            <div class="mt-2">
                                                <span class="text-sm text-gray-500">Arquivo atual: </span>
                                                @if (Storage::disk('public')->exists($devolucaoImobilizadoVeiculo->anexo_documento))
                                                    <a href="{{ url('storage/' . $devolucaoImobilizadoVeiculo->anexo_documento) }}"
                                                        target="_blank"
                                                        class="text-sm text-indigo-600 hover:text-indigo-800">
                                                        Visualizar arquivo
                                                    </a>
                                                @else
                                                    <span class="text-sm text-red-500">Arquivo não encontrado no
                                                        servidor</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-1">
                                <div>
                                    {{-- Observação --}}
                                    <label for="observacao"
                                        class="block text-sm font-medium text-gray-700">Observação</label>

                                    @if (isset($devolucaoImobilizadoVeiculo) && !empty($devolucaoImobilizadoVeiculo->observacao))
                                        <!-- Mostra o texto existente como readonly -->
                                        <div class="mb-2 rounded-md border border-gray-200 bg-gray-50 p-3">
                                            <label class="mb-1 block text-xs font-medium text-gray-500">Observação
                                                anterior:</label>
                                            <div class="whitespace-pre-wrap text-sm text-gray-700">
                                                {{ $devolucaoImobilizadoVeiculo->observacao }}</div>
                                        </div>

                                        <!-- Campo para nova observação -->
                                        <textarea name="observacao_adicional" placeholder="Adicione uma nova observação..."
                                            class="block w-full rounded-md border-gray-300 text-sm font-medium text-gray-700 hover:border-gray-400">{{ old('observacao_adicional', '') }}</textarea>

                                        <!-- Input hidden com a observação original -->
                                        <input type="hidden" name="observacao_original"
                                            value="{{ $devolucaoImobilizadoVeiculo->observacao }}">
                                    @else
                                        <!-- Campo normal se não há observação existente -->
                                        <textarea name="observacao"
                                            class="block w-full rounded-md border-gray-300 text-sm font-medium text-gray-700 hover:border-gray-400">{{ old('observacao', '') }}</textarea>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <!-- Botões de Ação -->
                        <div class="mt-6 flex justify-end space-x-4">
                            <div class="mt-6 flex justify-end space-x-4">
                                <a href="{{ route('admin.devolucaoimobilizadoveiculo.index') }}"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Voltar
                                </a>

                                @if (isset($devolucaoImobilizadoVeiculo) && ($devolucaoImobilizadoVeiculo->status ?? '') == '2')
                                    <button type="button" onclick="showModal(`mudar-status`)"
                                        class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        Aprovar/Reprovar
                                    </button>
                                @endif

                                <button type="submit" id="submit-button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <x-bladewind.modal name="mudar-status" cancel_button_label="Cancelar" ok_button_label=""
                    type="info" title="Confirmar aprovação" size="big">
                    <b>Atenção:</b> essa ação transferirá o veículo
                    <br>
                    <b>Esta ação não pode ser desfeita.</b>
                    <br>
                    <x-bladewind::button type="button" color="green"
                        onclick="onAprovar(
                        {{ isset($devolucaoImobilizadoVeiculo) ? $devolucaoImobilizadoVeiculo->id_devolucao_imobilizado_veiculo : '' }},
                        {{ isset($devolucaoImobilizadoVeiculo) ? $devolucaoImobilizadoVeiculo->status : '' }});
                        hideModal('mudar-status');"
                        class="me-2 mt-3 text-white">
                        Aprovar
                    </x-bladewind::button>
                    <x-bladewind::button type="button" color="red" onclick="showModal('reprovar')"
                        class="me-2 mt-3 text-white">
                        Reprovar
                    </x-bladewind::button>
                </x-bladewind.modal>

                <!--  para aparecer a justificativa e reprovar -->
                <x-bladewind.modal name="reprovar" cancel_button_label="Cancelar" ok_button_label="" type="warning"
                    title="Confirmar reprovação" size="large">
                    <b>
                        Esta ação não pode ser desfeita.
                    </b>
                    <br>
                    <br>
                    {{-- justificativa --}}
                    <label class="block text-sm font-medium text-gray-700">Justificativa</label>
                    <textarea name="justificativa" rows="5"
                        class="block w-full rounded-md border-gray-300 text-sm font-medium text-gray-700 hover:border-gray-400"></textarea>
                    <br>
                    <x-bladewind::button type="button" color="red" onclick="onReprovar()"
                        class="me-2 mt-3 text-white">
                        Reprovar
                    </x-bladewind::button>
                </x-bladewind.modal>

            </div>

        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variáveis para armazenar os valores atuais
            let currentTipoEquipamento = null;
            let currentFilial = null;

            // Função para carregar os veículos
            function carregarVeiculos(tipoEquipamento = null, filial = null) {
                // Usar valores passados como parâmetro ou valores armazenados ou valores do DOM
                const idTipoEquipamento = tipoEquipamento || currentTipoEquipamento || document.querySelector(
                    '[name="id_tipo_equipamento"]')?.value;
                const idFilial = filial || currentFilial || document.querySelector('[name="id_filial_origem"]')
                    ?.value;

                console.log('Carregando veículos:', {
                    idFilial,
                    idTipoEquipamento
                });

                if (!idTipoEquipamento) {
                    // Se não houver tipo selecionado, limpa a lista de veículos
                    updateSmartSelectOptions('id_veiculo', []);
                    return;
                }

                const headers = {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };

                console.log('Enviando requisição com:', {
                    tipoEquipamento: idTipoEquipamento,
                    filial: idFilial
                });

                fetch('/admin/devolucaoimobilizadoveiculo/get-vehicle-data', {
                        method: 'POST',
                        headers: headers,
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            tipoEquipamento: idTipoEquipamento,
                            filial: idFilial
                        })
                    })
                    .then(response => response.json())
                    .then(retorno => {
                        console.log('Resposta do servidor:', retorno);
                        updateSmartSelectOptions('id_veiculo', retorno.veiculos.map(item => ({
                            value: item.value,
                            label: item.label
                        })));
                    })
                    .catch(err => {
                        console.error('Erro ao buscar dados do veículo:', err);
                        updateSmartSelectOptions('id_veiculo', []);
                    });
            }

            // Função para atualizar os valores armazenados
            function atualizarValores() {
                currentTipoEquipamento = document.querySelector('[name="id_tipo_equipamento"]')?.value || null;
                currentFilial = document.querySelector('[name="id_filial_origem"]')?.value || null;
            }

            // Chama ao carregar a página
            setTimeout(() => {
                atualizarValores();
                carregarVeiculos();
            }, 500); // Pequeno delay para garantir que os selects estejam inicializados

            // Configurar listeners para mudanças nos campos
            if (typeof window.onSmartSelectChange === 'function') {
                // Listener para mudança no tipo de equipamento
                onSmartSelectChange('id_tipo_equipamento', function(data) {
                    console.log('Tipo de equipamento alterado:', data);
                    currentTipoEquipamento = data.value;
                    carregarVeiculos(data.value, currentFilial);
                });

                // Listener para mudança na filial origem
                onSmartSelectChange('id_filial_origem', function(data) {
                    console.log('Filial origem alterada:', data);
                    currentFilial = data.value;
                    carregarVeiculos(currentTipoEquipamento, data.value);
                });
            } else {
                console.warn('⚠️ onSmartSelectChange não disponível, usando listeners diretos');

                // Fallback usando listeners diretos nos elementos
                setTimeout(() => {
                    const tipoEquipamentoSelect = document.querySelector('[name="id_tipo_equipamento"]');
                    const filialOrigemSelect = document.querySelector('[name="id_filial_origem"]');

                    if (tipoEquipamentoSelect) {
                        tipoEquipamentoSelect.addEventListener('change', function() {
                            atualizarValores();
                            carregarVeiculos();
                        });
                    }

                    if (filialOrigemSelect) {
                        filialOrigemSelect.addEventListener('change', function() {
                            atualizarValores();
                            carregarVeiculos();
                        });
                    }
                }, 1000); // Delay para garantir que os elementos sejam inicializados
            }
        });
    </script>

    <script>
        function toggleFilial() {
            const selected = document.querySelector('input[name="tipo"]:checked');
            const filialDestinoDiv = document.getElementById('filialDestino');

            if (selected && selected.value === "FILIAL") {
                filialDestinoDiv.classList.remove('hidden');
            } else {
                filialDestinoDiv.classList.add('hidden');
            }
        }

        function toggleComodato() {
            const selected = document.querySelector('input[name="tipo"]:checked');
            const fornecedorDiv = document.getElementById('fornecedor');
            const departamentoDiv = document.getElementById('departamento');

            if (selected && selected.value === "COMODATO") {
                fornecedorDiv.classList.remove('hidden');
                departamentoDiv.classList.remove('hidden');
            } else {
                fornecedorDiv.classList.add('hidden');
                departamentoDiv.classList.add('hidden');
            }
        }

        // Executa ao carregar a página
        document.addEventListener('DOMContentLoaded', () => {
            toggleFilial();
            toggleComodato();

            // Adiciona o evento de mudança nos radio buttons
            document.querySelectorAll('input[name="tipo"]').forEach((el) => {
                el.addEventListener('change', toggleFilial);
                el.addEventListener('change', toggleComodato);
            });
        });
    </script>

    <script>
        function onAprovar(id, status) {
            const veiculo = document.querySelector(`[name="id_veiculo"]`).value;
            const observacao = document.querySelector(`[name="observacao"]`)?.value || '';
            const observacao_original = document.querySelector(`[name="observacao_original"]`).value;
            const observacao_adicional = document.querySelector(`[name="observacao_adicional"]`).value;

            if (!veiculo) {
                alert('Por favor, selecione um veículo antes de prosseguir.');
                return;
            }

            if (!confirm(`Atenção: essa ação transferirá o veículo. Deseja continuar? `)) {
                return;
            }

            let formData = new FormData();

            const editar = true;
            formData.append('id', id);
            formData.append('veiculo', veiculo);
            formData.append('observacao', observacao);
            formData.append('observacao_original', observacao_original);
            formData.append('observacao_adicional', observacao_adicional);
            formData.append('editar', editar);

            fetch(`{{ route('admin.devolucaoimobilizadoveiculo.verificarsituacao') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw err;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (typeof showNotification === 'function' && data.notification) {
                        showNotification(
                            data.notification.title,
                            data.notification.message,
                            data.notification.type
                        );
                    } else if (data.notification) {
                        alert(data.notification.message);
                    }

                    // Verifica se há redirect na resposta
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000); // Aumenta o tempo para 1 segundo
                    } else {
                        // Se não há redirect específico, recarrega a página
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    const message =
                        error?.notification?.message ||
                        error?.message ||
                        'Ocorreu um erro ao finalizar a requisição';

                    if (typeof showNotification === 'function') {
                        showNotification('Erro', message, 'error');
                    } else {
                        alert(message);
                    }
                });
        }

        function onReprovar() {
            if (!confirm("Atenção: essa ação irá reprovar a transferência. Deseja continuar?")) {
                return;
            }

            let formData = new FormData()

            const id = document.querySelector(`[name="id_devolucao_imobilizado_veiculo"]`).value
            const justificativa = document.querySelector(`[name="justificativa"]`).value

            formData.append('id', id);
            formData.append('justificativa', justificativa);

            fetch(`{{ route('admin.devolucaoimobilizadoveiculo.reprovar') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw err;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (typeof showNotification === 'function' && data.notification) {
                        showNotification(
                            data.notification.title,
                            data.notification.message,
                            data.notification.type
                        );
                    }

                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
                    }

                })
                .catch(error => {
                    const message =
                        error?.notification?.message ||
                        error?.message ||
                        'Ocorreu um erro ao finalizar a requisição';

                    if (typeof showNotification === 'function') {
                        showNotification('Erro', message, 'error');
                    } else {
                        alert(message);
                    }
                });
        }
    </script>
    @include('admin.devolucaoimobilizadoveiculo._scripts')
@endpush
