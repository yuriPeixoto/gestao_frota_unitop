<form id="form-veiculo" hx-get="{{ route('admin.listagemnotificacoes.index') }}" hx-target="#results-table"
    hx-preserve="[data-select-trigger], [data-dropdown]" hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <div>
            <x-forms.smart-select name="placa" label="Placa" :options="$veiculos"
                :searchUrl="route('admin.api.veiculos.search')" asyncSearch="true" required="true"
                data-select-trigger="true" data-dropdown-id="dropdown-placa" onchange="SelectManager.closeAll()" />
        </div>

        <div>
            {{-- Renavam --}}
            <x-forms.smart-select name="renavam" label="Renavam" placeholder="Selecione o renavam..."
                :options="$renavam" asyncSearch="false" />
        </div>

        <div>
            {{-- Nome Motorista --}}
            <x-forms.smart-select name="motorista_nome" label="Nome Motorista"
                placeholder="Selecione o Nome Motorista..." :options="$nomeMotorista" asyncSearch="false" />
        </div>

        <div>
            {{-- Orgao Autuador --}}
            <x-forms.smart-select name="orgao" label="Orgão Autuador" placeholder="Selecione o Orgão Autuador..."
                :options="$orgaoAutuador" asyncSearch="false" />
        </div>

        <div>
            {{-- Ait --}}
            <x-forms.smart-select name="ait" label="AIT" placeholder="Selecione a ait..." :options="$ait"
                asyncSearch="false" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <x-ui.export-buttons route="admin.listagemnotificacoes" :formats="['pdf', 'csv', 'xls', 'xml']" />

            <div>
                <button type="button" onclick="baixarBoletosLote()"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7v0a3 3 0 116 0v0" />
                    </svg>
                    Boletos
                </button>
            </div>

        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.listagemnotificacoes.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>

            {{-- Botão CONSULTAR
            <button type="submit" id="btn-consultar" name="action" value="consultarNot"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.refresh />
                Consultar Veículo
            </button>
            --}}

        </div>
    </div>




</form>

<script>
    // Sistema de namespace para evitar conflitos
    const SelectManager = {
        initialized: false,
        
        init: function() {
            if (this.initialized) return;
            
            // Fecha dropdowns ao clicar fora
            document.addEventListener('click', (e) => {
                if (!e.target.closest('[data-select-trigger]') && 
                    !e.target.closest('[data-dropdown]')) {
                    this.closeAll();
                }
            });
            
            // HTMX - Re-inicializa após atualizações
            document.body.addEventListener('htmx:afterSwap', () => {
                this.initialized = false;
                this.init();
            });
            
            this.initialized = true;
        },
        
        closeAll: function() {
            document.querySelectorAll('[data-dropdown]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        },
        
        toggle: function(event, dropdownId) {
            event.stopPropagation();
            event.preventDefault();
            
            this.closeAll();
            
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            
            dropdown.classList.toggle('hidden');
            
            if (!dropdown.classList.contains('hidden')) {
                const trigger = event.target.closest('[data-select-trigger]');
                if (trigger) {
                    const rect = trigger.getBoundingClientRect();
                    dropdown.style.position = 'fixed';
                    dropdown.style.left = `${rect.left}px`;
                    dropdown.style.top = `${rect.bottom + window.scrollY}px`;
                    dropdown.style.minWidth = `${rect.width}px`;
                    dropdown.style.zIndex = '9999';
                }
            }
        }
    };

    // Inicializa quando o DOM estiver pronto
    document.addEventListener('DOMContentLoaded', () => SelectManager.init());
</script>