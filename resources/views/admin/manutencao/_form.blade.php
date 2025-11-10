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
    <div class="flex justify-between items-end">
        <div>
            <x-bladewind::input name="id_manutencao" 
                readonly="true"
                searchable="true" label="Cod. Manutenção"
                selected_value="{{ old('id_manutencao', $manutencao->id_manutencao ?? '') }}" 
            />
        </div>
        <div>
            <x-input-label for="auxiliar" value="Auxiliar" />
            <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="auxiliar" value="true" 
                     {{ old('auxiliar',  ($manutencao->auxiliar ?? true)) == true ? 'checked' : '' }} 
                     class="hidden peer"
                     >
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                </label>
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="auxiliar" value="false"
                      {{ old('auxiliar', ($manutencao->auxiliar ?? false)) == false ? 'checked' : ''  }}
                       class="hidden peer"
                    >
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                </label>
            </div>
        </div>
    </div>


    <div class="flex gap-4 my-4">
        <div class="w-full">
            <x-bladewind::select
                name="id_tipo_manutencao"
                label="Tipo Manutenção"
                selected_value="{{ old('id_tipo_manutencao', $manutencao->id_tipo_manutencao ?? '') }}"
                error_message="id_tipo_manutencao"
                :data="$tipoManutencao"  
            />
        </div>
        <div class="w-full">
            <x-bladewind::input label="Descrição" name="descricao_manutencao" error_message="descricao_manutencao"
            selected_value="{{ old('descricao_manutenca', $manutencao->descricao_manutencao ?? '') }}" />
        </div>
    </div>

    <div class="flex justify-between w-full">
        <div>
            <x-input-label for="ativar" value="Manutenção Ativo" />
            <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="ativar" value="true"
                    {{ old('ativar', ($manutencao->ativar ?? true)) == true ? 'checked' : '' }} 
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                </label>
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="ativar"
                    {{ old('ativar', ($manutencao->ativar ?? false)) == false ? 'checked' : '' }} 
                    value="false" class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                </label>
            </div>
        </div>


        <div>
            <x-input-label for="km_configuracao" value="Quilômetro" />
            <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="km_configuracao" value="true" 
                    {{ old('km_configuracao', ($manutencao->km_configuracao ?? true)) == true ? 'checked' : '' }} 
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                </label>
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="km_configuracao" value="false" 
                    {{ old('km_configuracao',  ($manutencao->km_configuracao ?? false)) == false ? 'checked' : '' }} 
                     class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                </label>
            </div>
        </div>

        <div>
            <x-input-label for="tempo_configuracao" value="Dias" />
            <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="tempo_configuracao" value="true"
                    {{ old('tempo_configuracao', ($manutencao->tempo_configuracao ?? true)) == true ? 'checked' : '' }} 
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                </label>
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="tempo_configuracao" value="false"
                    {{ old('tempo_configuracao', ($manutencao->tempo_configuracao ?? false)) == false ? 'checked' : '' }} 
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                </label>
            </div>
        </div>


        <div>
            <x-input-label for="horas" value="Horas" />
            <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="horas" value="true"
                    {{ old('horas', ($manutencao->horas ?? true)) == true ? 'checked' : '' }} 
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                </label>
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="horas" value="false"
                    {{ old('horas', ($manutencao->horas ?? false)) == false ? 'checked' : '' }} 
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                </label>
            </div>
        </div>

        <div>
            <x-input-label for="Eventos" value="Eventos" />
            <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="eventos" value="true"
                    {{ old('eventos', ($manutencao->eventos ?? false)) == true ? 'checked' : '' }}
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                </label>
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="eventos" value=false
                    {{ old('eventos', ($manutencao->eventos ?? false)) == false ? 'checked' : '' }}
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                </label>
            </div>
        </div>


        <div>
            <x-input-label for="data_fim" value="Combustível(lt)" />
            <div class="inline-flex border border-gray-300 rounded-lg overflow-hidden">
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="combustivel" value="true"
                    {{ old('combustivel', ($manutencao->combustivel ?? false)) == true ? 'checked' : '' }}
                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Sim</span>
                </label>
                <label class="flex justify-beetwen itens-center cursor-pointer text-gray-700 bg-white">
                    <input type="radio" name="combustivel" value="false"
                    {{ old('combustivel',($manutencao->combustivel ?? false))== false ? 'checked' : '' }}

                    class="hidden peer">
                    <span class="px-4 py-2 font-bold peer-checked:text-black peer-checked:bg-black/50">Não</span>
                </label>
            </div>
        </div>
    </div>
    <div class="flex justify-end space-x-3 mt-6 col-span-full">
        <x-bladewind::button tag="a" href="{{ route('admin.manutencoes.index') }}" outline>
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
    