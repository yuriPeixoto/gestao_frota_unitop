<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Autenticação em 2 fatores') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Adicione segurança extra à sua conta usando autenticação em 2 fatores.') }}
        </p>
    </header>

    <div id="twoFactorAuth">
        @if(!auth()->user()->two_factor_confirmed_at)
            <!-- Botão de habilitar -->
            <div id="enableSection" {!! auth()->user()->two_factor_secret ? 'style="display: none;"' : '' !!}>
                <form method="POST" action="{{ route('two-factor.enable') }}" class="two-factor-form">
                    @csrf
                    <input type="hidden" name="ajax" value="1">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
                        {{ __('Habilitar autenticação em 2 fatores') }}
                    </button>
                </form>
            </div>

            <!-- QR Code e confirmação -->
            <div id="qrSection" {!! auth()->user()->two_factor_secret ? '' : 'style="display: none;"' !!}>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        {{ __('Para completar a configuração, escaneie o QR Code abaixo com seu aplicativo autenticador.') }}
                    </p>
                </div>

                <div class="mb-4">
                    <div class="p-2 bg-white border">
                        {!! auth()->user()->getTwoFactorQrCodeSvg() !!}
                    </div>
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}" class="confirmation-form">
                    @csrf
                    <div class="mb-4">
                        <x-bladewind::input
                            name="code"
                            label="{{ __('Código de verificação') }}"
                            class="mt-1 block w-full"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            required />
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
                        {{ __('Confirmar') }}
                    </button>
                </form>
            </div>
        @else
            <div class="text-green-600 font-medium mb-4">
                {{ __('Autenticação em 2 fatores está ativada.') }}
            </div>

            <div id="recoveryCodesSection">
                <div class="p-4 bg-gray-100 rounded">
                    <h3 class="font-bold mb-2">Códigos de Recuperação</h3>
                    <p class="text-sm text-gray-600 mb-2">Guarde estes códigos em um local seguro. Eles podem ser usados para recuperar acesso à sua conta caso você perca seu dispositivo 2FA.</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                            <div class="font-mono bg-white p-2 rounded border">{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-4 flex space-x-4">
                <!-- Download Recovery Codes -->
                <form method="POST" action="{{ route('two-factor.download-codes') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded transition-colors">
                        {{ __('Baixar códigos de recuperação') }}
                    </button>
                </form>

                <!-- Disable 2FA -->
                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded transition-colors">
                        {{ __('Desabilitar 2FA') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</section>

@push('scripts')
    <script>
        let twoFactorState = {
            enabled: {{ auth()->user()->two_factor_secret ? 'true' : 'false' }},
            confirmed: {{ auth()->user()->two_factor_confirmed_at ? 'true' : 'false' }},
        };

        function updateUI() {
            const enableSection = document.getElementById('enableSection');
            const qrSection = document.getElementById('qrSection');
            const recoverySection = document.getElementById('recoveryCodesSection');

            if (twoFactorState.enabled && !twoFactorState.confirmed) {
                enableSection.style.display = 'none';
                qrSection.style.display = 'block';
                if (recoverySection) recoverySection.style.display = 'none';
            } else if (twoFactorState.enabled && twoFactorState.confirmed) {
                enableSection.style.display = 'none';
                qrSection.style.display = 'none';
                if (recoverySection) recoverySection.style.display = 'block';
            } else {
                enableSection.style.display = 'block';
                qrSection.style.display = 'none';
                if (recoverySection) recoverySection.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.two-factor-form');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        location.reload();
                    }
                } catch (error) {
                    console.error(error);
                }
            });

            updateUI();
        });
    </script>
@endpush

