<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Chech List Recebimento Fornecedor') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                    Ajuda - Check List Recebimento Fornecedor
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Está tela tem como finalidade avaliar os fornecedores.
                                    Os campos abaixo servem para Avalia-los conforme necessário!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $notas = [
            ['value' => '10', 'label' => 'Conforme'],
            ['value' => '5', 'label' => 'Parcialmente Conforme'],
            ['value' => '0', 'label' => 'Não Conforme'],
            ['value' => 'N.A.', 'label' => 'Não Aplicavel'],
        ];
    @endphp

    <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <div>
                    <form action="{{ route('admin.checklistrecebimentofornecedor.store') }}" method="POST"
                        x-data="{ isSubmitting: false }"
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                        @csrf
                        <input type="hidden" name="id_nota_fiscal_entrada" value="{{ $idNotaFiscalEntrada ?? '' }}">
                        <input type="hidden" name="id_entrada_manutencao_pneu"
                            value="{{ $idEntradaManutencaoPneu ?? '' }}">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-4 py-3 bg-gray-50 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">
                                    </th>
                                    <th
                                        class="px-4 py-3 bg-gray-50 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">
                                        Nota:
                                    </th>
                                    <th
                                        class="px-4 py-3 bg-gray-50 text-left text-xl font-medium text-gray-500 uppercase tracking-wider">
                                        Observações:
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Conteúdo das linhas mantido, apenas ajustando paddings -->
                                <tr>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        Prazo da Entrega Conforme a Tratativa?
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <select name="checklist_fornecedor_prazo" id="checklist_fornecedor_prazo"
                                            required
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option class="text-xl" value="" selected>Selecione...</option>
                                            @foreach ($notas as $nota)
                                                <option class="text-xl" value="{{ $nota['value'] }}">
                                                    {{ $nota['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <textarea name="checklist_observacao_prazo" id="checklist_observacao_prazo" cols="30" rows="3"
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        Pontualidade (Horários da Empresa):
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <select name="checklist_fornecedor_pontualidade"
                                            id="checklist_fornecedor_pontualidade" required
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option class="text-xl" value="" selected>Selecione...</option>
                                            @foreach ($notas as $nota)
                                                <option class="text-xl" value="{{ $nota['value'] }}">
                                                    {{ $nota['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <textarea name="checklist_observacao_pontualidade" id="checklist_observacao_pontualidade" cols="30" rows="3"
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        Quantidade Conforme Documento de Compra?:
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <select name="checklist_fornecedor_quantidade_conforme"
                                            id="checklist_fornecedor_quantidade_conforme" required
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option class="text-xl" value="" selected>Selecione...</option>
                                            @foreach ($notas as $nota)
                                                <option class="text-xl" value="{{ $nota['value'] }}">
                                                    {{ $nota['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <textarea name="checklist_observacao_quantidade_conforme" id="checklist_observacao_quantidade_conforme" cols="30"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        Integridade das Embalagens:
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <select name="checklist_fornecedor_integridade_embalagens"
                                            id="checklist_fornecedor_integridade_embalagens" required
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option class="text-xl" value="" selected>Selecione...</option>
                                            @foreach ($notas as $nota)
                                                <option class="text-xl" value="{{ $nota['value'] }}">
                                                    {{ $nota['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-4 text-xl font-medium text-gray-900">
                                        <textarea name="checklist_observacao_integridade_embalagens" id="checklist_observacao_integridade_embalagens"
                                            cols="30" rows="3"
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="flex justify-right space-x-3 col-span-full mt-5">
                            <!-- Botão Enviar -->
                            <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                                class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                                <template x-if="!isSubmitting">
                                    <span>{{ isset($nfEntrada) ? 'Atualizar' : 'Salvar' }}</span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span>{{ isset($nfEntrada) ? 'Atualizando...' : 'Salvando...' }}</span>
                                </template>
                            </button>

                            {{-- <x-forms.button href="{{ route('admin.notafiscalentrada.index') }}" type="secondary"
                                variant="outlined">
                                Cancelar
                            </x-forms.button> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



</x-app-layout>
