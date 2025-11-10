<div x-show="vencimentarioOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">
    <!-- Submenus vão aqui -->
    <a href="{{ route('admin.dashboard-multas.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard-multas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Dashboard Multas
    </a>

    <a href="{{ route('admin.cadastroveiculovencimentario.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.cadastroveiculovencimentario.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Cadastro Veículo
    </a>

    <a href="{{ route('admin.condutores.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.condutores.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Condutores
    </a>

    <a href="{{ route('admin.controlelicencavencimentario.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.controlelicencavencimentario.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Controle Licenca
    </a>

    <a href="{{ route('admin.cronotacografovencimentario.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.cronotacografovencimentario.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Cronotacógrafo
    </a>
    
    <a href="{{ route('admin.restricoesbloqueios.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.restricoesbloqueios.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Restricoes e Bloqueios
    </a>

    <a href="{{ route('admin.licenciamentos.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.licenciamentos.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Licenciamentos
    </a>

    <a href="{{ route('admin.listagemipva.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.listagemipva.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Listagem IPVA
    </a>

    <a href="{{ route('admin.listagemmultas.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.listagemmultas.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Listagem Multas
    </a>

    <a href="{{ route('admin.listagemnotificacoes.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.listagemnotificacoes.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Listagem Notificações
    </a>

    <a href="{{ route('admin.listagemantt.index') }}"
        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.listagemantt.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
        Listagem ANTT
    </a>

</div>
