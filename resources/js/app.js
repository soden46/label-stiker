import './bootstrap';

const menuButton = document.querySelector('#menuButton');
const sidebar = document.querySelector('#sidebar');
menuButton?.addEventListener('click', () => sidebar?.classList.toggle('open'));

document.querySelectorAll('[data-catalog-input]').forEach(container => {
    const type = container.querySelector('[data-catalog-type]');
    const update = () => {
        const pdf = type.value === 'pdf';
        container.querySelector('[data-catalog-pdf]').hidden = !pdf;
        container.querySelector('[data-catalog-url]').hidden = pdf;
        container.querySelector('[name="catalog_file"]').disabled = !pdf;
        const url = container.querySelector('[name="catalog_url"]');
        url.disabled = pdf;
        url.required = !pdf;
    };
    type.addEventListener('change', update);
    update();
});

const builder = document.querySelector('[data-label-builder]');
if (builder) {
    const products = JSON.parse(builder.dataset.products || '[]');
    const catalogQrs = JSON.parse(builder.dataset.catalogQrs || '{}');
    const search = document.querySelector('#productSearch');
    const results = document.querySelector('#productResults');
    const productId = document.querySelector('#productId');
    const selected = document.querySelector('#selectedProduct');
    const generate = document.querySelector('#generateButton');
    const clear = document.querySelector('#clearProduct');
    const emptyPreview = document.querySelector('#emptyPreview');
    const preview = document.querySelector('#previewContent');

    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const formatQuantity = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 4 }).format(Number(value || 0));
    const emptyText = '-';
    const currentUom = () => document.querySelector('#uom').value || 'PCS';
    const updatePreviewQty = () => {
        document.querySelector('#previewQty').textContent = `${document.querySelector('#quantity').value || emptyText} ${currentUom()}`;
    };

    const renderResults = query => {
        const normalized = query.trim().toLowerCase();
        const matches = products.filter(product => [product.name, product.sku, product.customer_part_no, product.supplier_code].some(value => String(value || '').toLowerCase().includes(normalized))).slice(0, 8);
        results.innerHTML = matches.length ? matches.map(product => `<button type="button" class="product-result" data-id="${product.id}"><span><strong>${escapeHtml(product.name)}</strong><small>${escapeHtml(product.sku)} · ${escapeHtml(product.description || 'Tanpa deskripsi')} · Stok ${formatQuantity(product.inventory_stock)} ${escapeHtml(product.uom || 'PCS')}</small></span><em>${escapeHtml(product.supplier_code || product.uom)}</em></button>`).join('') : '<div class="empty-state">Part tidak ditemukan.</div>';
        results.classList.add('open');
    };

    const choose = product => {
        const customerPartNo = product.customer_part_no || '';

        productId.value = product.id;
        search.value = `${product.name} - ${product.sku}`;
        results.classList.remove('open');
        clear.style.display = 'block';
        selected.hidden = false;
        document.querySelector('#selectedName').textContent = product.name;
        document.querySelector('#selectedMeta').textContent = `${product.sku} · ${product.description || 'Tanpa deskripsi'} · Stok ${formatQuantity(product.inventory_stock)} ${product.uom || 'PCS'}`;
        document.querySelector('#customerPart').value = customerPartNo;
        document.querySelector('#uom').value = product.uom || 'PCS';
        document.querySelector('#previewDesc').textContent = product.description || emptyText;
        document.querySelector('#previewCustomer').textContent = customerPartNo || emptyText;
        document.querySelector('#previewSku').textContent = product.sku;
        document.querySelector('#previewCatalogQr').src = catalogQrs[product.catalog_url];
        document.querySelector('#selectedCategory').textContent = product.category_name || 'Belum dikategorikan (PATRIA)';
        document.querySelector('#selectedCatalogLink').href = product.catalog_url;
        document.querySelector('#selectedCatalog').hidden = false;
        updatePreviewQty();
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
    clear.addEventListener('click', () => { search.value=''; productId.value=''; selected.hidden=true; document.querySelector('#selectedCatalog').hidden=true; clear.style.display='none'; preview.hidden=true; emptyPreview.hidden=false; generate.disabled=true; search.focus(); renderResults(''); });
    document.addEventListener('click', event => { if (!event.target.closest('.product-picker')) results.classList.remove('open'); });
    document.querySelector('#customerPart').addEventListener('input', event => document.querySelector('#previewCustomer').textContent = event.target.value || emptyText);
    document.querySelector('#purchaseOrder').addEventListener('input', event => document.querySelector('#previewPo').textContent = event.target.value || emptyText);
    document.querySelector('#quantity').addEventListener('input', updatePreviewQty);
    document.querySelector('#uom').addEventListener('input', updatePreviewQty);

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
        pageCount.textContent = pages > 300 ? `${pages} halaman - melewati batas 300` : `${pages} halaman PDF`;
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

document.querySelectorAll('.file-picker input[type="file"]').forEach(input => {
    input.addEventListener('change', () => {
        const label = input.closest('.file-picker');
        const text = label?.querySelector('span');
        if (text && input.files?.[0]) text.textContent = input.files[0].name;
    });
});

const logoInput = document.querySelector('[data-logo-input]');
logoInput?.addEventListener('change', () => {
    const file = logoInput.files?.[0];
    const preview = document.querySelector('[data-logo-preview]');
    if (!file || !preview) return;

    const reader = new FileReader();
    reader.addEventListener('load', () => {
        preview.innerHTML = `<img src="${reader.result}" alt="Preview logo baru">`;
        const brandMark = document.querySelector('[data-brand-mark]');
        if (brandMark) {
            brandMark.classList.add('has-image');
            brandMark.innerHTML = `<img src="${reader.result}" alt="Preview logo baru">`;
        }
    });
    reader.readAsDataURL(file);
});

const faviconInput = document.querySelector('[data-favicon-input]');
faviconInput?.addEventListener('change', () => {
    const file = faviconInput.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.addEventListener('load', () => {
        document.querySelectorAll('[data-favicon-preview], [data-favicon-mini]').forEach(preview => {
            preview.innerHTML = `<img src="${reader.result}" alt="Preview favicon baru">`;
        });
    });
    reader.readAsDataURL(file);
});

const appNameInput = document.querySelector('input[name="app_name"]');
appNameInput?.addEventListener('input', () => {
    const name = appNameInput.value.trim() || 'Labelin';
    document.querySelectorAll('[data-brand-name], [data-tab-title]').forEach(element => element.textContent = name);
});

const rolePortal = document.querySelector('#rolePortal');
if (rolePortal) {
    const updatePermissionPortal = () => {
        document.querySelectorAll('[data-permission-portal]').forEach(section => {
            section.hidden = section.dataset.permissionPortal !== rolePortal.value;
        });
    };
    rolePortal.addEventListener('change', updatePermissionPortal);
    document.querySelector('[data-check-all]')?.addEventListener('click', () => {
        const active = document.querySelector(`[data-permission-portal="${rolePortal.value}"]`);
        const checkboxes = [...active.querySelectorAll('input[type="checkbox"]')];
        const shouldCheck = checkboxes.some(input => !input.checked);
        checkboxes.forEach(input => input.checked = shouldCheck);
    });
    updatePermissionPortal();
}

const deliveryOrderForm = document.querySelector('[data-delivery-order-form]');
if (deliveryOrderForm) {
    const products = JSON.parse(document.querySelector('#deliveryOrderProducts')?.textContent || '[]');
    const initialItems = JSON.parse(document.querySelector('#deliveryOrderInitialItems')?.textContent || '[]');
    const rows = document.querySelector('#deliveryOrderItems');
    const template = document.querySelector('#deliveryOrderItemTemplate');
    let index = 0;

    const populateProductDetails = row => {
        const product = products.find(item => String(item.id) === row.querySelector('[data-product]').value);
        row.querySelector('[data-waf]').textContent = product?.waf_part_no || '-';
        row.querySelector('[data-customer-part]').textContent = product?.customer_part_no || '-';
        row.querySelector('[data-catalog]').textContent = product?.catalog_code || '-';
        if (product) row.querySelector('[data-unit]').value = product.unit || '';
    };
    const addItem = item => {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('tr');
        row.innerHTML = row.innerHTML.replaceAll('__INDEX__', index++);
        rows.append(row);
        row.querySelector('[data-product]').value = item?.product_id || '';
        row.querySelector('input[name$="[quantity]"]').value = item?.quantity || '';
        row.querySelector('[data-unit]').value = item?.unit || '';
        row.querySelector('input[name$="[weight]"]').value = item?.weight || '';
        populateProductDetails(row);
    };
    initialItems.forEach(addItem);
    document.querySelector('#addDeliveryOrderItem')?.addEventListener('click', () => addItem());
    rows.addEventListener('change', event => {
        if (event.target.matches('[data-product]')) populateProductDetails(event.target.closest('tr'));
    });
    rows.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-item]');
        if (!button) return;
        if (rows.querySelectorAll('tr').length > 1) button.closest('tr').remove();
    });
    const customerDialog = document.querySelector('#customerDialog');
    const customerForm = document.querySelector('#customerForm');
    const customerError = document.querySelector('#customerFormError');
    let customerTarget;
    const openCustomerDialog = target => {
        customerTarget = target;
        customerError.hidden = true;
        customerForm.reset();
        customerDialog.showModal();
        document.querySelector('#customerName').focus();
    };
    const bindPartner = (selectId, addressId, phoneId) => {
        const select = document.querySelector(selectId);
        if (!select) return;
        select.dataset.lastValue = select.value;
        select.addEventListener('change', event => {
            if (event.target.value === '__add_customer__') {
                event.target.value = event.target.dataset.lastValue;
                openCustomerDialog(event.target);
                return;
            }
            event.target.dataset.lastValue = event.target.value;
            const option = event.target.selectedOptions[0];
            const address = document.querySelector(addressId);
            if (address && option?.dataset.address) address.value = option.dataset.address;
            const phone = phoneId && document.querySelector(phoneId);
            if (phone && option?.dataset.phone) phone.value = option.dataset.phone;
        });
    };
    bindPartner('#toPartner', '#toAddress');
    bindPartner('#shipToPartner', '#shipToAddress', '#shipToPhone');

    document.querySelectorAll('[data-add-customer]').forEach(button => button.addEventListener('click', () => {
        openCustomerDialog(document.querySelector(button.dataset.customerTarget));
    }));
    document.querySelectorAll('#closeCustomerDialog, #cancelCustomerDialog').forEach(button => button.addEventListener('click', () => customerDialog.close()));
    customerForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const response = await fetch(customerForm.dataset.customerStoreUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify(Object.fromEntries(new FormData(customerForm))),
        });
        const payload = await response.json();
        if (!response.ok) {
            customerError.textContent = Object.values(payload.errors || {}).flat().join(' ') || 'Customer tidak dapat disimpan.';
            customerError.hidden = false;
            return;
        }
        const customer = payload.customer;
        document.querySelectorAll('#toPartner, #shipToPartner').forEach(select => {
            const option = new Option(`${customer.code} — ${customer.name}`, customer.id, false, select === customerTarget);
            option.dataset.address = customer.address || '';
            option.dataset.phone = customer.phone || '';
            select.add(option);
        });
        customerTarget.dataset.lastValue = String(customer.id);
        customerTarget?.dispatchEvent(new Event('change'));
        customerDialog.close();
    });
}

const deliveryOrderBatch = document.querySelector('[data-delivery-order-batch]');
if (deliveryOrderBatch) {
    const selectAll = deliveryOrderBatch.querySelector('#selectAllDeliveryOrders');
    const checkboxes = [...deliveryOrderBatch.querySelectorAll('.delivery-order-checkbox')];
    const button = deliveryOrderBatch.querySelector('#batchDeliveryOrderButton');
    const count = deliveryOrderBatch.querySelector('#selectedDeliveryOrderCount');
    const update = () => {
        const selected = checkboxes.filter(checkbox => checkbox.checked);
        selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
        button.disabled = selected.length === 0;
        count.textContent = `${selected.length} DO dipilih`;
    };
    selectAll?.addEventListener('change', () => { checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked); update(); });
    checkboxes.forEach(checkbox => checkbox.addEventListener('change', update));
    update();
}
