document.addEventListener('DOMContentLoaded', function() {
    const alertContainer = document.getElementById('settingsAlertContainer');

    if (alertContainer && alertContainer.getAttribute('data-alert-visible') === '1') {
        const alertType = alertContainer.getAttribute('data-alert-type') || 'success';
        const alertMessage = alertContainer.getAttribute('data-alert-message') || '';

        if (alertMessage.trim() !== '') {
            const alert = document.createElement('div');
            alert.className = `alert-custom alert-${alertType}`;
            alert.textContent = alertMessage;
            alertContainer.appendChild(alert);
        }
    }

    const alerts = document.querySelectorAll('.alert-custom');

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s ease, transform 0.5s ease";
            alert.style.opacity = "0";
            alert.style.transform = "translateY(-10px)";

            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});