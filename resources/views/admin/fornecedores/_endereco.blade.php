<div class="bg-gray-50 p-4 rounded-lg">
    <div id="endereco-form" class="space-y-4">
        <input type="hidden" name="enderecos_json" id="enderecos_json" value="{{ old('enderecos_json', isset($fornecedor) && $fornecedor->endereco
                ? json_encode($fornecedor->endereco->map(function($m) {
                    return [
                        'id_endereco' => $m->id_endereco, // 👈 precisa disso
                        'rua' => $m->rua,
                        'cep' => $m->cep,
                        'complemento' => $m->complemento,
                        'numero' => $m->numero ?? null,
                        'bairro' => $m->bairro,
                        'id_municipio' => $m->id_municipio,
                        'id_uf' => $m->id_uf,
                        'data_inclusao' => optional($m->data_inclusao)->format('d/m/Y H:i'),
                        'data_alteracao' => optional($m->data_alteracao)->format('d/m/Y H:i'),
                    ];
                }))
                : '[]'
            ) }}">


        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="rua" class="block text-sm font-medium text-gray-700">Rua:</label>
                <input type="text" id="rua" name="rua"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="Rua">
            </div>
            <div>
                <label for="cep" class="block text-sm font-medium text-gray-700">CEP:</label>
                <input type="text" id="cep" name="cep"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="CEP">
            </div>
            <div>
                <label for="complemento" class="block text-sm font-medium text-gray-700">Complemento:</label>
                <input type="text" id="complemento" name="complemento"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="Complemento">
            </div>
            <div>
                <label for="numero" class="block text-sm font-medium text-gray-700">Número:</label>
                <input type="text" id="numero" name="numero"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="Número">
            </div>
            <div>
                <label for="bairro" class="block text-sm font-medium text-gray-700">Bairro:</label>
                <input type="text" id="bairro" name="bairro"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="Bairro">
            </div>

            <x-forms.smart-select id="id_municipio" name="id_municipio" label="Município:" placeholder="Selecionar"
                :options="$municipio" :selected="request('id_municipio')" minSearchLength="2"
                display-class="select-display" :searchUrl="route('admin.api.municipio.search')" asyncSearch="true" />

            <x-forms.smart-select id="id_uf" name="id_uf" label="Estado:" placeholder="Selecionar" :options="$uf"
                :selected="request('id_uf')" minSearchLength="2" display-class="select-display" />
        </div>
        <button type="button" id="btn-adicionar-endereco"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar
        </button>

    </div>

    <div class="mt-6">
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="py-3 px-6">Rua</th>
                        <th scope="col" class="py-3 px-6">CEP</th>
                        <th scope="col" class="py-3 px-6">Complemento</th>
                        <th scope="col" class="py-3 px-6">Numero</th>
                        <th scope="col" class="py-3 px-6">Bairro</th>
                        <th scope="col" class="py-3 px-6">Data Inclusão</th>
                        <th scope="col" class="py-3 px-6">Data Alteracão</th>
                        <th scope="col" class="py-3 px-6">Ações</th>

                    </tr>
                </thead>
                <tbody id="endereco-lista">
                    <!-- Será preenchido via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (() => {
    console.log('[enderecos] script carregado');

    const form = document.getElementById('endereco-form');
    const btnAdd = form.querySelector('#btn-adicionar-endereco'); // <— escopo no form
    const hidden = document.getElementById('enderecos_json');
    let enderecosSelecionados = JSON.parse(hidden.value || '[]');
    let indexEnderecoEditando = null;

    document.addEventListener('DOMContentLoaded', () => {
        atualizarTabelaEndereco();
        btnAdd.addEventListener('click', () => {
        console.log('[enderecos] botão clicado');
        if (indexEnderecoEditando === null) adicionarEndereco();
        else salvarEdicaoEndereco();
        });
    });

    function adicionarEndereco() {
        console.log('[enderecos] adicionando...');
        const rua = document.getElementById('rua').value;
        const cep = document.getElementById('cep').value;
        const complemento = document.getElementById('complemento').value;
        const numero = document.getElementById('numero').value;
        const bairro = document.getElementById('bairro').value;

        const id_municipio = form.querySelector('[name="id_municipio"]')?.value || '';
        const id_uf = form.querySelector('[name="id_uf"]')?.value || '';

        if (!rua && !cep) {
            alert('Por favor, preencha pelo menos Rua ou CEP');
            return;
        }

        // CORRIGIDO: Não enviar "-" para campos que devem ser numéricos/nulos
        enderecosSelecionados.push({
            id_endereco: null, 
            rua: rua || null,
            cep: cep || null,
            complemento: complemento || null,
            numero: numero || null,
            bairro: bairro || null,
            id_municipio: id_municipio || null,
            id_uf: id_uf || null,
            data_inclusao: new Date().toLocaleString('pt-BR'),
            data_alteracao: null
        });

        atualizarTabelaEndereco();
        limparCamposEndereco();
    }

    window.editarEndereco = function(index) {
        console.log('[enderecos] editar', index);
        const e = enderecosSelecionados[index];
        indexEnderecoEditando = index;

        document.getElementById('rua').value = e.rua !== '-' ? e.rua : '';
        document.getElementById('cep').value = e.cep !== '-' ? e.cep : '';
        document.getElementById('complemento').value = e.complemento !== '-' ? e.complemento : '';
        document.getElementById('numero').value = e.numero !== '-' ? e.numero : '';
        document.getElementById('bairro').value = e.bairro !== '-' ? e.bairro : '';

        const municipio = form.querySelector('[name="id_municipio"]');
        const uf = form.querySelector('[name="id_uf"]');

        if (municipio) {
        municipio.value = e.id_municipio !== '-' ? e.id_municipio : '';
        municipio.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (uf) {
        uf.value = e.id_uf !== '-' ? e.id_uf : '';
        uf.dispatchEvent(new Event('change', { bubbles: true }));
        }

        btnAdd.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Salvar Edição
        `;
    };

    function salvarEdicaoEndereco() {
        console.log('[enderecos] salvando edição');
        const rua = document.getElementById('rua').value;
        const cep = document.getElementById('cep').value;
        const complemento = document.getElementById('complemento').value;
        const numero = document.getElementById('numero').value;
        const bairro = document.getElementById('bairro').value;
        const id_municipio = form.querySelector('[name="id_municipio"]')?.value || '';
        const id_uf = form.querySelector('[name="id_uf"]')?.value || '';

        enderecosSelecionados[indexEnderecoEditando] = {
        ...enderecosSelecionados[indexEnderecoEditando],
        rua: rua || null,
        cep: cep || null,
        complemento: complemento || null,
        numero: numero || null,
        bairro: bairro || null,
        id_municipio: id_municipio || null,
        id_uf: id_uf || null,
        data_alteracao: new Date().toLocaleString('pt-BR')
        };


        atualizarTabelaEndereco();
        limparCamposEndereco();

        indexEnderecoEditando = null;
        btnAdd.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Adicionar
        `;
    }

    window.removerEndereco = function(index) {
        const e = enderecosSelecionados[index];

        if (confirm('Tem certeza que deseja remover este endereço?')) {
            // se já existe no banco
            if (e.id_endereco) {
                fetch(`/admin/fornecedores/endereco/${e.id_endereco}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(res => {
                    if (res.ok) {
                        enderecosSelecionados.splice(index, 1);
                        atualizarTabelaEndereco();
                    } else {
                        alert('Erro ao excluir endereço.');
                    }
                });
            } else {
                // se for novo (ainda não salvo no banco)
                enderecosSelecionados.splice(index, 1);
                atualizarTabelaEndereco();
            }
        }
    };


    function atualizarTabelaEndereco() {
        console.log('[enderecos] atualizar tabela');
        const tbody = document.getElementById('endereco-lista');
        tbody.innerHTML = '';

        if (enderecosSelecionados.length === 0) {
        tbody.innerHTML = `
            <tr class="bg-white border-b hover:bg-gray-50">
            <td colspan="8" class="py-6 px-6 text-center text-gray-500">
                Nenhum endereço adicionado
            </td>
            </tr>`;
        } else {
        enderecosSelecionados.forEach((e, index) => {
            tbody.innerHTML += `
            <tr class="bg-white border-b hover:bg-gray-50">
                <td class="py-3 px-6">${e.rua || '-'}</td>
                <td class="py-3 px-6">${e.cep || '-'}</td>
                <td class="py-3 px-6">${e.complemento || '-'}</td>
                <td class="py-3 px-6">${e.numero || '-'}</td>
                <td class="py-3 px-6">${e.bairro || '-'}</td>
                <td class="py-3 px-6">${e.data_inclusao || '-'}</td>
                <td class="py-3 px-6">${e.data_alteracao || '-'}</td>
                <td class="py-3 px-6">
                <button 
                    type="button" 
                    onclick="editarEndereco(${index})" 
                    title="Editar endereço"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> 
                    </svg> 
                </button>

                <button 
                    type="button" 
                    onclick="removerEndereco(${index})" 
                    title="Remover endereço"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"> 
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"> 
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /> 
                    </svg> 
                </button>

                </td>
            </tr>`;
        });
        }

        hidden.value = JSON.stringify(enderecosSelecionados);
        console.log('[enderecos] JSON atualizado', hidden.value);
    }

    function limparCamposEndereco() {
        form.querySelector('#rua').value = '';
        form.querySelector('#cep').value = '';
        form.querySelector('#complemento').value = '';
        form.querySelector('#numero').value = '';
        form.querySelector('#bairro').value = '';
        const municipio = form.querySelector('[name="id_municipio"]');
        const uf = form.querySelector('[name="id_uf"]');
        if (municipio) municipio.value = '';
        if (uf) uf.value = '';
    }
    })();
</script>