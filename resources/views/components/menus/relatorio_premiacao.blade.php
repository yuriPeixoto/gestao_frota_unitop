<div>
    <div x-data="{ premiosrelatorio: false }">
        <a href="#" @click="premiosrelatorio = !premiosrelatorio"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Cadastros</span>
            <svg x-bind:class="{ 'rotate-180': premiosrelatorio }" class="w-4 h-4 ml-2 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="premiosrelatorio" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            {{-- Itens comentados - mantidos conforme original --}}

            <a href="{{route('admin.tipooperacao.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Tipo Operações
            </a>
            <a href="{{route('admin.deflatoreseventospormotoristas.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Deflatores Eventos por Motoristas
            </a>
            <a href="{{route('admin.deflatorescarvalima.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Deflatores Carvalimas
            </a>
            <a href="{{route('admin.jornadaferiado.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Feriados Prêmio Superação
            </a>
            <a href="{{route('admin.franquiapremiorv.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Franquia Prêmio Rv
            </a>
            <a href="{{route('admin.franquiapremiosmensal.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Franquia Prêmio Mensal
            </a>

        </div>
    </div>
    @can('ver_premio')
    <a href="{{route('admin.premiosuperacao.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Abertura de Prêmio
    </a>
    @endcan
    @can('ver_premio')
    <a href="{{route('admin.manutencaopremio.index')}}"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Manutenção Prêmio
    </a>
    @endcan
    {{-- @can('ver_premio')
    <a href="#"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Distância com Inconsistência
    </a>
    @endcan
    @can('ver_premio')
    <a href="#"
        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
        Placas sem login
    </a>
    @endcan --}}
    <div x-data="{ premiosrelatorio: false }">
        <a href="#" @click="premiosrelatorio = !premiosrelatorio"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Relatórios</span>
            <svg x-bind:class="{ 'rotate-180': premiosrelatorio }" class="w-4 h-4 ml-2 transition-transform" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="premiosrelatorio" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            {{-- Itens comentados - mantidos conforme original --}}

            <a href="{{route('admin.relatorioveiculosemlogin.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Veiculos sem login/Deflatores Detalhados
            </a>
            <a href="{{route('admin.relatoriopremiodeflatores.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Premio Deflatores
            </a>
            <a href="{{ route('admin.relatoriomotoristanaocalculado.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Motoristas Não Calculados
            </a>

            <a href="{{route('admin.relatorioconferenciapremiorvmensal.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Conferência Mensal e RV
            </a>
            <a href="{{route('admin.relatorioconferenciatabelao.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Prêmio Tabelão
            </a>
            <a href="{{route('admin.relatoriopremioconferencia.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Prêmio Tabelão
            </a>
            <a href="{{route('admin.relatoriopremioconferencia.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Prêmio Conferência
            </a>
            <div x-data="{ premiosrelatorio: false }">
                <a href="#" @click="premiosrelatorio = !premiosrelatorio"
                    class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                    <span class="tracking-tight">Finalizados</span>
                    <svg x-bind:class="{ 'rotate-180': premiosrelatorio }" class="w-4 h-4 ml-2 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
                <div x-show="premiosrelatorio" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

                    <a href="{{route('admin.relatoriopremiacaomotorista.index')}}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Relatorio Extrato Premiação Motorista
                    </a>
                    <a href="{{route('admin.relatoriopremiacaomotorista.index')}}"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                        Relatorio Extrato Prévia
                    </a>

                </div>
            </div>
            <a href="{{route('admin.relatorioextratomotoristarh.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio Extrato Motorista/RH
            </a>
            <a href="{{route('admin.relatoriovaloresexcedentes.index')}}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Valores Excedentes Prévia
            </a>


        </div>
        {{-- @can('ver_premio')
        <a href="#"
            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            O Historico de Prêmios
        </a>
        @endcan --}}
    </div>