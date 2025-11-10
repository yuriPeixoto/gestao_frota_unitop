<div class="space-y-6 bg-white">
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 p-4">
            <ul class="list-inside list-disc text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('notification'))
        <div class="mb-4 rounded bg-red-800 p-4 text-white">
            <strong>{{ session('notification.title') }}</strong>
            <p>{{ session('notification.message') }}</p>
        </div>
    @endif

    @php
        $isCreate = Route::currentRouteName() == 'transferenciaEntreEstoque.create';
    @endphp

    <x-bladewind::tab-group name="tab-transferencia">
        <x-bladewind::tab-body>
            <x-bladewind::tab-content name="dados_transferencia" active="true">
                @include('admin.transferenciaEntreEstoque._cadastros')

            </x-bladewind::tab-content>
        </x-bladewind::tab-body>
    </x-bladewind::tab-group>

    <!-- Botões -->
    <div class="col-span-full flex justify-end space-x-3">
        <a href="{{ route('admin.transferenciaEntreEstoque.index') }}"
            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
            Cancelar
        </a>

        <button type="submit" id="submit-form" name="action" value="salvar"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
            <span id="submit-label">{{ isset($transferencia) ? 'Finalizar' : 'Salvar' }}</span>
        </button>

    </div>
</div>

@push('scripts')
    @include('admin.transferenciaEntreEstoque._scripts')
@endpush
