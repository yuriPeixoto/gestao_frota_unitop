<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informações do Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Atualize suas informações de perfil e endereço de email.") }}
        </p>
    </header>

    <!-- Seção do Avatar - MOVIDA PARA ANTES DO FORMULÁRIO PRINCIPAL -->
    <div class="mt-6 mb-6">
        <div class="flex items-center gap-4">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                class="w-20 h-20 rounded-full object-cover">

            <div class="flex flex-col gap-2">
                <form action="{{ route('profile.avatar.update') }}" method="post" enctype="multipart/form-data"
                    class="inline">
                    @csrf

                    <input type="file" id="avatar" name="avatar" class="hidden" accept="image/*">

                    <div class="flex gap-2">
                        <label for="avatar"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer"
                            style="color: white !important;">
                            {{ __('Selecionar Nova Foto') }}
                        </label>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            {{ __('Enviar') }}
                        </button>
                    </div>
                </form>

                @if(auth()->user()->avatar)
                <form action="{{ route('profile.avatar.delete') }}" method="post" class="inline">
                    @csrf
                    @method('delete')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-900">
                        {{ __('Remover Foto') }}
                    </button>
                </form>
                @endif

                @if (session('status') === 'avatar-updated')
                <p class="text-sm text-green-600">
                    {{ __('Foto atualizada com sucesso.') }}
                </p>
                @endif

                @if (session('error'))
                <p class="text-sm text-red-600">
                    {{ session('error') }}
                </p>
                @endif
            </div>
        </div>

        @if ($errors->has('avatar'))
        <div class="mt-2 text-sm text-red-600">
            {{ $errors->first('avatar') }}
        </div>
        @endif
    </div>

    <!-- Formulário Principal de Atualização -->
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600">{{ __('Salvo.') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
    document.getElementById('avatar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
