document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('bioMoreBtn');
    const modal = document.getElementById('bioModal');
    const closeBtn = document.getElementById('bioModalClose');
    const modalText = document.getElementById('bioModalText');

    if (!btn || !modal || !closeBtn || !modalText) return;

    btn.addEventListener('click', () => {
        modalText.textContent = btn.dataset.fullDescription || '';
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        }
    });
});