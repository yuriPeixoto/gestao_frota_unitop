<script>
    function getInfoCalibragem(modeloCalibragem) {
        const veiculo = document.getElementById('id_veiculo');
        const dataInicial = document.getElementById('data_inclusao');
        const dataFinal = document.getElementById('data_final');
        const usuario = document.getElementById('id_user_calibragem');
        const filial = document.getElementById('id_filial');

        if (!modeloCalibragem) {
            console.log('Nenhuma calibragem selecionada');
            return;
        }

        const headers = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        fetch(`/admin/calibragem/info/${modeloCalibragem}`, {
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

                    const setValue = (element, value, defaultValue = '') => {
                        if (element) {
                            element.value = value ?? defaultValue;
                        }
                    };

                    setValue(veiculo, data.veiculo?.id);
                    setValue(dataInicial, data.data_inclusao);
                    setValue(dataFinal, data.data_final);
                    setValue(usuario, data.usuario?.id);
                    setValue(filial, data.filial?.id);
                } else {
                    console.log('Informações não encontradas');
                }
            })
            .catch(err => {
                console.error('Erro ao buscar dados da calibragem:', err);
            });
    }
</script>