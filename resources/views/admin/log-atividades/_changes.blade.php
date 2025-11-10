<div class="border rounded bg-gray-50 p-4">
    @if($activity->action === 'created')
        @php
            $relevantData = getRelevantChanges($activity->new_values ?? []);
        @endphp

        {{-- DEBUG: Mostrar dados brutos se não há dados relevantes --}}
        @if(count($relevantData) === 0 && !empty($activity->new_values))
            <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-3">
                <p class="text-sm text-yellow-800 font-medium">Debug: Dados brutos disponíveis mas filtrados</p>
                <p class="text-xs text-yellow-600">Raw data count: {{ count($activity->new_values ?? []) }}</p>
                <p class="text-xs text-yellow-600">Filtered count: {{ count($relevantData) }}</p>
                @if(auth()->user()->can('ver_auditoria_completa'))
                    <details class="mt-2">
                        <summary class="text-xs cursor-pointer">Ver dados brutos</summary>
                        <pre class="text-xs mt-1 bg-white p-2 rounded overflow-auto max-h-32">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @endif
            </div>
        @endif

        @if(count($relevantData) > 0)
            <h4 class="font-medium text-sm mb-2 text-green-800">Dados criados:</h4>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($relevantData as $key => $value)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">
                            {{ formatActivityAttribute($key) }}:
                        </dt>
                        <dd class="text-sm">
                            {!! formatActivityValue($key, $value, $users) !!}
                        </dd>
                    </div>
                @endforeach
            </dl>
        @else
            <div class="bg-red-50 border border-red-200 rounded p-3">
                <p class="text-sm text-red-800 font-medium">🔍 DEBUG: Nenhum dado relevante encontrado</p>
                <div class="text-xs text-red-600 mt-2 space-y-1">
                    <p><strong>Activity ID:</strong> {{ $activity->id }}</p>
                    <p><strong>new_values é null:</strong> {{ $activity->new_values === null ? 'SIM' : 'NÃO' }}</p>
                    <p><strong>new_values é array vazio:</strong> {{ empty($activity->new_values) ? 'SIM' : 'NÃO' }}</p>
                    <p><strong>Campos em new_values:</strong> {{ $activity->new_values ? count($activity->new_values) : 0 }}</p>
                    <p><strong>relevantData count:</strong> {{ count($relevantData) }}</p>
                    @if($activity->new_values)
                        <p><strong>Campos disponíveis:</strong> {{ implode(', ', array_keys($activity->new_values)) }}</p>
                    @endif
                </div>
                @if($activity->new_values)
                    <details class="mt-2">
                        <summary class="text-xs cursor-pointer text-red-700">Ver raw data</summary>
                        <pre class="text-xs bg-white p-2 rounded mt-1">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </details>
                @endif
            </div>
        @endif

    @elseif($activity->action === 'updated')
        @php
            $relevantOldValues = getRelevantChanges($activity->old_values ?? []);
            $relevantNewValues = getRelevantChanges($activity->new_values ?? []);

            // Mostrar apenas campos que realmente mudaram
            $changedFields = [];
            foreach($relevantNewValues as $key => $newValue) {
                // Só verificar se o campo existe em ambos os arrays
                if (array_key_exists($key, $relevantOldValues)) {
                    $oldValue = $relevantOldValues[$key];

                    // Normalizar os valores para comparação (converter tudo para string)
                    $oldNormalized = is_null($oldValue) ? '' : (string) $oldValue;
                    $newNormalized = is_null($newValue) ? '' : (string) $newValue;

                    // Comparar os valores normalizados - só incluir se forem diferentes
                    if ($oldNormalized !== $newNormalized) {
                        $changedFields[$key] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                }
            }
        @endphp

        {{-- DEBUG: Para updates também --}}
        @if(count($changedFields) === 0 && (!empty($activity->old_values) || !empty($activity->new_values)))
            <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-3">
                <p class="text-sm text-yellow-800 font-medium">Debug: Dados de update disponíveis mas sem mudanças detectadas</p>
                <p class="text-xs text-yellow-600">Old values count: {{ count($activity->old_values ?? []) }}</p>
                <p class="text-xs text-yellow-600">New values count: {{ count($activity->new_values ?? []) }}</p>
                <p class="text-xs text-yellow-600">Relevant old count: {{ count($relevantOldValues) }}</p>
                <p class="text-xs text-yellow-600">Relevant new count: {{ count($relevantNewValues) }}</p>
                <p class="text-xs text-yellow-600">Changed fields count: {{ count($changedFields) }}</p>
                @if(auth()->user()->can('ver_auditoria_completa'))
                    <details class="mt-2">
                        <summary class="text-xs cursor-pointer">Ver comparação de dados</summary>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <div>
                                <strong class="text-xs">Old:</strong>
                                <pre class="text-xs bg-white p-2 rounded overflow-auto max-h-32">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            <div>
                                <strong class="text-xs">New:</strong>
                                <pre class="text-xs bg-white p-2 rounded overflow-auto max-h-32">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </details>
                @endif
            </div>
        @endif

        @if(count($changedFields) > 0)
            <h4 class="font-medium text-sm mb-3 text-blue-800">Campos alterados:</h4>
            <div class="space-y-3">
                @foreach($changedFields as $key => $values)
                    <div class="bg-white rounded p-3 border-l-4 border-blue-500">
                        <div class="text-xs font-medium text-gray-500 mb-2">
                            {{ formatActivityAttribute($key) }}
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs text-red-600 font-medium">Anterior:</span>
                                <div class="text-sm">
                                    {!! formatActivityValue($key, $values['old'], $users) !!}
                                </div>
                            </div>
                            <div>
                                <span class="text-xs text-green-600 font-medium">Novo:</span>
                                <div class="text-sm">
                                    {!! formatActivityValue($key, $values['new'], $users) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded p-3">
                <p class="text-sm text-red-800 font-medium">🔍 DEBUG: Nenhuma alteração detectada</p>
                <div class="text-xs text-red-600 mt-2 space-y-1">
                    <p><strong>Activity ID:</strong> {{ $activity->id }}</p>
                    <p><strong>old_values é null:</strong> {{ $activity->old_values === null ? 'SIM' : 'NÃO' }}</p>
                    <p><strong>new_values é null:</strong> {{ $activity->new_values === null ? 'SIM' : 'NÃO' }}</p>
                    <p><strong>Campos em old_values:</strong> {{ $activity->old_values ? count($activity->old_values) : 0 }}</p>
                    <p><strong>Campos em new_values:</strong> {{ $activity->new_values ? count($activity->new_values) : 0 }}</p>
                    <p><strong>relevantOldValues count:</strong> {{ count($relevantOldValues) }}</p>
                    <p><strong>relevantNewValues count:</strong> {{ count($relevantNewValues) }}</p>
                    <p><strong>changedFields count:</strong> {{ count($changedFields) }}</p>
                    @if($activity->old_values)
                        <p><strong>Campos old disponíveis:</strong> {{ implode(', ', array_keys($activity->old_values)) }}</p>
                    @endif
                    @if($activity->new_values)
                        <p><strong>Campos new disponíveis:</strong> {{ implode(', ', array_keys($activity->new_values)) }}</p>
                    @endif
                </div>
                @if($activity->old_values || $activity->new_values)
                    <details class="mt-2">
                        <summary class="text-xs cursor-pointer text-red-700">Ver raw data</summary>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <div>
                                <strong>Old:</strong>
                                <pre class="text-xs bg-white p-2 rounded">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            <div>
                                <strong>New:</strong>
                                <pre class="text-xs bg-white p-2 rounded">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </details>
                @endif
            </div>
        @endif

    @elseif($activity->action === 'deleted')
        @php
            $relevantData = getRelevantChanges($activity->old_values ?? []);
        @endphp

        @if(count($relevantData) > 0)
            <h4 class="font-medium text-sm mb-2 text-red-800">Dados do registro excluído:</h4>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($relevantData as $key => $value)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">
                            {{ formatActivityAttribute($key) }}:
                        </dt>
                        <dd class="text-sm">
                            {!! formatActivityValue($key, $value, $users) !!}
                        </dd>
                    </div>
                @endforeach
            </dl>
        @else
            <p class="text-sm text-gray-600">Registro excluído sem dados relevantes visíveis.</p>
        @endif
    @endif

    {{-- Botão para ver dados completos (apenas para auditoria) --}}
    @if(auth()->user()->can('ver_auditoria_completa'))
        <div class="mt-4 pt-4 border-t">
            <button
                class="text-xs text-blue-600 hover:text-blue-800"
                onclick="toggleAuditDetails(this)"
            >
                Ver dados completos de auditoria
            </button>
            <div class="hidden mt-2 bg-gray-100 p-3 rounded text-xs">
                <div class="space-y-2">
                    @if($activity->old_values)
                        <div>
                            <strong>Valores anteriores (completo):</strong>
                            <pre class="mt-1 overflow-auto max-h-32">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                    @if($activity->new_values)
                        <div>
                            <strong>Novos valores (completo):</strong>
                            <pre class="mt-1 overflow-auto max-h-32">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                    @if($activity->ip_address)
                        <div><strong>IP:</strong> {{ $activity->ip_address }}</div>
                    @endif
                    @if($activity->user_agent)
                        <div><strong>User Agent:</strong> {{ $activity->user_agent }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function toggleAuditDetails(button) {
    const details = button.nextElementSibling;
    details.classList.toggle('hidden');
    button.textContent = details.classList.contains('hidden')
        ? 'Ver dados completos de auditoria'
        : 'Ocultar dados completos de auditoria';
}

function toggleDetails(button) {
    const details = button.nextElementSibling;
    details.classList.toggle('hidden');
    button.textContent = details.classList.contains('hidden')
        ? 'ver detalhes'
        : 'ocultar detalhes';
}
</script>
