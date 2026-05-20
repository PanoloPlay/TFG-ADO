document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.sidebar-links a');
    const sections = document.querySelectorAll('.settings-panel .section');
    
    // Contenedor principal para quitarle la clase de bloqueo de animación
    const settingsPanel = document.querySelector('.settings-panel');

    const allowedSections = [
        '#seccion-avatar', 
        '#seccion-datos', 
        '#seccion-privacidad', 
        '#seccion-password',
        '../DEV/settings-game.php'
    ];

    function showSection(targetId) {
        if (!allowedSections.includes(targetId)) {
            targetId = '#seccion-avatar';
        }

        sections.forEach(section => section.classList.remove('active'));
        links.forEach(link => link.classList.remove('active'));

        const targetSection = document.querySelector(targetId);
        if (targetSection) targetSection.classList.add('active');

        const activeLink = document.querySelector(`.sidebar-links a[href="${targetId}"]`);
        if (activeLink) activeLink.classList.add('active');
    }

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (!targetId || !targetId.startsWith('#')) {
                return; // dejar navegar enlaces externos normalmente
            }

            e.preventDefault();
            if (settingsPanel) settingsPanel.classList.remove('no-anim');
            showSection(targetId);
            history.pushState(null, null, targetId);
        });
    });

    const initialHash = window.location.hash || '#seccion-avatar';
    showSection(initialHash);
});