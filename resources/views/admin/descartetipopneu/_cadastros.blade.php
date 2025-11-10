<div class="py-6 px-4 sm:px-6 lg:px-8 w-full">
    <div class="w-full">
        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center" id="formCalibragem">

            <x-forms.input type="number" name="id_tipo_descarte" label="Cód."
                value="{{ $descarte->id_tipo_descarte ?? '' }}" readonly />

            <x-forms.input type="text" name="descricao_tipo_descarte" label="Descrição Descarte:"
                value="{{ $descarte->descricao_tipo_descarte ?? '' }}" />

        </div>
    </div>
</div>