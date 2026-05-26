document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('library-list');
    const searchInput = document.getElementById('library-search');
    const emptySearchEl = document.getElementById('library-empty-search');

    if (!listEl || !searchInput) return;

    const items = Array.from(listEl.querySelectorAll('.library-item'));

    function normalize(text) {
        return (text || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }


    function filterLibrary() {
        if (!searchInput) return;

        const query = normalize(searchInput.value);
        let visibleCount = 0;

        items.forEach(item => {
            const searchText = normalize(item.dataset.search || item.textContent);
            const match = searchText.includes(query);

            item.hidden = !match;
            if (match) visibleCount++;
        });

        if (emptySearchEl) {
            emptySearchEl.hidden = visibleCount !== 0;
        }
    }

    searchInput.addEventListener('input', filterLibrary);
    filterLibrary();
});