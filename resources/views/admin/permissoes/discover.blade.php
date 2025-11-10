<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Descobrir Permissões</h2>
                        <div class="space-x-2">
                            <a href="{{ route('admin.permissoes.index') }}"
                               class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                Voltar
                            </a>
                            @if($hasNewPermissions)
                                <form action="{{ route('admin.permissoes.sync') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                        Sincronizar Novas Permissões
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($hasNewPermissions)
                        @foreach($groupedNewPermissions as $groupKey => $permissions)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-4">
                                    {{ ucfirst(str_replace('_', ' ', $groupKey)) }}
                                </h3>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <ul class="space-y-2">
                                        @foreach($permissions as $permission)
                                            <li class="flex items-start space-x-2">
                                                <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                <div>
                                                    <span class="font-medium">{{ $permission['name'] }}</span>
                                                    <p class="text-sm text-gray-600">{{ $permission['description'] }}</p>
                                                    <p class="text-xs text-gray-500">Slug: {{ $permission['slug'] }}</p>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-yellow-800">Não foram encontradas novas permissões para adicionar ao sistema.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
