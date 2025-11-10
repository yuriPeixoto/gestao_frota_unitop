<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o registro?')) {
            excluirFornecedor(id);
        }
    }

    function excluirFornecedor(id) {
        fetch(`/admin/fornecedores/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('O registro foi excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir registro');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir o registro');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }
</script>

{{-- TABS --}}
<script>
    function openTab(evt, tabName) {
        // Esconde todos os conteúdos das abas
        const tabcontents = document.querySelectorAll(".tabcontent");
        tabcontents.forEach((tab) => {
            tab.classList.add("hidden");
        });

        // Remove a classe "active" de todos os botões
        const tablinks = document.querySelectorAll(".tablink");
        tablinks.forEach((link) => {
            link.classList.remove("bg-blue-500", "text-white");
            link.classList.add("bg-gray-200", "text-gray-700");
        });

        // Mostra o conteúdo da aba atual e adiciona a classe "active" ao botão
        const tabContent = document.getElementById(tabName);
        if (tabContent) {
            tabContent.classList.remove("hidden");
        }
        
        if (evt.currentTarget) {
            evt.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
            evt.currentTarget.classList.add("bg-blue-500", "text-white");
        }
    }

    // Mostra a primeira aba por padrão - versão segura
    document.addEventListener("DOMContentLoaded", () => {
        const firstTabLink = document.querySelector(".tablink");
        if (firstTabLink) {
            // Cria um evento de clique artificial
            const event = new MouseEvent('click', {
                view: window,
                bubbles: true,
                cancelable: true
            });
            firstTabLink.dispatchEvent(event);
        } else {
            console.error("Nenhuma aba encontrada - verifique elementos com classe .tablink");
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputs = document.querySelectorAll('input[name="is_juridico"]');

        inputs.forEach(input => {
            input.addEventListener('click', ({
                target
            }) => {
                const label = document.getElementById('label_campo_cpf_cnpj');
                const input = document.getElementById('campo_cpf_cnpj');
                if (target.value === '1') {
                    label.textContent = 'CNPJ';
                    input.name = 'cnpj_fornecedor';
                } else {
                    label.textContent = 'CPF';
                    input.name = 'cpf_fornecedor';
                }
            });
        });
    });
</script>

{{-- Mascaras para telefones --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const aplicarMascara = (input) => {
            input.addEventListener("input", () => {
                let valor = input.value.replace(/\D/g, ""); // Remove caracteres não numéricos

                // Aplica máscara baseada no tamanho do número
                valor = valor.length > 10 ?
                    valor.replace(/(\d{2})(\d)(\d{4})(\d{4})/,
                        "($1) $2 $3-$4") // Celular (XX) 9XXXX-XXXX
                    :
                    valor.replace(/(\d{2})(\d{4})(\d{4})/, "($1) $2-$3"); // Fixo (XX) XXXX-XXXX

                input.value = valor;
            });
        };

        ["telefone_fixo", "telefone_celular", "telefone_contato"].forEach(id => {
            const input = document.getElementById(id);
            if (input) aplicarMascara(input);
        });
    });
</script>

{{-- api para preencher campo CNPJ --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const radioButtons = document.querySelectorAll('input[name="is_juridico"]');

        radioButtons.forEach(input => {
            function handleblur(event) {
                const cnpj = event.target.value.replace(/\D/g, ''); // Remove non-numeric characters
                if (event.target.name === 'cnpj_fornecedor') {
                    if (cnpj && /^\d{14}$/.test(cnpj)) {
                        // Faz a requisição para a API
                        fetch(`https://open.cnpja.com/office/${cnpj}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Erro na API');
                                }
                                return response.json();
                            })
                            .then(data => {
                                // Preenchendo outro campo com o retorno da API
                                //document.getElementById('nome_fornecedor').value = data.razao_social || '';
                                document.getElementById('nome_fornecedor').value = data.company
                                    .name || '';
                                // document.getElementById('apelido_fornecedor').value = data.nome_fantasia || '';
                                document.getElementById('apelido_fornecedor').value = data.alias ||
                                    '';
                                document.getElementById('email').value = data.emails[0].address ||
                                    '';
                                document.getElementById('site').value = data.emails[0].domain || '';

                                data.registrations.forEach((registro) => {
                                    if (registro.enabled) {
                                        document.getElementById('inscricao_estadual')
                                            .value = registro.number || '';
                                    }
                                });

                                // document.getElementById('rua').value = data.logradouro || '';
                                document.getElementById('rua').value = data.address.street || '';
                                document.getElementById('numero').value = data.address.number || '';
                                //document.getElementById('complemento').value = data.complemento || '';
                                document.getElementById('complemento').value = data.address
                                    .details || '';
                                // document.getElementById('bairro').value = data.bairro || '';
                                document.getElementById('bairro').value = data.address.district ||
                                    '';
                                // document.getElementById('nome_municipio').value = data.municipio || '';
                                document.getElementById('nome_municipio').value = data.address
                                    .city || '';
                                // document.getElementById('cep').value = data.cep || '';
                                document.getElementById('cep').value = data.address.zip || '';
                                // document.getElementById('id_uf').value = data.uf || '';
                                document.getElementById('id_uf').value = data.address.state || '';

                                const telefone =
                                    `(${data.phones[0].area}) ${data.phones[0].number.slice(0, 4)}-${data.phones[0].number.slice(4)}`;
                                if (data.phones[0].type === 'LANDLINE') {

                                    document.getElementById('telefone_fixo').value = telefone || '';
                                } else {
                                    document.getElementById('telefone_celular').value = telefone ||
                                        '';
                                }
                            })
                            .catch(error => {
                                console.error('Erro ao buscar dados do CNPJ:', error);
                            });
                    } else {
                        alert('Por favor, insira um CNPJ válido com 14 dígitos.');
                    }
                } else {
                    const CEP = event.target.value.replace(/\D/g, ''); // Remove non-numeric characters

                    if (event.target.name === 'cep') {
                        if (CEP && /^\d{8}$/.test(CEP)) {
                            // Faz a requisição para a API
                            fetch(`https://brasilapi.com.br/api/cep/v1/${CEP}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Erro na API');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    // Preenchendo outro campo com o retorno da API
                                    document.getElementById('rua').value = data.street || '';
                                    document.getElementById('bairro').value = data.neighborhood ||
                                        '';
                                    document.getElementById('nome_municipio').value = data.city ||
                                        '';
                                    document.getElementById('id_uf').value = data.state || '';
                                })
                                .catch(error => {
                                    console.error('Erro ao buscar dados do CEP:', error);
                                });
                        } else {
                            alert('Por favor, insira um CEP válido com 8 dígitos.');
                        }
                    }
                }
            }

            input.addEventListener('click', (event) => {
                const campo = document.querySelector('[for="cpf_cnpj"]');
                const input = document.querySelector('#campo_cpf_cnpj');
                if (event.target.value === '1') {
                    const inputCNPJ = document.getElementById("campo_cpf_cnpj");

                    inputCNPJ.addEventListener("input", () => {
                        let valor = inputCNPJ.value;

                        // Remove todos os caracteres que não sejam números
                        valor = valor.replace(/\D/g, "");

                        // Aplica a máscara no formato 00.000.000/0000-00
                        valor = valor.replace(/(\d{2})(\d)/, "$1.$2");
                        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
                        valor = valor.replace(/(\d{3})(\d{4})/, "$1/$2");
                        valor = valor.replace(/(\d{4})(\d{2})$/, "$1-$2");

                        // Atualiza o valor do campo com a máscara
                        inputCNPJ.value = valor;
                    });

                    //Chama função para buscar dados do CNPJ
                    document.querySelector('#campo_cpf_cnpj').addEventListener('blur',
                        handleblur);

                    //limpa campos
                    const ids = ['campo_cpf_cnpj', 'nome_fornecedor', 'apelido_fornecedor',
                        'rua',
                        'complemento', 'bairro', 'cep', 'id_uf', 'telefone_fixo',
                        'telefone_celular',
                        'email', 'site', 'inscricao_estadual', 'numero', 'nome_municipio'
                    ];

                    ids.forEach(id => {
                        document.getElementById(id).value = '';
                    });
                } else {
                    var element = document.getElementById('campo_cpf_cnpj');
                    const inputCPF = document.getElementById("campo_cpf_cnpj");

                    inputCPF.addEventListener("input", () => {
                        let valor = inputCPF.value;

                        // Remove todos os caracteres que não sejam números
                        valor = valor.replace(/\D/g, "");

                        // Aplica a máscara no formato 000.000.000-00
                        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
                        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
                        valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

                        // Atualiza o valor do campo com a máscara
                        inputCPF.value = valor;
                    });
                    //Chama função para buscar dados do CEP
                    document.getElementById('cep').addEventListener('blur', handleblur);

                    //limpa campos
                    const ids = ['campo_cpf_cnpj', 'nome_fornecedor', 'apelido_fornecedor',
                        'rua',
                        'complemento', 'bairro', 'cep', 'id_uf', 'telefone_fixo',
                        'telefone_celular',
                        'email', 'site', 'inscricao_estadual', 'numero', 'nome_municipio'
                    ];

                    ids.forEach(id => {
                        document.getElementById(id).value = '';
                    });
                }
            });
        });
    });
</script>