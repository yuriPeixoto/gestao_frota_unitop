<div class="relative inline-block">
    <button
        class="dropdown-button bg-white border px-4 py-2 rounded shadow flex items-center space-x-2 {{ $buttonClass }}">
        @if ($buttonIcon)
            <x-dynamic-component :component="'icons.' . $buttonIcon" class="w-4 h-4" />
        @endif
        <span>{{ $buttonText }}</span>
    </button>

    <ul
        class="dropdown-menu absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg hidden z-50 {{ $menuClass }}">
        @foreach ($menuItems as $item)
            <li>
                @if ($item['type'] === 'link')
                    <a href="{{ $item['url'] ?? '#' }}"
                        class="flex items-center px-4 py-2 hover:bg-gray-100 {{ $item['class'] ?? 'text-gray-700' }}"
                        @if (isset($item['onclick'])) onclick="{{ $item['onclick'] }}" @endif>
                        @if (isset($item['icon']))
                            <x-dynamic-component :component="'icons.' . $item['icon']" class="h-4 w-4 mr-2 {{ $item['iconClass'] ?? '' }}" />
                        @endif
                        {{ $item['text'] }}
                    </a>
                @elseif($item['type'] === 'button')
                    <button
                        class="flex items-center px-4 py-2 hover:bg-gray-100 w-full text-left {{ $item['class'] ?? 'text-gray-700' }}"
                        @if (isset($item['onclick'])) onclick="{{ $item['onclick'] }}" @endif
                        @if (isset($item['wireClick'])) wire:click="{{ $item['wireClick'] }}" @endif>
                        @if (isset($item['icon']))
                            <x-dynamic-component :component="'icons.' . $item['icon']"
                                class="h-4 w-4 mr-2 {{ $item['iconClass'] ?? '' }}" />
                        @endif
                        {{ $item['text'] }}
                    </button>
                @elseif($item['type'] === 'divider')
                    <hr class="my-1">
                @endif
            </li>
        @endforeach

        {{ $slot }}
    </ul>
</div>

@pushOnce('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".dropdown-button");

            buttons.forEach(button => {
                button.addEventListener("click", function(event) {
                    event.stopPropagation();

                    // Fecha todos os outros dropdowns
                    document.querySelectorAll(".dropdown-menu").forEach(menu => {
                        if (menu !== this.nextElementSibling) {
                            menu.classList.add("hidden");
                        }
                    });

                    // Alterna apenas o menu clicado
                    this.nextElementSibling.classList.toggle("hidden");
                });
            });

            // Fecha o dropdown ao clicar fora
            document.addEventListener("click", function() {
                document.querySelectorAll(".dropdown-menu").forEach(menu => {
                    menu.classList.add("hidden");
                });
            });
        });
    </script>
@endPushOnce
