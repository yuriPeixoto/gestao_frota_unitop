<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Por favor, insira o código de autenticação fornecido pelo seu aplicativo autenticador.') }}
    </div>

    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf
        <div class="mt-4">
            <x-bladewind::input
                name="code"
                label="{{ __('Código') }}"
                type="text"
                inputmode="numeric"
                autofocus
                required />
        </div>

        <div class="mt-4">
            <x-primary-button>
                {{ __('Confirmar') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-sm text-gray-600">
        <a href="{{ route('two-factor.recovery-codes') }}" class="text-blue-500 hover:text-blue-700">
            {{ __('Usar código de recuperação') }}
        </a>
    </div>
</x-guest-layout>
