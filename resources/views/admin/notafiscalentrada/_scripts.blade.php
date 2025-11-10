{{-- exclusão --}}
<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir a nota fiscal de entrada?')) {
            excluirRegistro(id);
        }
    }

    function excluirRegistro(id) {
        const url = "{{ route('admin.notafiscalentrada.destroy', ':id') }}".replace(':id', id);

        fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Nota Fiscal de Entrada excluída com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Nota Fiscal de Entrada');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Nota Fiscal de Entrada');
            });
    }
</script>

{{-- botão atualizar estoque --}}
<script>
    let currentConfirmationData = null;

    async function refreshEstoque(id) {
        try {
            showLoading(true);

            const response = await fetch('/admin/notafiscalentrada/atualizaEstoque', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    idNofiscalEntrada: id
                })
            });

            const data = await response.json();
            showLoading(false);

            if (data.needs_confirmation) {
                showConfirmationModal(data.message, data.action, data.data);
            } else if (data.success) {
                showSuccessMessage(data.message, data.idNotaFiscalEntrada);
            } else {
                showErrorMessage(data.message);
                if (data.error) {
                    console.warn('Erro do servidor:', data.error);
                }
            }
        } catch (error) {
            showLoading(false);
            console.error('Erro na requisição:', error);
            showErrorMessage('Erro ao atualizar estoque. Tente novamente.');
        }
    }

    function showConfirmationModal(message, action, data) {
        currentConfirmationData = {
            action,
            data
        };

        const modalMessage = document.getElementById('modal-message');
        const modal = document.getElementById('confirmation-modal');

        if (!modalMessage || !modal) {
            console.error('Elementos do modal não encontrados');
            return;
        }

        // Adicionar informação de progresso se for confirmação de pneu
        let displayMessage = message;
        if (action === 'conferir_pneu' && data.total_pneus) {
            const progresso = data.indice_atual + 1;
            displayMessage = `(${progresso}/${data.total_pneus}) ${message}`;
        }

        modalMessage.textContent = displayMessage;
        modal.classList.remove('hidden');
    }

    function hideConfirmationModal() {
        const modal = document.getElementById('confirmation-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    async function handleUserChoice(confirmed) {
        if (!currentConfirmationData) {
            console.error('currentConfirmationData é null');
            showErrorMessage('Erro: dados de confirmação não encontrados');
            return;
        }

        if (!currentConfirmationData.action) {
            console.error('action não encontrada em currentConfirmationData');
            showErrorMessage('Erro: ação não identificada');
            return;
        }

        const confirmationData = currentConfirmationData;

        try {
            hideConfirmationModal();
            showLoading(true);

            const response = await fetch('/admin/notafiscalentrada/handleConfirmation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    confirmed: confirmed,
                    action: confirmationData.action,
                    data: confirmationData.data
                })
            });

            const data = await response.json();
            showLoading(false);
            currentConfirmationData = null;

            if (data.needs_confirmation) {
                // Há outra confirmação pendente (próximo pneu)
                showConfirmationModal(data.message, data.action, data.data);
            } else if (data.success) {
                showSuccessMessage(data.message, data.idNotaFiscalEntrada);
            } else {
                showErrorMessage(data.message);
            }

        } catch (error) {
            showLoading(false);
            currentConfirmationData = null;
            console.error('Erro ao processar confirmação:', error);
            showErrorMessage('Erro de conexão. Tente novamente.');
        }
    }

    function showLoading(show) {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            if (show) {
                overlay.classList.remove('hidden');
            } else {
                overlay.classList.add('hidden');
            }
        }
    }

    function showSuccessMessage(message, id) {
        alert('Sucesso: ' + message);
        window.location.href = `/admin/checklistrecebimentofornecedor/${id}`;

    }

    function showErrorMessage(message) {
        alert('Erro: ' + message);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const confirmBtn = document.getElementById('confirm-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const modal = document.getElementById('confirmation-modal');

        if (confirmBtn) {
            confirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                handleUserChoice(true);
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                handleUserChoice(false);
            });
        }

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target.id === 'confirmation-modal') {
                    hideConfirmationModal();
                    currentConfirmationData = null;
                }
            });
        }

    });
</script>

{{-- Função para preencher campos com a nota fiscal --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chaveNFe = document.getElementById('chave_nf_entrada');

        chaveNFe.addEventListener('blur', async function() {
            if (!chaveNFe.value) {
                return;
            }
            try {
                const response = await fetch('/admin/notafiscalentrada/buscarDadosNFe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    },
                    body: JSON.stringify({
                        chaveNFe: chaveNFe.value
                    })
                });

                const data = await response.json();

                if (data.success) {
                    console.log('Dados da NFe:', data.dados);

                    preencherCampos(data.dados);
                } else {
                    if (data.error) {
                        console.warn('Erro do servidor:', data.error);
                    }
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
            }
        });

        function preencherCampos(data) {
            const idFornecedor = document.getElementById('id_fornecedor');
            const nomeEmpresa = document.getElementById('nome_empresa');
            const cnpj = document.getElementById('cnpj');
            const rua = document.getElementById('endereco');
            const bairro = document.getElementById('bairro');
            const numero = document.getElementById('numero');
            const cep = document.getElementById('cep');
            const cod_nota_fiscal = document.getElementById('cod_nota_fiscal');
            const numeroNotaFiscal = document.getElementById('numero_nota_fiscal');
            const naturezaOperacao = document.getElementById('natureza_operacao');
            const dataEmissao = document.getElementById('data_emissao');
            const dataSaida = document.getElementById('data_saida');
            const valorNotaFiscal = document.getElementById('valor_nota_fiscal');
            const valorDescontoNfe = document.getElementById('valor_desconto_nfe');
            const valorFrete = document.getElementById('valor_frete');

            idFornecedor.value = data.id_fornecedor;
            nomeEmpresa.value = data.xnome;
            cnpj.value = formatCNPJ(data.cnpj);
            rua.value = data.xlgr;
            bairro.value = data.xbairro;
            numero.value = data.nro;

            setSmartSelectValue('nome_municipio', data.nome_municipio, {
                createIfNotFound: true,
                tempLabel: data.nome_municipio
            });

            setSmartSelectValue('uf', data.uf, {
                createIfNotFound: true,
                tempLabel: data.uf
            });

            cep.value = formatCEP(data.cep);
            cod_nota_fiscal.value = data.cnf;
            numeroNotaFiscal.value = data.nnf;
            naturezaOperacao.value = data.natop;
            dataEmissao.value = formatDate(data.dhemi);
            dataSaida.value = formatDate(data.dhsaient);
            valorNotaFiscal.value = data.vnf;
            valorDescontoNfe.value = data.vdesc;
            valorFrete.value = data.vfrete;

        }

        function formatCNPJ(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');

            if (cnpj.length !== 14) {
                return 'CNPJ deve ter 14 dígitos';
            }

            return cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }

        function formatCEP(cep) {
            if (!cep) {
                return '';
            }

            cep = cep.toString().replace(/\D/g, '');

            if (cep.length !== 8) {
                return 'CEP deve ter 8 dígitos';
            }

            return cep.replace(/(\d{5})(\d{3})/, '$1-$2');
        }

        function formatDate(data) {
            let date;

            if (typeof data === 'string') {
                date = new Date(data);
            } else if (data instanceof Date) {
                date = data;
            } else if (!data) {
                date = new Date();
            } else {
                date = new Date(data);
            }

            if (isNaN(date.getTime())) {
                return 'Data inválida';
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${year}-${month}-${day} ${hours}:${minutes}`;
        }

    });
</script>
