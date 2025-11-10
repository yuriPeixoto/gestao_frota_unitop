<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/logoM.svg') }}" type="image/svg+xml">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }

        .error-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #1737c7 0%, #181717 100%);
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .error-animation {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .btn-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #1134d2 100%);
            transition: all 0.3s ease;
        }

        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <div class="error-container flex items-center justify-center p-4">
        <div class="error-card max-w-md w-full p-8 rounded-2xl shadow-2xl text-center">
            <!-- Logo -->
            <div class="mb-6">
                <img src="{{ asset('images/logo.png') }}"
                     alt="{{ config('app.name') }}"
                     class="mx-auto h-20 w-auto error-animation">
            </div>

            <!-- Error Code -->
            <div class="mb-4">
                <h1 class="text-6xl font-bold text-gray-800 mb-2">@yield('code')</h1>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">@yield('title')</h2>
            </div>

            <!-- Error Message -->
            <div class="mb-8">
                <p class="text-gray-600 leading-relaxed">@yield('message')</p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="btn-dashboard w-full inline-flex items-center justify-center px-6 py-3 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Voltar ao Dashboard
                </a>

                @yield('additional-actions')
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Se o problema persistir, entre em contato com o suporte técnico.
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts opcionais -->
    @yield('scripts')
</body>
</html>
