<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Motorista') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="button" onclick="abrirModalTransferencia()"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                    Atribuir KM
                </button>
            </div>
        </div>
    </x-slot>
    <div id="modalTransferencia"
        class="hidden fixed inset-0 z-40 bg-black/40 backdrop-blur-md flex items-center justify-center">

        <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 relative">
            <h2 class="text-xl font-semibold mb-4">Distância sem Login</h2>

            <div id="conteudoModalTransferencia" class="overflow-y-auto max-h-[70vh]">
                <p class="text-gray-500">Carregando...</p>
            </div>

            <button onclick="fecharModalTransferencia()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                Fechar
            </button>
        </div>
    </div>
    <script>
        function abrirModalTransferencia(id) {
            const modal = document.getElementById('modalTransferencia');
            const conteudo = document.getElementById('conteudoModalTransferencia');

            modal.classList.remove('hidden');
            conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

                fetch(`/admin/manutencaopremio/modalDistancia`)
                    .then(response => {
                        if (!response.ok) throw new Error('Erro ao carregar dados');
                        return response.text();
                    })
                    .then(html => {
                        conteudo.innerHTML = html;
                    })
                    .catch(() => {
                        conteudo.innerHTML = '<p class="text-red-500">Erro ao carregar a transferência.</p>';
                    });
            }

        function fecharModalTransferencia() {
            const modal = document.getElementById('modalTransferencia');
            modal.classList.add('hidden');
        }
    </script>
    @include('admin.manutencaopremio._form_motorista',[$veiculo->id_distauxiliar])
</x-app-layout>