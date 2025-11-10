<div class="bg-white p-8 overflow-hidden shadow-sm sm:rounded-lg">
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-bladewind::tab-group name="free-pics">

        <x-slot:headings>
            <x-bladewind::tab-heading
                name="servicos" label="Serviço" active="true"/>

            <x-bladewind::tab-heading
                name="pecas" label="Peças Relacionadas" />
           
        </x-slot:headings>

        <x-bladewind::tab-body>
            {{-- inicio --}}
            <x-bladewind::tab-content name="servicos" active="true">
                <div class="w-2/12">
                    <x-bladewind::input 
                        name="id_servico" 
                        label="Cód. Serviço"
                        readonly="true"
                        searchable="true" 
                        selected_value="{{ old('id_servico', isset($servico->id_servico) ?? '') }}" 
                    />
                </div>
                
                <div class="flex w-full gap-4">
                    <div class="pt-4 flex w-full gap-4">
                        <div class="w-full ">
                            <select disabled class="block outline-none  w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option selected value={{$matriz->id}}>{{$matriz->name}}</option>
                            </select>
                        </div>
                        <div class="w-full">
                            <x-bladewind::select
                                label="Cod. Grupo"
                                name="id_grupo"
                                selected_value="{{ old('id_grupo', $servico->id_grupo ?? '') }}"
                                error_message="id_grupo"
                                :data="$grupo"  
                            />
                        </div>
                    </div>

                    <div class="b-4">
                        <x-input-label for="ativar" value="Serviço Ativo?" />
                        <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                            <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                                <input type="radio" name="ativar_servico" value="true"
                                {{ old('ativar_servico', ($servico->ativar_servico ?? true)) == true ? 'checked' : '' }} 
                                class="hidden peer">
                                <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                            </label>
                            <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                                <input type="radio" name="ativar_servico"
                                {{ old('ativar_servico', ($servico->ativar_servico ?? false)) == false ? 'checked' : '' }} 
                                value="false" class="hidden peer">
                                <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="w-full">
                    <x-bladewind::input 
                        name="descricao_manutencao" 
                        label="Descrição Serviço"
                        searchable="true" 
                        selected_value="{{ old('descricao_manutencao', $servico->descricao_manutencao ?? '') }}" 
                    />
                </div>

                <div class="flex w-full gap-4">
                    <div class="pt-4 flex w-full gap-4">
                        <div class="w-full">
                            <x-bladewind::select
                                label="Manutenção"
                                name="id_manutencao"
                                selected_value="{{ old('id_manutencao', isset($servico->id_manutencao) ?? '') }}"
                                error_message="id_manutencao"
                                :data="$manutencao"  
                            />
                        </div>
                        <div class="w-full">
                            <x-bladewind::input 
                                label="Tempo Serviço"
                                name="hora_servico" 
                                type="time"
                                searchable="true" 
                                selected_value="{{ old('hora_servico', isset($servico->hora_servico) ?? '') }}" 
                            />
                        </div>
                    </div>

                    <div class="w-full">
                        <x-input-label for="ativo_servico" value="Auxiliar" />
                        <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                            <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                                <input type="radio" name="ativo_servico" value="true"
                                {{ old('ativo_servico', ($servico->ativo_servico ?? true)) == true ? 'checked' : '' }} 
                                class="hidden peer">
                                <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                            </label>
                            <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                                <input type="radio" name="ativo_servico"
                                {{ old('ativo_servico', $servico->ativo_servico ?? false) == false ? 'checked' : '' }} 
                                value="false" class="hidden peer">
                                <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 mb-8 w-7/12 items-center">
                    <select name="id_categoria" id="id_categoria" class="block outline-none py-3  w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" >
                        @foreach ($veiculos as $veiculo)
                            <option value={{ htmlspecialchars(json_encode($veiculo), ENT_QUOTES, 'UTF-8') }}>{{$veiculo->id_categoria}} - {{$veiculo->descricao_categoria}}</option>
                        @endforeach
                    </select>
                     <div class="h-16 pt-2">
                        <button id="adicionar" type="button" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                            class="px-4 py-3 text-white bg-blue-500 rounded hover:bg-blue-600">
                            Adicionar
                        </button>
                     </div>
                </div>

                <table id="table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               data inclusão
                            </th>
    
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               data Alteração
                            </th>
    
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               cód.categoria
                            </th>
    
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                categoria
                            </th>
    
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                               Cód.serviço
                            </th>
                        </tr>
                    </thead>
                    {{-- <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($results as $result)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $result->id_servico }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->data_inclusao }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->data_alteracao }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->id_tipo_servico }} - {{   $result->tipo_servico_descricao }}
                            </td>
                            <td class="px-6 py-4  text-sm text-gray-500">
                                {{ $result->descricao_servico }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $result->descricao_servico }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->km_configuracao }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->tempo_configutacao }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->horas}}
                            </td>
                            <td class="px-6 py-4 flex justify-end items-center whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.manutencoes.edit', $result->id_servico) }}"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-md border border-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                                    </svg>
                                    editar
                                </a> 
                                 <a href="{{ route('admin.checklist.edit', $result->id) }}"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-md border border-gray-300">
                                    <svg class="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    Editar
                                </a>
                                 <form
                                    action="{{ route('admin.checklist.delete', $result->id) }}"
                                    method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Tem certeza que deseja excluir?')"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-white hover:bg-red-50 rounded-md border border-red-300">
                                        <svg class="mr-2 h-4 w-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Excluir
                                    </button>
                                </form> 
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                Nenhuma pergunta cadastrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody> --}}
                </table>

            </x-bladewind::tab-content>
            {{-- fim --}}

            {{-- inicio --}}
            <x-bladewind::tab-content name="pecas">
                <div class="flex gap-4 items-end">
                    <x-forms.smart-select 
                        label="Cód.Produto" 
                        name="id_pessoal"
                        {{-- :options="$motoristasFrequentes"  --}}
                        {{-- :searchUrl="route('admin.api.pessoas.search')" --}}
                        :selected="request('id_pessoal')"
                        asyncSearch="true"
                     />
                     <div class="h-15 pt-3">
                        <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                            class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                            Adicionar
                        </button>
                     </div>
                </div>
            </x-bladewind::tab-content>
            {{-- fim --}}

        </x-bladewind::tab-body>

    </x-bladewind::tab-group>
    <div class="flex justify-end space-x-3 mt-6 col-span-full">
        <x-bladewind::button tag="a" href="{{ route('admin.servicos.index') }}" outline>
            Cancelar
        </x-bladewind::button>
        <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
            class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
            <template x-if="!isSubmitting">
                <span>{{ isset($checklist) ? 'Atualizar' : 'Salvar' }}</span>
            </template>
            <template x-if="isSubmitting">
                <span>{{ isset($checklist) ? 'Atualizando...' : 'Salvando...' }}</span>
            </template>
        </button>
    </div>
</div>

<script>

let button = document.querySelector('[type="button"]');
let select = document.querySelector('[name="id_categoria"]');
let table = document.querySelector('[id="table"]');


button.addEventListener('click', () => {
    console.log(select.value);
    console.log('clicou');  
})
</script>