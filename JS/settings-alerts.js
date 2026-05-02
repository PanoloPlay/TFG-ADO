document.addEventListener('DOMContentLoaded', function() {
    // Buscamos todas las alertas personalizadas
    const alerts = document.querySelectorAll('.alert-custom');

    alerts.forEach(alert => {
        // Configuramos un temporizador de 5000 milisegundos (5 segundos)
        setTimeout(() => {
            // Añadimos una transición suave
            alert.style.transition = "opacity 0.5s ease, transform 0.5s ease";
            alert.style.opacity = "0";
            alert.style.transform = "translateY(-10px)";

            // Después de la transición (500ms), eliminamos el elemento del mapa real
            setTimeout(() => {
                alert.remove();
            }, 500);
            
        }, 50000); 
    });
});