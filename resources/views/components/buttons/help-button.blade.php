<div x-data="{ isOpen: false }" class="relative inline-block">
    <button
        @click="isOpen = !isOpen"
        class="w-10 h-10 rounded-full bg-orange-400 hover:bg-orange-600 flex items-center justify-center text-white hover:text-slate-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        type="button"
        >
        <span class="text-xl font-semibold">?</span>
    </button>

    <div
        x-show = "isOpen"
        @click.away="isOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 w-64 px-4 py-3 mt-2 text-sm bg-slate-100 rounded-lg shadow-lg border border-slate-700"
        style="display: none"
        >
        <div class="text-gray-700">
            {{ $slot }}
        </div>
    </div>


</div>
