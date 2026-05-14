document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('wishlist-list');
    const searchInput = document.getElementById('wishlist-search');
    const emptySearchEl = document.getElementById('wishlist-empty-search');

    if (!listEl) return;

    const currentOrder = new URLSearchParams(window.location.search).get('orden') || 'usuario';
    const canReorder = currentOrder === 'usuario';
    const saveUrl = listEl.getAttribute('data-save-url');
    const items = Array.from(listEl.querySelectorAll('.wishlist-item'));

    function normalize(text) {
        return (text || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function filterWishlist() {
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

    function updateOrderIndexes() {
        const visibleItems = Array.from(listEl.querySelectorAll('.wishlist-item:not([hidden])'));
        visibleItems.forEach((item, index) => {
            const input = item.querySelector('.wishlist-rank-input');
            if (input) {
                input.value = index + 1;
                input.setAttribute('data-original-value', index + 1);
            }
        });
    }

    function handleManualRankChange(inputEl) {
        let newRank = parseInt(inputEl.value, 10);
        const originalRank = parseInt(inputEl.getAttribute('data-original-value'), 10);
        const itemsVisible = Array.from(listEl.querySelectorAll('.wishlist-item:not([hidden])'));

        if (isNaN(newRank) || newRank < 1) newRank = 1;
        if (newRank > itemsVisible.length) newRank = itemsVisible.length;

        if (newRank !== originalRank) {
            const itemToMove = inputEl.closest('.wishlist-item');
            const targetIndex = newRank - 1;

            if (targetIndex >= itemsVisible.length - 1) {
                listEl.appendChild(itemToMove);
            } else if (newRank < originalRank) {
                listEl.insertBefore(itemToMove, itemsVisible[targetIndex]);
            } else {
                listEl.insertBefore(itemToMove, itemsVisible[targetIndex + 1]);
            }
        }
    }

    function saveOrder(url) {
        const itemsVisible = Array.from(listEl.querySelectorAll('.wishlist-item'));
        const orderData = itemsVisible.map(item => item.getAttribute('data-wishlist-id'));

        const formData = new FormData();
        formData.append('action', 'reorder');
        orderData.forEach(id => formData.append('order[]', id));

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.ok) {
                console.error('Error al guardar el orden');
            }
        })
        .catch(err => console.error('Error de red:', err));
    }

    if (canReorder && listEl.querySelector('.wishlist-handle')) {
        new Sortable(listEl, {
            handle: '.wishlist-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function () {
                updateOrderIndexes();
                saveOrder(saveUrl);
            }
        });

        const rankInputs = listEl.querySelectorAll('.wishlist-rank-input');
        rankInputs.forEach(input => {
            input.addEventListener('change', function () {
                handleManualRankChange(this);
                updateOrderIndexes();
                saveOrder(saveUrl);
            });
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            filterWishlist();
            if (canReorder) updateOrderIndexes();
        });
    }

    filterWishlist();
});