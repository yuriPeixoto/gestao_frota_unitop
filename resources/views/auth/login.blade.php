<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="{{ asset('images/logoM.svg') }}" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full" style="background-size: cover; background-position: center;">
    <video id="background-video" autoplay loop muted playsinline preload="auto"
        style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
        <source src="{{ asset('images/truck.mp4') }}" type="video/mp4">
    </video>

    <div class="min-h-screen flex items-center justify-center p-4">
        <!-- Card de Login -->
        <div class="w-full max-w-md">
            <x-ui.auth-session-status class="mb-4" :status="session('status')" />

            <div class="glass-effect rounded-3xl p-8">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <img class="h-32 w-auto" src="{{ asset('images/logo.png') }}" alt="Logo">
                </div>

                <!-- Título -->
                <h2 class="text-2xl font-semibold text-center text-gray-800 mb-2">Login</h2>
                <p class="text-center text-gray-600 text-sm mb-8">Entre com seus dados de acesso</p>

                <!-- Formulário -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Login (Email ou Matrícula) -->
                    <div>
                        <input id="login" name="login" type="text" required placeholder="Email ou Matrícula"
                            value="{{ old('login') }}" class="input-glass w-full px-4 py-3 rounded-xl text-gray-700 placeholder-gray-500
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 
                                      @error('login') border-red-500 ring-red-500 @enderror">

                        @error('login')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Indicador dinâmico do tipo de login -->
                        <div id="login-indicator" class="mt-1 text-sm hidden"></div>
                    </div>

                    <!-- Senha -->
                    <div>
                        <input id="password" name="password" type="password" required placeholder="Senha" class="input-glass w-full px-4 py-3 rounded-xl text-gray-700 placeholder-gray-500
                                      focus:outline-none focus:ring-2 focus:ring-blue-500
                                      @error('password') border-red-500 ring-red-500 @enderror">

                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Filial -->
                    <div>
                        <div class="relative">
                            <select id="branch_id" name="branch_id" class="input-glass w-full px-4 py-3 rounded-xl text-gray-700
                                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione a Filial</option>
                            </select>
                            <!-- Indicador de carregamento -->
                            <div id="filial-loading" class="absolute right-3 top-3 hidden">
                                <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <p id="filial-message" class="mt-1 text-sm text-gray-600 hidden"></p>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember_me"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 rounded">
                            <label for="remember_me" class="ml-2 text-gray-700">Lembrar-me</label>
                        </div>

                        {{-- @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-700">
                            Esqueceu a senha?
                        </a>
                        @endif --}}
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white rounded-xl py-3 px-4 hover:bg-blue-700
                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                            transition-colors duration-200">
                        Entrar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const video = document.getElementById('background-video');
            const loginInput = document.getElementById('login'); // Campo renomeado de 'email' para 'login'
            const branchSelect = document.getElementById('branch_id');
            const filialLoading = document.getElementById('filial-loading');
            const filialMessage = document.getElementById('filial-message');
            const loginIndicator = document.getElementById('login-indicator'); // Novo elemento para indicar tipo
            let debounceTimer;
            
            // Script para buscar filiais do usuário (email OU matrícula)
            loginInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    const loginValue = loginInput.value.trim();
                    
                    if (loginValue) {
                        const loginType = detectLoginType(loginValue);
                        updateLoginIndicator(loginType, loginValue);
                        
                        if (loginType === 'email' || loginType === 'matricula') {
                            fetchUserFiliais(loginValue, loginType);
                        } else {
                            resetBranchSelect();
                        }
                    } else {
                        resetBranchSelect();
                        resetLoginIndicator();
                    }
                }, 500); // Delay de 500ms para evitar muitas requisições
            });
            
            /**
            * Detecta se o input é email ou matrícula
            * @param {string} value 
            * @returns {string} 'email', 'matricula' ou 'invalid'
            */
            function detectLoginType(value) {
                // Verificar se é email válido
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(value)) {
                    return 'email';
                }
                
                // Verificar se é número (matrícula)
                if (/^\d+$/.test(value) && value.length >= 3) {
                    return 'matricula';
                }
                
                return 'invalid';
            }
            
            /**
            * Atualiza o indicador visual do tipo de login
            * @param {string} type 
            * @param {string} value 
            */
            function updateLoginIndicator(type, value) {
                if (!loginIndicator) return;
                
                switch (type) {
                    case 'email':
                        loginIndicator.innerHTML = '📧 <span class="text-blue-600">Login por email</span>';
                        loginIndicator.classList.remove('hidden');
                        break;
                    case 'matricula':
                        loginIndicator.innerHTML = '🆔 <span class="text-green-600">Login por matrícula</span>';
                        loginIndicator.classList.remove('hidden');
                        break;
                    case 'invalid':
                        if (value.length > 0) {
                            loginIndicator.innerHTML = '⚠️ <span class="text-orange-500">Digite um email válido ou número da matrícula</span>';
                            loginIndicator.classList.remove('hidden');
                        } else {
                            resetLoginIndicator();
                        }
                        break;
                    default:
                        resetLoginIndicator();
                }
            }
            
            /**
            * Remove o indicador visual
            */
            function resetLoginIndicator() {
                if (loginIndicator) {
                    loginIndicator.classList.add('hidden');
                }
            }
            
            /**
            * Função para buscar filiais do usuário (email ou matrícula)
            * @param {string} loginValue 
            * @param {string} loginType 
            */
            function fetchUserFiliais(loginValue, loginType) {
                // Mostrar indicador de carregamento
                filialLoading.classList.remove('hidden');
                branchSelect.disabled = true;
                filialMessage.classList.add('hidden');
                
                // Determinar endpoint baseado no tipo
                let endpoint;
                let paramName;
                
                if (loginType === 'email') {
                    endpoint = '/api/user/filiais-by-email';
                    paramName = 'email';
                } else if (loginType === 'matricula') {
                    endpoint = '/api/user/filiais-by-matricula';
                    paramName = 'matricula';
                }
                
                const url = `${endpoint}?${paramName}=${encodeURIComponent(loginValue)}`;
                
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                })
                .then(response => response.json())
                .then(data => {
                    filialLoading.classList.add('hidden');
                    branchSelect.disabled = false;
                    
                    if (data.success && data.filiais && data.filiais.length > 0) {
                        populateBranchSelect(data.filiais, data.filial_principal_id);
                        
                        // Mostrar nome do usuário se disponível (para matrícula)
                        let message = '';
                        if (data.user_name) {
                            message = `Usuário: ${data.user_name}. `;
                        }
                        
                        if (data.filiais.length > 1) {
                            message += 'Você tem acesso a múltiplas filiais. Selecione a desejada.';
                        } else {
                            message += 'Filial selecionada automaticamente.';
                        }
                        
                        filialMessage.textContent = message;
                        filialMessage.classList.remove('hidden');
                        filialMessage.className = 'mt-1 text-sm text-green-600';
                        
                    } else {
                        resetBranchSelect();
                        
                        let errorMessage = 'Usuário não encontrado no sistema.';
                        if (loginType === 'matricula') {
                            errorMessage = 'Matrícula não encontrada no sistema.';
                        }
                        
                        filialMessage.textContent = errorMessage;
                        filialMessage.classList.remove('hidden');
                        filialMessage.className = 'mt-1 text-sm text-red-600';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar filiais:', error);
                    filialLoading.classList.add('hidden');
                    branchSelect.disabled = false;
                    resetBranchSelect();
                    
                    filialMessage.textContent = 'Erro ao buscar filiais. Tente novamente.';
                    filialMessage.classList.remove('hidden');
                    filialMessage.className = 'mt-1 text-sm text-red-600';
                });
            }
            
            /**
            * Função para preencher o select de filiais
            * @param {Array} filiais 
            * @param {number} filialPrincipalId 
            */
            function populateBranchSelect(filiais, filialPrincipalId) {
                branchSelect.innerHTML = '';
                
                if (filiais.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Nenhuma filial disponível';
                    branchSelect.appendChild(option);
                    return;
                }
                
                filiais.forEach(filial => {
                    const option = document.createElement('option');
                    option.value = filial.id;
                    option.textContent = filial.name;
                    
                    // Marcar a filial principal como selecionada
                    if (filial.id === filialPrincipalId) {
                        option.textContent += ' (Principal)';
                        option.selected = true;
                    }
                    
                    branchSelect.appendChild(option);
                });
            }
            
            /**
            * Função para resetar o select de filiais
            */
            function resetBranchSelect() {
                branchSelect.innerHTML = '<option value="">Selecione a Filial</option>';
                filialMessage.classList.add('hidden');
            }

            // Controle do vídeo de fundo
            video.addEventListener('canplay', () => {
                video.play().catch(e => console.error('Erro ao reproduzir:', e));
            });
            
            video.addEventListener('timeupdate', function() {
                if (this.currentTime >= this.duration - 0.1) {
                    this.pause();
                    this.currentTime = 0;
                    this.play().catch(e => console.error('Erro ao reproduzir:', e));
                }
            });

            // Força reprodução inicial
            if (video.paused) {
                video.play().catch(e => console.error('Erro inicial:', e));
            }
        });
    </script>
</body>

</html>