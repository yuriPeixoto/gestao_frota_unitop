@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Itens para Compra') }}
            </h2>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-4">
            <div class="border-b border-gray-200 bg-white p-6">
                <x-bladewind::notification />

                <!-- Search Form -->
                @include('admin.itensparacompra._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto">
                    @include('admin.itensparacompra._table')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @include('admin.itensparacompra._scripts')

        <style>
            .group-header {
                background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
                border-left: 4px solid #3b82f6;
            }

            .group-header td {
                font-weight: 600;
                color: #374151;
                padding: 12px 16px;
            }

            .group-header .fas {
                color: #3b82f6;
            }
        </style>

        <!-- Script de debug temporário -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('=== DEBUG CHECKLIST ===');
                console.log('1. Botão criar solicitação:', document.getElementById('btn-criar-solicitacao'));
                console.log('2. Contador itens:', document.getElementById('items-count'));
                console.log('3. Checkbox select-all:', document.getElementById('select-all'));
                console.log('4. Checkboxes individuais:', document.querySelectorAll('.item-checkbox'));
                console.log('5. Modal:', document.getElementById('modal-criar-solicitacao'));
                console.log('6. Form:', document.getElementById('form-criar-solicitacao'));

                // Teste manual de clique
                const btn = document.getElementById('btn-criar-solicitacao');
                if (btn) {
                    console.log('Botão encontrado, testando clique...');
                    btn.onclick = function() {
                        console.log('CLIQUE DETECTADO NO BOTÃO!');
                    };
                }

                // Teste checkboxes
                const checkboxes = document.querySelectorAll('.item-checkbox');
                checkboxes.forEach((cb, index) => {
                    cb.onclick = function() {
                        console.log(`Checkbox ${index} clicado:`, this.checked);
                    };
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const problematicContainers = document.querySelectorAll(
                    '.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg');

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
    @endpush
</x-app-layout>
