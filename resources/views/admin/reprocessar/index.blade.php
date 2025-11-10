<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reprocessar Integrações') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Reprocessar Integrações"
                    content="Esta tela permite reprocessar integrações de abastecimentos do ATS e TruckPag. No ATS, o reprocessamento está temporariamente desabilitado. No TruckPag, você pode reprocessar informações de abastecimentos dentro do período especificado." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 bg-white border-b border-gray-200">
                    <!-- Sistema de abas -->
                    <div x-data="{ activeTab: 'ats' }">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8">
                                <button @click="activeTab = 'ats'"
                                    :class="{'border-indigo-500 text-indigo-600': activeTab === 'ats', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'ats'}"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Reprocessar ATS
                                </button>
                                <button @click="activeTab = 'truckpag'"
                                    :class="{'border-indigo-500 text-indigo-600': activeTab === 'truckpag', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'truckpag'}"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Reprocessar TruckPag
                                </button>
                            </nav>
                        </div>

                        <!-- Conteúdo das abas -->
                        <div class="mt-6">
                            <!-- Tab ATS -->
                            <div x-show="activeTab === 'ats'" x-cloak>
                                @include('admin.reprocessar._tab_ats')
                            </div>

                            <!-- Tab TruckPag -->
                            <div x-show="activeTab === 'truckpag'" x-cloak>
                                @include('admin.reprocessar._tab_truckpag')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
    <div class="mt-4 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div class="mt-4 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if (session('info'))
    <div class="mt-4 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p>{{ session('info') }}</p>
        </div>
    </div>
    @endif
</x-app-layout>