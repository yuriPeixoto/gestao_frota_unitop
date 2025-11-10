<script>
    // Auto-hide messages after 5 seconds
    setTimeout(function() {
        const successMsg = document.getElementById('success-message');
        const errorMsg = document.getElementById('error-message');
        if (successMsg) successMsg.style.display = 'none';
        if (errorMsg) errorMsg.style.display = 'none';
    }, 5000);
</script>