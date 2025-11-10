<div class="space-y-6">
    <!-- Informações do Usuário -->
    <div class="rounded-lg bg-gray-50 p-4">
        @if (session('notification'))
            <x-notification :notification="session('notification')" />
        @endif
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="mb-4 text-lg font-medium text-gray-900">Informações do Usuário</h3>
            </div>
            <div class="space-x-2">
                <input type="radio" name="is_ativo" value="1"
                    {{ old('is_ativo', $user->is_ativo ?? '') == '1' ? 'checked' : '' }}>
                <span>Ativo</span>
                <input type="radio" name="is_ativo" value="0"
                    {{ old('is_ativo', $user->is_ativo ?? '') == '0' ? 'checked' : '' }}>
                <span>Inativo</span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Nome -->
            <div>
                <x-forms.input name="name" label="Nome" value="{{ old('name', $user->name ?? '') }}"
                    required="true" />
            </div>

            <!-- Email -->
            <div>
                <x-forms.input type="email" name="email" label="Email"
                    value="{{ old('email', $user->email ?? '') }}" required="true" />
            </div>

            <!-- Senha -->
            <div>
                <x-forms.input type="password" name="password"
                    label="{{ isset($user) ? 'Nova Senha (deixe em branco para manter)' : 'Senha' }}"
                    :required="isset($user) ? false : true" />
            </div>

            <!-- Confirmação de Senha -->
            <div>
                <x-forms.input type="password" name="password_confirmation" label="Confirmar Senha" :required="isset($user) ? false : true" />
            </div>

            <!-- CPF -->
            <div>
                <x-forms.input name="cpf" label="CPF" value="{{ old('cpf', $user->cpf ?? '') }}"
                    mask="000.000.000-00" />
            </div>

            <!-- Matricula -->
            <div>
                <x-forms.input name="matricula" label="Matrícula" type="number"
                    value="{{ old('matricula', $user->matricula ?? '') }}"
                    placeholder="Digite a matrícula do usuário" />
            </div>

            <!-- Filial -->
            <div>
                <x-forms.smart-select name="filiais[]" label="Filiais" placeholder="Selecione a(s) filial(is)..."
                    :options="$filiais" :selected="old('filiais', isset($user) ? $user->filiais->pluck('id')->toArray() : [])" :multiple="true" asyncSearch="false" />
            </div>

            {{-- Caso precisemos pegar o filial_id para relacionamento de campo --}}
            <input type="hidden" name="filial_id" id="filial_id_hidden"
                value="{{ old('filial_id', $user->filial_id ?? '') }}">

            <!-- Departamento -->
            <div class="z-20">
                <x-forms.smart-select name="departamento_id" label="Departamento"
                    placeholder="Selecione o departamento..." :options="$departamentos" :selected="old('departamento_id', $user->departamento_id ?? '')" asyncSearch="false" />
            </div>

            <!-- Cargo -->
            <div class="z-10">
                <x-forms.smart-select name="pessoal_id" label="Cargo" placeholder="Selecione o cargo..."
                    :options="$cargos" :selected="old('pessoal_id', $user->pessoal_id ?? '')" asyncSearch="false" />
            </div>

            <!-- Grupos -->
            <div>
                <x-forms.smart-select name="roles[]" label="Funções (Roles)" placeholder="Selecione as funções..."
                    :options="$grupos" :selected="old('roles', isset($user) ? $user->roles->pluck('id')->toArray() : [])" :multiple="true" />
            </div>

            <!-- Super Usuário (visível apenas para outros super usuários) -->
            @if (auth()->user()->is_superuser)
                <div>
                    <div class="mt-6 flex h-full items-center">
                        <x-forms.checkbox name="is_superuser" label="Super Usuário" :checked="old('is_superuser', $user->is_superuser ?? false)" />
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Endereço (Opcional) -->
    <div class="rounded-lg bg-gray-50 p-4">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Endereço (Opcional)</h3>
            <p class="text-sm text-gray-500">Digite o CEP para preenchimento automático</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- CEP -->
            <div>
                <x-forms.input name="zip_code" id="zip_code" label="CEP"
                    value="{{ old('zip_code', $user->address->zip_code ?? '') }}" mask="00000-000"
                    x-on:input="formatCep($event.target)" />
                <div x-show="loading" class="mt-2 flex items-center text-sm text-gray-500">
                    <svg class="mr-2 h-4 w-4 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Buscando endereço...
                </div>
            </div>

            <!-- Estado -->
            <div>
                <x-forms.select name="state" label="Estado" :options="[
                    '' => 'Selecione...',
                    'AC' => 'AC',
                    'AL' => 'AL',
                    'AM' => 'AM',
                    'AP' => 'AP',
                    'BA' => 'BA',
                    'CE' => 'CE',
                    'DF' => 'DF',
                    'ES' => 'ES',
                    'GO' => 'GO',
                    'MA' => 'MA',
                    'MG' => 'MG',
                    'MS' => 'MS',
                    'MT' => 'MT',
                    'PA' => 'PA',
                    'PB' => 'PB',
                    'PE' => 'PE',
                    'PI' => 'PI',
                    'PR' => 'PR',
                    'RJ' => 'RJ',
                    'RN' => 'RN',
                    'RO' => 'RO',
                    'RR' => 'RR',
                    'RS' => 'RS',
                    'SC' => 'SC',
                    'SE' => 'SE',
                    'SP' => 'SP',
                    'TO' => 'TO',
                ]" :selected="old('state', $user->address->state ?? '')" />
            </div>

            <!-- Cidade -->
            <div>
                <x-forms.input name="city" label="Cidade" value="{{ old('city', $user->address->city ?? '') }}" />
            </div>

            <!-- Bairro -->
            <div>
                <x-forms.input name="district" label="Bairro"
                    value="{{ old('district', $user->address->district ?? '') }}" />
            </div>

            <!-- Rua -->
            <div class="sm:col-span-2">
                <x-forms.input name="street" label="Rua"
                    value="{{ old('street', $user->address->street ?? '') }}" />
            </div>

            <!-- Número -->
            <div>
                <x-forms.input name="number" label="Número"
                    value="{{ old('number', $user->address->number ?? '') }}" />
            </div>

            <!-- Complemento -->
            <div>
                <x-forms.input name="complement" label="Complemento"
                    value="{{ old('complement', $user->address->complement ?? '') }}" />
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.usuarios.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
            {{ isset($user) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>

@push('styles')
    <style>
        /* Ajuste para garantir que os dropdowns apareçam em camadas apropriadas */
        [x-data] {
            position: relative;
        }

        .dropdown-list {
            z-index: 50 !important;
            /* Garante que os dropdowns tenham um z-index alto */
        }
    </style>
@endpush

@push('scripts')
    @include('admin.usuarios._scripts')
@endpush
