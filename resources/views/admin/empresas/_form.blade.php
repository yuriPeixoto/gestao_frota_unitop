<div class="space-y-6">
    @if($errors->any())
    <div class="mb-4 bg-red-50 p-4 rounded">
        <ul class="list-disc list-inside text-red-600">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <h3 class="text-lg font-medium text-gray-900 mb-4">Empresa</h3>

    <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-4 lg:grid-cols-6 items-center">
        <div>
            <x-bladewind::filepicker name="logo" required="false" placeholder="Escolha uma logo"
                accepted_file_types="image/*" selected_value_class="h-full w-full" base64="false"
                url="{{ old('logo', isset($empresa) && $empresa->logo ? url('storage/' . $empresa->logo) : '') }}"
                selected_value="{{ old('logo', $empresa->logo ?? '') }}" />
        </div>

        @foreach ($data as $key => $field)
        @switch($field['type'])
        @case('select')

        @php
        $options = $field['options'];
        @endphp



        <x-bladewind::select name="{{ $key }}" placeholder="{{ $field['label'] }}" :data="$options"
            label="{{$field['label']}}" searchable="true" id="{{$key}}"
            selected_value="{{ old($key, $empresa->$key ?? '') }}" />
        @break

        @case('radio')
        <div class="grid grid-cols-2 ">
            <label for="{{$key}}" class="uppercase col-span-full text-sm self-center">{{$key}}</label>
            @foreach ($field['options'] as $option)
            <x-bladewind::radio-button name="{{ $key }}" value="{{ $option['value'] }}" add_clearing
                label="{{ $option['label'] }}"
                checked="{{ old($key, $empresa->$key ?? '') == $option['value'] ? 'true' : 'false' }}" />
            @endforeach
        </div>
        @break
        @default



        <x-bladewind::input name="{{ $key }}" type="{{$field['type']}}" maxlength="{{$field['maxlength'] ?? 100}}"
            label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
            selected_value="{{ old($key, $empresa->$key ?? '')}}" />
        @break

        @endswitch

        @endforeach

        <!-- Botões -->
        <div class="flex justify-end space-x-3 col-span-full">
            <a href="{{ route('admin.empresas.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Cancelar
            </a>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                {{ isset($empresa) ? 'Atualizar' : 'Salvar' }}
            </button>
        </div>
    </div>
</div>



<script>
    const cnpjInput = document.getElementById('cnpj');

                cnpjInput.addEventListener('input', function () {
                let value = cnpjInput.value;

                // Remove qualquer caractere que não seja número
                value = value.replace(/\D/g, '');

                // Adiciona a formatação
                value = value.replace(/^(\d{2})(\d)/, '$1.$2'); // Coloca o ponto após os 2 primeiros dígitos
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3'); // Coloca o ponto após os próximos 3
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2'); // Coloca a barra após os próximos 3
                value = value.replace(/(\d{4})(\d)/, '$1-$2'); // Coloca o traço antes dos últimos 2

                // Atualiza o valor do campo
                cnpjInput.value = value;
                });


                

    // Apenas quando tiver criando que ira consultar a api de CNPJ 
                if(location.href.includes('criar')) {
                    console.log('criar')
                    cnpjInput.addEventListener('blur', async function () {
                        try {
                                const clearInput = cnpjInput.value.replace(/\D/g, ""); // Remove tudo que não é número;

                                if(clearInput && clearInput.toString().length === 14)  {
                                    const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${clearInput}`);


                                    if(response.ok) {
                                        const json = await response.json();

                                        if(typeof json === 'object' && 'uf' in json) {
                                            document.getElementById('logradouro').value = json.logradouro;
                                            document.getElementById('municipio').value = json.municipio;
                                            document.getElementById('razaosocial').value = json.razao_social;
                                            document.getElementById('email').value = json.email;
                                            document.getElementById('telefone').value = json.ddd_telefone_1;
                                            document.getElementById('nomefantasia').value = json.nome_fantasia;
                                            document.getElementById('bairro').value = json.bairro;
                                            document.getElementById('numero').value = json.numero;
                                            document.getElementById('cep').value = json.cep;

                                            bw_uf.selectByValue(json.uf);
                                        }
                                    }
                                }
                              

                            } catch (error) {
                                console.log(error);
                            }
                });
            }





</script>