<script>
    let registrosParciais = []; //váriavel que será feito o rascunho

    function salvarParcial(){
        const form = document.getElementById('OrdemServicoForm');
        const formData = new FormData(form);

        const dados = {};
        formData.forEach((value, key) =>{
            dados[key] = value;
        });

        // log do registro atual

        console.log("Campos adicionados neste registro:", dados);

        registrosParciais.push(dados);

        const btnParcial = document.getElementById('btnSalvarParcial');
        btnParcial.innerText = `Salvar Parcial (${registrosParciais.length})`;
        
        
        //form.reset();
        console.log("Registros parciais acumulados:", registrosParciais);
    }

    document.getElementById('OrdemServicoForm').addEventListener('submit', function(e) {
        if(registrosParciais.length > 0){
            e.preventDefault(); //evita envio padrão

            fetch(this.action, {
                method: this.method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify({ ordens: registrosParciais })
            })
            .then(res => {
                console.log(res.status, res.headers.get('content-type'));
                return res.text(); // pegar como texto para debug
            })
            .then(text => console.log("Resposta do servidor:", text))
            .catch(err => console.error(err));
        }
    });
</script>