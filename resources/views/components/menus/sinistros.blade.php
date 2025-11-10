<div x-show="sinistrosOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">

    <!-- Sinistros -->
    @can('ver_sinistro')
    <a href="{{ route('admin.sinistros.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.sinistros.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Sinistros
    </a>

    <div x-data="{ submenuRelatoriosSinistosOpen: false }">
        <a href="#" @click="submenuRelatoriosSinistosOpen = !submenuRelatoriosSinistosOpen"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            Relatórios
        </a>

        <div x-show="submenuRelatoriosSinistosOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 space-y-2 mt-2">

            {{-- <a href="{{ route('admin.relatorios.sinistro.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Sinistro
            </a>

            <a href="{{ route('admin.relatorios.sinistrogeral.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório Geral Sinistro
            </a>

            <a href="{{ route('admin.relatorios.relatoriosinistro.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatório por Sinistro
            </a> --}}

            <a href="{{ route('admin.relatoriosinistro.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Sinistro
            </a>
            <a href="{{ route('admin.relatoriogeralsinistro.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Geral de Sinistro
            </a>
            <a href="{{ route('admin.relatoriosinistroll.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Sinistro
            </a>
        </div>
    </div>
    @endcan
</div>