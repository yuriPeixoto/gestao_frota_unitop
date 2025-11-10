<style>
    .demo-container {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .demo-title {
        margin-bottom: 30px;
        color: #333;
        font-size: 24px;
    }

    /* Modal Overlay - Simula o fundo do Ctrl+P */
    .pdf-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #525659;
        /* Cor exata do Chrome Ctrl+P */
        z-index: 10000;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .pdf-modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    /* Container do Modal */
    .pdf-modal-container {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
    }

    /* Header do Modal - Simula a barra superior do Chrome */
    .pdf-modal-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: #424242;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        z-index: 10001;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .pdf-modal-title {
        color: #ffffff;
        font-size: 16px;
        font-weight: 500;
    }

    .pdf-modal-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .control-btn {
        background: none;
        border: none;
        color: #ffffff;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    .control-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .close-btn {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 24px;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .close-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Viewer do PDF */
    .pdf-viewer-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        width: 90%;
        max-width: 900px;
        height: calc(100% - 100px);
        margin-top: 60px;
        position: relative;
    }

    .pdf-iframe {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 8px;
    }

    /* Loading */
    .pdf-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #666;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #ddd;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsivo */
    @media (max-width: 768px) {
        .pdf-modal-container {
            padding: 10px;
        }

        .pdf-viewer-container {
            width: 95%;
            height: calc(100% - 80px);
        }

        .pdf-modal-header {
            padding: 0 15px;
        }

        .pdf-modal-title {
            font-size: 14px;
        }
    }

    /* Animação de entrada */
    .pdf-viewer-container {
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }

    .pdf-modal-overlay.active .pdf-viewer-container {
        transform: scale(1);
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro de Veículos') }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <x-bladewind::notification />

                <form method="POST" id="form-veiculo"
                    action="{{ route('admin.cadastroveiculovencimentario.onAction') }}" class="space-y-4"
                    hx-target="#results-table" hx-select="#results-table" hx-trigger="change delay:500ms, search">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-forms.smart-select name="placa" label="Placa" :options="$veiculos"
                            :searchUrl="route('admin.api.veiculos.search')" asyncSearch="true" required="true" />
                    </div>

                    <div class="flex justify-between mt-4">
                        <div class="flex space-x-2">

                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.magnifying-glass />
                                Buscar
                            </button>

                            <button type="submit" name="action" value="cadastrar"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.check />
                                Cadastrar Placas
                            </button>

                            {{-- Botão CONSULTAR
                            <button type="submit" id="btn-consultar" name="action" value="consultar"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.refresh />
                                Consultar Veículo
                            </button>

                            --}}
                        </div>
                    </div>
                </form>


                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.cadastroveiculovencimentario._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @include('admin.cadastroveiculovencimentario._scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
          const problematicContainers = document.querySelectorAll('.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg');
          
          problematicContainers.forEach(container => {
            const smartSelects = container.querySelectorAll('[x-data*="simpleSelect"]');
            
            smartSelects.forEach(smartSelect => {
              smartSelect.classList.add('smart-select-container');
              
              const dropdownButton = smartSelect.querySelector('[x-ref="button"]');
              
              if (dropdownButton) {
                dropdownButton.addEventListener('click', function() {
                  container.classList.toggle('dropdown-active');
                });
                
                document.addEventListener('click', function(event) {
                  if (!smartSelect.contains(event.target)) {
                    container.classList.remove('dropdown-active');
                  }
                });
              }
            });
          });
        });
    </script>

    <script>
        const form = document.getElementById('form-veiculo');
    const btnConsultar = document.getElementById('btn-consultar');

    btnConsultar.addEventListener('click', function (event) {
        const inputPlaca = document.querySelector('input[name="placa"]');
        const placa = inputPlaca?.value;

        if (!placa) {
            alert('Selecione uma placa antes de consultar.');
            event.preventDefault();
            return;
        }

        if (!confirm('Deseja consultar o veículo?')) {
            event.preventDefault();
            return;
        }

        form.action = '{{ route("admin.cadastroveiculovencimentario.consultar") }}';

        // Se quiser garantir o campo "action"
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'action';
        hidden.value = 'consultar';
        form.appendChild(hidden);
    });
    </script>

    @endpush
</x-app-layout>