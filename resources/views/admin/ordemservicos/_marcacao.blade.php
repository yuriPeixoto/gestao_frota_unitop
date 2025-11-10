<div class="overflow-x-auto">
    <table class="min-w-full bg-white rounded shadow">
        <thead>
            <tr>
                <th colspan="4" class="text-lg font-semibold py-4 bg-gray-100 border-b text-black">
                    Marcação de Número de Fogo
                </th>
            </tr>
            <tr class="bg-gray-50">
                <th class="px-4 py-2 text-left text-black">Número de Fogo</th>
                <th class="px-4 py-2 text-left text-black">Descrição</th>
                <th class="px-4 py-2 text-left text-black">Marcado por</th>
                <th class="px-4 py-2 text-left text-black">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="checkAllMarcacoes"
                            class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring focus:ring-blue-300"
                            onclick="toggleAllMarcacoes(this); processarSelecionarTodos();">
                        <span class="text-sm text-gray-700">Marcar todos</span>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($marcacoes as $marcacao)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-bold text-black">{{ $marcacao->id_pneu }}</td>
                    <td class="px-4 py-2 text-black">{{ $marcacao->pneus->modeloPneu->descricao_modelo }}</td>
                    <td class="px-4 py-2">{{ $marcacao->usuarioMarcadoPor->name ?? '-' }}</td>
                    <td class="px-4 py-2 flex items-center gap-2">
                        <input type="checkbox" name="marcacao[]" value="{{ $marcacao->id_pneu }}"
                            class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring focus:ring-blue-300"
                            {{ $marcacao->is_marcado ? 'disabled' : '' }}
                            onchange="atualizarMarcadoPor(this, {{ $marcacao->id_pneu }})"
                            @if ($marcacao->is_marcado) checked @endif>
                        <span class="text-sm text-gray-700">Marcado</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-500">
                        Nenhum registro encontrado
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <script>
        function toggleAllMarcacoes(source) {
            const checkboxes = document.querySelectorAll('input[name="marcacao[]"]');
            checkboxes.forEach(cb => {
                cb.checked = source.checked;
            });
        }

        function atualizarMarcadoPor(checkbox, marcacaoId) {
            idOrdemservico = {{ $ordem->id_ordem_servico }};

            fetch('/admin/ordemservicos/marcar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        idOrdemservico: idOrdemservico,
                        id: marcacaoId,
                        marcado: checkbox.checked
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualiza o nome do usuário na célula
                        const td = checkbox.closest('tr').querySelector('td:nth-child(3)');
                        td.textContent = data.marcado_por ?? '-';
                    } else {
                        alert('Erro ao marcar: ' + (data.message || ''));
                    }
                });
        }

        function processarSelecionarTodos() {
            const checkboxes = document.querySelectorAll('input[name="marcacao[]"]:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            idOrdemservico = {{ $ordem->id_ordem_servico }};

            if (ids.length === 0) {
                alert('Nenhuma marcação selecionada!');
                return;
            }

            fetch('/admin/ordemservicos/marcar-todos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ids: ids,
                        idOrdemservico: idOrdemservico
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualiza todos os nomes na coluna "Marcado por"
                        checkboxes.forEach(cb => {
                            const td = cb.closest('tr').querySelector('td:nth-child(3)');
                            td.textContent = data.marcado_por ?? '-';
                        });
                        alert('Marcações processadas com sucesso!');
                    } else {
                        alert('Erro ao processar: ' + (data.message || ''));
                    }
                });
        }
    </script>
</div>
