<div x-show="checklistOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2 ">

    {{-- Itens comentados - mantidos conforme original --}}
    {{-- @can('ver_checklistresposta')
    <a href="{{ route('admin.checklistResposta.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Checklist Respostas
    </a>
    @endcan --}}

    {{-- @can('ver_tipochecklist')
    <a href="{{ route('admin.tipoChecklist.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Tipo Checklist
    </a>
    @endcan --}}

    <!-- Painel Checklists (Link externo - sempre visível se módulo acessível) -->
    <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Painel Checklists
    </a>

    {{-- Item comentado - mantido conforme original --}}
    {{-- @can('ver_checklist')
    <a href="{{ route('admin.checklist.index') }}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Checklist
    </a>
    @endcan --}}

</div>