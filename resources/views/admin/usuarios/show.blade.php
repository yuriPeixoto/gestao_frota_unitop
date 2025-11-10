<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes do Usuário') }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.usuarios.edit', $user) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <x-icons.pencil class="h-4 w-4 mr-2" />
                    Editar
                </a>
                <a href="{{ route('admin.usuarios.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="max-w-7xl mx-auto space-y-6">
                        {{-- Seção: Informações Básicas --}}
                        <div class="bg-gradient-to-br from-white to-blue-50 rounded-xl p-6 shadow-lg border border-blue-100">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Informações Básicas</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500">Nome</p>
                                    <p class="font-medium">{{ $user->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">CPF</p>
                                    <p class="font-medium font-mono">
                                        @if($user->cpf)
                                            {{ preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $user->cpf) }}
                                        @else
                                            Não informado
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Matrícula</p>
                                    <p class="font-medium">
                                        @if($user->matricula)
                                        <span class="font-mono">{{ $user->matricula }}</span>
                                        @else
                                        Não informada
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Último Acesso</p>
                                    <p class="font-medium">
                                        {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca
                                        acessou' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Criado em</p>
                                    <p class="font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Atualizado em</p>
                                    <p class="font-medium">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Telefone Fixo</p>
                                    @php $tel = optional($user->telefones->first()); @endphp
                                    <p class="font-medium font-mono">
                                        @if($tel->telefone_fixo)
                                            {{ preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', preg_replace('/[^\d]/', '', $tel->telefone_fixo)) }}
                                        @else
                                            Não informado
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Telefone Celular</p>
                                    <p class="font-medium font-mono">
                                        @if($tel->telefone_celular)
                                            {{ preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', preg_replace('/[^\d]/', '', $tel->telefone_celular)) }}
                                        @else
                                            Não informado
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Tipo de Usuário</p>
                                    <div class="mt-1">
                                        @if($user->is_superuser)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-red-100 text-red-800">
                                            Super Usuário
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800">
                                            Usuário Padrão
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Departamento e Filial --}}
                        <div class="bg-gradient-to-br from-white to-green-50 rounded-xl p-6 shadow-lg border border-green-100">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Departamento e Filial</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500">Filiais</p>
                                    <div class="font-medium space-y-1">
                                        <div>
                                            <span class="text-gray-600 text-xs">Principal:</span>
                                            <span>{{ $user->filial->name ?? 'Não atribuída' }}</span>
                                        </div>
                                        @php
                                            $secundarias = $user->filiais->pluck('name', 'id')->toArray();
                                            // Evitar duplicar a principal na listagem de secundárias
                                            if ($user->filial_id) { unset($secundarias[$user->filial_id]); }
                                        @endphp
                                        @if(!empty($secundarias))
                                            <div class="text-gray-600 text-xs">Secundárias:</div>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach($secundarias as $fid => $fname)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ $fname }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Departamento</p>
                                    <p class="font-medium">{{ $user->departamento->descricao_departamento ?? 'Não
                                        atribuído' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Cargo</p>
                                    <p class="font-medium">{{ $user->tipoPessoal->descricao_tipo ?? 'Não atribuído' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Endereço --}}
                        <div class="bg-gradient-to-br from-white to-purple-50 rounded-xl p-6 shadow-lg border border-purple-100">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Endereço</h3>
                            </div>
                            @if($user->address)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500">CEP</p>
                                    <p class="font-medium font-mono">
                                        @if($user->address->zip_code)
                                            {{ preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', preg_replace('/[^\d]/', '', $user->address->zip_code)) }}
                                        @else
                                            Não informado
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Cidade</p>
                                    <p class="font-medium">{{ $user->address->city ?? 'Não informado' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Estado</p>
                                    <p class="font-medium">{{ $user->address->state ?? 'Não informado' }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-sm text-gray-500">Logradouro</p>
                                    <p class="font-medium">
                                        {{ $user->address->street ?? 'Não informado' }}
                                        @if($user->address->number), {{ $user->address->number }}@endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Bairro</p>
                                    <p class="font-medium">{{ $user->address->district ?? 'Não informado' }}</p>
                                </div>
                                @if($user->address->complement)
                                <div class="md:col-span-3">
                                    <p class="text-sm text-gray-500">Complemento</p>
                                    <p class="font-medium">{{ $user->address->complement }}</p>
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-purple-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Nenhum endereço cadastrado</h4>
                                <p class="text-gray-500">Este usuário ainda não possui endereço registrado no sistema.</p>
                            </div>
                            @endif
                        </div>

                        {{-- Seção: Histórico de Alterações --}}
                        @if($activityLogs->count() > 0)
                        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl p-6 shadow-lg border border-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-indigo-100 rounded-lg">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900">Histórico de Alterações</h3>
                                        <p class="text-sm text-gray-500 mt-1">{{ $activityLogs->total() }} {{ $activityLogs->total() == 1 ? 'registro encontrado' : 'registros encontrados' }}</p>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                    Página {{ $activityLogs->currentPage() }} de {{ $activityLogs->lastPage() }}
                                </div>
                            </div>

                            <div class="overflow-hidden shadow-sm rounded-xl border border-gray-200">
                                <x-tables.table>
                                    <x-tables.header class="bg-gray-50">
                                        <x-tables.head-cell class="font-semibold text-gray-700">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>Data/Hora</span>
                                            </div>
                                        </x-tables.head-cell>
                                        <x-tables.head-cell class="font-semibold text-gray-700">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                                <span>Ação</span>
                                            </div>
                                        </x-tables.head-cell>
                                        <x-tables.head-cell class="font-semibold text-gray-700">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span>Realizado por</span>
                                            </div>
                                        </x-tables.head-cell>
                                        <x-tables.head-cell class="font-semibold text-gray-700">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span>Detalhes</span>
                                            </div>
                                        </x-tables.head-cell>
                                    </x-tables.header>
                                    <x-tables.body>
                                        @foreach($activityLogs as $index => $log)
                                        <x-tables.row :index="$index" class="hover:bg-gray-50 transition-colors duration-150">
                                            <x-tables.cell nowrap class="font-mono text-sm">
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-gray-900">{{ $log->created_at->format('d/m/Y') }}</span>
                                                    <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                                                </div>
                                            </x-tables.cell>
                                            <x-tables.cell>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                    {{ $log->action === 'created' ? 'bg-green-100 text-green-800' :
                                                       ($log->action === 'updated' ? 'bg-blue-100 text-blue-800' :
                                                        ($log->action === 'deleted' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ $log->translated_action ?? ucfirst($log->action) }}
                                                </span>
                                            </x-tables.cell>
                                            <x-tables.cell>
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                        <span class="text-xs font-medium text-gray-600">{{ substr($log->user->name ?? 'S', 0, 1) }}</span>
                                                    </div>
                                                    <span class="font-medium text-gray-900">{{ $log->user->name ?? 'Sistema' }}</span>
                                                </div>
                                            </x-tables.cell>
                                            <x-tables.cell>
                                                @if(is_array($log->old_values) && is_array($log->new_values))
                                                @php
                                                $oldValues = $log->old_values;
                                                $newValues = $log->new_values;
                                                $modifiedValues = array_diff_assoc($newValues, $oldValues);
                                                @endphp
                                                @if(!empty($modifiedValues))
                                                <button type="button" onclick="toggleChanges('changes-{{ $log->id }}')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-100 transition-colors duration-150 border border-indigo-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Ver alterações ({{ count($modifiedValues) }})
                                                </button>
                                                <div id="changes-{{ $log->id }}" class="hidden mt-3">
                                                    <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-3">
                                                        @foreach($modifiedValues as $key => $newValue)
                                                        <div class="border-l-4 border-blue-400 pl-4">
                                                            <div class="font-semibold text-gray-900 text-sm mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                                            <div class="space-y-2">
                                                                <div class="flex items-center space-x-2">
                                                                    <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded">Anterior:</span>
                                                                    <span class="text-sm text-red-600 line-through">{{ $oldValues[$key] ?? 'Não definido' }}</span>
                                                                </div>
                                                                <div class="flex items-center space-x-2">
                                                                    <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">Novo:</span>
                                                                    <span class="text-sm text-green-600 font-medium">{{ $newValue }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                    Sem alterações detectadas
                                                </span>
                                                @endif
                                                @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Dados não disponíveis
                                                </span>
                                                @endif
                                            </x-tables.cell>
                                        </x-tables.row>
                                        @endforeach
                                    </x-tables.body>
                                </x-tables.table>
                            </div>

                            {{-- Paginação --}}
                            @if($activityLogs->hasPages())
                            <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Exibindo {{ $activityLogs->firstItem() ?? 0 }} até {{ $activityLogs->lastItem() ?? 0 }} de {{ $activityLogs->total() }} registros
                                    </div>
                                    <div class="pagination-wrapper">
                                        {{ $activityLogs->links('custom.pagination') }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @elseif($activityLogs->total() == 0)
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-8 text-center border border-gray-200">
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma alteração registrada</h3>
                            <p class="text-gray-500">Este usuário ainda não possui histórico de alterações.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

    @push('scripts')
    <script>
        function toggleChanges(id) {
                const element = document.getElementById(id);
                if (element.classList.contains('hidden')) {
                    element.classList.remove('hidden');
                } else {
                    element.classList.add('hidden');
                }
            }
    </script>
    @endpush
</x-app-layout>
