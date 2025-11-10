<form method="GET" action="{{ route('admin.ordemservicos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.ordemservicos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
            <x-forms.input name="id_ordem_servico" label="Cód. Ordem de Serviço"
                value="{{ request('id_ordem_servico') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_abertura" label="Data abertura"
                value="{{ request('data_abertura') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_ordem_servico" label="Tipo Ordem Serviço"
                placeholder="Selecione o tipo de ordem de serviço..." :options="$tipoOrdemServico" asyncSearch="false"
                value="{{ request('id_tipo_ordem_servico') }}" />
        </div>

        <div>
            <x-forms.smart-select name="situacao_tipo_os_corretiva" label="Tipo Corretiva"
                placeholder="Selecione o tipo de corretiva..." :options="$tipoCorretiva" asyncSearch="false"
                value="{{ request('situacao_tipo_os_corretiva') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_status_ordem_servico" label="Situação Ordem Serviço"
                placeholder="Selecione a situação de ordem de serviço..." :options="$situacaoOrdemServico" asyncSearch="false"
                value="{{ request('id_status_ordem_servico') }}" />
        </div>

        <div>
            <x-forms.input name="id_lancamento_os_auxiliar" label="Cód. Lançamento"
                value="{{ request('id_lancamento_os_auxiliar') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div>
            <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a placa..." :options="$veiculosFrequentes"
                :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" returnLabel="true" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="recepcionista" label="Recepcionista" placeholder="Selecione o recepcionista..."
                :options="$usuariosFrequentes" :selected="request('recepcionista')" asyncSearch="false" />
        </div>

        <div>
            @php
                $localManutencao = [
                    'INTERNO' => 'INTERNO',
                    'EXTERNO' => 'EXTERNO',
                ];
            @endphp
            <x-forms.select name="local_manutencao" label="Local Manutenção" :options="$localManutencao" :selected="request('local_manutencao')" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..." :options="$filiais"
                :selected="request('id_filial')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="grupo_resolvedor" label="Grupo Resolvedor"
                placeholder="Selecione o Grupo Resolvedor..." :options="$grupoResolvedor" :selected="request('grupo_resolvedor')"
                asyncSearch="false" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>

            <a href="{{ route('admin.ordemservicos.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const storageKey = 'ordemservicos_search';
                const raw = localStorage.getItem(storageKey);
                if (!raw) return;

                const payload = JSON.parse(raw);
                const values = payload.values || {};

                const form = document.querySelector('form[action="{{ route('admin.ordemservicos.index') }}"]');
                if (!form) return;

                Object.entries(values).forEach(([name, value]) => {
                    try {
                        // seleciona elementos exatamente com esse name dentro do form
                        const els = Array.from(form.querySelectorAll('[name]')).filter(e => e.getAttribute('name') === name);
                        if (!els.length) return;

                        if (els.length > 1) {
                            els.forEach(el => {
                                if (el.type === 'checkbox' || el.type === 'radio') {
                                    if (Array.isArray(value)) {
                                        el.checked = value.includes(el.value);
                                    } else {
                                        el.checked = String(el.value) === String(value);
                                    }
                                } else {
                                    el.value = value;
                                }
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                            });
                            return;
                        }

                        const el = els[0];
                        if (!el) return;

                        if (el.type === 'checkbox') {
                            el.checked = !!value;
                        } else if (el.type === 'radio') {
                            const radios = form.querySelectorAll(`input[type="radio"][name="${name}"]`);
                            radios.forEach(r => r.checked = String(r.value) === String(value));
                        } else if (el.tagName === 'SELECT') {
                            if (el.multiple && Array.isArray(value)) {
                                Array.from(el.options).forEach(opt => opt.selected = value.includes(opt.value));
                            } else {
                                el.value = value;
                            }
                        } else {
                            el.value = value;
                        }

                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    } catch (err) {
                        console.warn('Erro ao restaurar campo', name, err);
                    }
                });

                // limpar localStorage ao clicar no link 'Limpar'
                const clearLink = form.querySelector('a[href="{{ route('admin.ordemservicos.index') }}"]');
                if (clearLink) {
                    clearLink.addEventListener('click', function() {
                        try { localStorage.removeItem(storageKey); } catch (e) { /* ignore */ }
                    });
                }
            } catch (err) {
                console.error('Erro ao restaurar pesquisa O.S.:', err);
            }
        });
    </script>
@endpush
