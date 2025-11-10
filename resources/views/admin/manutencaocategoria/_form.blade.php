<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="id_manutencao_categoria" class="block text-sm font-medium text-gray-700">Cód.
                                    categoria planejamento manutenção</label>
                                <input type="text" id="id_manutencao_categoria" name="id_manutencao_categoria"
                                    readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->id_manutencao_categoria ?? '' }}">
                            </div>

                            <div>
                                <label for="id_categoria"
                                    class="block text-sm font-medium text-gray-700">Categoria</label>
                                <select id="id_categoria" name="id_categoria" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id_categoria }}"
                                            {{ old('id_categoria', $manutencaoConfig->id_categoria ?? '') == $categoria->id_categoria ? 'selected' : '' }}>
                                            {{ $categoria->descricao_categoria }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_planejamento"
                                    class="block text-sm font-medium text-gray-700">Planejamento</label>
                                <select id="id_planejamento" name="id_planejamento" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($planejamentos as $planeja)
                                        <option value="{{ $planeja->id_planejamento_manutencao }}"
                                            {{ old('id_planejamento', $manutencaoConfig->id_planejamento ?? '') == $planeja->id_planejamento_manutencao ? 'selected' : '' }}>
                                            {{ $planeja->manutencao->descricao_manutencao }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                            <div>
                                <label for="hora_gerar_os_automatica"
                                    class="block text-sm font-medium text-gray-700">Horas Gerar Fim da Carência</label>
                                <input type="number" id="hora_gerar_os_automatica" name="hora_gerar_os_automatica"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->hora_gerar_os_automatica ?? '' }}">
                            </div>

                            <div>
                                <label for="km_gerar_os_automatica" class="block text-sm font-medium text-gray-700">Km
                                    Gerar O.S ao fim da Carência</label>
                                <input type="number" id="km_gerar_os_automatica" name="km_gerar_os_automatica"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->km_gerar_os_automatica ?? '' }}">
                            </div>

                            <div>
                                <label for="horas_frequencia" class="block text-sm font-medium text-gray-700">Horas
                                    Frequência</label>
                                <input type="number" id="horas_frequencia" name="horas_frequencia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->horas_frequencia ?? '' }}">
                            </div>

                            <div>
                                <label for="km_frequencia" class="block text-sm font-medium text-gray-700">Km
                                    Frequência</label>
                                <input type="number" id="km_frequencia" name="km_frequencia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->km_frequencia ?? '' }}">
                            </div>

                            <div>
                                <label for="dias_frequencia" class="block text-sm font-medium text-gray-700">Dias
                                    Frequência</label>
                                <input type="number" id="dias_frequencia" name="dias_frequencia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->dias_frequencia ?? '' }}">
                            </div>

                            <div>
                                <label for="eventos_frequencia" class="block text-sm font-medium text-gray-700">Eventos
                                    Frequência</label>
                                <input type="number" id="eventos_frequencia" name="eventos_frequencia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->eventos_frequencia ?? '' }}">
                            </div>

                            <div>
                                <label for="litros_frequencia" class="block text-sm font-medium text-gray-700">Litros
                                    Frequência</label>
                                <input type="number" id="litros_frequencia" name="litros_frequencia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->litros_frequencia ?? '' }}">
                            </div>

                            <div>
                                <label for="horas_tolerancia" class="block text-sm font-medium text-gray-700">Horas
                                    Tolerância</label>
                                <input type="number" id="horas_tolerancia" name="horas_tolerancia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->horas_tolerancia ?? '' }}">
                            </div>

                            <div>
                                <label for="km_tolerancia" class="block text-sm font-medium text-gray-700">Km
                                    Tolerância</label>
                                <input type="number" id="km_tolerancia" name="km_tolerancia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->km_tolerancia ?? '' }}">
                            </div>

                            <div>
                                <label for="dia_tolerancia" class="block text-sm font-medium text-gray-700">Dias
                                    Tolerância</label>
                                <input type="number" id="dia_tolerancia" name="dia_tolerancia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->dia_tolerancia ?? '' }}">
                            </div>

                            <div>
                                <label for="eventos_tolerancia"
                                    class="block text-sm font-medium text-gray-700">Eventos Tolerância</label>
                                <input type="number" id="eventos_tolerancia" name="eventos_tolerancia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->eventos_tolerancia ?? '' }}">
                            </div>

                            <div>
                                <label for="litros_tolerancia" class="block text-sm font-medium text-gray-700">Litros
                                    Tolerância</label>
                                <input type="number" id="litros_tolerancia" name="litros_tolerancia"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->litros_tolerancia ?? '' }}">
                            </div>

                            <div>
                                <label for="hora_alerta" class="block text-sm font-medium text-gray-700">Horas Alerta
                                    ao Faltar</label>
                                <input type="number" id="hora_alerta" name="hora_alerta"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->hora_alerta ?? '' }}">
                            </div>

                            <div>
                                <label for="km_alerta" class="block text-sm font-medium text-gray-700">Km Alerta ao
                                    Faltar</label>
                                <input type="number" id="km_alerta" name="km_alerta"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->km_alerta ?? '' }}">
                            </div>

                            <div>
                                <label for="dias_alerta" class="block text-sm font-medium text-gray-700">Dias Alerta
                                    ao Faltar</label>
                                <input type="number" id="dias_alerta" name="dias_alerta"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->dias_alerta ?? '' }}">
                            </div>

                            <div>
                                <label for="eventos_alerta" class="block text-sm font-medium text-gray-700">Eventos
                                    Alerta ao Faltar</label>
                                <input type="number" id="eventos_alerta" name="eventos_alerta"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->eventos_alerta ?? '' }}">
                            </div>

                            <div>
                                <label for="litros_alerta" class="block text-sm font-medium text-gray-700">Litros
                                    Alerta ao Faltar</label>
                                <input type="number" id="litros_alerta" name="litros_alerta"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->litros_alerta ?? '' }}">
                            </div>

                            <div>
                                <label for="hora_adiantamento" class="block text-sm font-medium text-gray-700">Hora
                                    Adiantamento Máximo</label>
                                <input type="number" id="hora_adiantamento" name="hora_adiantamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->hora_adiantamento ?? '' }}">
                            </div>

                            <div>
                                <label for="km_adiantamento" class="block text-sm font-medium text-gray-700">Km
                                    Adiantamento Máximo</label>
                                <input type="number" id="km_adiantamento" name="km_adiantamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->km_adiantamento ?? '' }}">
                            </div>

                            <div>
                                <label for="dias_adiantamento" class="block text-sm font-medium text-gray-700">Dias
                                    Adiantamento Máximo</label>
                                <input type="number" id="dias_adiantamento" name="dias_adiantamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->dias_adiantamento ?? '' }}">
                            </div>

                            <div>
                                <label for="eventos_adiantamento"
                                    class="block text-sm font-medium text-gray-700">Eventos Adiantamento Máximo</label>
                                <input type="number" id="eventos_adiantamento" name="eventos_adiantamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->eventos_adiantamento ?? '' }}">
                            </div>

                            <div>
                                <label for="litros_adiantamento"
                                    class="block text-sm font-medium text-gray-700">Litros Adiantamento Máximo</label>
                                <input type="number" id="litros_adiantamento" name="litros_adiantamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->litros_adiantamento ?? '' }}">
                            </div>

                            <div>
                                <label for="dias_previstos" class="block text-sm font-medium text-gray-700">Dias
                                    Previstos</label>
                                <input type="number" id="dias_previstos" name="dias_previstos"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->dias_previstos ?? '' }}">
                            </div>

                            <div>
                                <label for="horas_tempo_previsto"
                                    class="block text-sm font-medium text-gray-700">Horas Previstas</label>
                                <input type="number" id="horas_tempo_previsto" name="horas_tempo_previsto"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->horas_tempo_previsto ?? '' }}">
                            </div>

                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" x-on:click="limparFormulario"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar Formulário
                        </button>

                        <a href="{{ route('admin.abastecimentomanual.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
