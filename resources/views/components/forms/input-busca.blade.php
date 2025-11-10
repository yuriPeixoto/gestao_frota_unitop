@props([
    'target' => '',
    'controller' => '',
    'placeholder' => 'Buscar...'
])

<div
    x-data="searchComponent('{{ $controller }}')"
    x-init="console.log('Alpine component initialized', $el)"
    class="relative"
>
    <input
        type="text"
        x-model.debounce.300ms="query"
        x-on:input="quickSearch()"
        @input.debug="console.log('Input changed', $event.target.value)"
        placeholder="{{ $placeholder }}"
        class="w-full px-3 py-2 border rounded mt-4"
    />

    <div
        x-show="results.length > 0"
        x-cloak
        class="absolute z-10 w-full bg-white border rounded mt-2 shadow-lg"
    >
        <ul>
            <template x-for="result in results" :key="result.id">
                <li
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                    x-text="result.descricao_categoria"
                    @click="$dispatch('select-result', result)"
                ></li>
            </template>
        </ul>
    </div>

    <div x-show="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
        Carregando...
    </div>
</div>
