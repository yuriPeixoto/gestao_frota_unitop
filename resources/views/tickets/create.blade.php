<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎫 Novo Chamado de Suporte
            </h2>
            <a href="{{ route('tickets.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </a>
        </div>
    </x-slot>

    {{--<div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">--}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" id="ticket-form">
                    @csrf

                    <div class="p-6 space-y-6">
                        {{-- Tipo de Chamado --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Chamado <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="ticket-type-grid">
                                @foreach(\App\Enums\TicketType::cases() as $type)
                                    <label class="ticket-type-option relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer transition border-gray-200 hover:border-{{ $type->color() }}-500" data-type="{{ $type->value }}">
                                        <input type="radio" name="type" value="{{ $type->value }}" class="sr-only ticket-type-radio" required {{ old('type') === $type->value ? 'checked' : '' }}>
                                        <i class="fas fa-{{ $type->icon() }} text-3xl text-{{ $type->color() }}-500 mb-2"></i>
                                        <span class="text-sm font-medium text-gray-900">{{ $type->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Categoria e Prioridade --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoria <span class="text-red-500">*</span>
                                </label>
                                <select name="category_id" id="category_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecione...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                    Prioridade
                                </label>
                                <select name="priority" id="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach(\App\Enums\TicketPriority::cases() as $priority)
                                        <option value="{{ $priority->value }}" {{ old('priority', 'media') === $priority->value ? 'selected' : '' }}>
                                            {{ $priority->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Assunto --}}
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                Assunto <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="subject"
                                id="subject"
                                required
                                maxlength="255"
                                value="{{ old('subject') }}"
                                placeholder="Descreva brevemente o problema ou solicitação"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Descrição --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição Detalhada <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                name="description"
                                id="description"
                                rows="8"
                                required
                                placeholder="Descreva em detalhes o que aconteceu, quando aconteceu, e quais passos reproduzem o problema..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                💡 Dica: Quanto mais detalhes você fornecer, mais rápido poderemos resolver.
                            </p>
                        </div>

                        {{-- URL (se aplicável) --}}
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                                URL onde ocorreu (se aplicável)
                            </label>
                            <input
                                type="url"
                                name="url"
                                id="url"
                                value="{{ old('url', url()->previous()) }}"
                                placeholder="https://..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            @error('url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tags --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tags (opcional)
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($tags as $tag)
                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            name="tags[]"
                                            value="{{ $tag->id }}"
                                            {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Anexos --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Anexos (opcional)
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <input
                                    type="file"
                                    name="attachments[]"
                                    id="attachments"
                                    multiple
                                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                                    class="hidden"
                                    onchange="updateFileList(this)"
                                >
                                <label for="attachments" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">
                                        Clique para selecionar arquivos ou arraste aqui
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Imagens, PDF, DOC, XLS (máx 10MB cada)
                                    </p>
                                </label>
                                <div id="file-list" class="mt-4 text-left"></div>
                            </div>
                            @error('attachments')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alerta de Melhoria --}}
                        <div id="quality-alert" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Melhorias passam pela Equipe de Qualidade:</strong>
                                        Sua solicitação será analisada pela equipe de qualidade antes de ser encaminhada para desenvolvimento.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <a href="{{ route('tickets.index') }}" class="text-gray-600 hover:text-gray-900">
                            Cancelar
                        </a>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-paper-plane mr-2"></i>
                            Criar Chamado
                        </button>
                    </div>
                </form>
            </div>
        {{--</div>
    </div>--}}

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar visualmente os botões de tipo de ticket
            const typeOptions = document.querySelectorAll('.ticket-type-option');
            const typeRadios = document.querySelectorAll('.ticket-type-radio');

            function updateTypeSelection() {
                // Remover seleção de todos
                typeOptions.forEach(option => {
                    option.classList.remove('border-blue-500', 'border-green-500', 'border-red-500', 'border-yellow-500', 'border-purple-500');
                    option.classList.remove('bg-blue-50', 'bg-green-50', 'bg-red-50', 'bg-yellow-50', 'bg-purple-50');
                    option.classList.add('border-gray-200');
                });

                // Adicionar seleção ao marcado
                typeRadios.forEach(radio => {
                    if (radio.checked) {
                        const label = radio.closest('.ticket-type-option');
                        const type = label.dataset.type;

                        label.classList.remove('border-gray-200');

                        // Adicionar classes específicas por tipo
                        if (type === 'bug') {
                            label.classList.add('border-red-500', 'bg-red-50');
                        } else if (type === 'melhoria') {
                            label.classList.add('border-green-500', 'bg-green-50');
                        } else if (type === 'duvida') {
                            label.classList.add('border-yellow-500', 'bg-yellow-50');
                        } else if (type === 'suporte') {
                            label.classList.add('border-blue-500', 'bg-blue-50');
                        }
                    }
                });
            }

            // Inicializar seleção se houver valor old()
            updateTypeSelection();

            // Listener para mudanças
            typeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    updateTypeSelection();

                    // Mostrar alerta se tipo for "melhoria"
                    const alert = document.getElementById('quality-alert');
                    if (this.value === 'melhoria') {
                        alert.classList.remove('hidden');
                    } else {
                        alert.classList.add('hidden');
                    }
                });
            });
        });

        // Atualizar lista de arquivos selecionados
        function updateFileList(input) {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';

            if (input.files.length > 0) {
                const ul = document.createElement('ul');
                ul.className = 'space-y-1';

                Array.from(input.files).forEach(file => {
                    const li = document.createElement('li');
                    li.className = 'text-sm text-gray-700 flex items-center';
                    li.innerHTML = `
                        <i class="fas fa-file mr-2 text-gray-400"></i>
                        <span>${file.name}</span>
                        <span class="ml-2 text-xs text-gray-500">(${(file.size / 1024).toFixed(1)} KB)</span>
                    `;
                    ul.appendChild(li);
                });

                fileList.appendChild(ul);
            }
        }

        // Drag and drop
        const dropZone = document.querySelector('.border-dashed');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }

        function unhighlight() {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('attachments').files = files;
            updateFileList(document.getElementById('attachments'));
        }
    </script>
    @endpush
</x-app-layout>
