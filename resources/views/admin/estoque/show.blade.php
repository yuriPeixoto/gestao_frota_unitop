<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Estoque Vinculado #{{ $estoque->id_estoque }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.estoque.dashboard') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-white min-h-screen">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Mensagens de Sucesso/Erro -->
            @if (session('success'))
            <div class="mb-4 border-l-4 border-green-500 bg-green-100 p-4 text-green-700" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 border-l-4 border-red-500 bg-red-100 p-4 text-red-700" role="alert">
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                <div class="border-b border-gray-200 bg-white p-6 rounded-lg">
                    <div class="space-y-6">

                        <!-- Informações principais em 2 colunas -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Coluna 1 -->
                            <div
                                class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6 rounded-t-lg">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações do estoque
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Código Estoque
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $estoque->id_estoque }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Descrição:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $estoque->descricao_estoque ?? '-' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{$itens->count()}} item(ns)
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Coluna 2 -->
                            <div
                                class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6 rounded-t-lg">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações Adicionais
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $estoque->filial->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Data Inclusão
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $itens->first() ? format_date($itens->first()->data_inclusao) : 'Não
                                                definida' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Quantidade de itens
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $itens->count() }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Produtos Solicitados -->
                        <div
                            class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between bg-gray-50 px-4 py-5 sm:px-6 rounded-t-lg">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Produtos Solicitados
                                </h3>
                                <span class="text-sm text-gray-500">{{$itens->count()}} item(ns)</span>
                            </div>
                            <div class="border-t border-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Código</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Descrição</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Quantidade Atual</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Quantidade Mínima</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Quantidade Máxima</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @forelse($itens as $item)
                                            <tr>
                                                <td
                                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                    {{ $item->id_produto ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">{{
                                                    $item->produto->descricao_produto ?? 'Produto não encontrado' }}
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{
                                                    number_format($item->quantidade_atual, 2, ',', '.') }}</td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{
                                                    number_format($item->quantidade_minima, 2, ',', '.') }}</td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{
                                                    number_format($item->quantidade_maxima, 2, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Nenhum produto encontrado.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>


</x-app-layout>