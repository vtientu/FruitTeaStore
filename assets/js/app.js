// Progressive enhancement only. Forms and navigation also work without JavaScript.
const productForm = document.querySelector('[data-product-form]');
if (productForm) {
    const output = productForm.querySelector('[data-product-total]');
    const formatMoney = (value) => new Intl.NumberFormat('vi-VN').format(value) + 'đ';
    const updateTotal = () => {
        const data = new FormData(productForm);
        const quantity = Math.min(20, Math.max(1, Number(data.get('quantity')) || 1));
        const unitPrice =
            Number(productForm.dataset.basePrice) +
            (data.get('size') === 'L' ? Number(productForm.dataset.sizePrice) : 0) +
            [...productForm.querySelectorAll('input[name="toppings[]"]:checked')].reduce(
                (sum, input) => sum + Number(input.dataset.price),
                0,
            );
        output.textContent = formatMoney(unitPrice * quantity);
    };
    productForm.addEventListener('input', updateTotal);
}
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('details[open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary')?.focus();
    });
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
