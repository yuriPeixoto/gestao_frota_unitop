<div x-data="{
    showShortcutHelper: false,
    shortcuts: {
        '1': '/admin/cargos',
        '2': '/admin/filiais',
        '3': '/admin/log-atividades',
        '4': '/admin/permissoes',
        '5': '/admin/tipocategorias',
        '6': '/admin/subcategoriaveiculos',
        '7': '/admin/tipocombustiveis',
        '8': '/admin/tipoequipamentos',
        '9': '/admin/tipoborrachapneus',
        '10': '/admin/tipodesenhopneus',
        '11': '/admin/tipodimensaopneus',
        '12': '/admin/tipofornecedores',
        '13': '/admin/tipoimobilizados',
        '14': '/admin/tipomanutencoes',
        '15': '/admin/tipomotivosinistros',
        '16': '/admin/tiporeformapneus',
    },
    keySequence: '',
    keyTimeout: null,
    init() {
        let timeout;

        window.addEventListener('keydown', (e) => {
            // Ignora se o foco estiver em um input ou textarea
            if (document.activeElement.tagName === 'INPUT' ||
                document.activeElement.tagName === 'TEXTAREA') return;

            // Adiciona um atalho para exibir a lista de atalhos com Shift + ?
            if (e.key === '?' && e.shiftKey) {
                this.showShortcutHelper = !this.showShortcutHelper;
                return;
            }

            if (e.ctrlKey && !isNaN(e.key)) {
                e.preventDefault();
                this.keySequence += e.key;

                if (this.keyTimeout) {
                    clearTimeout(this.keyTimeout);
                }

                this.keyTimeout = setTimeout(() => {
                    const shortcut = this.shortcuts[this.keySequence];
                    if (shortcut) {
                        window.location.href = shortcut;
                    }
                    this.keySequence = '';
                    this.keyTimeout = null;
                }, 500);
            } else if (!e.ctrlKey) {
                this.keySequence = '';
                if (this.keyTimeout) {
                    clearTimeout(this.keyTimeout);
                    this.keyTimeout = null;
                }
            }
        });
    }
}">
    <div x-cloak
         x-show="showShortcutHelper"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 bg-gray-800 bg-opacity-85 text-white p-8 rounded-lg shadow-lg z-50 overflow-y-auto"
         @keydown.escape.window="showShortcutHelper = false">
        <h3 class="font-bold mb-2">Atalhos de Teclado</h3>
        <div>
            <h4 class="font-semibold mb-2 text-xl">Configurações</h4>
            <ul class="space-y-1">
                <li>Ctrl + 1 - Cargos</li>
                <li>Ctrl + 2 - Filiais</li>
                <li>Ctrl + 3 - Log de Atividades</li>
                <li>Ctrl + 4 - Permissões</li>
                <li>Ctrl + 5 - Tipos de Categorias de Veículos</li>
                <li>Ctrl + 6 - Tipos de Subcategorias de Veículos</li>
                <li>Ctrl + 7 - Tipos de Combustíveis</li>
                <li>Ctrl + 8 - Tipos de Equipamentos</li>
                <li>Ctrl + 9 - Tipos de Borracha de Pneus</li>
                <li>Ctrl + 10 - Tipos de Desenho de Pneus</li>
                <li>Ctrl + 11 - Tipos de Dimensão de Pneus</li>
                <li>Ctrl + 12 - Tipos de Fornecedores</li>
                <li>Ctrl + 13 - Tipos de Imobiliizados</li>
                <li>Ctrl + 14 - Tipos de Manutenções</li>
                <li>Ctrl + 15 - Tipos de Motivos de Inistros</li>
                <li>Ctrl + 16 - Tipos de Reforma de Pneus</li>
            </ul>
        </div>

    </div>
</div>
