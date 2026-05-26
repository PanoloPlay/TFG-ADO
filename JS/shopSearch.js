document.addEventListener('DOMContentLoaded', () => {
    const filterInputs = document.querySelectorAll('.js-filter-input');
    const clearButtons = document.querySelectorAll('.js-clear-filter');
    const filterForm = document.getElementById('shopSearchForm');
    const minPriceRange = document.getElementById('minPrice');
    const maxPriceRange = document.getElementById('maxPrice');
    const minPriceLabel = document.getElementById('minPriceLabel');
    const maxPriceLabel = document.getElementById('maxPriceLabel');

    function filterOptionList(input) {
        const targetId = input.dataset.filterTarget;
        const list = targetId ? document.getElementById(targetId) : null;
        if (!list) return;

        const query = input.value.trim().toLowerCase();
        list.querySelectorAll('.js-filter-item').forEach((item) => {
            const text = (item.textContent || '').toLowerCase();
            item.style.display = query === '' || text.includes(query) ? '' : 'none';
        });
    }

    let priceSubmitTimer = null;
    const PRICE_SUBMIT_DELAY = 250;

    function formatPriceLabel(value, isMin, otherValue) {
        const isEmpty = value === '';
        const numericValue = Number(value);
        const otherEmpty = otherValue === '';
        const otherNumericValue = Number(otherValue);

        if (!isEmpty && !otherEmpty && numericValue === 0 && otherNumericValue === 0) {
            return 'Gratis';
        }

        if (isMin) {
            if (isEmpty) {
                return 'Min';
            }
            if (numericValue === 0) {
                return 'Min';
            }
            return `${numericValue}€`;
        }

        if (isEmpty) {
            return 'Max';
        }

        return `${numericValue}€`;
    }

    function updateSliderZIndex() {
        if (!minPriceRange || !maxPriceRange) return;
        const minValue = Number(minPriceRange.value);
        const maxValue = Number(maxPriceRange.value);

        if (minValue >= maxValue) {
            minPriceRange.style.zIndex = '1';
            maxPriceRange.style.zIndex = '2';
        } else {
            minPriceRange.style.zIndex = '2';
            maxPriceRange.style.zIndex = '1';
        }
    }

    function syncPriceLabels() {
        if (!minPriceRange || !maxPriceRange || !minPriceLabel || !maxPriceLabel) return;

        const minEmpty = minPriceRange.value === '';
        const maxEmpty = maxPriceRange.value === '';
        const minValue = Number(minPriceRange.value);
        const maxValue = Number(maxPriceRange.value);

        if (!minEmpty && !maxEmpty && minValue > maxValue) {
            maxPriceRange.value = minPriceRange.value;
        }
        if (!minEmpty && !maxEmpty && maxValue < minValue) {
            minPriceRange.value = maxPriceRange.value;
        }

        const isFree = !minEmpty && !maxEmpty && minValue === 0 && maxValue === 0;

        minPriceLabel.textContent = formatPriceLabel(minPriceRange.value, true, maxPriceRange.value);
        maxPriceLabel.textContent = formatPriceLabel(maxPriceRange.value, false, minPriceRange.value);

        const fromLabel = document.querySelector('.price-range-label--from');
        const untilLabel = document.querySelector('.price-range-label--until');
        const freeLabel = document.getElementById('freePriceLabel');

        if (fromLabel && untilLabel && freeLabel) {
            fromLabel.hidden = isFree;
            untilLabel.hidden = isFree;
            freeLabel.hidden = !isFree;
        }

        updateSliderZIndex();
    }

    async function submitFilters(event) {
        if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
        }
        if (!filterForm) return;

        const formData = new FormData(filterForm);
        formData.append('ajax', '1');

        try {
            const response = await fetch(filterForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                filterForm.submit();
                return;
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newResults = doc.querySelector('#shop-search-results');
            const currentResults = document.querySelector('#shop-search-results');
            const newCount = doc.querySelector('#shop-result-count');
            const currentCount = document.querySelector('#shop-result-count');

            if (newResults && currentResults) {
                currentResults.innerHTML = newResults.innerHTML;
            }

            if (newCount && currentCount) {
                currentCount.textContent = newCount.textContent;
            }
        } catch (error) {
            filterForm.submit();
        }
    }

    function submitPriceDebounced() {
        if (priceSubmitTimer) {
            clearTimeout(priceSubmitTimer);
        }
        priceSubmitTimer = setTimeout(() => submitFilters(), PRICE_SUBMIT_DELAY);
    }

    if (minPriceRange && maxPriceRange) {
        minPriceRange.addEventListener('input', () => {
            const minEmpty = minPriceRange.value === '';
            const maxEmpty = maxPriceRange.value === '';
            if (!minEmpty && !maxEmpty && Number(minPriceRange.value) > Number(maxPriceRange.value)) {
                maxPriceRange.value = minPriceRange.value;
            }
            syncPriceLabels();
            submitPriceDebounced();
        });

        maxPriceRange.addEventListener('input', () => {
            const minEmpty = minPriceRange.value === '';
            const maxEmpty = maxPriceRange.value === '';
            if (!minEmpty && !maxEmpty && Number(maxPriceRange.value) < Number(minPriceRange.value)) {
                minPriceRange.value = maxPriceRange.value;
            }
            syncPriceLabels();
            submitPriceDebounced();
        });

        minPriceRange.addEventListener('change', submitFilters);
        maxPriceRange.addEventListener('change', submitFilters);
    }

    if (filterForm) {
        filterForm.addEventListener('submit', submitFilters);
        filterForm.querySelectorAll('input[type="checkbox"], input[type="number"], input[type="date"], select').forEach((element) => {
            element.addEventListener('change', submitFilters);
        });
    }

    filterInputs.forEach((input) => {
        input.addEventListener('input', () => filterOptionList(input));
        input.addEventListener('change', () => filterOptionList(input));
        input.addEventListener('keyup', () => filterOptionList(input));
    });

    clearButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.filterTarget;
            const list = targetId ? document.getElementById(targetId) : null;
            if (!list) return;

            const wrapper = button.closest('.search-box');
            const input = wrapper ? wrapper.querySelector('.js-filter-input') : null;
            if (input) {
                input.value = '';
                input.focus();
                filterOptionList(input);
            }

            list.querySelectorAll('.js-filter-item').forEach((item) => {
                item.style.display = '';
            });
        });
    });

    syncPriceLabels();
});
