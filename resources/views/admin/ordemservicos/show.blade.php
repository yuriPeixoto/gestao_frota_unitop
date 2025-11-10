<div class="flex items-center justify-between w-full">
    @foreach($steps as $index => $step)
    @php
    // Pega o índice do status atual no array $steps
    $currentIndex = array_search($ordem->statusOrdemServico->situacao_ordem_servico, $steps);

    // Marca como ativo se o índice atual for maior ou igual ao do step
    $isActive = $currentIndex !== false && $currentIndex >= $index;

    // Verifica se está cancelada (normalizando para maiúsculas)
    $isCanceled = strtoupper($ordem->statusOrdemServico->situacao_ordem_servico) === "CANCELADA";
    @endphp

    <div class="flex flex-col items-center w-full relative">
        <div
            class="w-10 h-10 rounded-full flex items-center justify-center
                {{ $isCanceled ? 'bg-red-500 text-white' : ($isActive ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') }}">
            @if($isCanceled)
            ✖
            @elseif($isActive)
            ✔
            @else
            ⏳
            @endif
        </div>
        <span class="text-xs mt-2 text-center">{{ $step }}</span>

        @if($index < count($steps) - 1) <div class="absolute top-5 left-1/2 w-full h-1 
                    {{ $isActive ? 'bg-green-500' : 'bg-gray-300' }}">
    </div>
    @endif
</div>
@endforeach