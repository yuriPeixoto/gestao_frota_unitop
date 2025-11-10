@extends('layouts.checklist')

@section('title', 'Dashboard Checklist')

@push('styles')
<style>
    /* Estilos específicos do dashboard se necessário */
    .checklist-dashboard {
        /* Personalizações específicas aqui */
    }
</style>
@endpush

@push('scripts')
<script>
    // Scripts específicos do dashboard se necessário
    console.log('Dashboard Checklist carregado via Laravel!');
    
    // Debug info
    if (window.ChecklistConfig.debug) {
        console.log('Configurações:', window.ChecklistConfig);
        console.log('Laravel Config:', window.Laravel);
    }
</script>
@endpush