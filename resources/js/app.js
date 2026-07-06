import './bootstrap';

const menuButton = document.querySelector('#menuButton');
const sidebar = document.querySelector('#sidebar');
menuButton?.addEventListener('click', () => sidebar?.classList.toggle('open'));

const builder = document.querySelector('[data-label-builder]');
if (builder) {
    const products = JSON.parse(builder.dataset.products || '[]');
    const search = document.querySelector('#productSearch');
    const results = document.querySelector('#productResults');
    const productId = document.querySelector('#productId');
    const selected = document.querySelector('#selectedProduct');
    const generate = document.querySelector('#generateButton');
    const clear = document.querySelector('#clearProduct');
    const emptyPreview = document.querySelector('#emptyPreview');
    const preview = document.querySelector('#previewContent');

    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const renderResults = query => {
        const normalized = query.trim().toLowerCase();
        const matches = products.filter(product => [product.name, product.sku, product.customer_part_no, product.supplier_code].some(value => String(value || '').toLowerCase().includes(normalized))).slice(0, 8);
        results.innerHTML = matches.length ? matches.map(product => `<button type="button" class="product-result" data-id="${product.id}"><span><strong>${escapeHtml(product.name)}</strong><small>${escapeHtml(product.sku)} · ${escapeHtml(product.description || 'Tanpa deskripsi')}</small></span><em>${escapeHtml(product.supplier_code || product.uom)}</em></button>`).join('') : '<div class="empty-state">Part tidak ditemukan.</div>';
        results.classList.add('open');
    };
    const choose = product => {
        productId.value = product.id;
        search.value = `${product.name} — ${product.sku}`;
        results.classList.remove('open');
        clear.style.display = 'block';
        selected.hidden = false;
        document.querySelector('#selectedName').textContent = product.name;
        document.querySelector('#selectedMeta').textContent = `${product.sku} · ${product.description || 'Tanpa deskripsi'} · ${product.uom}`;
        document.querySelector('#customerPart').value = product.customer_part_no || '';
        document.querySelector('#uom').value = product.uom || 'PCS';
        document.querySelector('#previewName').textContent = product.name;
        document.querySelector('#previewDesc').textContent = product.description || '—';
        document.querySelector('#previewCustomer').textContent = product.customer_part_no || '—';
        document.querySelector('#previewSku').textContent = product.sku;
        document.querySelector('#previewCode').textContent = product.supplier_code || '—';
        emptyPreview.hidden = true;
        preview.hidden = false;
        generate.disabled = false;
    };
    search.addEventListener('focus', () => renderResults(search.value));
    search.addEventListener('input', () => { productId.value = ''; generate.disabled = true; renderResults(search.value); });
    results.addEventListener('click', event => {
        const button = event.target.closest('[data-id]');
        if (button) choose(products.find(product => String(product.id) === button.dataset.id));
    });
    clear.addEventListener('click', () => { search.value=''; productId.value=''; selected.hidden=true; clear.style.display='none'; preview.hidden=true; emptyPreview.hidden=false; generate.disabled=true; search.focus(); renderResults(''); });
    document.addEventListener('click', event => { if (!event.target.closest('.product-picker')) results.classList.remove('open'); });
    document.querySelector('#customerPart').addEventListener('input', event => document.querySelector('#previewCustomer').textContent = event.target.value || '—');
    document.querySelector('#purchaseOrder').addEventListener('input', event => document.querySelector('#previewPo').textContent = event.target.value || '—');
    document.querySelector('#quantity').addEventListener('input', event => document.querySelector('#previewQty').textContent = `${event.target.value || '—'} ${document.querySelector('#uom').value}`);
    document.querySelector('#uom').addEventListener('input', event => document.querySelector('#previewQty').textContent = `${document.querySelector('#quantity').value || '—'} ${event.target.value}`);
    if (productId.value) {
        const initial = products.find(product => String(product.id) === productId.value);
        if (initial) choose(initial);
    }
}

const bulkPrint = document.querySelector('[data-bulk-print]');
if (bulkPrint) {
    const selectAll = bulkPrint.querySelector('#selectAllLabels');
    const checkboxes = [...bulkPrint.querySelectorAll('.label-checkbox')];
    const printButton = bulkPrint.querySelector('#bulkPrintButton');
    const labelCount = bulkPrint.querySelector('#selectedLabelCount');
    const pageCount = bulkPrint.querySelector('#selectedPageCount');

    const updateBulkState = () => {
        const selected = checkboxes.filter(checkbox => checkbox.checked);
        let pages = 0;
        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const copies = row.querySelector('.copy-input');
            copies.disabled = !checkbox.checked;
            if (checkbox.checked) pages += Number(copies.value || 1);
        });
        selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
        printButton.disabled = selected.length === 0 || pages > 300;
        labelCount.textContent = `${selected.length} label dipilih`;
        pageCount.textContent = pages > 300 ? `${pages} halaman — melewati batas 300` : `${pages} halaman PDF`;
    };

    selectAll?.addEventListener('change', () => {
        checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
        updateBulkState();
    });
    checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateBulkState));
    bulkPrint.addEventListener('click', event => {
        const button = event.target.closest('[data-copy-minus], [data-copy-plus]');
        if (!button) return;
        const row = button.closest('tr');
        const checkbox = row.querySelector('.label-checkbox');
        const input = row.querySelector('.copy-input');
        checkbox.checked = true;
        const direction = button.hasAttribute('data-copy-plus') ? 1 : -1;
        input.value = Math.max(1, Math.min(50, Number(input.value || 1) + direction));
        updateBulkState();
    });
    bulkPrint.addEventListener('input', event => {
        if (!event.target.matches('.copy-input')) return;
        event.target.value = Math.max(1, Math.min(50, Number(event.target.value || 1)));
        event.target.closest('tr').querySelector('.label-checkbox').checked = true;
        updateBulkState();
    });
    updateBulkState();
}
