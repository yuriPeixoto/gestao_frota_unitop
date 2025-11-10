<div x-show="certificadosOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2 ">

    <!-- AETs - Autorização Especiais de Trânsito -->
    @can('ver_certificadoveiculos')
        <a href="{{ route('admin.autorizacoesesptransitos.index') }}"
            class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.autorizacoesesptransitos.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            AETs - Autorização Especiais de Trânsito
        </a>
    @endcan

    <!-- Cronotacógrafos -->
    @can('ver_certificadoveiculos')
        <a href="{{ route('admin.cronotacografos.index') }}"
            class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.cronotacografos.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            Cronotacógrafos
        </a>
    @endcan

    <!-- Teste de Frio -->
    @can('ver_teste_frio')
        <a href="{{ route('admin.testefrios.index') }}"
            class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.testefrios.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            Teste de Frio
        </a>
    @endcan

    <!-- Teste de Opacidade -->
    @can('ver_teste_fumaca')
        <a href="{{ route('admin.testefumacas.index') }}"
            class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.testefumacas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            Teste de Opacidade
        </a>
    @endcan
</div>
