<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                @if (session('notification'))
                    <x-notification :notification="session('notification')" />
                @endif
                <form method="POST"
                    action="{{ isset($role) ? route('admin.cargos.update', $role) : route('admin.cargos.store') }}"
                    class="space-y-6">
                    @csrf
                    @if (isset($role))
                        @method('PUT')
                    @endif

                    <!-- Card para Informações Básicas -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h3>

                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-2 flex items-center">
                                    <x-forms.input id="name" name="name" type="text" label="Cargo"
                                        :value="old('name', $role->name ?? '')" required />
                                    <div class="flex justify-end space-x-4">
                                        <input type="radio" name="is_ativo" value="1"
                                            {{ old('is_ativo', $role->is_ativo ?? '') == '1' ? 'checked' : '' }}>
                                        Ativo

                                        <input type="radio" name="is_ativo" value="0"
                                            {{ old('is_ativo', $role->is_ativo ?? '') == '0' ? 'checked' : '' }}>
                                        Inativo
                                    </div>
                                </div>

                                <div>
                                    <x-forms.textarea id="description" name="description" label="Descrição"
                                        class="block mt-1 w-full" rows="3"
                                        required>{{ old('description', $role->description ?? '') }}
                                    </x-forms.textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="window.location='{{ route('admin.cargos.index') }}'"
                            class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                            {{ isset($role) ? 'Atualizar' : 'Criar' }} Cargo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('admin.cargos._scripts')
@endpush
