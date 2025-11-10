<div class="bg-gray-50 p-4 rounded-lg">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Telefone</h3>

    <form id="telefone-form" class="mb-6">
        <input type="hidden" id="id_telefone" name="id_telefone" value="">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="telefone_fixo" class="block text-sm font-medium text-gray-700">Telefone Fixo:</label>
                <input type="text" id="telefone_fixo" name="telefones[0][telefone_fixo]"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="(00) 0000-0000">
            </div>

            <div>
                <label for="telefone_celular" class="block text-sm font-medium text-gray-700">Telefone Celular:</label>
                <input type="text" id="telefone_celular" name="telefones[0][telefone_celular]"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="(00) 00000-0000">
            </div>
        </div>

        <div class="mt-4">
            <button type="button" id="btn_adicionar_telefone"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Adicionar
            </button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Inclusão
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Alteração
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Telefone Fixo
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Telefone Celular
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Pessoal
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody id="telefones-lista" class="bg-white divide-y divide-gray-200">
                @if(isset($telefones) && count($telefones) > 0)
                @foreach($telefones as $telefone)
                <tr data-id="{{ $telefone->id_telefone ?? '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $telefone->data_inclusao ? date('d/m/Y', strtotime($telefone->data_inclusao)) : '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $telefone->data_alteracao ? date('d/m/Y', strtotime($telefone->data_alteracao)) : '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $telefone->telefone_fixo ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $telefone->telefone_celular ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $telefone->id_pessoal ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="text-indigo-600 hover:text-indigo-900"
                            onclick="editarTelefone({{ $telefone->id_telefone ?? 'null' }})">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                        <button type="button" class="ml-2 text-red-600 hover:text-red-900"
                            onclick="excluirTelefone({{ $telefone->id_telefone ?? 'null' }})">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhum telefone cadastrado
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Armazenamento local para telefones antes de enviar ao servidor
    let telefonesTemporarios = [];
    
    document.addEventListener('DOMContentLoaded', function() {
        // Aplicar máscara de telefone para os campos
        const telefoneFixoInput = document.getElementById('telefone_fixo');
        const telefoneCelularInput = document.getElementById('telefone_celular');
        
        if (telefoneFixoInput) {
            telefoneFixoInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                // Formato para telefone fixo: (00) 0000-0000
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                
                e.target.value = value;
            });
        }
        
        if (telefoneCelularInput) {
            telefoneCelularInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                // Formato para celular: (00) 00000-0000
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                
                e.target.value = value;
            });
        }
        
        // Botão de adicionar telefone
        const btnAdicionarTelefone = document.getElementById('btn_adicionar_telefone');
        if (btnAdicionarTelefone) {
            btnAdicionarTelefone.addEventListener('click', function() {
                adicionarTelefoneLocal();
            });
        }
        
        // Adicionar telefones ao formulário principal
        // Adicionar telefones ao formulário principal
        const form = document.getElementById('form-fornecedor');
        if (form) {
            form.addEventListener('submit', function() {
                telefonesTemporarios.forEach((telefone, index) => {
                    const fixoInput = document.createElement('input');
                    fixoInput.type = 'hidden';
                    fixoInput.name = `telefones[${index}][telefone_fixo]`;
                    fixoInput.value = telefone.telefone_fixo || '';
                    form.appendChild(fixoInput);

                    const celularInput = document.createElement('input');
                    celularInput.type = 'hidden';
                    celularInput.name = `telefones[${index}][telefone_celular]`;
                    celularInput.value = telefone.telefone_celular || '';
                    form.appendChild(celularInput);
                });
            });
        }


        
        // Verificar se estamos na edição de um fornecedor existente
        const isEditing = window.location.pathname.includes('/edit');
        const idFornecedor = document.getElementById('contrato_fornecedor_fornecedor_id_fornecedor')?.value;
        
        // Se estamos na edição, carregar telefones via AJAX
        if (isEditing && idFornecedor) {
            carregarTelefones(idFornecedor);
        }
    });
    
    // Função para adicionar telefone localmente (sem enviar ao servidor ainda)
    function adicionarTelefoneLocal() {
        const idTelefone = document.getElementById('id_telefone').value;
        const telefoneFixo = document.getElementById('telefone_fixo').value;
        const telefoneCelular = document.getElementById('telefone_celular').value;
        
        // Validação básica
        if (!telefoneFixo && !telefoneCelular) {
            alert('Informe pelo menos um telefone!');
            return;
        }
        
        // Criar objeto de telefone
        const novoTelefone = {
            id: idTelefone || 'temp_' + Date.now(),
            telefone_fixo: telefoneFixo,
            telefone_celular: telefoneCelular,
            data_inclusao: new Date().toLocaleDateString('pt-BR')
        };
        
        // Se estamos editando um telefone existente
        if (idTelefone && idTelefone.startsWith('temp_')) {
            // Encontrar e atualizar no array local
            const index = telefonesTemporarios.findIndex(t => t.id === idTelefone);
            if (index >= 0) {
                telefonesTemporarios[index] = novoTelefone;
                atualizarLinhaTelefone(novoTelefone);
            } else {
                telefonesTemporarios.push(novoTelefone);
                adicionarLinhaTelefone(novoTelefone);
            }
        } else {
            // Adicionar novo telefone
            telefonesTemporarios.push(novoTelefone);
            adicionarLinhaTelefone(novoTelefone);
        }
        
        // Limpar formulário para próxima entrada
        limparFormularioTelefone();
    }
    
    // Função para carregar telefones de um fornecedor (apenas na edição)
    function carregarTelefones(idFornecedor) {
        fetch(`/admin/api/telefones?id_fornecedor=${idFornecedor}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar telefones');
            }
            return response.json();
        })
        .then(data => {
            if (data.telefones && Array.isArray(data.telefones)) {
                // Limpar tabela
                const tabela = document.getElementById('telefones-lista');
                if (tabela) {
                    tabela.innerHTML = '';
                }
                
                // Adicionar cada telefone à tabela
                data.telefones.forEach(telefone => {
                    adicionarLinhaTelefone(telefone, true);
                });
                
                // Se não houver telefones, mostrar mensagem
                if (data.telefones.length === 0 && tabela) {
                    tabela.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Nenhum telefone cadastrado
                            </td>
                        </tr>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Erro:', error);
        });
    }
    
    // Função para editar um telefone local
    function editarTelefone(idTelefone) {
        // Verificar se é um telefone temporário ou persistido
        if (typeof idTelefone === 'string' && idTelefone.startsWith('temp_')) {
            // Telefone temporário - buscar no array local
            const telefone = telefonesTemporarios.find(t => t.id === idTelefone);
            
            if (telefone) {
                document.getElementById('id_telefone').value = telefone.id;
                document.getElementById('telefone_fixo').value = telefone.telefone_fixo || '';
                document.getElementById('telefone_celular').value = telefone.telefone_celular || '';
                document.getElementById('telefone_fixo').focus();
            }
        } else {
            // Telefone persistido - buscar via API
            fetch(`/admin/api/telefones/${idTelefone}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao buscar telefone');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('id_telefone').value = data.id_telefone;
                document.getElementById('telefone_fixo').value = data.telefone_fixo || '';
                document.getElementById('telefone_celular').value = data.telefone_celular || '';
                document.getElementById('telefone_fixo').focus();
            })
            .catch(error => {
                console.error('Erro:', error);
                alert(error.message);
            });
        }
    }
    
    // Função para excluir telefone
    function excluirTelefone(idTelefone) {
        if (!confirm('Deseja realmente excluir este telefone?')) return;
        
        // Verificar se é um telefone temporário ou persistido
        if (typeof idTelefone === 'string' && idTelefone.startsWith('temp_')) {
            // Telefone temporário - remover do array local
            const index = telefonesTemporarios.findIndex(t => t.id === idTelefone);
            
            if (index >= 0) {
                telefonesTemporarios.splice(index, 1);
                removerLinhaTelefone(idTelefone);
            }
        } else {
            // Telefone persistido - excluir via API
            const idFornecedor = document.getElementById('contrato_fornecedor_fornecedor_id_fornecedor')?.value;
            
            if (!idFornecedor) {
                alert('ID do fornecedor não encontrado');
                return;
            }
            
            fetch(`/admin/api/telefones/${idTelefone}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao excluir telefone');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Telefone excluído com sucesso!');
                    removerLinhaTelefone(idTelefone);
                } else {
                    throw new Error(data.message || 'Erro ao excluir telefone');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert(error.message);
            });
        }
    }
    
    // Funções auxiliares para manipulação da tabela
    function limparFormularioTelefone() {
        document.getElementById('id_telefone').value = '';
        document.getElementById('telefone_fixo').value = '';
        document.getElementById('telefone_celular').value = '';
    }
    
    function adicionarLinhaTelefone(telefone, isPersistido = false) {
        const tabela = document.getElementById('telefones-lista');
        const linhaSemTelefones = tabela.querySelector('tr td[colspan="6"]');
        
        if (linhaSemTelefones) {
            tabela.innerHTML = '';
        }
        
        const novaLinha = document.createElement('tr');
        novaLinha.dataset.id = telefone.id || telefone.id_telefone;
        
        const dataInclusao = telefone.data_inclusao || '-';
        const dataAlteracao = telefone.data_alteracao || '-';
        
        novaLinha.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dataInclusao}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dataAlteracao}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${telefone.telefone_fixo || ''}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${telefone.telefone_celular || ''}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${isPersistido ? (telefone.id_pessoal || '') : '(Pendente)'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button type="button" class="text-indigo-600 hover:text-indigo-900" 
                    onclick="editarTelefone('${telefone.id || telefone.id_telefone}')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </button>
                <button type="button" class="ml-2 text-red-600 hover:text-red-900" 
                    onclick="excluirTelefone('${telefone.id || telefone.id_telefone}')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>
            </td>
        `;
        
        tabela.appendChild(novaLinha);
    }
    
    function atualizarLinhaTelefone(telefone, isPersistido = false) {
        const id = telefone.id || telefone.id_telefone;
        const linha = document.querySelector(`#telefones-lista tr[data-id="${id}"]`);
        
        if (!linha) {
            adicionarLinhaTelefone(telefone, isPersistido);
            return;
        }
        
        const dataAlteracao = telefone.data_alteracao || '-';
        
        linha.children[1].textContent = dataAlteracao;
        linha.children[2].textContent = telefone.telefone_fixo || '';
        linha.children[3].textContent = telefone.telefone_celular || '';
        linha.children[4].textContent = isPersistido ? (telefone.id_pessoal || '') : '(Pendente)';
    }
    
    function removerLinhaTelefone(idTelefone) {
        const linha = document.querySelector(`#telefones-lista tr[data-id="${idTelefone}"]`);
        
        if (linha) {
            linha.remove();
            
            // Se não houver mais linhas, exibir mensagem
            const tabela = document.getElementById('telefones-lista');
            if (tabela.childElementCount === 0) {
                tabela.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum telefone cadastrado
                        </td>
                    </tr>
                `;
            }
        }
    }
</script>
@endpush