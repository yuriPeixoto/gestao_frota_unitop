@extends('errors.layout')

@section('code', '403')
@section('title', 'Acesso Negado')

@section('message')
    Você não tem permissão para acessar esta funcionalidade do sistema. 
    Entre em contato com seu administrador para solicitar as permissões necessárias.
@endsection

@section('additional-actions')
    <button onclick="history.back()" 
            class="w-full inline-flex items-center justify-center px-6 py-3 text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Voltar à Página Anterior
    </button>
@endsection

@section('scripts')
<script>
    // Log do erro para auditoria
    console.log('403 - Acesso negado registrado:', {
        url: window.location.href,
        timestamp: new Date().toISOString(),
        user_agent: navigator.userAgent
    });
</script>
@endsection