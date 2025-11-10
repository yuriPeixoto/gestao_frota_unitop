<div x-show="veiculosOpen" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2">

    <!-- Cadastro de Veiculos -->
    @can('ver_veiculo')
        <a href="{{ route('admin.veiculos.index') }}"
            class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.veiculos.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
            Cadastro de Veiculos
        </a>
    @endcan

    <!-- Submenu Certificados -->
    <div x-data="{ certificadosOpen: false }" class="relative">
        @php
            $hasCertificadosVeiculosPermissions =
                auth()->user()->can('ver_certificadoveiculos') || auth()->user()->can('ver_testefumaca');
        @endphp

        @if ($hasCertificadosVeiculosPermissions)
            <button @click="certificadosOpen = !certificadosOpen"
                class="w-full flex items-center px-4 py-2.5 text-sm font-medium {{ request()->routeIs('admin.certificadoveiculos.*', 'admin.testefumaca.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Certificados
                <svg x-bind:class="{ 'rotate-180': certificadosOpen }" class="w-4 h-4 ml-auto transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @include('components.menus.certificados')
        @endif
    </div>

    <!-- Submenu Controle de Licenças -->
    <div x-data="{ controlelicencasOpen: false }" class="relative">
        @php
            $hasControleLicencasPermissions =
                auth()->user()->can('ver_licenciamentoveiculo') ||
                auth()->user()->can('ver_ipvaveiculo') ||
                auth()->user()->can('ver_seguroobrigatorio');
        @endphp

        @if ($hasControleLicencasPermissions)
            <button @click="controlelicencasOpen = !controlelicencasOpen"
                class="w-full flex items-center px-4 py-2.5 text-sm font-medium {{ request()->routeIs('admin.licenciamentoveiculos.*', 'admin.ipvaveiculos.*', 'admin.seguroobrigatorio.*', 'admin.lancipvalicenciamentoseguros.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Controle de Licenças
                <svg x-bind:class="{ 'rotate-180': controlelicencasOpen }" class="w-4 h-4 ml-auto transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="controlelicencasOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95" class="pl-6 space-y-2 mt-2 ">

                <!-- Cadastro Licenciamento de Veículos -->
                @can('ver_licenciamento_veiculo')
                    <a href="{{ route('admin.licenciamentoveiculos.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.licenciamentoveiculos.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Cadastro Licenciamento de Veículos
                    </a>
                @endcan

                <!-- Cadastro IPVA -->
                @can('ver_ipva_veiculo')
                    <a href="{{ route('admin.ipvaveiculos.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.ipvaveiculos.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Cadastro IPVA
                    </a>
                @endcan

                <!-- Cadastro Seguro Obrigatório -->
                @can('ver_seguro_obrigatorio')
                    <a href="{{ route('admin.seguroobrigatorio.index') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.seguroobrigatorio.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Cadastro Seguro Obrigatório
                    </a>
                @endcan

                <!-- Lançamentos em Lote IPVA/Licenciamento/Seguro Obrigatório -->
                @if (auth()->user()->can('ver_ipva_veiculo') ||
                        auth()->user()->can('ver_licenciamento_veiculo') ||
                        auth()->user()->can('ver_seguro_obrigatorio'))
                    <a href="{{ route('admin.lancipvalicenciamentoseguros.create') }}"
                        class="block px-4 py-2 text-sm font-medium {{ request()->routeIs('admin.lancipvalicenciamentoseguros.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                        Lançamentos em Lote IPVA/Licenciamento/Seguro Obrigatório
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Submenu Multas -->
    <div x-data="{ multasOpen: false }" class="relative">
        @php
            $hasMultasVeiculosPermissions =
                auth()->user()->can('ver_multa') || auth()->user()->can('ver_classificacao_multa');
        @endphp

        @if ($hasMultasVeiculosPermissions)
            <button @click="multasOpen = !multasOpen"
                class="w-full flex items-center px-4 py-2.5 text-sm font-medium {{ request()->routeIs('admin.multas.*', 'admin.classificacaomulta.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50' }} rounded-lg transition-all duration-200">
                Multas
                <svg x-bind:class="{ 'rotate-180': multasOpen }" class="w-4 h-4 ml-auto transition-transform"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            @include('components.menus.multas')
        @endif
    </div>

    <div x-data="{ veiculorelatorio: false }">
        <a href="#" @click="veiculorelatorio = !veiculorelatorio"
            class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
            <span class="tracking-tight">Relatórios</span>
            <svg x-bind:class="{ 'rotate-180': veiculorelatorio }" class="w-4 h-4 ml-2 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <div x-show="veiculorelatorio" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="pl-4 mt-2 max-h-48 overflow-y-auto space-y-2">

            {{-- Itens comentados - mantidos conforme original --}}


            <a href="{{ route('admin.relatorioconsultarveiculo.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Consultar de Veiculos
            </a>
            <a href="{{ route('admin.relatoriocompraevendaveiculo.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Compra e Venda de Veiculos
            </a>
            <a href="{{ route('admin.relatoriocontacorrentefornecedor.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Conta Corrente Fornecedor
            </a>
            <a href="{{ route('admin.relatorioextratoipva.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Extrato IPVA
            </a>
            <a href="{{ route('admin.relatoriohistoricokm.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Historico de Km do Veiculo
            </a>
            <a href="{{ route('admin.relatorioipvalicenciamento.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                IPVA e Licenciamento por Veiculo
            </a>
            <a href="{{ route('admin.relatoriocertificadoveiculo.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorios de Certificados
            </a>
            <a href="{{ route('admin.relatoriomultas.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Multas
            </a>
            <a href="{{ route('admin.relatorioveiculos.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Relatorio de Veiculos
            </a>
            <a href="{{ route('admin.relatoriotransferenciaveiculo.index') }}"
                class="block px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                Transferencias de Veiculos ( Filiais)
            </a>


        </div>
    </div>
</div>
