 <div class="results-table">
     <x-tables.table>
         <x-tables.header>
             <x-tables.head-cell>Cód. Serviço X <br> Fornecedor</x-tables.head-cell>
             <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
             <x-tables.head-cell>Data Alteração</x-tables.head-cell>
             <x-tables.head-cell>Serviço</x-tables.head-cell>
             <x-tables.head-cell>Fornecedor</x-tables.head-cell>
             <x-tables.head-cell>Valor Serviço Fornecedor</x-tables.head-cell>
             <x-tables.head-cell>Ações</x-tables.head-cell>
         </x-tables.header>

         <x-tables.body>
             @forelse ($servicoFornecedor as $controle)
                 <x-tables.row>
                     <x-tables.cell>{{ $controle->id_precoservicoxfornecedor }}</x-tables.cell>
                     <x-tables.cell>{{ format_date($controle->data_inclusao) }}</x-tables.cell>
                     <x-tables.cell>{{ format_date($controle->data_alteracao) }}</x-tables.cell>
                     <x-tables.cell nowrap>{{ $controle->servico->descricao_servico }}</x-tables.cell>
                     <x-tables.cell nowrap>{{ $controle->fornecedor->nome_fornecedor }}</x-tables.cell>
                     <x-tables.cell nowrap>R$
                         {{ number_format($controle->valor_servico_fornecedor, 2, ',', '.') }}</x-tables.cell>
                     <x-tables.cell>
                         <div class="flex items-center space-x-2">
                             <a href="{{ route('admin.servicofornecedor.edit', $controle->id_precoservicoxfornecedor) }}"
                                 class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                 <x-icons.pencil class="h-3 w-3" />
                             </a>

                             @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [2, 3, 4, 25]))
                                 <button type="button"
                                     onclick="confirmarExclusao({{ $controle->id_precoservicoxfornecedor }})"
                                     class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                     <x-icons.trash class="h-3 w-3" />
                                 </button>
                             @endif
                         </div>
                     </x-tables.cell>
                 </x-tables.row>
             @empty
                 <x-tables.empty cols="9" message="Nenhum registro encontrado" />
             @endforelse
         </x-tables.body>
     </x-tables.table>
     {{ $servicoFornecedor->links() }}
 </div>
 </div>
