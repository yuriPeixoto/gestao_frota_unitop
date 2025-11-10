@extends('errors.layout')

@section('code', '419')
@section('title', 'Sessão Expirada')

@section('message')
    Sua sessão expirou por motivos de segurança. 
    Isso acontece quando você fica inativo por muito tempo ou quando há problemas de conectividade.
@endsection

@section('additional-actions')
    <button onclick="window.location.reload()" 
            class="w-full inline-flex items-center justify-center px-6 py-3 text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Recarregar Página
    </button>
@endsection

@section('scripts')
<script>
    // Auto-reload após alguns segundos para renovar o token CSRF
    setTimeout(() => {
        window.location.reload();
    }, 5000);
</script>
@endsection
