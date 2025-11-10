<div class="py-6 px-4 sm:px-6 lg:px-8 w-full">
    <div class="w-full">
        {{--<form action="{{ route('admin.calibragempneus.store') }}" method="POST"> --}}
            {{-- @csrf --}}

            <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center" id="formCalibragem">

                <x-forms.input type="number" name="id_calibragem_pneu" label="Cód."
                    value="{{ $calibragem->id_calibragem_pneu ?? '' }}" readonly />

                {{-- Data da Calibragem --}}
                <x-forms.input type="date" name="data_inclusao" label="Data Última Calibragem"
                    value="{{ request('data_inclusao') ?? $calibragem->data_inclusao ?? '' }}" readonly />

                {{-- Libras Geral --}}
                @if ($isCreate)
                <x-forms.input type="number" name="libras" label="Libras Geral" value="{{ old('libras') }}" />
                @else
                <x-forms.input type="number" name="libras" label="Libras Geral" value="{{ $calibragem->libras }}"
                    disabled />
                @endif

                {{-- Placa --}}
                <div>
                    @if ($isCreate)
                    <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecionar" :options="$veiculos"
                        :searchUrl="route('admin.api.veiculos.search')" :selected="request('placa')" asyncSearch="true"
                        minSearchLength="2" />
                    @else
                    <x-forms.input type="text" name="placa_visual" label="Placa"
                        value="{{ $calibragem->veiculo->placa ?? '' }}" readonly />
                    <input type="hidden" name="id_veiculo" value="{{ $calibragem->id_veiculo }}" />
                    @endif
                </div>

                {{-- Usuário autenticado --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Usuário</label>
                    <input type="text"
                        class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm"
                        value="{{ auth()->user()->name ?? '' }}" readonly>
                    <input type="hidden" name="usuario_id" value="{{ auth()->id() }}">
                </div>

                {{-- Filial (visualização apenas) --}}
                <x-forms.input name="filial" label="Filial"
                    value="{{ auth()->user()->userFilial->filial->name ?? $calibragem->filial->name ?? '-' }}"
                    disabled />
            </div>

            <div class="col-span-full mt-10">
                <h3 class="font-medium text-gray-800 uppercase text-sm">Pneus Calibrados</h3>
                <hr class="mb-2">
                <div class="shadow overflow-hidden sm:rounded-md mt-4">
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell>Número de Fogo</x-tables.head-cell>
                            <x-tables.head-cell>Localização</x-tables.head-cell>
                            <x-tables.head-cell>Libras</x-tables.head-cell>
                            <x-tables.head-cell>Sulco Pneu</x-tables.head-cell>
                            <x-tables.head-cell>Calibrado?</x-tables.head-cell>
                            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
                        </x-tables.header>

                        <x-tables.body id="tbody-pneus">
                            @foreach ($pneus as $index => $pneu)
                            <x-tables.row :index="$index">
                                <x-tables.cell>
                                    {{ $pneu->id_numero_fogo }}
                                    <input type="hidden" name="pneus[{{ $index }}][id_calibragem_pneus_itens]"
                                        value="{{ $pneu->id_calibragem_pneus_itens }}">
                                </x-tables.cell>

                                <x-tables.cell>
                                    {{ $pneu->localizacao }}
                                    <input type="hidden" name="pneus[{{ $index }}][localizacao]"
                                        value="{{ $pneu->localizacao }}">
                                </x-tables.cell>

                                <x-tables.cell>
                                    <input type="text" name="pneus[{{ $index }}][libras]" value="{{ $pneu->libras }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                        data-id="{{ $pneu->id_calibragem_pneus_itens }}" data-field="libras">
                                </x-tables.cell>

                                <x-tables.cell>
                                    <input type="text" name="pneus[{{ $index }}][sulco_pneu]"
                                        value="{{ $pneu->sulco_pneu }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                        data-id="{{ $pneu->id_calibragem_pneus_itens }}" data-field="sulco_pneu">
                                </x-tables.cell>

                                <x-tables.cell>
                                    <select name="pneus[{{ $index }}][calibrado]"
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="1" {{ $pneu->calibrado ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ !$pneu->calibrado ? 'selected' : '' }}>Não</option>
                                    </select>
                                </x-tables.cell>

                                <x-tables.cell>
                                    {{ \Carbon\Carbon::parse($pneu->data_inclusao)->format('d/m/Y') }}
                                    <button type="button"
                                        class="text-red-500 ml-2 hover:text-red-700 focus:outline-none"
                                        onclick="openHistoricoModal({{ $pneu->id_calibragem_pneus_itens }})"
                                        title="Ver histórico">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
                                        </svg>
                                    </button>
                                </x-tables.cell>
                            </x-tables.row>
                            @endforeach
                        </x-tables.body>
                    </x-tables.table>

                </div>
                <div id="modal-historico"
                    class="hidden fixed inset-0 flex items-center justify-center z-50  bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full relative">
                        <!-- Botão fechar no canto -->
                        <button type="button" class="absolute top-2 right-2 text-gray-50 hover:text-gray-500"
                            onclick="document.getElementById('modal-historico').classList.add('hidden')">
                            &times;
                        </button>

                        <h3 class="text-lg font-semibold mb-4">Histórico de alterações</h3>

                        <div id="historico-content" class="text-sm text-gray-700">
                            Carregando...
                        </div>

                        <div class="mt-6 flex justify-end space-x-2">
                            <button type="button" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                                onclick="document.getElementById('modal-historico').classList.add('hidden')">
                                Fechar
                            </button>

                        </div>
                    </div>
                </div>
                <!-- </form> -->
            </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    onSmartSelectChange('id_veiculo', function(data) {
        console.log('Selecionado:', data.value);
        console.log('Label:', data.label);
        //console.log('Ultima Calibragem', data.data_inclusao);
        const dataCalibragemInput = document.querySelector('input[name="data_inclusao"]');
        dataCalibragemInput.value = new Date();

        const idVeiculo = data.value;

            if (!idVeiculo) return;

            fetch(`{{ url('admin/calibragempneus/ultima-data') }}/${idVeiculo}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao buscar data da última calibragem');
                    }
                    return response.json();
                })
                .then(result => {
                    const input = document.querySelector('input[name="data_inclusao"]');
                    if (input) {
                        input.value = result.data || '';
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                });

            fetch(`{{ url('admin/calibragempneus/validar-calibragem') }}/${idVeiculo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.existe) {
                        alert(data.mensagem); // popup direto
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar calibragem recente:', error);
                });

        });

    });


    </script>

    <script>
        let historicoSelecionado = null;
let idCalibragemAtual = null;

function openHistoricoModal(id) {
    const modal = document.getElementById('modal-historico');
    const content = document.getElementById('historico-content');
    modal.classList.remove('hidden');
    content.innerHTML = 'Carregando...';

    idCalibragemAtual = id; // Armazena para uso posterior

    fetch(`{{ url('admin/calibragempneus/historico') }}/${id}`)
        .then(res => res.json())
        .then(data => {
            if (!data.length) {
                content.innerHTML = '<p>Nenhum histórico encontrado.</p>';
                return;
            }

            let html = '<ul class="space-y-2">';
            data.forEach(item => {
    html += `<li class="border p-3 rounded hover:bg-gray-50">
        <div>
            <strong>Código do histórico:</strong> ${item.id_calibragem_medicao} <br/>
            <strong>Data:</strong> ${item.data_medicao} <br/>
            <strong>Libras:</strong> ${item.libras} <br/>
            <strong>Sulco:</strong> ${item.milimetro}
        </div>
    
        
    </li>`;
});
            html += '</ul>';
            content.innerHTML = html;
        })
        .catch(() => {
            content.innerHTML = '<p class="text-red-500">Erro ao carregar histórico.</p>';
        });
}
/*
function selecionarHistorico(id, element) {
    historicoSelecionado = id;

    // Remove destaque de todos
    document.querySelectorAll('#historico-content li').forEach(li => {
        li.classList.remove('ring', 'ring-blue-500');
    });

    // Destaca selecionado
    element.classList.add('ring', 'ring-blue-500');
}

// Restauração ao clicar no botão principal

function restaurarHistoricoDireto(idCalibragemItem, idHistorico) {
    console.log('Restaurando', idCalibragemItem, idHistorico);
    fetch(`{{ url('admin/calibragempneus/restaurar') }}/${idCalibragemItem}`, {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ id_calibragem_medicao: idHistorico })
})

    .then(res => res.json())
.then(data => {
    if (data.success) {
        let item = data.item;

        // Atualiza inputs
        const sulcoInput = document.querySelector(`input[data-id="${item.id_numero_fogo}"][data-field="sulco_pneu"]`);
        const librasInput = document.querySelector(`input[data-id="${item.id_numero_fogo}"][data-field="libras"]`);

        if (sulcoInput) sulcoInput.value = item.sulco_pneu ?? '';
        if (librasInput) librasInput.value = item.libras ?? '';

        // Fecha modal
        document.getElementById('modal-historico').classList.add('hidden');

        // Agora sim, recarrega
        location.reload();
    } else {
        alert(data.message || 'Erro ao restaurar.');
    }
})



}
*/

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectPlaca = document.querySelector('[name="id_veiculo"]');
            let ultimoValor = selectPlaca.value;

            setInterval(() => {
                if (selectPlaca.value !== ultimoValor) {
                    ultimoValor = selectPlaca.value;

                    console.log('Veículo mudou via script:', selectPlaca.value);

                    fetch(`/admin/calibragempneus/pneus-veiculo/${selectPlaca.value}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Dados dos pneus recebidos:', data);
                            popularTabelaPneus(data);
                        })
                        .catch(err => console.error('Erro ao buscar pneus do veículo:', err));
                }
            }, 500); // verifica a cada 500ms
        });

            function formatDate(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                const dia = String(date.getDate()).padStart(2, '0');
                const mes = String(date.getMonth() + 1).padStart(2, '0');
                const ano = date.getFullYear();
                return `${dia}/${mes}/${ano}`;
            }

            function popularTabelaPneus(pneus) {
                const tbody = document.getElementById('tbody-pneus');
                tbody.innerHTML = ''; // Limpa tabela

                pneus.forEach((pneu, index) => {
                    const dataInclusaoFormatada = pneu.data_inclusao
                        ? new Date(pneu.data_inclusao).toLocaleDateString('pt-BR')
                        : '-';

                    const rowHTML = `
                        <tr>
                            <td>
                                ${pneu.id_numero_fogo ?? '-'}
                                <input type="hidden" name="pneus[${index}][id_numero_fogo]" value="${pneu.id_numero_fogo}">
                            </td>
                            <td>
                                ${pneu.localizacao ?? '-'}
                                <input type="hidden" name="pneus[${index}][localizacao]" value="${pneu.localizacao}">
                            </td>
                            <td>
                                <input type="text" name="pneus[${index}][libras]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
                            </td>
                            <td>
                                <input type="text" name="pneus[${index}][sulco_pneu]" class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
                            </td>
                            <td>
                                <select name="pneus[${index}][calibrado]" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </td>
                            <td>
                                ${dataInclusaoFormatada}
                            </td>
                        </tr>
                    `;

                    tbody.insertAdjacentHTML('beforeend', rowHTML);
                });
        }

    </script>