<div x-show="multasOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">

    <!-- Classificação de Multas -->
    @can('ver_classificacao_multa')
        <a href="{{ route('admin.classificacaomultas.index') }}"
            class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.classificacaomultas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            Classificação de Multas
        </a>
    @endcan

    <!-- Multas -->
    @can('ver_multa')
        <a href="{{ route('admin.multas.index') }}"
            class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.multas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            Multas
        </a>
    @endcan
</div>
