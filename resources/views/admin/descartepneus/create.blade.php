<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.descartepneus.index') }}"
                   class="inline-flex items-center p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Cadastrar Baixa de Pneu (Manual)
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Funcionalidade restrita a superusuários - Pneus normalmente vêm de manutenção
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                {{-- STATUS INDICATOR --}}
                <div class="flex items-center space-x-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg">
                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-amber-700">Cadastro Manual</span>
                </div>

                {{-- HELP BUTTON --}}
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                         class="origin-top-right absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-3 px-4">
                            <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                Ajuda - Cadastro Manual de Baixa
                            </p>
                            <p class="mt-2 text-xs leading-5 text-gray-600">
                                Esta funcionalidade permite criar baixas de pneu manualmente.
                                <br><br>
                                <strong>Campos obrigatórios:</strong><br>
                                • Número de Fogo do pneu<br>
                                • Tipo de descarte<br>
                                • Valor de venda<br>
                                • Motivo/observação<br><br>
                                O laudo pode ser anexado opcionalmente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- FORM CONTAINER --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Corrija os seguintes erros:
                            </h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form id="descartePneuForm" method="POST" action="{{ route('admin.descartepneus.store') }}"
                  class="space-y-6" enctype="multipart/form-data">
                @csrf

                {{-- INFORMAÇÕES BÁSICAS --}}
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Informações do Pneu
                    </h3>

                    <div class="grid gap-6 sm:grid-cols-1 md:grid-cols-2">
                        {{-- NÚMERO FOGO --}}
                        <div>
                            <x-forms.smart-select name="id_pneu" label="Número de Fogo"
                                                  placeholder="Selecione o pneu..." :options="$pneu"
                                                  :selected="old('id_pneu')" required />
                            <p class="mt-1 text-sm text-gray-500">
                                Apenas pneus disponíveis para descarte são exibidos
                            </p>
                        </div>

                        {{-- TIPO DESCARTE --}}
                        <div>
                            <x-forms.smart-select name="id_tipo_descarte" label="Tipo de Descarte"
                                                  placeholder="Selecione o tipo..." :options="$tipodescarte"
                                                  :searchUrl="route('admin.api.tipodescarte.search')"
                                                  :selected="old('id_tipo_descarte')" asyncSearch="true" required />
                            <p class="mt-1 text-sm text-gray-500">
                                Ex: Sinistro, Sucata, Venda, Fazenda
                            </p>
                        </div>
                    </div>
                </div>

                {{-- VALORES E DOCUMENTAÇÃO --}}
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        Valores e Documentação
                    </h3>

                    <div class="grid gap-6 sm:grid-cols-1 md:grid-cols-2">
                        {{-- VALOR VENDA --}}
                        <div>
                            <label for="valor_venda_pneu" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor de Venda do Pneu <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                </div>
                                <input type="text" oninput="formatarMoedaBrasileira(this)" id="valor_venda_pneu"
                                       name="valor_venda_pneu" required
                                       class="pl-8 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       value="{{ old('valor_venda_pneu') }}">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Valor pelo qual o pneu foi ou será vendido
                            </p>
                        </div>

                        {{-- ANEXO LAUDO --}}
                        <div>
                            <label for="nome_arquivo" class="block text-sm font-medium text-gray-700 mb-2">
                                Laudo de Descarte
                            </label>
                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors duration-150">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="nome_arquivo" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Enviar arquivo</span>
                                            <input id="nome_arquivo" name="nome_arquivo" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                                        </label>
                                        <p class="pl-1">ou arraste e solte</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PDF, JPG, JPEG, PNG até 2MB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- OBSERVAÇÕES --}}
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Motivo do Descarte
                    </h3>

                    <div>
                        <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">
                            Observações <span class="text-red-500">*</span>
                        </label>
                        <textarea id="observacao" name="observacao" rows="4" required
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                  placeholder="Descreva detalhadamente o motivo do descarte do pneu...">{{ old('observacao') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            Máximo 700 caracteres. Seja específico sobre as condições que levaram ao descarte.
                        </p>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Campos com <span class="text-red-500 mx-1">*</span> são obrigatórios
                    </div>

                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.descartepneus.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancelar
                        </a>
                        <button type="submit" id="submit-form"
                                class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Cadastrar Baixa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @include('admin.descartepneus._scripts')
    @endpush
</x-app-layout>
