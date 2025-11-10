@forelse($fornecedores as $fornecedor)
    <div class="select-option cursor-pointer hover:bg-gray-100 px-4 py-2"
         data-value="{{ $fornecedor->id_fornecedor }}"
         data-label="{{ $fornecedor->razao_social }}">
        <div class="flex flex-col">
            <span class="font-medium">{{ $fornecedor->razao_social }}</span>
            <span class="text-sm text-gray-600">CNPJ: {{ $fornecedor->cnpj }}</span>
            @if($fornecedor->nome_fantasia)
                <span class="text-xs text-gray-500">{{ $fornecedor->nome_fantasia }}</span>
            @endif
        </div>
    </div>
@empty
    <div class="px-4 py-2 text-sm text-gray-500">
        Nenhum fornecedor encontrado.
    </div>
@endforelse
