document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('validation-client');

    button.addEventListener('click', async function (e) {

        const clientIdInput = document.querySelector('input[name="client_id"]');
        const clientId = clientIdInput ? clientIdInput.value : null;

        if (!clientId) {
            Swal.fire({
                title: 'Missing Client ID',
                text: 'Please enter a client ID before validating.',
                icon: 'warning',
                customClass: {
                    confirmButton: 'btn btn-warning'
                }
            });
            return;
        }

        this.disabled = true;
        this.classList.add('disabled');

        try {
            const response = await fetch(`${window.baseUrlApiAdmin}/services/validation?client_id=${clientId}`, {
                method: 'GET',
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Unexpected error occurred.');
            }

            const result = await response.json();

            Swal.fire({
                icon: 'success',
                title: `Client Validated`,
                text: `${result.data.name} has been validated successfully.`,
                customClass: {
                    confirmButton: 'btn btn-success'
                }
            });

        } catch (err) {
            Swal.fire({
                title: 'Validation Failed',
                text: err.message ?? 'An error occurred during validation.',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-danger'
                }
            });
        } finally {
            this.disabled = false;
            this.classList.remove('disabled');
        }
    });
});
