<div x-show="pessoalOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">

    <!-- Pessoal -->
    @can('ver_pessoal')
    <a href="{{ route('admin.pessoas.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.pessoas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Pessoal
    </a>
    @endcan

    <!-- Fornecedor -->
    @can('ver_fornecedor')
    <a href="{{ route('admin.fornecedores.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.fornecedores.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Fornecedor
    </a>
    @endcan

    <div x-data="{ pessoasrelatorio: false }">
        <a href="#" @click="pessoasrelatorio = !pessoasrelatorio"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Relatórios</span>
            <svg x-bind:class="{ 'rotate-180': pessoasrelatorio }" class="w-4 h-4 ml-2 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="pessoasrelatorio" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            {{-- Itens comentados - mantidos conforme original --}}


            <a href=" {{ route('admin.relatorioservicosfornecedores.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Serviços Fornecedores
            </a>
            <a href="{{ route('admin.relatoriocontratofornecedores.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório de Contratos de Fornecedores
            </a>

        </div>
    </div>
</div>