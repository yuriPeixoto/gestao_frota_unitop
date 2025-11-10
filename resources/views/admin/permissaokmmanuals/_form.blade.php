<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div id="permissaoFormContainer">
                    @if ($errors->any())
                    <div class="bg-red-100 text-red-800 p-4 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados da Permissão de KM Manual</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="id_permissao_km_manual"
                                    class="block text-sm font-medium text-gray-700">Código</label>
                                <input type="text" id="id_permissao_km_manual" name="id_permissao_km_manual" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $permissaokmmanual->id_permissao_km_manual ?? '' }}">
                            </div>

                            <div>
                                <x-forms.smart-select name="id_filial" label="Filial"
                                    placeholder="Selecione a filial..." :options="$formOptions['filiais']"
                                    :selected="old('id_filial', $permissaokmmanual->id_filial ?? '')"
                                    asyncSearch="false" required="true" onSelectCallback="onFilialChanged" />
                                @error('id_filial')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-forms.smart-select name="id_departamento" label="Departamento"
                                    placeholder="Selecione o departamento..." :options="$formOptions['departamentos']"
                                    :selected="old('id_departamento', $permissaokmmanual->id_departamento ?? '')"
                                    asyncSearch="false" onSelectCallback="onDepartamentoChanged" />
                                @error('id_departamento')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-forms.smart-select name="id_categoria" label="Categoria"
                                    placeholder="Selecione a categoria..." :options="$formOptions['categorias']"
                                    :selected="old('id_categoria', $permissaokmmanual->id_categoria ?? '')"
                                    asyncSearch="false" onSelectCallback="onCategoriaChanged" />
                                @error('id_categoria')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-forms.smart-select name="id_veiculo" label="Placa"
                                    placeholder="Selecione o veículo..." :options="$formOptions['veiculos']"
                                    :selected="old('id_veiculo', $permissaokmmanual->id_veiculo ?? '')"
                                    asyncSearch="false" multiple="true" required="true" />
                                @error('id_veiculo')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" id="btnLimpar"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Limpar
                        </button>

                        <a href="{{ route('admin.permissaokmmanuals.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Salvar
                        </button>
                    </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[DEBUG] Inicializando formulário de permissão km manual...');
        
        // Inicializar handlers
        initFormHandlers();
    });
    
    function initFormHandlers() {
        // Botão limpar
        const btnLimpar = document.getElementById('btnLimpar');
        if (btnLimpar) {
            btnLimpar.addEventListener('click', function() {
                const form = document.getElementById('permissaoForm');
                if (form) {
                    // Manter apenas os campos hidden e o token CSRF
                    const hiddenInputs = form.querySelectorAll('input[type="hidden"]');
                    const nonHiddenInputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
                    
                    // Limpar campos não-hidden
                    nonHiddenInputs.forEach(input => {
                        if (input.tagName === 'SELECT') {
                            if (input.multiple) {
                                Array.from(input.options).forEach(option => {
                                    option.selected = false;
                                });
                            } else {
                                input.selectedIndex = 0;
                            }
                            
                            // Disparar evento de mudança para atualizar componentes SmartSelect
                            const event = new Event('change', { bubbles: true });
                            input.dispatchEvent(event);
                        } else if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                }
            });
        }
        
        // Configurar callback de filial (quando a filial muda, filtrar veículos)
        window.onFilialChanged = function(value, formattedValue) {
            console.log('Filial alterada para:', value);
            if (value) {
                // Aqui poderíamos filtrar os veículos por filial se necessário
            }
        };
        
        // Configurar callback para importar todas as placas de um departamento
        window.onDepartamentoChanged = function(value, formattedValue) {
            console.log('Departamento alterado para:', value);
            if (value) {
                const idFilial = document.querySelector('input[name="id_filial"]').value;
                if (idFilial) {
                    // Perguntar se deseja importar todos os veículos deste departamento
                    if (confirm('Deseja importar todas as placas deste departamento para inclusão do Km Manual?')) {
                        importarVeiculosPorDepartamento(idFilial, value);
                    }
                } else {
                    alert('Por favor, selecione uma filial primeiro.');
                }
            }
        };
        
        // Configurar callback para importar todos os veículos de uma categoria
        window.onCategoriaChanged = function(value, formattedValue) {
            console.log('Categoria alterada para:', value);
            if (value) {
                const idFilial = document.querySelector('input[name="id_filial"]').value;
                if (idFilial) {
                    // Perguntar se deseja importar todos os veículos desta categoria
                    if (confirm('Deseja incluir todos os veículos desta categoria para permissão de inclusão de km Manual?')) {
                        importarVeiculosPorCategoria(idFilial, value);
                    }
                } else {
                    alert('Por favor, selecione uma filial primeiro.');
                }
            }
        };
    }
    
    function importarVeiculosPorDepartamento(idFilial, idDepartamento) {
        // Obter o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error('CSRF token não encontrado');
            alert('Erro de segurança: CSRF token não encontrado.');
            return;
        }
        
        // Mostrar loading
        showLoading(true);
        
        // Fazer a requisição para importar veículos
        fetch(`/admin/permissaokmmanuals/importar-por-departamento`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_filial: idFilial,
                id_departamento: idDepartamento
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            showLoading(false);
            
            if (data.success) {
                showToast(data.message, 'success');
                
                // Redirecionar para a listagem após um breve momento
                setTimeout(() => {
                    window.location.href = "{{ route('admin.permissaokmmanuals.index') }}";
                }, 1500);
            } else {
                showToast(data.message || 'Erro ao importar veículos.', 'error');
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Erro:', error);
            showToast('Erro ao importar veículos: ' + error.message, 'error');
        });
    }
    
    function importarVeiculosPorCategoria(idFilial, idCategoria) {
        // Obter o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            console.error('CSRF token não encontrado');
            alert('Erro de segurança: CSRF token não encontrado.');
            return;
        }
        
        // Mostrar loading
        showLoading(true);
        
        // Fazer a requisição para importar veículos
        fetch(`/admin/permissaokmmanuals/importar-por-categoria`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id_filial: idFilial,
                id_categoria: idCategoria
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            showLoading(false);
            
            if (data.success) {
                showToast(data.message, 'success');
                
                // Redirecionar para a listagem após um breve momento
                setTimeout(() => {
                    window.location.href = "{{ route('admin.permissaokmmanuals.index') }}";
                }, 1500);
            } else {
                showToast(data.message || 'Erro ao importar veículos.', 'error');
            }
        })
        .catch(error => {
            showLoading(false);
            console.error('Erro:', error);
            showToast('Erro ao importar veículos: ' + error.message, 'error');
        });
    }
    
    function showLoading(show) {
        // Implementar lógica para mostrar/esconder loading
        // Se tiver um elemento de loading específico, pode usar ele aqui
        if (show) {
            // Mostrar loading
            const loadingHtml = document.createElement('div');
            loadingHtml.id = 'form-loading';
            loadingHtml.classList.add('fixed', 'inset-0', 'flex', 'items-center', 'justify-center', 'bg-gray-900', 'bg-opacity-50', 'z-50');
            loadingHtml.innerHTML = `
                <div class="bg-white p-4 rounded-lg shadow-lg flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Processando...</span>
                </div>
            `;
            document.body.appendChild(loadingHtml);
        } else {
            // Esconder loading
            const loadingElement = document.getElementById('form-loading');
            if (loadingElement) {
                loadingElement.remove();
            }
        }
    }
    
    function showToast(message, type = 'info') {
        // Verificar se existe alguma biblioteca de toast
        if (typeof window.toast === 'function') {
            window.toast(message, type);
        } else if (window.Toastify) {
            // Toastify
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: type === 'success' ? "#48BB78" : type === 'error' ? "#F56565" : "#4299E1"
            }).showToast();
        } else {
            // Fallback para alert em desenvolvimento
            if (type === 'error') {
                alert(message);
            } else {
                // Em produção, criar um toast simples
                const toast = document.createElement('div');
                toast.style.position = 'fixed';
                toast.style.right = '20px';
                toast.style.top = '20px';
                toast.style.padding = '12px 20px';
                toast.style.backgroundColor = type === 'success' ? '#48BB78' : type === 'error' ? '#F56565' : '#4299E1';
                toast.style.color = 'white';
                toast.style.borderRadius = '4px';
                toast.style.zIndex = '9999';
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease-in-out';
                toast.textContent = message;
                
                document.body.appendChild(toast);
                
                // Mostrar com animação
                setTimeout(() => { toast.style.opacity = '1'; }, 10);
                
                // Remover após 3 segundos
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        }
    }
</script>
@endpush