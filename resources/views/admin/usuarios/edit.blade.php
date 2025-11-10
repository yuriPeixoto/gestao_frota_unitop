<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Usuário') }}
            </h2>
            <a href="{{ route('admin.usuarios.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Voltar
            </a>
        </div>
    </x-slot>

    {{--<div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">--}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.usuarios.update', $user) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-forms.input name="name" label="Nome" required value="{{ old('name', $user->name) }}" />
                            <x-forms.input name="email" type="email" label="Email" value="{{ old('email', $user->email) }}" />
                            <x-forms.input name="cpf" label="CPF" value="{{ old('cpf', $user->cpf) }}" />
                            <x-forms.input name="matricula" type="number" label="Matrícula" value="{{ old('matricula', $user->matricula) }}" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-forms.input name="password" type="password" label="Nova Senha" />
                            <x-forms.input name="password_confirmation" type="password" label="Confirmar Nova Senha" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-forms.smart-select name="filiais[]" label="Filiais" placeholder="Selecione as Filiais" :options="$filiais ?? []" multiple="true" :selected="old('filiais', array_values(array_unique(array_filter(array_merge([$user->filial_id], $user->filiais->pluck('id')->toArray())))))" />
                            <x-forms.smart-select name="departamento_id" label="Departamento" placeholder="Selecione o Departamento" :options="$departamentos ?? []" :selected="old('departamento_id', $user->departamento_id)" />
                            <x-forms.smart-select name="pessoal_id" label="Cargo" placeholder="Selecione o Cargo" :options="$cargos ?? []" :selected="old('pessoal_id', $user->pessoal_id)" />
                            <x-forms.smart-select name="roles[]" label="Grupos (Roles)" placeholder="Selecione os grupos" :options="$grupos ?? []" multiple="true" :selected="old('roles', $user->roles->pluck('id')->toArray())" />
                        </div>
                        <p class="text-xs text-gray-500 -mt-4">Dica: a primeira filial selecionada será considerada a principal.</p>

                        @php $tel = optional($user->telefones->first()); @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-forms.input name="telefone_fixo" label="Telefone Fixo" value="{{ old('telefone_fixo', $tel->telefone_fixo ?? '') }}" />
                            <x-forms.input name="telefone_celular" label="Telefone Celular" value="{{ old('telefone_celular', $tel->telefone_celular ?? '') }}" />
                        </div>

                        <div class="border-t pt-4 mt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Endereço (opcional)</h3>
                            @php $addr = optional($user->address); @endphp
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <x-forms.input name="zip_code" label="CEP" value="{{ old('zip_code', $addr->zip_code) }}" />
                                <x-forms.input name="state" label="UF" maxlength="2" value="{{ old('state', $addr->state) }}" />
                                <x-forms.input name="city" label="Cidade" value="{{ old('city', $addr->city) }}" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                                <x-forms.input name="street" label="Logradouro" value="{{ old('street', $addr->street) }}" />
                                <x-forms.input name="number" label="Número" value="{{ old('number', $addr->number) }}" />
                                <x-forms.input name="complement" label="Complemento" value="{{ old('complement', $addr->complement) }}" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <x-forms.input name="district" label="Bairro" value="{{ old('district', $addr->district) }}" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.usuarios.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        {{--</div>
    </div>--}}
    @push('scripts')
    <script>
        (function() {
            // Helpers
            function onlyDigits(str){ return (str || '').replace(/\D/g, ''); }
            function formatCPF(v){
                const d = onlyDigits(v).slice(0,11);
                let out = '';
                if (d.length > 9) out = d.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, (m,a,b,c,d4)=> `${a}.${b}.${c}${d4?'-'+d4:''}`);
                else if (d.length > 6) out = d.replace(/(\d{3})(\d{3})(\d{0,3})/, (m,a,b,c)=> `${a}.${b}.${c}`);
                else if (d.length > 3) out = d.replace(/(\d{3})(\d{0,3})/, (m,a,b)=> `${a}.${b}`);
                else out = d;
                return out;
            }
            function formatCEP(v){
                const d = onlyDigits(v).slice(0,8);
                if (d.length > 5) return d.replace(/(\d{5})(\d{0,3})/, '$1-$2');
                return d;
            }
            function formatPhone(v){
                const d = onlyDigits(v).slice(0,11);
                if (d.length <= 10){
                    if (d.length > 6) return d.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                    if (d.length > 2) return d.replace(/(\d{2})(\d{0,4})/, '($1) $2');
                    return d;
                } else {
                    return d.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                }
            }

            // Elements
            const form = document.querySelector('form[action*="admin.usuarios.update"], form[action*="/admin/usuarios"]') || document.querySelector('form');
            const cpfInput = document.querySelector('input[name="cpf"]');
            const telFixo = document.querySelector('input[name="telefone_fixo"]');
            const telCel = document.querySelector('input[name="telefone_celular"]');
            const cepInput = document.querySelector('input[name="zip_code"]');
            const stateInput = document.querySelector('input[name="state"]');
            const cityInput = document.querySelector('input[name="city"]');
            const streetInput = document.querySelector('input[name="street"]');
            const districtInput = document.querySelector('input[name="district"]');
            const numberInput = document.querySelector('input[name="number"]');
            const complementInput = document.querySelector('input[name="complement"]');

            // Masks on load and input
            if (cpfInput){
                const apply = () => { cpfInput.value = formatCPF(cpfInput.value); };
                cpfInput.addEventListener('input', apply);
                cpfInput.addEventListener('blur', apply);
                apply();
                cpfInput.setAttribute('inputmode','numeric');
            }
            if (telFixo){
                const apply = () => { telFixo.value = formatPhone(telFixo.value); };
                telFixo.addEventListener('input', apply);
                telFixo.addEventListener('blur', apply);
                apply();
                telFixo.setAttribute('inputmode','numeric');
            }
            if (telCel){
                const apply = () => { telCel.value = formatPhone(telCel.value); };
                telCel.addEventListener('input', apply);
                telCel.addEventListener('blur', apply);
                apply();
                telCel.setAttribute('inputmode','numeric');
            }

            let lastFetchedCep = null;
            function setLoading(isLoading) {
                if (!cepInput) return;
                if (isLoading) {
                    cepInput.dataset.loading = '1';
                    cepInput.style.backgroundImage = 'linear-gradient(to right, rgba(99,102,241,0.12), rgba(99,102,241,0.12))';
                } else {
                    if (cepInput){ delete cepInput.dataset.loading; cepInput.style.backgroundImage = ''; }
                }
            }
            function clearAddressFields() {
                if (streetInput) streetInput.value = '';
                if (districtInput) districtInput.value = '';
                if (cityInput) cityInput.value = '';
                if (stateInput) stateInput.value = '';
                if (numberInput) numberInput.value = '';
                if (complementInput) complementInput.value = '';
            }
            async function fetchCep(cepRaw) {
                const cep = onlyDigits(cepRaw);
                if (cep.length !== 8) return; // CEP precisa de 8 dígitos
                if (cep === lastFetchedCep) return;
                setLoading(true);

                // Limpar campos número e complemento sempre que buscar novo CEP
                if (numberInput) numberInput.value = '';
                if (complementInput) complementInput.value = '';

                try {
                    const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    if (!resp.ok) throw new Error('Falha na consulta de CEP');
                    const data = await resp.json();
                    if (data.erro) {
                        clearAddressFields();
                        lastFetchedCep = cep; // evita repetição
                        return;
                    }
                    if (streetInput && data.logradouro) streetInput.value = data.logradouro;
                    if (districtInput && data.bairro) districtInput.value = data.bairro;
                    if (cityInput && data.localidade) cityInput.value = data.localidade;
                    if (stateInput && data.uf) stateInput.value = data.uf;
                    lastFetchedCep = cep;
                } catch (e) {
                    console.warn('CEP lookup failed:', e);
                } finally {
                    setLoading(false);
                }
            }
            if (cepInput){
                const applyCepMask = () => { cepInput.value = formatCEP(cepInput.value); };

                // Aplicar máscara no load mas NÃO buscar CEP automaticamente
                applyCepMask();

                // Só buscar CEP quando usuário alterar o campo
                cepInput.addEventListener('input', (e) => {
                    applyCepMask();
                    const digits = onlyDigits(e.target.value);
                    if (digits.length === 8) fetchCep(digits);
                });
                cepInput.addEventListener('blur', () => {
                    applyCepMask();
                    // Só buscar se o valor foi alterado pelo usuário
                    const digits = onlyDigits(cepInput.value);
                    if (digits.length === 8 && digits !== lastFetchedCep) {
                        fetchCep(cepInput.value);
                    }
                });
            }

            // Before submit, strip non-digits
            if (form){
                form.addEventListener('submit', () => {
                    if (cpfInput) cpfInput.value = onlyDigits(cpfInput.value);
                    if (telFixo) telFixo.value = onlyDigits(telFixo.value);
                    if (telCel) telCel.value = onlyDigits(telCel.value);
                    if (cepInput) cepInput.value = onlyDigits(cepInput.value);
                }, { once: true });
            }
        })();
    </script>
    @endpush
</x-app-layout>
