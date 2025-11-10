<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o registro?')) {
            excluirPessoa(id);
        }
    }

    function excluirPessoa(id) {
        fetch(`/admin/pessoas/${id}`, {
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
</script>


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
        document.getElementById(tabName).classList.remove("hidden");
        evt.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
        evt.currentTarget.classList.add("bg-blue-500", "text-white");
    }

    // Mostra a primeira aba por padrão
    document.addEventListener("DOMContentLoaded", () => {
        // Código para abertura da primeira aba
        const firstTabLink = document.querySelector(".tablink");
        if (firstTabLink) {
            firstTabLink.click();
        }
        
        // Formatação e validação do CPF
        const inputCPF = document.getElementById("cpf");
        if (inputCPF) {
            const feedback = document.getElementById("cpf-feedback");
            
            // Função para formatar o CPF
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
            
            // Função para validar o CPF
            function validarCPF(cpf) {
                // Remove caracteres não numéricos
                cpf = cpf.replace(/[^\d]+/g, '');
                
                // Verifica se o CPF tem 11 dígitos ou é uma sequência inválida
                if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                    return false;
                }
                
                // Função auxiliar para calcular dígito verificador
                function calcularDigito(cpf, fator) {
                    let soma = 0;
                    for (let i = 0; i < fator - 1; i++) {
                        soma += parseInt(cpf.charAt(i)) * (fator - i);
                    }
                    const resto = (soma * 10) % 11;
                    return resto === 10 ? 0 : resto;
                }
                
                // Calcula o primeiro e o segundo dígito verificador
                const digito1 = calcularDigito(cpf, 10);
                const digito2 = calcularDigito(cpf, 11);
                
                // Verifica se os dígitos calculados correspondem aos informados
                return digito1 === parseInt(cpf.charAt(9)) && digito2 === parseInt(cpf.charAt(10));
            }
            
            // Evento para validação do CPF no blur
            inputCPF.addEventListener("blur", () => {
                const cpfSemMascara = inputCPF.value.replace(/\D/g, '');
                if (cpfSemMascara === "") {
                    feedback.textContent = "O campo CPF não pode estar vazio.";
                    feedback.className = "text-red-500 text-xs";
                } else if (validarCPF(cpfSemMascara)) {
                    feedback.textContent = "CPF válido!";
                    feedback.className = "text-green-500 text-xs";
                } else {
                    feedback.textContent = "CPF inválido, verifique!";
                    feedback.className = "text-red-500 text-xs";
                }
            });
        }
        
        // Preview da imagem selecionada
        const inputImagem = document.getElementById("imagem_pessoal");
        const previewImagem = document.getElementById("imagem_preview");
        
        if (inputImagem && previewImagem) {
            inputImagem.addEventListener("change", function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImagem.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Busca de CEP e preenchimento automático
        const inputCEP = document.getElementById("cep");
        
        if (inputCEP) {
            // Formatação do CEP
            inputCEP.addEventListener("input", () => {
                let valor = inputCEP.value;
                valor = valor.replace(/\D/g, "");
                
                if (valor.length > 5) {
                    valor = valor.replace(/^(\d{5})(\d)/, "$1-$2");
                }
                
                inputCEP.value = valor;
            });
            
            // Busca de CEP ao sair do campo
            inputCEP.addEventListener("blur", () => {
                const cep = inputCEP.value.replace(/\D/g, "");
                
                if (cep.length === 8) {
                    console.log("Buscando CEP:", cep);
                    // Desabilitar campos enquanto busca
                    document.getElementById('rua').disabled = true;
                    document.getElementById('bairro').disabled = true;
                    document.getElementById('nome_municipio').disabled = true;
                    document.getElementById('id_uf').disabled = true;
                    
                    fetch(`https://viacep.com.br/ws/${cep}/json/`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error("Erro na resposta do ViaCEP");
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log("Resposta ViaCEP:", data);
                            if (!data.erro) {
                                document.getElementById('rua').value = data.logradouro || '';
                                document.getElementById('bairro').value = data.bairro || '';
                                document.getElementById('nome_municipio').value = data.localidade || '';
                                
                                // Selecionar o estado (UF)
                                const selectUf = document.getElementById('id_uf');
                                for (let i = 0; i < selectUf.options.length; i++) {
                                    if (selectUf.options[i].text === data.uf) {
                                        selectUf.selectedIndex = i;
                                        break;
                                    }
                                }
                                
                                // Focar no campo número
                                document.getElementById('numero').focus();
                            } else {
                                alert('CEP não encontrado. Por favor, verifique o número informado.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar CEP:', error);
                            alert('Erro ao buscar CEP. Por favor, tente novamente ou preencha os campos manualmente.');
                        })
                        .finally(() => {
                            // Habilitar os campos novamente
                            document.getElementById('rua').disabled = false;
                            document.getElementById('bairro').disabled = false;
                            document.getElementById('nome_municipio').disabled = false;
                            document.getElementById('id_uf').disabled = false;
                        });
                }
            });
        }
        
        // Máscara para telefones
        const inputTelFixo = document.getElementById('telefone_fixo');
        const inputTelCelular = document.getElementById('telefone_celular');
        const inputTelContato = document.getElementById('telefone_contato');
        
        function aplicarMascaraTelefone(input) {
            input.addEventListener('input', function() {
                let valor = this.value.replace(/\D/g, '');
                
                if (valor.length > 10) {
                    // Celular com 9 dígitos + DDD: (99) 99999-9999
                    valor = valor.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                } else if (valor.length > 6) {
                    // Telefone com 8 dígitos + DDD: (99) 9999-9999
                    valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                } else if (valor.length > 2) {
                    // Apenas DDD: (99)
                    valor = valor.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
                }
                
                this.value = valor;
            });
        }
        
        // Aplicar máscaras aos campos de telefone
        if (inputTelFixo) aplicarMascaraTelefone(inputTelFixo);
        if (inputTelCelular) aplicarMascaraTelefone(inputTelCelular);
        if (inputTelContato) aplicarMascaraTelefone(inputTelContato);
    });
</script>