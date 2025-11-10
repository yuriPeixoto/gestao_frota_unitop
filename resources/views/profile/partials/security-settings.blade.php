<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Configurações de Segurança') }}
        </h2>
    </header>

    <div class="bg-white shadow rounded-lg p-4 space-y-4">
        <!-- Último Login -->
        <div>
            <h3 class="text-md font-medium text-gray-700">Último Acesso</h3>
            <p class="text-sm text-gray-600">
                @if($user->last_login_at)
                    {{ $user->last_login_at->format('d/m/Y H:i:s') }}
                    de {{ $user->last_login_ip }}
                @else
                    Nenhum registro de login anterior
                @endif
            </p>
        </div>

        <!-- Status da Senha -->
        <div>
            <h3 class="text-md font-medium text-gray-700">Status da Senha</h3>
            <p class="text-sm text-gray-600">
                @if($user->has_password_updated)
                    Senha já foi alterada desde o cadastro
                    @if($user->password_updated_at && $user->password_updated_at->lt(now()->subMonths(3)))
                        <span class="text-yellow-600">
                            Recomendamos alterar sua senha, pois já se passaram mais de três meses desde a última alteração.
                        </span>
                    @endif
                @else
                    <span class="text-yellow-600">
                        Recomendamos alterar sua senha inicial
                    </span>
                @endif
            </p>
        </div>

        <!-- Sessões Ativas (implementação futura) -->
        {{-- <div>
            <h3 class="text-md font-medium text-gray-700">Sessões Ativas</h3>
            <p class="text-sm text-gray-600">
                Funcionalidade em desenvolvimento
            </p>
        </div> --}}
    </div>
</section>
