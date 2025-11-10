@php
$keyboardShortcuts = config('keyboard-shortcuts', []);

$shortcutsJson = json_encode($keyboardShortcuts);
@endphp

<div x-data="{
    showShortcutHelper: false,
    searchTerm: '',
    activeGroup: null,
    shortcuts: {{ $shortcutsJson }},
    
    get filteredShortcuts() {
        if (!this.searchTerm.trim()) {
            return this.shortcuts;
        }
        
        const term = this.searchTerm.toLowerCase();
        const result = {};
        
        Object.keys(this.shortcuts).forEach(group => {
            if (!this.shortcuts[group] || !this.shortcuts[group].items) return;
            
            const filteredItems = Object.entries(this.shortcuts[group].items)
                .filter(([key, value]) => {
                    return value.title.toLowerCase().includes(term) || 
                        key.toLowerCase().includes(term);
                });
                
            if (filteredItems.length > 0) {
                result[group] = {
                    title: this.shortcuts[group].title,
                    items: Object.fromEntries(filteredItems)
                };
            }
        });
        
        return result;
    },
    
    toggleGroup(group) {
        this.activeGroup = this.activeGroup === group ? null : group;
    }
}" x-init="
    // Configurar listener para Shift+?
    window.addEventListener('keydown', (e) => {
        if (e.key === '?' && e.shiftKey && 
            !e.target.matches('input, textarea, [contenteditable]')) {
            e.preventDefault();
            showShortcutHelper = !showShortcutHelper;
            if (showShortcutHelper) {
                $nextTick(() => {
                    $refs.searchInput?.focus();
                });
            }
        }
    });
    
    // Iniciar com o primeiro grupo aberto
    if (Object.keys(shortcuts).length > 0) {
        activeGroup = Object.keys(shortcuts)[0];
    }
">
    {{-- Modal de atalhos de teclado --}}
    <div x-cloak
         x-show="showShortcutHelper"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 bg-gray-900 bg-opacity-90 flex items-center justify-center p-4 z-50 overflow-hidden"
         @keydown.escape="showShortcutHelper = false">
        
        <div @click.away="showShortcutHelper = false" 
             class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
            
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Atalhos de Teclado</h2>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input 
                            x-ref="searchInput"
                            x-model="searchTerm" 
                            type="search" 
                            placeholder="Buscar atalho..." 
                            class="px-4 py-2 pl-10 pr-4 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 w-64"
                        />
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button @click="showShortcutHelper = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Conteúdo -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="(group, groupKey) in filteredShortcuts" :key="groupKey">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                            <div @click="toggleGroup(groupKey)" 
                                 class="p-3 bg-gray-100 dark:bg-gray-800 cursor-pointer flex justify-between items-center">
                                <h3 class="font-medium text-gray-800 dark:text-white" x-text="group.title"></h3>
                                <svg :class="{'rotate-180': activeGroup === groupKey}" class="h-5 w-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="activeGroup === groupKey" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 class="p-3">
                                <ul class="space-y-1 text-sm">
                                    <template x-for="(shortcut, key) in group.items" :key="key">
                                        <li class="py-1 px-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded flex justify-between">
                                            <span class="text-gray-800 dark:text-gray-200" x-text="shortcut.title"></span>
                                            <code class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs font-mono">
                                                Ctrl + <span x-text="key"></span>
                                            </code>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Mensagem de nenhum resultado encontrado na busca -->
                <div 
                    x-show="Object.keys(filteredShortcuts).length === 0" 
                    class="text-center py-10 text-gray-500 dark:text-gray-400"
                >
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 2a10 10 0 110 20 10 10 0 010-20z"></path>
                    </svg>
                    <p class="mt-2 text-lg">Nenhum atalho encontrado para "<span x-text="searchTerm"></span>"</p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">
                Pressione <code class="px-1 py-0.5 bg-gray-200 dark:bg-gray-700 rounded font-mono">Shift + ?</code> a qualquer momento para abrir este menu
            </div>
        </div>
    </div>
</div>

{{-- Script para atalhos de teclado global --}}
<script>
    // Adicionar detecção de atalhos Ctrl+número
    document.addEventListener('DOMContentLoaded', function() {
        // Dados dos atalhos
        const shortcuts = @json($keyboardShortcuts);
        
        let keySequence = '';
        let keyTimeout = null;
        
        document.addEventListener('keydown', function(e) {
            // Ignorar se estiver em um campo de texto
            if (e.target.matches('input, textarea, [contenteditable]')) {
                return;
            }
            
            // Detectar Ctrl+número
            if (e.ctrlKey && !e.shiftKey && !e.altKey && /^\d$/.test(e.key)) {
                e.preventDefault();
                keySequence += e.key;
                
                if (keyTimeout) {
                    clearTimeout(keyTimeout);
                }
                
                keyTimeout = setTimeout(function() {
                    processShortcut(keySequence);
                    keySequence = '';
                    keyTimeout = null;
                }, 1000);
            }
        });
        
        // Processar atalho
        function processShortcut(sequence) {
            let found = false;
            
            // Buscar em todos os grupos
            for (const group in shortcuts) {
                // E buscar nos itens de cada grupo
                const items = shortcuts[group].items || {};
                for (const key in items) {
                    if (key === sequence) {
                        const url = items[key].url;
                        if (url) {
                            window.location.href = url;
                            found = true;
                            break;
                        }
                    }
                }
                if (found) break;
            }
            
            // Feedback para atalho não encontrado
            if (!found) {
                showToast(`Atalho Ctrl+${sequence} não encontrado`);
            }
        }
        
        // Mostrar mensagem toast
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(function() {
                toast.style.opacity = '0';
                setTimeout(function() {
                    document.body.removeChild(toast);
                }, 300);
            }, 2000);
        }
        
        // Adicionar badges aos links
        setTimeout(function() {
            for (const group in shortcuts) {
                for (const key in shortcuts[group].items) {
                    const url = shortcuts[group].items[key].url;
                    const links = document.querySelectorAll(`a[href="${url}"]`);
                    
                    links.forEach(link => {
                        if (!link.dataset.shortcut) {
                            link.dataset.shortcut = key;
                            
                            const badge = document.createElement('span');
                            badge.className = 'ml-auto text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono';
                            badge.textContent = `Ctrl+${key}`;
                            
                            if (link.classList.contains('flex') || link.classList.contains('inline-flex')) {
                                link.appendChild(badge);
                            } else {
                                link.classList.add('flex', 'items-center', 'justify-between');
                                
                                const originalContent = link.innerHTML;
                                const contentSpan = document.createElement('span');
                                contentSpan.innerHTML = originalContent;
                                
                                link.innerHTML = '';
                                link.appendChild(contentSpan);
                                link.appendChild(badge);
                            }
                        }
                    });
                }
            }
        }, 500);
    });
</script>