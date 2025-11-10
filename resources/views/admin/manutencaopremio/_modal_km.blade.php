<form method="POST" action="{{ route('admin.manutencaopremio.update_motorista', $distancia[0]->id_distancia_sem) }}">
    @csrf

    <!-- Adicione este campo hidden para o ID -->
    <input type="hidden" name="id_pedido_compras" value="{{ $distancia[0]->id_pedido_compras }}">

    <div class="flex w-full gap-3">
        <div class="w-full">
            <x-forms.smart-select name="id_motorista" label="Cód:" :options="$motorista" />
        </div>
        <div class="w-full">
            <x-forms.input name="id_veiculo" label="veiculo:" value="{{ $distancia[0]->id_veiculo}}" readonly />
        </div>
        <div class="w-full">
            <x-forms.input name="subcategoria" label="Subcategoria:" value="{{ $distancia[0]->subcategoria}}"
                readonly />
        </div>
    </div>

    <div class="flex w-full gap-3">
        <div class="w-full">
            <x-forms.input name="km_sem_mot" label="Fornecedor" value="{{ $distancia[0]->km_sem_mot }}" />

        </div>

        <div class="w-full">
            <x-forms.input name="media" label="Media:" type="number" required />
        </div>

        <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg">
            Salvar
        </button>
</form>