<x-bladewind::card reduce_padding="true">
    <div id="preventivaContainer">
        <div class="notasFaturamento-actions p-3 bg-gray-100/50 rounded-lg" id="actionsContainer">
            <x-bladewind::button size="tiny" type="secondary" color="blue" id="confirmButton"
                class="inline-flex items-center bg-primary">
                <div class="flex items-center">
                    <x-icons.clipboard-document-check class="w-4 h-4 mr-2" />
                    Gerar Função De Preventivas
                </div>
            </x-bladewind::button>
        </div>

        <x-bladewind::table selectable="false" checkable="false" name="controleFrota">
            <style>
                .bw-table th {
                    line-height: 1.2;
                    padding-top: 0.5rem;
                    padding-bottom: 0.5rem;
                }

                .centralizado {
                    text-align: center !important;
                }
            </style>
            <x-slot name="header">
                <th class="text-sm centralizado">
                    <input type="checkbox" id="selectAllCheckbox" />
                </th>
                <th class="text-sm centralizado">Código Manutenção</th>
                <th class="text-sm centralizado">Placa</th>
                <th class="text-sm centralizado">Categoria</th>
                <th class="text-sm centralizado">Manutenção</th>
                <th class="text-sm centralizado">Tipo <br>Manutenção</th>
                <th class="text-sm centralizado">Último KM</th>
                <th class="text-sm centralizado">Data <br>Última</th>
                <th class="text-sm centralizado">KM <br>Frequência</th>
                <th class="text-sm centralizado">KM <br>Atual</th>
                <th class="text-sm centralizado">KM à<br>Vencer</th>
                <th class="text-sm centralizado">Data<br>Vencimento</th>
                <th class="text-sm centralizado">Dias<br>Vencidos</th>
                <th class="text-sm centralizado">Filial</th>
            </x-slot>

            @forelse ($preOrdemOs as $manutencao)
                <tr data-id="{{ $manutencao->id_manutencao }}" class="text-xs cursor-pointer">
                    <td class="whitespace-pre-wrap">
                        <input type="checkbox" class="rowCheckbox" data-id="{{ $manutencao->id_manutencao }}" />
                    </td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->id_manutencao }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->id_veiculo }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->descricao_categoria }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->descricao_manutencao }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->tipo_manutencao_descricao }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->ultkm }}</td>
                    <td class="whitespace-nowrap">
                        {{ format_date($manutencao->datault, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->km_frequencia }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->km_atual }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->kmavencer }}</td>
                    <td class="whitespace-nowrap">
                        {{ format_date($manutencao->datavencer, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->dias_vencidos }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->nome_filial }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-center">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </x-bladewind::table>
    </div>
</x-bladewind::card>

<script>
    function preventivaGerar(idPreOs) {
        return {
            selectedRows: JSON.parse(localStorage.getItem('selectedRows')) || [],

            toggleRow(id) {
                if (!id || !/^\d+$/.test(id)) return; // Só aceita IDs numéricos

                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
                } else {
                    this.selectedRows.push(id);
                }

                this.atualizarLocalStorage();
                this.sincronizarLinhaCheckbox(id);
            },

            toggleSelecionarTodos(checked) {
                const idsNaPaginaAtual = Array.from(document.querySelectorAll('.rowCheckbox'))
                    .map(checkbox => checkbox.getAttribute('data-id'))
                    .filter(id => id && /^\d+$/.test(id));

                if (checked) {
                    idsNaPaginaAtual.forEach(id => {
                        if (!this.selectedRows.includes(id)) {
                            this.selectedRows.push(id);
                        }
                    });
                } else {
                    this.selectedRows = this.selectedRows.filter(id => !idsNaPaginaAtual.includes(id));
                }

                this.atualizarLocalStorage();
                this.sincronizarTodosCheckboxes(checked);
            },

            confirmarRows() {
                const ids = this.selectedRows.join(',');
                window.location.href =
                    `/admin/manutencaopreordemserviconova/gerarpreventiva?ids=${ids}&preos=${idPreOs}`;
                console.log(`/admin/manutencaopreordemserviconova/gerarpreventiva?ids=${ids}&preos=${idPreOs}`);
            },

            init() {
                const idsNaPaginaAtual = Array.from(document.querySelectorAll('.rowCheckbox'))
                    .map(checkbox => checkbox.getAttribute('data-id'))
                    .filter(id => id && /^\d+$/.test(id));

                document.querySelectorAll('.rowCheckbox').forEach(checkbox => {
                    const id = checkbox.getAttribute('data-id');
                    const row = checkbox.closest('tr');

                    if (this.selectedRows.includes(id)) {
                        row.classList.add('bg-gray-100');
                        checkbox.checked = true;
                    } else {
                        row.classList.remove('bg-gray-100');
                        checkbox.checked = false;
                    }
                });

                const checkboxSelecionarTodos = document.getElementById('selectAllCheckbox');
                if (checkboxSelecionarTodos) {
                    const todosSelecionados = idsNaPaginaAtual.length > 0 && idsNaPaginaAtual.every(id =>
                        this.selectedRows.includes(id));
                    checkboxSelecionarTodos.checked = todosSelecionados;
                }

                this.observarCheckboxSelecionarTodos();
            },

            sincronizarLinhaCheckbox(id) {
                const checkbox = document.querySelector(`.rowCheckbox[data-id="${id}"]`);
                const row = checkbox ? checkbox.closest('tr') : null;

                if (checkbox) {
                    if (this.selectedRows.includes(id)) {
                        row.classList.add('bg-gray-100');
                        checkbox.checked = true;
                    } else {
                        row.classList.remove('bg-gray-100');
                        checkbox.checked = false;
                    }
                }

                // Atualizar também o "Selecionar Todos" se necessário
                this.init();
            },

            sincronizarTodosCheckboxes(checked) {
                document.querySelectorAll('.rowCheckbox').forEach(checkbox => {
                    const id = checkbox.getAttribute('data-id');
                    const row = checkbox.closest('tr');

                    if (checked) {
                        row.classList.add('bg-gray-100');
                        checkbox.checked = true;
                    } else {
                        row.classList.remove('bg-gray-100');
                        checkbox.checked = false;
                    }
                });
            },

            observarCheckboxSelecionarTodos() {
                const checkboxSelecionarTodos = document.getElementById('selectAllCheckbox');
                if (checkboxSelecionarTodos && !checkboxSelecionarTodos.hasAttribute('data-listener')) {
                    checkboxSelecionarTodos.setAttribute('data-listener', 'true');
                    checkboxSelecionarTodos.addEventListener('change', (e) => {
                        this.toggleSelecionarTodos(e.target.checked);
                    });
                }
            },

            atualizarLocalStorage() {
                if (this.selectedRows.length === 0) {
                    localStorage.removeItem('selectedRows');
                } else {
                    localStorage.setItem('selectedRows', JSON.stringify(this.selectedRows));
                }
            }
        };
    }

    // Inicialização manual
    document.addEventListener('DOMContentLoaded', function() {
        const idPreOs = '{{ $idPreOs }}'; // Passando o parâmetro dinamicamente
        const preventiva = preventivaGerar(idPreOs);

        // Vincular eventos
        document.querySelectorAll('.rowCheckbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const id = checkbox.getAttribute('data-id');
                preventiva.toggleRow(id);
            });
        });

        document.getElementById('confirmButton').addEventListener('click', () => {
            preventiva.confirmarRows();
        });

        // Inicializar a função
        preventiva.init();
    });
</script>
