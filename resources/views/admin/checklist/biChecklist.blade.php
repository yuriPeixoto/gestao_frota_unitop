<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <x-slot name="header">
        <div class="flex gap-8 justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{-- {{ __('Checklist') }} --}}
            </h2>

            <div class="w-full">
                <x-bladewind::select
                    name="ano"
                    error_message="tipo_checklist_id"
                    label="Ano"
                    :data="$anos"  
                />
            </div>

           
            <div class="w-full">
                <x-bladewind::select
                    name="mes"
                    error_message="tipo_checklist_id"
                    label="Mês"
                    :data="$meses"  
                />
            </div>

            <div class="w-full">
                <x-bladewind::select
                    name="tipo_checklist "
                    error_message="tipo_checklist_id"
                    label="Tipo de checklist"
                    :data="$tipoChecklist"  
                />
            </div>

            <x-bladewind::input 
                name="Nome" 
                type="text" 
                label="Nome" 
            />

            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Tipo de Categoria"
                    content="Nesta tela, você pode visualizar os checklists respondidos e as divergências entre eles." />
            </div>
        </div>
    </x-slot>


    <div class="bg-white shadow-sm sm:rounded-lg">
        <div style="width: 80%; margin: auto;">
            <canvas id="checklistChart"></canvas>
        </div>
    </div>
</x-app-layout>

<script>
    const ctx = document.getElementById('checklistChart').getContext('2d');
    const chartData = {!! json_encode($chart->datasets) !!};
    const chartLabels = {!! json_encode($chart->labels) !!};

    new Chart(ctx, {
        type: '{{ $chart->type }}',
        data: {
            labels: chartLabels,
            datasets: chartData
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>