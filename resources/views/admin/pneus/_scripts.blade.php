<script>
    function getInfoPneu(modeloPneu) {
        desenho = document.getElementById('id_desenho_pneu');
        dimensao = document.getElementById('id_dimensao_pneu');
        fornecedor = document.getElementById('id_fornecedor');
        kmRodagem = document.getElementById('km_rodagem');
        sulcoNovo = document.getElementById('sulco_pneu_novo');
        sulcoReformado = document.getElementById('sulco_pneu_reformado');
        lonas = document.getElementById('numero_lonas');
        vidaPneu = document.getElementById('id_controle_vida_pneu');

        if (!modeloPneu) {
            console.log('Nada foi encontrado');
            return;
        }

        // Configure os headers corretamente
        const headers = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        fetch(`/admin/pneus/info/${modeloPneu}`, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta da API: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data) {
                    console.log(data);
                    // Função auxiliar para padronizar a atribuição
                    const setValue = (element, value, defaultValue = '') => {
                        element.value = value ?? defaultValue;
                    };

                    // Atribuições simplificadas
                    setValue(desenho, data.desenho_pneu?.descricao_desenho_pneu, 'N/A');
                    setValue(dimensao, data.dimensao_pneu?.descricao_pneu, 'N/A');
                    setValue(fornecedor, data.fornecedor?.nome_fornecedor, 'N/A');

                    // Valores relacionados a vida_pneu (com padrão vazio)
                    const vidaPneuData = data.vida_pneu || {};
                    setValue(kmRodagem, vidaPneuData.km_rodagem);
                    setValue(sulcoNovo, vidaPneuData.sulco_pneu_novo);
                    setValue(sulcoReformado, vidaPneuData.sulco_pneu_reformado);
                    setValue(lonas, vidaPneuData.numero_lonas);
                    setValue(vidaPneu, vidaPneuData.descricao_vida_pneu);
                } else {
                    console.log('Informações não foram encontradas na resposta da API');
                    // Corrigi um bug aqui - os elementos precisam ter seu value atribuído
                    desenho.value = 'N/A';
                    dimensao.value = 'N/A';
                    fornecedor.value = 'N/A';
                }
            })
            .catch(err => {
                console.error('Erro ao buscar modelo do pneu:', err);
                desenho.value = 'Erro ao buscar';
                dimensao.value = 'Erro ao buscar';
                fornecedor.value = 'Erro ao buscar';
            });
    }
</script>
