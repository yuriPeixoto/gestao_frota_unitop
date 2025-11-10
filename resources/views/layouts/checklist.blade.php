<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistema Checklist') - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- React CSS Bundle Inline -->
    <style>
        {
             ! ! file_get_contents(public_path('dashboards/checklist/assets/index-ed1f4689.css')) ! !
        }
    </style>

    <!-- Reset CSS para evitar conflitos -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
        }

        #root {
            min-height: 100vh;
            width: 100%;
        }

        /* Garantir que React assume controle total */
        .react-app {
            position: relative;
            z-index: 1;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Container principal do React -->
    <div id="root" class="react-app">
        <!-- Loading inicial enquanto React não carrega -->
        <div id="loading-initial"
            style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #1A237E; color: white; font-size: 18px;">
            <div style="text-align: center;">
                <div style="margin-bottom: 20px;">
                    <svg style="width: 48px; height: 48px; animation: spin 1s linear infinite;" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                <div>Carregando Sistema Checklist...</div>
                <div style="font-size: 14px; opacity: 0.8; margin-top: 8px;">Aguarde um momento</div>
            </div>
        </div>
    </div>

    <!-- Configurações globais para o React -->
    <script>
        // Configurações que o React pode precisar
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ url('/') }}',
            apiUrl: '{{ url('/api/checklist') }}',
            user: @auth @json(auth()->user()) @else null @endauth
        };
        
        // Configuração específica do checklist
        window.ChecklistConfig = {
            clientId: 'carvalima',
            baseApiUrl: '{{ url('/api/checklist') }}',
            environment: '{{ app()->environment() }}',
            debug: {{ config('app.debug') ? 'true' : 'false' }}
        };
        
        // Remover loading inicial quando React carregar
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const loading = document.getElementById('loading-initial');
                if (loading && document.querySelector('[data-reactroot]')) {
                    loading.style.display = 'none';
                }
            }, 1000);
        });
    </script>

    <!-- React JS Bundle Inline -->
    <script>
        {!! file_get_contents(public_path('dashboards/checklist/assets/index-3b812d7b.js')) !!}
    </script>

    @stack('scripts')

    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</body>

</html>