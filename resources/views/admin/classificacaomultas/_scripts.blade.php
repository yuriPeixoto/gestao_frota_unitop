@push('scripts')
<script>
    let classificacaomultaId = null;
        

            function editClassificacaoMulta(id) {
                window.location.href = `{{ route('admin.classificacaomultas.edit', ':id') }}`.replace(':id', id)
            }

            function destroyClassificacaoMulta(id, nome) {
                showModal('delete-classificacaomulta');
                classificacaomultaId = id;
                domEl('.bw-delete-classificacaomulta .title').innerText = nome;
            }

            function confirmDeleteClassificacaoMulta() {
                fetch(`{{ route('admin.classificacaomultas.destroy', ':id') }}`.replace(':id', classificacaomultaId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (!response.ok) {
                        return response.text().then(errorText => {
                            console.error('Error response text:', errorText);
                            throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                        });
                    }
                    return response.json();
                }).then(data => {
                    if (data.notification) {
                        showNotification(
                            data.notification.title,
                            data.notification.message,
                            data.notification.type
                        );

                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                }).catch(error => {
                    console.error('Full error:', error);

                    showNotification(
                        'Erro',
                        error.message,
                        'error'
                    );
                });
            }

            @if(session('notification') && is_array(session('notification')))
                showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
            @endif

            function executeSearch() {
            const searchTerm = document.getElementById('search-input').value;
            const currentUrl = new URL(window.location.href);

            if (searchTerm) {
                currentUrl.searchParams.delete('page');

                currentUrl.searchParams.set('search', searchTerm);
            } else {
                currentUrl.searchParams.delete('search');
            }

            window.location.href = currentUrl.toString();
        }

        function clearSearch() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('search');
            window.location.href = currentUrl.toString();
        }

        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                executeSearch();
            }
        });
</script>
@endpush