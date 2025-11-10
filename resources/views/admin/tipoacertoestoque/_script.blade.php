<script>
    function openDrawer() {
    document.getElementById('drawer').classList.remove('translate-x-full');
    }

    function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    }

    function openDrawerEdit(id) {
        const drawer = document.getElementById('drawerEdit');
        drawer.classList.remove('translate-x-full');

        fetch(`/admin/tipoacertoestoque/${id}/editar`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('drawerEdit-content').innerHTML = html;
            });
    }

    function closeDrawerEdit() {
        document.getElementById('drawerEdit').classList.add('translate-x-full');
    }

</script>