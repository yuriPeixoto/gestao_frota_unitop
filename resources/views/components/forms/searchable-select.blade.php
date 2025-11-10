@props([
'name',
'label' => null,
'placeholder' => 'Selecione...',
'initialOptions' => [],
'searchUrl' => null,
'selected' => null,
'required' => false,
'disabled' => false,
'error' => null,
'valueField' => 'value',
'textField' => 'label',
'minSearchLength' => 2,
'multiple' => false,
'onSelectCallback' => null,
'dataAttributes' => []
])

<div x-data="asyncSearchableSelect({
    name: '{{ $name }}',
    searchUrl: '{{ $searchUrl }}',
    initialOptions: {{ json_encode($initialOptions) }},
    selected: {{ json_encode($selected) }},
    valueField: '{{ $valueField }}',
    textField: '{{ $textField }}',
    placeholder: '{{ $placeholder }}',
    minSearchLength: {{ $minSearchLength }},
    multiple: {{ $multiple ? 'true' : 'false' }},
    onSelectCallback: '{{ $onSelectCallback }}'
})" class="w-full">
    @if($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required)
        <span class="text-red-500">*</span>
        @endif
    </label>
    @endif

    <div class="relative mt-1">
        <button type="button" x-ref="button" @click="toggleDropdown" @keydown.arrow-up.prevent="selectPrev"
            @keydown.arrow-down.prevent="selectNext" @keydown.enter.prevent="selectOption"
            @keydown.space.prevent="selectOption" @keydown.escape.prevent="open = false"
            :disabled="{{ $disabled ? 'true' : 'false' }}"
            class="relative w-full flex items-center bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm {{ $disabled ? 'bg-gray-100' : '' }}"
            :class="{ 'border-red-300': {{ isset($error) ? 'true' : 'false' }} }" aria-haspopup="listbox"
            id="{{ $name }}-button">
            <template x-if="selectedValues.length === 0">
                <span class="block truncate text-gray-500" x-text="placeholder"></span>
            </template>

            <template x-if="!multiple && selectedValues.length > 0">
                <span class="block truncate" x-text="selectedLabels[0]"></span>
            </template>

            <template x-if="multiple && selectedValues.length > 0">
                <div class="flex flex-wrap gap-1 max-w-full overflow-hidden">
                    <template x-for="(label, index) in selectedLabels" :key="index">
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            <span x-text="label"></span>
                            <button @click.stop="removeItem(index)" type="button"
                                class="ml-1 inline-flex text-blue-500 hover:text-blue-700">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 8.586L6.707 5.293a1 1 0 00-1.414 1.414L8.586 10l-3.293 3.293a1 1 0 101.414 1.414L10 11.414l3.293 3.293a1 1 0 001.414-1.414L11.414 10l3.293-3.293a1 1 0 00-1.414-1.414L10 8.586z" />
                                </svg>
                            </button>
                        </span>
                    </template>
                </div>
            </template>

            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
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
                <input type="hidden" name="{{ $name }}[]" :value="value" :id="`{{ $name }}_${index}`">
            </template>
        </template>
    </div>

    <!-- Usamos teleport para renderizar o dropdown no portal -->
    <template x-teleport="#portal-root">
        <div x-show="open" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.away="open = false"
            class="fixed z-[10000] bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
            :style="{ top: dropdownTop + 'px', left: dropdownLeft + 'px', width: dropdownWidth + 'px' }"
            x-ref="dropdown" tabindex="-1" role="listbox" :id="`dropdown-${uniqueId}`" style="display: none;">
            <div class="sticky top-0 z-10 bg-white px-1 py-1.5">
                <input x-ref="search" x-model.debounce.300ms="search" @keydown.enter.prevent
                    @keydown.arrow-up.prevent="highlightPrev" @keydown.arrow-down.prevent="highlightNext" type="search"
                    class="w-full h-10 px-3 py-2 text-sm leading-5 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Digite pelo menos {{ $minSearchLength }} caracteres para buscar...">
            </div>

            <div x-show="loading" class="flex justify-center items-center py-4">
                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="ml-2">Carregando...</span>
            </div>

            <template x-for="(option, index) in filteredOptions" :key="index">
                <div @click="selectOption(option)" @mouseenter="highlightIndex = index"
                    :class="{ 'bg-indigo-100': highlightIndex === index, 'text-gray-900': highlightIndex === index, 'text-gray-700': highlightIndex !== index }"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100" role="option"
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
                class="py-2 px-3 text-gray-500 italic text-sm">
                Nenhum resultado encontrado
            </div>

            <div x-show="!loading && search.length > 0 && search.length < minSearchLength"
                class="py-2 px-3 text-gray-500 italic text-sm">
                Digite pelo menos <span x-text="minSearchLength"></span> caracteres para buscar
            </div>
        </div>
    </template>

    @if($error)
    <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
    @endif

    @error($name)
    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<script>
    function asyncSearchableSelect(config) {
        return {
            open: false,
            search: '',
            loading: false,
            highlightIndex: 0,
            name: config.name,
            searchUrl: config.searchUrl,
            options: config.initialOptions || [],
            valueField: config.valueField || 'value',
            textField: config.textField || 'label',
            placeholder: config.placeholder || 'Selecione...',
            selectedValues: Array.isArray(config.selected) ? 
                config.selected : 
                (config.selected ? [config.selected] : []),
            selectedLabels: [],
            disabled: config.disabled || false,
            error: config.error || null,
            minSearchLength: config.minSearchLength || 2,
            multiple: config.multiple || false,
            searchTimeout: null,
            onSelectCallback: config.onSelectCallback || null,
            // Novas propriedades para posicionamento do dropdown
            dropdownTop: 0,
            dropdownLeft: 0,
            dropdownWidth: 0,
            uniqueId: Date.now() + Math.floor(Math.random() * 1000), // ID único para o dropdown

            get filteredOptions() {
                return this.options;
            },

            getOptionValue(option) {
                if (typeof option === 'object' && option !== null) {
                    return option[this.valueField];
                }
                return option;
            },

            getOptionText(option) {
                if (typeof option === 'object' && option !== null) {
                    return option[this.textField];
                }
                return option;
            },

            isSelected(option) {
                const value = this.getOptionValue(option);
                return this.selectedValues.includes(value);
            },

            toggleDropdown() {
                this.open = !this.open;
                if (this.open) {
                    // Garante que todos os outros dropdowns sejam fechados
                    window.dispatchEvent(new CustomEvent('close-all-dropdowns', {
                        detail: { except: `dropdown-${this.uniqueId}` }
                    }));
                    // Adiciona listener para fechar este dropdown quando outro for aberto
                    window.addEventListener('close-all-dropdowns', this.handleCloseAllDropdowns);
                } else {
                    window.removeEventListener('close-all-dropdowns', this.handleCloseAllDropdowns);
                }
            },

            handleCloseAllDropdowns(event) {
                if (event.detail && event.detail.except !== `dropdown-${this.uniqueId}`) {
                    this.open = false;
                }
            },

            selectOption(option) {
                if (typeof option === 'object') {
                    const value = this.getOptionValue(option);
                    const label = this.getOptionText(option);
                    
                    if (this.multiple) {
                        // Se já selecionado, remova
                        if (this.isSelected(option)) {
                            const index = this.selectedValues.findIndex(v => v == value);
                            this.selectedValues.splice(index, 1);
                            this.selectedLabels.splice(index, 1);
                        } else {
                            // Se não selecionado, adicione
                            this.selectedValues.push(value);
                            this.selectedLabels.push(label);
                        }
                        // Mantenha o dropdown aberto em seleção múltipla
                        this.$refs.search.focus();
                    } else {
                        // Para seleção única, substitua o valor
                        this.selectedValues = [value];
                        this.selectedLabels = [label];
                        // Feche o dropdown em seleção única
                        this.open = false;
                    }

                    this.search = '';

                    // Executa callbacks personalizados se definidos
                    this.executeCallback(option);
                    
                    // Dispara um evento personalizado com os detalhes da seleção
                    this.dispatchSelectionEvent(option);
                }
            },

            removeItem(index) {
                const removedOption = { 
                    [this.valueField]: this.selectedValues[index], 
                    [this.textField]: this.selectedLabels[index] 
                };
                
                this.selectedValues.splice(index, 1);
                this.selectedLabels.splice(index, 1);
                
                // Dispara um evento de remoção
                this.dispatchRemovalEvent(removedOption);
            },

            dispatchSelectionEvent(option) {
                const detail = {
                    name: this.name,
                    value: this.getOptionValue(option),
                    label: this.getOptionText(option),
                    object: option
                };

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
                    label: this.getOptionText(option)
                };

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
                    window[this.onSelectCallback](this.getOptionValue(option), option);
                }
            },

            selectPrev() {
                if (!this.open) {
                    this.open = true;
                    return;
                }
                if (this.highlightIndex > 0) {
                    this.highlightIndex--;
                } else {
                    this.highlightIndex = this.filteredOptions.length - 1;
                }
            },

            selectNext() {
                if (!this.open) {
                    this.open = true;
                    return;
                }
                if (this.highlightIndex < this.filteredOptions.length - 1) {
                    this.highlightIndex++;
                } else {
                    this.highlightIndex = 0;
                }
            },

            highlightPrev() {
                if (this.highlightIndex > 0) {
                    this.highlightIndex--;
                } else {
                    this.highlightIndex = this.filteredOptions.length - 1;
                }
            },

            highlightNext() {
                if (this.highlightIndex < this.filteredOptions.length - 1) {
                    this.highlightIndex++;
                } else {
                    this.highlightIndex = 0;
                }
            },

            updateDropdownPosition() {
                if (!this.open || !this.$refs.button) return;
                
                const buttonRect = this.$refs.button.getBoundingClientRect();
                const scrollY = window.scrollY || window.pageYOffset;
                const scrollX = window.scrollX || window.pageXOffset;
                
                // Posiciona o dropdown logo abaixo do botão
                this.dropdownTop = buttonRect.bottom + scrollY;
                this.dropdownLeft = buttonRect.left + scrollX;
                this.dropdownWidth = buttonRect.width;
            },

            async searchOptions() {
                if (this.search.length < this.minSearchLength) {
                    this.options = [];
                    return;
                }

                this.loading = true;
                this.highlightIndex = 0;

                try {
                    const response = await fetch(`${this.searchUrl}?term=${encodeURIComponent(this.search)}`);
                    if (!response.ok) {
                        throw new Error('Falha na busca');
                    }
                    const data = await response.json();
                    this.options = data;
                } catch (error) {
                    console.error('Erro na busca:', error);
                    this.options = [];
                } finally {
                    this.loading = false;
                }
            },

            async fetchSelectedOptions() {
                if (this.selectedValues.length === 0) return;
                
                try {
                    // Para cada ID selecionado, busque o objeto completo
                    const fetchPromises = this.selectedValues.map(async (id) => {
                        try {
                            const response = await fetch(`${this.searchUrl.replace('/search', '')}/single/${id}`);
                            if (!response.ok) throw new Error('Falha ao buscar item');
                            return await response.json();
                        } catch (error) {
                            console.error('Erro ao buscar item:', error);
                            return { [this.valueField]: id, [this.textField]: `ID: ${id}` };
                        }
                    });
                    
                    const results = await Promise.all(fetchPromises);
                    this.selectedLabels = results.map(item => this.getOptionText(item));
                } catch (error) {
                    console.error('Erro ao buscar itens selecionados:', error);
                    this.selectedLabels = this.selectedValues.map(v => `ID: ${v}`);
                }
            },

            init() {
                // Observe changes in search term
                this.$watch('search', value => {
                    clearTimeout(this.searchTimeout);
                    if (value.length >= this.minSearchLength) {
                        this.searchTimeout = setTimeout(() => {
                            this.searchOptions();
                        }, 300);
                    }
                });

                // If we have selected values, fetch the complete items to display labels
                if (this.selectedValues.length > 0) {
                    // First check if the selected items are in initialOptions
                    const foundLabels = [];
                    let needsFetch = false;
                    
                    this.selectedValues.forEach(value => {
                        const option = this.options.find(opt => this.getOptionValue(opt) == value);
                        if (option) {
                            foundLabels.push(this.getOptionText(option));
                        } else {
                            needsFetch = true;
                            foundLabels.push(null); // Placeholder
                        }
                    });
                    
                    if (needsFetch) {
                        this.fetchSelectedOptions();
                    } else {
                        this.selectedLabels = foundLabels.filter(label => label !== null);
                    }
                } else {
                    this.selectedLabels = [];
                }

                // When dropdown opens, focus on search input and calculate position
                this.$watch('open', value => {
                    if (value) {
                        this.updateDropdownPosition();
                        this.$nextTick(() => {
                            if (this.$refs.search) {
                                this.$refs.search.focus();
                            }
                        });
                    }
                });

                // Atualizar posição ao rolar ou redimensionar
                window.addEventListener('scroll', () => this.updateDropdownPosition());
                window.addEventListener('resize', () => this.updateDropdownPosition());
            }
        };
    }
</script>