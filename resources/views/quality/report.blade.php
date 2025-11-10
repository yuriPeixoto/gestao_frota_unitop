<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Relatório de Melhorias
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </p>
            </div>
            <a href="{{ route('quality.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar ao Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Filtros de Data --}}
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-semibold text-lg text-gray-900 mb-4">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar Período
                </h3>
                <form method="GET" action="{{ route('quality.report') }}" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
                        <input
                            type="date"
                            name="start_date"
                            value="{{ $startDate }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
                        <input
                            type="date"
                            name="end_date"
                            value="{{ $endDate }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>
                        Filtrar
                    </button>
                </form>
            </div>

            {{-- Estatísticas --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-lightbulb text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total</p>
                            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Aguardando</p>
                            <p class="text-2xl font-bold">{{ $stats['aguardando'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Aprovadas</p>
                            <p class="text-2xl font-bold">{{ $stats['aprovadas'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-times text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Rejeitadas</p>
                            <p class="text-2xl font-bold">{{ $stats['rejeitadas'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-hourglass-half text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Tempo Médio</p>
                            <p class="text-2xl font-bold">{{ round($stats['tempo_medio_revisao'] ?? 0) }}h</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabela de Melhorias --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-lg text-gray-900">
                            <i class="fas fa-list mr-2"></i>
                            Todas as Melhorias ({{ $melhorias->count() }})
                        </h3>
                        <a href="{{ route('quality.report.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                           class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Exportar PDF
                        </a>
                    </div>
                </div>

                @if($melhorias->isEmpty())
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Nenhuma melhoria encontrada</p>
                        <p class="text-gray-400 text-sm mt-2">Tente selecionar outro período</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ticket
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Título
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Solicitante
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Revisor
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data Criação
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data Revisão
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($melhorias as $melhoria)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-sm font-bold text-gray-700">#{{ $melhoria->ticket_number }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $melhoria->subject }}</div>
                                            <div class="text-sm text-gray-500">{{ Illuminate\Support\Str::limit($melhoria->description, 60) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $melhoria->user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $melhoria->status->color() }}-100 text-{{ $melhoria->status->color() }}-800">
                                                <i class="fas fa-{{ $melhoria->status->icon() }} mr-1"></i>
                                                {{ $melhoria->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $melhoria->qualityReviewer->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $melhoria->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $melhoria->quality_reviewed_at ? $melhoria->quality_reviewed_at->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <a href="{{ route('tickets.show', $melhoria) }}" class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
    @endpush
</x-app-layout>