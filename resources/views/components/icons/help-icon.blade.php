@props([
    'title' => 'Ajuda',
    'content' => 'Nenhuma informação de ajuda disponível para esta tela.'
])

<div x-data="{ isHelpModalOpen: false }">
    <button
        type="button"
        @click="isHelpModalOpen = true"
        class="text-orange-500 hover:text-orange-700 focus:outline-none ml-2"
        aria-label="Abrir modal de ajuda"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>

    {{-- Modal de Ajuda --}}
    <div
        x-show="isHelpModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none"
    >
        <div
            @click.away="isHelpModalOpen = false"
            class="relative w-auto max-w-3xl mx-auto my-6"
        >
            <div class="relative flex flex-col w-full bg-white border-0 rounded-lg shadow-lg outline-none focus:outline-none">
                {{-- Header --}}
                <div class="flex items-start justify-between p-5 border-b border-solid rounded-t border-blueGray-200 bg-orange-50">
                    <h3 class="text-2xl font-semibold text-orange-800">
                        {{ $title }}
                    </h3>
                    <button
                        type="button"
                        @click="isHelpModalOpen = false"
                        class="float-right p-1 ml-auto text-3xl font-semibold leading-none text-orange-700 bg-transparent border-0 outline-none opacity-70 hover:opacity-100 focus:outline-none"
                    >
                        <span class="block w-6 h-6 text-2xl text-orange-700">
                            ×
                        </span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="relative flex-auto p-6">
                    <p class="my-4 text-lg leading-relaxed text-gray-700">
                        {{ $content }}
                    </p>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end p-6 border-t border-solid rounded-b border-blueGray-200 bg-orange-50">
                    <button
                        type="button"
                        @click="isHelpModalOpen = false"
                        class="px-6 py-2 mb-1 mr-1 text-sm font-bold text-orange-700 uppercase transition-all duration-150 ease-linear outline-none hover:bg-orange-100 focus:outline-none"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
