<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Respostas Checklist') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Respostas Checklist"
                    content="Nesta tela você pode visualizar todos os detalhes da Resposta do Checklist." />
            </div>
        </div>
    </x-slot>

    <div class=" rounded-md shadow-sm bg-white py-10 sm:px-6 lg:px-8">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dados do Usuário
        </h2>

        

        <div class="text-black">
            <span class="font-semibold">Nome:</span>
            <span>{{ $user[0]->name ?? null}}</span>
        </div>
        <div class="text-black">
            <span class="font-semibold">E-mail:</span>
            <span>{{ $user[0]->email }}</span>
        </div>
       
        <div class="text-black">
            <span class="font-semibold">Departamento:</span>
            <span>{{ $user[0]->departamento }}</span>
        </div>

        <hr/>

        <div class="flex gap-24 w-full">

            <div class="flex w-6/12 gap-10 mt-10">
                <div class="w-full text-black">
                    <h1 class="font-semibold text-xl text-gray-800 leading-tight">Checklist início</h1>
                    <hr class="mb-4"/>
                    @foreach ($resposta1[0]->checklistRespostas as $item)
                        <div class="flex shadow-lg rounded-md h-62 gap-10 p-8 h-72 mb-6">
                            <span>{{$item->colunaChecklist}}:</span>
                            <div class="flex flex-col items-center">
                                @if ($item->foto)
                                <img class="w-40 rounded-md" src="{{ asset('storage/'.$item->foto) }}" alt="Foto">
                                @endif
                                <div class="bg-white">
                                    @if ($item->assinatura)
                                    <img class="w-40 rounded-md" src="{{ asset('storage/'.$item->assinatura) }}" alt="Foto">
                                @endif
                                </div>
                                <span>
                                    {{$item->simOuNao}}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex w-6/12 gap-10 mt-10">
                <div class="w-full text-black">
                    <h1 class="font-semibold text-xl text-gray-800 leading-tight">Checklist Fim</h1>
                    <hr class="mb-4"/>
                    @foreach ($resposta2[0]->checklistRespostas as $item)
                        <div class="flex shadow-lg rounded-md h-72 gap-10 h-62 p-8 mb-6">
                            <span>{{$item->colunaChecklist}}:</span>
                            <div class="flex flex-col items-center">
                                @if ($item->foto)
                                <img class="w-40 rounded-md" src="{{ asset('storage/'.$item->foto) }}" alt="Foto">
                                @endif
                                <div class="bg-white">
                                    @if ($item->assinatura)
                                    <img class="w-40 rounded-md" src="{{ asset('storage/'.$item->assinatura) }}" alt="Foto">
                                @endif
                                </div>
                                <span>
                                    {{$item->simOuNao}}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        {{-- @if($resposta1[0]->status == 'pendente') --}}
            <div class="w-full flex gap-2 items-center justify-end">
                <a href="{{route('admin.checklistResposta.index')}}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 rounded-md border border-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>                  
                    Voltar
                </a>
                <form action="{{ route('admin.checklistResposta.update', $resposta1[0]->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="status" value="reprovado">
                    <button href="" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-400  rounded-md border border-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        
                        Reprovar
                    </button>
                </form>
                <form action="{{ route('admin.checklistResposta.update', $resposta1[0]->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="status" value="aprovado">
                    <button href="" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-500 hover:bg-green-400  rounded-md border border-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Aprovar
                    </button>
                </form>
            </div>
        {{-- @endif --}}
    </div>
    
</x-app-layout>