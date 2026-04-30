document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('btnFilterToggle');
    const popover = document.getElementById('filterPopover');
    const inputs = popover ? Array.from(popover.querySelectorAll('input[type="checkbox"]')) : [];

    if (!toggleButton || !popover || inputs.length === 0) {
        return;
    }
    
    toggleButton.addEventListener('click', function (event) {
        event.stopPropagation();
        popover.classList.toggle('open');
        popover.setAttribute('aria-hidden', String(!popover.classList.contains('open')));
    });

    document.addEventListener('click', function (event) {
        if (!popover.contains(event.target) && !toggleButton.contains(event.target)) {
            popover.classList.remove('open');
            popover.setAttribute('aria-hidden', 'true');
        }
    });

    inputs.forEach(function (input) {
        input.addEventListener('change', updateWishlist);
    });

    updateWishlist();
});

function updateWishlist() {
    const nameVisible = document.querySelector('input[value="name"]')?.checked;
    const priceVisible = document.querySelector('input[value="price"]')?.checked;
    const discountVisible = document.querySelector('input[value="discount"]')?.checked;
    const dateVisible = document.querySelector('input[value="date"]')?.checked;

    document.querySelectorAll('.field-name').forEach(el => {
        el.classList.toggle('field-hidden', !nameVisible);
    });
    document.querySelectorAll('.field-price').forEach(el => {
        el.classList.toggle('field-hidden', !priceVisible);
    });
    document.querySelectorAll('.field-discount').forEach(el => {
        el.classList.toggle('field-hidden', !discountVisible);
    });
    document.querySelectorAll('.field-date').forEach(el => {
        el.classList.toggle('field-hidden', !dateVisible);
    });
}