<style>
    .select-display {
        border: 1px dashed red;
        padding: 2px;
    }
</style>
<div class="bg-gray-50 p-4 rounded-lg">
    <div id="mecanico-form" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="mecanicos_json" id="mecanicos_json" value="{{ old('mecanicos_json', json_encode($fornecedor->mecanicos->map(function($m) {
                return [
                    'id' => $m->id_fornecedor_x_mecanico,
                    'nome_mecanico' => $m->nome_mecanico,
                    'id_user_mecanico' => $m->id_user_mecanico,
                    'nome_interno' => $m->mecanicoInterno->name ?? null,
                    'data_inclusao' => $m->data_inclusao ? \Carbon\Carbon::parse($m->data_inclusao)->format('d/m/Y H:i') : null,
                    'data_alteracao' => $m->data_alteracao ? \Carbon\Carbon::parse($m->data_alteracao)->format('d/m/Y H:i') : null,
                ];
            }))) }}">

            <div>
                <label for="nome_mecanico" class="block text-sm font-medium text-gray-700">Nome Mecânico Externo</label>
                <input type="text" id="nome_mecanico" name="nome_mecanico"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="Nome Mecânico">
            </div>

            <x-forms.smart-select id="id_user_mecanico" name="id_user_mecanico" label="Mecânico Interno:"
                placeholder="Selecionar" :options="$mecanicosSelect" :searchUrl="route('admin.api.users.search')"
                :selected="request('id_user_mecanico')" asyncSearch="true" minSearchLength="2"
                display-class="select-display" />


        </div>

        <button type="button" id="btn-adicionar"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar
        </button>
    </div>

    <table class="w-full text-sm text-left text-gray-700">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
            <tr>
                <th scope="col" class="py-3 px-6">Nome Mecânico</th>
                <th scope="col" class="py-3 px-6">Mecânico Interno</th>
                <th scope="col" class="py-3 px-6">Data Inclusão</th>
                <th scope="col" class="py-3 px-6">Data Alteração</th>
                <th scope="col" class="py-3 px-6">Ações</th>
            </tr>
        </thead>
        <tbody id="mecanico-lista">
            <!-- Será preenchido via JavaScript -->
        </tbody>
    </table>
</div>

<script>
    let mecanicosSelecionados = JSON.parse(document.getElementById('mecanicos_json').value || '[]');
    let indexEditando = null; // guarda o índice do item em edição

    document.addEventListener('DOMContentLoaded', function() {
        atualizarTabela();
    });

    document.getElementById('btn-adicionar').addEventListener('click', function() {
        if (indexEditando === null) {
            adicionarMecanico();
        } else {
            salvarEdicao();
        }
    });

    function adicionarMecanico() {
        let nome_mecanico = document.getElementById('nome_mecanico').value;
        let idInterno = document.getElementById('id_user_mecanico').value;
        
        let nomeInterno = '';
        let alpineComponent = document.querySelector('[data-id="id_user_mecanico"]');
        if (alpineComponent && alpineComponent.__x) {
            const alpineState = alpineComponent.__x.$data;
            if (alpineState.selectedLabels && alpineState.selectedLabels.length > 0) {
                nomeInterno = alpineState.selectedLabels[0];
            }
        } else {
            let displayElement = document.querySelector('#id_user_mecanico-button [x-text="selectedLabels[0]"]');
            if (displayElement) {
                nomeInterno = displayElement.textContent.trim().replace(/×$/, '').trim();
            }
        }

        if (!nome_mecanico && !idInterno) {
            alert('Por favor, preencha pelo menos um dos campos de mecânico');
            return;
        }

        mecanicosSelecionados.push({
            id: 'new_' + Date.now(),
            nome_mecanico: nome_mecanico || '-',
            id_user_mecanico: idInterno || '-',
            nome_interno: nomeInterno || '-',
            data_inclusao: new Date().toLocaleString(),
            data_alteracao: null
        });

        atualizarTabela();
        limparCampos();
    }

    function editarMecanico(index) {
        let m = mecanicosSelecionados[index];
        indexEditando = index;

        // Preenche os campos
        document.getElementById('nome_mecanico').value = m.nome_mecanico !== '-' ? m.nome_mecanico : '';

        // Select do mecânico interno
        document.getElementById('id_user_mecanico').value = m.id_user_mecanico !== '-' ? m.id_user_mecanico : '';

        // Troca o texto do botão
        document.getElementById('btn-adicionar').innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 13l4 4L19 7" />
            </svg>
            Salvar Edição
        `;
    }

    function salvarEdicao() {
        let nome_mecanico = document.getElementById('nome_mecanico').value;
        let idInterno = document.getElementById('id_user_mecanico').value;

        let nomeInterno = '';
        let alpineComponent = document.querySelector('[data-id="id_user_mecanico"]');
        if (alpineComponent && alpineComponent.__x) {
            const alpineState = alpineComponent.__x.$data;
            if (alpineState.selectedLabels && alpineState.selectedLabels.length > 0) {
                nomeInterno = alpineState.selectedLabels[0];
            }
        } else {
            let displayElement = document.querySelector('#id_user_mecanico-button [x-text="selectedLabels[0]"]');
            if (displayElement) {
                nomeInterno = displayElement.textContent.trim().replace(/×$/, '').trim();
            }
        }
        
        mecanicosSelecionados[indexEditando] = {
            ...mecanicosSelecionados[indexEditando],
            nome_mecanico: nome_mecanico || '-',
            id_user_mecanico: idInterno || '-',
            nome_interno: nomeInterno || '-',   // agora vai atualizar também
            data_alteracao: new Date().toLocaleString()
        };

        atualizarTabela();
        limparCampos();

        // Resetar estado
        indexEditando = null;
        document.getElementById('btn-adicionar').innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar
        `;
    }


    function removerMecanico(index) {
        mecanicosSelecionados.splice(index, 1);
        atualizarTabela();
    }

    function atualizarTabela() {
        let tbody = document.getElementById('mecanico-lista');
        tbody.innerHTML = '';

        if (mecanicosSelecionados.length === 0) {
            tbody.innerHTML = `
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td colspan="5" class="py-6 px-6 text-center text-gray-500">
                        Nenhum mecânico adicionado
                    </td>
                </tr>
            `;
        } else {
            mecanicosSelecionados.forEach((m, index) => {
                tbody.innerHTML += `
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="py-3 px-6">${m.nome_mecanico || '-'}</td>
                        <td class="py-3 px-6">${m.nome_interno || '-'}</td>
                        <td class="py-3 px-6">${m.data_inclusao || '-'}</td>
                        <td class="py-3 px-6">${m.data_alteracao || '-'}</td>
                        <td class="py-3 px-6">
                             <button type="button" onclick="editarMecanico(${index})"
                                    title="Editar Mecânico"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"> 
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> 
                                        </svg>
                            </button>
                            <button type="button" onclick="removerMecanico(${index})"
                                title="Remover Mecânico" 
                                class=" inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"> 
                                        path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /> 
                                    </svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        document.getElementById('mecanicos_json').value = JSON.stringify(mecanicosSelecionados);
    }

    function limparCampos() {
        document.getElementById('nome_mecanico').value = '';
        document.getElementById('id_user_mecanico').value = '';
    }
</script>