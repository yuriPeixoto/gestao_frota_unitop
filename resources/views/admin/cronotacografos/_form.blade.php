<!-- Cabeçalho -->
<div class="rounded-lg mb-6">
    <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Cronotacógrafo</h3>
    @if (session('notification'))
    <x-notification :notification="session('notification')" />
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="id_certificado_veiculo" class="block text-sm font-medium text-gray-700">Código</label>
            <input type="text" id="id_certificado_veiculo" name="id_certificado_veiculo" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ $cronotacografo->id_certificado_veiculo ?? '' }}">
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_certificado" label="Tipo Certificado" placeholder="Selecione o tipo..."
                :options="$tiposCertificado"
                :selected="old('id_tipo_certificado', $cronotacografo->id_tipo_certificado ?? 2)" required="true"
                asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a placa..."
                :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')"
                :selected="old('id_veiculo', $cronotacografo->id_veiculo ?? '')" asyncSearch="true" />
        </div>

        <div>
            <label for="situacao" class="block text-sm font-medium text-gray-700">Situação</label>
            <select name="situacao"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                <option value="Cancelado" {{ old('situacao', $autorizacao->situacao ?? '') === 'Cancelado' ?
                    'selected' : '' }}>Cancelado
                </option>
                <option value="A Vencer" {{ old('situacao', $autorizacao->situacao ?? '') === 'A Vencer' ?
                    'selected' : '' }}>A Vencer
                </option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <label for="chassis" class="block text-sm font-medium text-gray-700">
                Chassi:
            </label>
            <input type="text" name="chassi" id="chassi" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>

        <div>
            <label for="renavam" class="block text-sm font-medium text-gray-700">
                Renavam:
            </label>
            <input type="text" id="renavam" name="renavam" id="renavam" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>

        <div>
            <label for="filial" class="block text-sm font-medium text-gray-700">
                Filial:
            </label>
            <input type="text" id="filial" name="filial" id="filial" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <x-forms.smart-select name="id_uf" label="UF" placeholder="Selecione a UF..." :options="$estados"
                :selected="old('id_uf', $cronotacografo->id_uf ?? '')" asyncSearch="false" />
        </div>

        <div>
            <label for="data_certificacao" class="block text-sm font-medium text-gray-700">Data de
                Emissão</label>
            <input type="date" id="data_certificacao" name="data_certificacao" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('data_certificacao', isset($cronotacografo->data_certificacao) && $cronotacografo->data_certificacao instanceof DateTime ? $cronotacografo->data_certificacao->format('Y-m-d') : '') }}">
        </div>

        <div>
            <label for="data_vencimento" class="block text-sm font-medium text-gray-700">Data de
                Vencimento</label>
            <input type="date" id="data_vencimento" name="data_vencimento" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('data_vencimento', isset($cronotacografo->data_vencimento) && $cronotacografo->data_vencimento instanceof DateTime ? $cronotacografo->data_vencimento->format('Y-m-d') : '') }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <label for="numero_certificado" class="block text-sm font-medium text-gray-700">Número do
                Certificado</label>
            <input type="text" id="numero_certificado" name="numero_certificado" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('numero_certificado', $cronotacografo->numero_certificado ?? '') }}">
        </div>

        <div>
            <label for="valor_certificado" class="block text-sm font-medium text-gray-700">Valor do
                Certificado</label>
            <input type="text" id="valor_certificado" name="valor_certificado" step="0.01" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('valor_certificado', $cronotacografo->valor_certificado ?? '') }}">
        </div>

        <div>
            <label for="caminho_arquivo" class="block text-sm font-medium text-gray-700">Anexo Laudo
                (PDF)</label>
            <input type="file" id="caminho_arquivo" name="caminho_arquivo" accept=".pdf"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

            @if (isset($cronotacografo) && $cronotacografo->caminho_arquivo)
            <div class="mt-2">
                <a href="{{ asset('storage/' . $cronotacografo->caminho_arquivo) }}" target="_blank"
                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Visualizar arquivo atual
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Botões de Ação -->

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
            // Obter referências aos elementos do DOM
            const veiculoSelect = document.querySelector('[name="id_veiculo"]');
            const chassiInput = document.getElementById('chassi');
            const renavamInput = document.getElementById('renavam');
            const input = document.getElementById('valor_certificado');

            input.addEventListener('input', function(e) {
                // Obter apenas os dígitos do valor atual
                let valor = e.target.value.replace(/\D/g, '');

                // Converter para número com formatação de centavos
                let valorNumerico = parseFloat(valor) / 100;

                // Se tiver dígitos, formatar como moeda
                if (valor !== '') {
                    e.target.value = valorNumerico.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                        minimumFractionDigits: 2
                    });
                } else {
                    e.target.value = '';
                }
            });

            // Supondo que o blade esteja gerando isso corretamente
            const veiculosFrequentes = @json($veiculosFrequentes);

            function atualizarChassi() {
                const idVeiculo = hiddenInput.value;
                const veiculoSelecionado = veiculosFrequentes.find(v => v.value == idVeiculo);
                chassiInput.value = veiculoSelecionado ? (veiculoSelecionado.chassi || 'Não encontrado') : '';
            }

            function atualizarRenavam() {
                const idVeiculo = hiddenInput.value;
                const veiculoSelecionado = veiculosFrequentes.find(v => v.value == idVeiculo);
                filialInput.value = veiculoSelecionado ? (veiculoSelecionado.renavam || 'Não encontrado') : '';
            }

            function atualizarTodosCampos() {
                atualizarChassi();
                atualizarRenavam();
            }

            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        atualizarTodosCampos();
                    }
                });
                return;
            })

            // Função para atualizar os campos quando uma placa for selecionada
            function atualizarDadosVeiculo(idVeiculo, veiculo) {
                if (!idVeiculo) return;
                fetch('{{ route('admin.autorizacoesesptransitos.pega-renavam-data') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            placa: idVeiculo
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            document.getElementById('renavam').value = data.renavam;
                            document.getElementById('chassi').value = data.chassi;
                            document.getElementById('filial').value = data.filial;
                        } else {
                            console.error(data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar dados do veículo:', error);
                    });
            }

            // Adicionar o callback à window para que o smart-select possa acessá-lo
            window.atualizarDadosVeiculo = atualizarDadosVeiculo;

            // Escutar eventos personalizados do componente smart-select
            window.addEventListener('id_veiculo:selected', function(event) {
                atualizarDadosVeiculo(event.detail.value, event.detail.object);
            });

            // Se um veículo já estiver selecionado ao carregar a página (no caso de edição),
            // buscar seus dados para preencher os campos
            if (veiculoSelect.value) {
                atualizarDadosVeiculo(veiculoSelect.value);
            }
        });
</script>
@endpush