<div class="space-y-6 bg-white">
    @if ($errors->any())
        <div class="mb-4 bg-red-50 p-4 rounded">
            <ul class="list-disc list-inside text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('notification'))
        <div class="mb-4 bg-red-800 text-white p-4 rounded">
            <strong>{{ session('notification.title') }}</strong>
            <p>{{ session('notification.message') }}</p>
        </div>
    @endif


    <h3 class="text-lg font-medium text-gray-900 mb-4">Pneu</h3>

    @php
        $isCreate = Route::currentRouteName() == 'admin.pneus.create' ? true : false;
    @endphp

    <x-bladewind::tab-group name="tab-icon">
        <x-slot name="headings">
            <x-bladewind::tab-heading name="dados_pneu" icon="truck" active="true" label="Cadastros"
                icon_type="outline" />
            <x-bladewind::tab-heading name="nota_fiscal" icon="truck" label="Nota Fiscal" icon_type="outline" />
        </x-slot>

        <x-bladewind::tab-body>

            <x-bladewind::tab-content name="dados_pneu" active="true">
                @include('admin.pneus._cadastros')
            </x-bladewind::tab-content>

            <x-bladewind::tab-content name="nota_fiscal">
                @include('admin.pneus._nfPneu')
            </x-bladewind::tab-content>
        </x-bladewind::tab-body>
    </x-bladewind::tab-group>

    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">
        <a href="{{ route('admin.pneus.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit" id="submit-form"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            {{ isset($pneus) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>


</div>

@push('scripts')
    <script src="{{ asset('js/hist_pneus.js') }}"></script>
    <script src="{{ asset('js/nf_pneus.js') }}"></script>
    @include('admin.pneus._scripts')
@endpush
