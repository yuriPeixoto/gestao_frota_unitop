<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir o Tipo Borracha')) {
            excluirRegistro(id);
        }
    }

    function excluirRegistro(id) {
        fetch(`/admin/tipoborrachapneus/${id}`, {
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
                    alert('Tipo Borracha excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Tipo Borracha');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Tipo Borracha');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }

    function openDrawer() {
    document.getElementById('drawer').classList.remove('translate-x-full');
    }

    function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    }

    function openDrawerEdit(id) {
        const drawer = document.getElementById('drawerEdit');
        drawer.classList.remove('translate-x-full');

        fetch(`/admin/tipoborrachapneus/${id}/editar`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('drawerEdit-content').innerHTML = html;
            });
    }

    function closeDrawerEdit() {
        document.getElementById('drawerEdit').classList.add('translate-x-full');
    }
</script>