@props([
    'name',
    'label' => null,
    'placeholder' => 'Selecione...',
    'options' => [],
    'searchUrl' => null,
    'selected' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'valueField' => 'value',
    'textField' => 'label',
    'asyncSearch' => false,
    'minSearchLength' => 3,
    'multiple' => false,
    'onSelectCallback' => null,
    'includeEmptyOption' => true, // Nova propriedade para controlar a opção vazia
    'emptyOptionText' => 'Selecionar...', // Texto para a opção vazia
    'debug' => false, // Parâmetro para habilitar logs de debug
])

@php
    // Compatibilidade: aceitar tanto "selected" quanto "value"
    $selectedValue = $selected ?? ($value ?? null); // Adicionado fallback para null
    $isDisabled = $disabled ?? false;

    // Adiciona a opção vazia se solicitado
    if ($includeEmptyOption ?? true) {
        // Valor padrão true se não especificado
        $emptyOption = [
            $valueField => '',
            $textField => $emptyOptionText ?? 'Selecionar...',
        ];

        // Converte options para array se necessário
        $optionsArray = $options instanceof \Illuminate\Support\Collection ? $options->toArray() : (array) $options;

        $options = array_merge([$emptyOption], $optionsArray);
    }
@endphp

<div x-data="asyncSearchableSelect({
    name: '{{ $name }}',
    searchUrl: '{{ $searchUrl }}',
    initialOptions: {{ json_encode($options) }},
    selected: {{ json_encode($selectedValue) }},
    valueField: '{{ $valueField }}',
    textField: '{{ $textField }}',
    placeholder: '{{ $placeholder }}',
    minSearchLength: {{ $minSearchLength }},
    multiple: {{ $multiple ? 'true' : 'false' }},
    onSelectCallback: '{{ $onSelectCallback }}',
    includeEmptyOption: {{ $includeEmptyOption ? 'true' : 'false' }}, // Passa para o JS
    emptyOptionText: '{{ $emptyOptionText }}', // Passa para o JS
    debug: {{ $debug ? 'true' : 'false' }} // Passa parâmetro de debug para o JS
})" class="w-full" @option-selected.window="handleExternalSelection($event.detail)">
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative mt-1">
        <button type="button" x-ref="button" @click.stop="toggleDropdown($event)" @keydown.arrow-up.prevent="selectPrev"
            @keydown.arrow-down.prevent="selectNext" @keydown.enter.prevent="selectOption"
            @keydown.space.prevent="selectOption" @keydown.escape.prevent="open = false"
            {{ $isDisabled ? 'disabled' : '' }}
            class="{{ $isDisabled ? 'bg-gray-200 text-gray-800 cursor-not-allowed opacity-60' : 'text-gray-900' }} relative flex w-full cursor-default items-center rounded-md border border-gray-300 bg-white py-[10px] pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': {{ isset($error) ? 'true' : 'false' }} }" aria-haspopup="listbox"
            id="{{ $name }}-button">

            <template x-if="selectedValues.length === 0">
                <span class="block truncate text-gray-600" x-text="placeholder"></span>
            </template>

            <template x-if="!multiple && selectedValues.length > 0">
                <span class="block truncate text-gray-900" x-text="selectedLabels[0]"></span>
            </template>

            <template x-if="multiple && selectedValues.length > 0">
                <div class="flex max-w-full flex-wrap gap-1 overflow-hidden">
                    <template x-for="(label, index) in selectedLabels" :key="index">
                        <span
                            class="inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                            <span x-text="label"></span>
                            @if (!$isDisabled)
                                <button @click.stop="removeItem(index)" type="button"
                                    class="ml-1 inline-flex text-blue-500 hover:text-blue-700">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 8.586L6.707 5.293a1 1 0 00-1.414 1.414L8.586 10l-3.293 3.293a1 1 0 101.414 1.414L10 11.414l3.293 3.293a1 1 0 001.414-1.414L11.414 10l3.293-3.293a1 1 0 00-1.414-1.414L10 8.586z" />
                                    </svg>
                                </button>
                            @endif
                        </span>
                    </template>
                </div>
            </template>

            <!-- CLEAR: botão X para limpar -->
            <button x-show="!multiple && selectedValues.length > 0 && !{{ $isDisabled ? 'true' : 'false' }}"
                @click.stop="clearSelection()" type="button"
                class="absolute inset-y-0 right-7 flex items-center pr-1 text-gray-400 hover:text-gray-600"
                x-tooltip.raw="'Remover todos os itens'">
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <svg class="{{ $isDisabled ? 'opacity-50 text-gray-800' : 'text-gray-500' }} h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </button>

        <!-- Input hidden para seleção única -->
        <template x-if="!multiple">
            <input type="hidden" name="{{ $name }}" x-model="selectedValues[0]" id="{{ $name }}">
        </template>

        <!-- Inputs hidden para seleção múltipla -->
        <template x-if="multiple">
            <template x-for="(value, index) in selectedValues" :key="index">
                <input type="hidden" name="{{ $name }}[]" :value="value"
                    :id="`{{ $name }}_${index}`">
            </template>
        </template>
    </div>

    <!-- Dropdown agora renderizado diretamente (sem teleport) -->
    <div x-show="open && !{{ $isDisabled ? 'true' : 'false' }}" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.away="open = false"
        class="absolute z-[10000] max-h-60 overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
        :style="{ top: dropdownTop + 'px', left: dropdownLeft + 'px', width: dropdownWidth + 'px' }" x-ref="dropdown"
        tabindex="-1" role="listbox" :id="`dropdown-${uniqueId}`" style="display: none;">

        <div class="sticky top-0 z-10 bg-white px-1 py-1.5">
            <input x-ref="search" x-model.debounce.300ms="search" @keydown.enter.prevent
                @keydown.arrow-up.prevent="highlightPrev" @keydown.arrow-down.prevent="highlightNext" type="search"
                class="h-10 w-full rounded-md border border-gray-300 px-3 py-2 text-sm leading-5 text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                placeholder="Digite pelo menos {{ $minSearchLength }} caracteres para buscar...">
        </div>

        <div x-show="loading" class="flex items-center justify-center py-4">
            <svg class="h-5 w-5 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="ml-2">Carregando...</span>
        </div>

        <template x-for="(option, index) in filteredOptions" :key="index">
            <div @click="selectOption(option)" @mouseenter="highlightIndex = index"
                :class="{
                    'bg-indigo-100': highlightIndex === index,
                    'text-gray-900': highlightIndex ===
                        index,
                    'text-gray-800': highlightIndex !== index
                }"
                class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-gray-100" role="option"
                :aria-selected="isSelected(option)">
                <span x-text="getOptionText(option)" class="block truncate"
                    :class="{ 'font-semibold': isSelected(option) }"></span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-4" x-show="isSelected(option)">
                    <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </div>
        </template>

        <div x-show="!loading && filteredOptions.length === 0 && search.length >= minSearchLength"
            class="px-3 py-2 text-sm italic text-gray-700">
            Nenhum resultado encontrado
        </div>

        <div x-show="!loading && search.length > 0 && search.length < minSearchLength"
            class="px-3 py-2 text-sm italic text-gray-700">
            Digite pelo menos <span x-text="minSearchLength"></span> caracteres para buscar
        </div>
    </div>

    @if ($error)
        <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@pushOnce('scripts')
    <script>
        // Função para inicializar o componente
        window.asyncSearchableSelect = function(config) {
            return {
                open: false,
                search: '',
                loading: false,
                options: config.initialOptions || [],
                valueField: config.valueField || 'value',
                textField: config.textField || 'label',
                name: config.name || '',
                placeholder: config.placeholder || 'Selecione...',
                searchUrl: config.searchUrl || '',
                selectedValues: Array.isArray(config.selected) ?
                    config.selected : (config.selected ? [config.selected] : []),
                selectedLabels: [],
                selectedObjects: [],
                selectedObjectsJson: '',
                multiple: config.multiple || false,
                minSearchLength: config.minSearchLength || 3,
                onSelectCallback: config.onSelectCallback || null,
                dropdownTop: 0,
                dropdownLeft: 0,
                dropdownWidth: 0,
                uniqueId: Date.now() + Math.floor(Math.random() * 1000),
                // Para navegação por teclado
                highlightIndex: -1,
                includeEmptyOption: config.includeEmptyOption || false,
                emptyOptionText: config.emptyOptionText || 'Selecionar...',
                debug: config.debug || false, // Controle de debug

                // Função auxiliar para logging condicional
                log(message, data = null) {
                    if (this.debug) {
                        console.log(`[SmartSelect:${this.name}] ${message}`, data || '');
                    }
                },

                get filteredOptions() {
                    this.log('Filtering options', {
                        search: this.search,
                        totalOptions: this.options.length,
                        hasSearchUrl: !!this.searchUrl
                    });

                    // Se tem URL de busca assíncrona, retorna as opções sem filtrar
                    // pois o backend já fez a filtragem e ordenação correta
                    if (this.searchUrl) {
                        this.log('Using async search - returning options as-is from backend', this.options);
                        return this.options;
                    }

                    // Se não tem busca assíncrona, faz a filtragem local
                    if (!this.search || this.search.length < 1) {
                        this.log('No search term, returning all options', this.options);
                        return this.options;
                    }

                    const filtered = this.options.filter(option => {
                        const text = this.getOptionText(option);
                        const searchTerm = String(this.search);

                        // Converte ambos para string e depois para lowercase para comparação
                        return text.toLowerCase().includes(searchTerm.toLowerCase());
                    });

                    this.log('Filtered options result', {
                        searchTerm: this.search,
                        filteredCount: filtered.length
                    });

                    return filtered;
                },

                clearSelection() {
                    this.log('Clearing selection', {
                        currentSelection: this.selectedValues
                    });

                    if (this.selectedValues.length === 0) return;

                    // Guarda itens removidos para evento
                    const removed = [...this.selectedObjects];

                    this.selectedValues = [];
                    this.selectedLabels = [];
                    this.selectedObjects = [];
                    this.selectedObjectsJson = '[]';

                    this.log('Selection cleared', {
                        removedItems: removed
                    });

                    // Para seleção única, também limpar o input escondido acontece via x-model/hidden
                    // Dispara eventos de remoção para quem escuta
                    removed.forEach(opt => this.dispatchRemovalEvent(opt));
                },

                init() {
                    this.log('Initializing component', {
                        config: {
                            name: this.name,
                            multiple: this.multiple,
                            searchUrl: this.searchUrl,
                            initialOptions: this.options.length,
                            selectedValues: this.selectedValues,
                            debug: this.debug
                        }
                    });

                    // Inicializar rótulos e objetos selecionados
                    if (this.includeEmptyOption && !this.options.some(opt => this.getOptionValue(opt) === '')) {
                        this.options.unshift({
                            [this.valueField]: '',
                            [this.textField]: this.emptyOptionText
                        });
                        this.log('Added empty option', {
                            emptyOptionText: this.emptyOptionText
                        });
                    }

                    this.selectedObjects = this.selectedValues.map(value => {
                        const option = this.options.find(opt =>
                            String(this.getOptionValue(opt)) === String(value)
                        );
                        return option || {
                            [this.valueField]: value,
                            [this.textField]: value
                        };
                    });

                    this.selectedLabels = this.selectedObjects.map(obj => this.getOptionText(obj));
                    this.selectedObjectsJson = JSON.stringify(this.selectedObjects);

                    this.log('Initial selection mapped', {
                        selectedObjects: this.selectedObjects,
                        selectedLabels: this.selectedLabels
                    });

                    // Observar mudanças na propriedade open
                    this.$watch('open', value => {
                        this.log('Dropdown state changed', {
                            open: value
                        });

                        if (value) {
                            this.updateDropdownPosition();
                            this.$nextTick(() => {
                                if (this.$refs.search) {
                                    this.highlightIndex = -1;
                                    this.$refs.search.focus();
                                    this.log('Search input focused');
                                }
                            });
                        }
                    });

                    // Eventos de ajuste de posição
                    window.addEventListener('scroll', this.updateDropdownPosition.bind(this));
                    window.addEventListener('resize', this.updateDropdownPosition.bind(this));

                    // Eventos para busca e resultados
                    this.$watch('search', value => {
                        this.log('Search term changed', {
                            searchTerm: value,
                            minLength: this.minSearchLength
                        });

                        if (value && value.length >= this.minSearchLength && this.searchUrl) {
                            this.log('Triggering search', {
                                url: this.searchUrl
                            });
                            this.searchItems();
                        }
                    });

                    // Dispatch eventos para seleções iniciais
                    if (this.selectedObjects.length > 0) {
                        this.log('Dispatching initial selection events', {
                            count: this.selectedObjects.length
                        });

                        this.$nextTick(() => {
                            this.selectedObjects.forEach(option => {
                                this.dispatchSelectionEvent(option);
                            });
                        });
                    }

                    this.log('Component initialization completed');
                },

                // Métodos auxiliares para obter valor e texto
                getOptionValue(option) {
                    if (typeof option === 'object' && option !== null) {
                        return option[this.valueField];
                    }
                    return option;
                },

                getOptionText(option) {
                    if (typeof option === 'object' && option !== null) {
                        const text = option[this.textField];
                        return text !== null && text !== undefined ? String(text) : '';
                    }
                    return option !== null && option !== undefined ? String(option) : '';
                },

                isSelected(option) {
                    const value = this.getOptionValue(option);
                    return this.selectedValues.some(v => String(v) === String(value));
                },

                // Evento principal para abrir/fechar o dropdown
                toggleDropdown(event) {
                    this.log('Toggle dropdown triggered', {
                        currentState: this.open,
                        disabled: this.$refs.button?.disabled
                    });

                    // Verificar se está desabilitado
                    if (this.$refs.button && this.$refs.button.disabled) {
                        this.log('Dropdown toggle blocked - component is disabled');
                        return;
                    }

                    // Garantir que o evento não se propague
                    if (event) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    this.open = !this.open;

                    this.log('Dropdown state updated', {
                        newState: this.open
                    });

                    if (this.open) {
                        // Atualizar posição
                        this.updateDropdownPosition();

                        // Apagar a busca anterior
                        this.search = '';
                        this.log('Search cleared for new dropdown session');

                        // Focar no campo de busca
                        this.$nextTick(() => {
                            if (this.$refs.search) {
                                this.$refs.search.focus();
                            }
                        });
                    }

                    window.addEventListener('smart-select:set-value', (e) => {
                        if (e.detail.name === this.name) {
                            this.selectOption({
                                [this.valueField]: e.detail.value,
                                [this.textField]: e.detail.label
                            });
                        }
                    });
                },

                // Atualizar a posição do dropdown
                updateDropdownPosition() {
                    if (!this.open || !this.$refs.button) return;

                    const buttonRect = this.$refs.button.getBoundingClientRect();
                    const scrollY = window.scrollY || window.pageYOffset;
                    const scrollX = window.scrollX || window.pageXOffset;

                    // Posiciona o dropdown abaixo do botão
                    this.dropdownTop = buttonRect.bottom + scrollY;
                    this.dropdownLeft = buttonRect.left + scrollX;
                    this.dropdownWidth = buttonRect.width;

                    this.log('Dropdown position calculated', {
                        top: this.dropdownTop,
                        left: this.dropdownLeft,
                        width: this.dropdownWidth,
                        buttonRect: buttonRect
                    });

                    // Verificar se o dropdown vai sair da tela na parte inferior
                    const windowHeight = window.innerHeight;
                    const dropdownHeight = this.$refs.dropdown ? this.$refs.dropdown.offsetHeight : 300;

                    if (buttonRect.bottom + dropdownHeight > windowHeight) {
                        // Posicionar acima do botão se não houver espaço abaixo
                        this.dropdownTop = buttonRect.top + scrollY - dropdownHeight;
                        this.log('Dropdown repositioned above button', {
                            newTop: this.dropdownTop
                        });
                    }
                },

                // Selecionar uma opção
                selectOption(option) {
                    if (!option) {
                        this.log('SelectOption called with null/undefined option');
                        return;
                    }

                    const value = this.getOptionValue(option);
                    const label = this.getOptionText(option);

                    this.log('Selecting option', {
                        value: value,
                        label: label,
                        option: option,
                        isMultiple: this.multiple,
                        currentlySelected: this.selectedValues
                    });

                    if (this.multiple) {
                        // Se já selecionado, remova
                        if (this.isSelected(option)) {
                            const index = this.selectedValues.findIndex(v => String(v) === String(value));
                            if (index !== -1) {
                                this.selectedValues.splice(index, 1);
                                this.selectedLabels.splice(index, 1);
                                this.selectedObjects.splice(index, 1);
                                this.log('Option deselected in multiple mode', {
                                    removedValue: value,
                                    remainingCount: this.selectedValues.length
                                });
                            }
                        } else {
                            // Se não selecionado, adicione
                            this.selectedValues.push(value);
                            this.selectedLabels.push(label);
                            this.selectedObjects.push(option);
                            this.log('Option added in multiple mode', {
                                addedValue: value,
                                totalCount: this.selectedValues.length
                            });
                        }
                        // Manter o dropdown aberto em seleção múltipla
                        if (this.$refs.search) {
                            this.$refs.search.focus();
                        }
                    } else {
                        // Para seleção única, substituir
                        this.selectedValues = [value];
                        this.selectedLabels = [label];
                        this.selectedObjects = [option];
                        this.log('Option selected in single mode', {
                            selectedValue: value
                        });
                        // Fechar o dropdown em seleção única
                        this.open = false;
                    }

                    // Atualiza a versão JSON dos objetos selecionados
                    this.selectedObjectsJson = JSON.stringify(this.selectedObjects);

                    this.log('Selection updated', {
                        selectedValues: this.selectedValues,
                        selectedLabels: this.selectedLabels,
                        selectedObjectsJson: this.selectedObjectsJson
                    });

                    // Dispara um evento personalizado
                    this.dispatchSelectionEvent(option);

                    // Executar callback
                    this.executeCallback(option);

                    // Limpar a busca
                    this.search = '';
                    this.highlightIndex = -1;
                },

                // Remover um item da seleção múltipla
                removeItem(index) {
                    this.log('Removing item', {
                        index: index,
                        totalItems: this.selectedValues.length
                    });

                    if (index < 0 || index >= this.selectedValues.length) {
                        this.log('Invalid index for removal', {
                            index: index
                        });
                        return;
                    }

                    const removedOption = this.selectedObjects[index];
                    this.selectedValues.splice(index, 1);
                    this.selectedLabels.splice(index, 1);
                    this.selectedObjects.splice(index, 1);
                    this.selectedObjectsJson = JSON.stringify(this.selectedObjects);

                    this.log('Item removed', {
                        removedOption: removedOption,
                        remainingCount: this.selectedValues.length
                    });

                    // Dispara um evento de remoção
                    this.dispatchRemovalEvent(removedOption);
                },

                // Buscar itens assincronamente
                async searchItems() {
                    if (this.search.length < this.minSearchLength || !this.searchUrl) {
                        this.log('Search blocked', {
                            searchLength: this.search.length,
                            minLength: this.minSearchLength,
                            hasUrl: !!this.searchUrl
                        });
                        return;
                    }

                    this.log('Starting async search', {
                        searchTerm: this.search,
                        url: this.searchUrl
                    });

                    this.loading = true;

                    try {
                        const url = new URL(this.searchUrl);
                        url.searchParams.append('term', this.search);

                        this.log('Making fetch request', {
                            fullUrl: url.toString()
                        });

                        // Adiciona todos os parâmetros extras

                        const response = await fetch(url.toString());

                        if (!response.ok) {
                            throw new Error(`Erro na busca: ${response.status}`);
                        }

                        const data = await response.json();

                        this.log('Search response received', {
                            resultCount: data.length,
                            results: data
                        });

                        // Mesclar os resultados com os itens selecionados
                        const currentValues = new Set(data.map(item => String(this.getOptionValue(item))));

                        // Adicionar itens selecionados que não estejam nos resultados
                        const selectedItems = this.selectedObjects.filter(item =>
                            !currentValues.has(String(this.getOptionValue(item)))
                        );

                        this.options = [...data, ...selectedItems];
                        this.highlightIndex = this.options.length > 0 ? 0 : -1;

                        this.log('Options updated after search', {
                            totalOptions: this.options.length,
                            selectedItemsAdded: selectedItems.length
                        });

                    } catch (error) {
                        this.log('Search error occurred', error);
                        console.error('Erro na busca:', error);
                    } finally {
                        this.loading = false;
                        this.log('Search loading completed');
                    }
                },

                // Métodos para navegação por teclado
                highlightNext() {
                    if (this.filteredOptions.length === 0) return;
                    const prevIndex = this.highlightIndex;
                    this.highlightIndex = (this.highlightIndex + 1) % this.filteredOptions.length;
                    this.log('Highlight moved next', {
                        from: prevIndex,
                        to: this.highlightIndex
                    });
                    this.scrollToHighlighted();
                },

                highlightPrev() {
                    if (this.filteredOptions.length === 0) return;
                    const prevIndex = this.highlightIndex;
                    this.highlightIndex = this.highlightIndex <= 0 ?
                        this.filteredOptions.length - 1 :
                        this.highlightIndex - 1;
                    this.log('Highlight moved previous', {
                        from: prevIndex,
                        to: this.highlightIndex
                    });
                    this.scrollToHighlighted();
                },

                // Método para fazer scroll até o item destacado
                scrollToHighlighted() {
                    this.$nextTick(() => {
                        if (!this.$refs.dropdown) return;

                        const dropdown = this.$refs.dropdown;
                        const highlightedElement = dropdown.querySelector('[role="option"]:nth-child(' + (this
                            .highlightIndex + 2) + ')');

                        if (!highlightedElement) return;

                        const dropdownRect = dropdown.getBoundingClientRect();
                        const elementRect = highlightedElement.getBoundingClientRect();

                        // Calcula a posição do elemento em relação ao dropdown
                        const elementTop = elementRect.top - dropdownRect.top + dropdown.scrollTop;
                        const elementBottom = elementTop + elementRect.height;

                        // Verifica se o elemento está acima da área visível
                        if (elementTop < dropdown.scrollTop) {
                            dropdown.scrollTop = elementTop;
                            this.log('Scrolled up to show highlighted item', {
                                scrollTop: dropdown.scrollTop
                            });
                        }
                        // Verifica se o elemento está abaixo da área visível
                        else if (elementBottom > dropdown.scrollTop + dropdown.clientHeight) {
                            dropdown.scrollTop = elementBottom - dropdown.clientHeight;
                            this.log('Scrolled down to show highlighted item', {
                                scrollTop: dropdown.scrollTop
                            });
                        }
                    });
                },

                selectHighlighted() {
                    if (this.highlightIndex >= 0 && this.highlightIndex < this.filteredOptions.length) {
                        const option = this.filteredOptions[this.highlightIndex];
                        this.log('Selecting highlighted option', {
                            highlightIndex: this.highlightIndex,
                            option: option
                        });
                        this.selectOption(option);
                    } else {
                        this.log('No valid highlighted option to select', {
                            highlightIndex: this.highlightIndex,
                            filteredCount: this.filteredOptions.length
                        });
                    }
                },

                // Métodos para eventos de seleção
                dispatchSelectionEvent(option) {
                    const detail = {
                        name: this.name,
                        value: this.getOptionValue(option),
                        label: this.getOptionText(option),
                        object: option,
                        objects: this.selectedObjects
                    };

                    this.log('Dispatching selection event', detail);

                    // Dispatch para o elemento atual
                    this.$el.dispatchEvent(new CustomEvent('select-change', {
                        detail,
                        bubbles: true
                    }));

                    // Dispatch para window para permitir ouvir globalmente
                    window.dispatchEvent(new CustomEvent(`${this.name}:selected`, {
                        detail
                    }));
                },

                dispatchRemovalEvent(option) {
                    const detail = {
                        name: this.name,
                        value: this.getOptionValue(option),
                        label: this.getOptionText(option),
                        object: option,
                        objects: this.selectedObjects
                    };

                    this.log('Dispatching removal event', detail);

                    this.$el.dispatchEvent(new CustomEvent('select-remove', {
                        detail,
                        bubbles: true
                    }));

                    window.dispatchEvent(new CustomEvent(`${this.name}:removed`, {
                        detail
                    }));
                },

                executeCallback(option) {
                    if (this.onSelectCallback && typeof window[this.onSelectCallback] === 'function') {
                        this.log('Executing callback', {
                            callback: this.onSelectCallback,
                            option: option
                        });
                        window[this.onSelectCallback](this.getOptionValue(option), option);
                    } else if (this.onSelectCallback) {
                        this.log('Callback not found or not a function', {
                            callback: this.onSelectCallback,
                            type: typeof window[this.onSelectCallback]
                        });
                    }
                },

                // Lidar com seleção externa
                handleExternalSelection(detail) {
                    this.log('External selection event received', detail);

                    if (detail && detail.targetId === this.name) {
                        const option = this.options.find(opt =>
                            String(this.getOptionValue(opt)) === String(detail.value)
                        );
                        if (option) {
                            this.log('External option found and selecting', option);
                            this.selectOption(option);
                        } else {
                            this.log('External option not found in current options', {
                                searchValue: detail.value,
                                availableOptions: this.options.length
                            });
                        }
                    }
                },

                // Métodos para navegação por setas
                selectNext() {
                    if (this.open) {
                        this.highlightNext();
                    } else if (!this.$refs.button.disabled) {
                        this.log('Opening dropdown via down arrow');
                        this.toggleDropdown();
                    }
                },

                selectPrev() {
                    if (this.open) {
                        this.highlightPrev();
                    } else if (!this.$refs.button.disabled) {
                        this.log('Opening dropdown via up arrow');
                        this.toggleDropdown();
                    }
                }
            };
        }
    </script>
@endPushOnce
