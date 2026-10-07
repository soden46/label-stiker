<label for="categoryName">Nama kategori *</label>
<input id="categoryName" name="name" value="{{ old('name', $category->name ?? '') }}" maxlength="150" required>
<div data-catalog-input>
    <label for="catalogType">Katalog *</label>
    <select id="catalogType" name="catalog_type" data-catalog-type>
        <option value="pdf" @selected(old('catalog_type', isset($category) && $category->catalog_url ? 'url' : 'pdf') === 'pdf')>Upload PDF</option>
        <option value="url" @selected(old('catalog_type', isset($category) && $category->catalog_url ? 'url' : 'pdf') === 'url')>Link katalog</option>
    </select>
    <div data-catalog-pdf>
        <label for="catalogFile">File katalog PDF</label>
        <input id="catalogFile" class="simple-file-input" type="file" name="catalog_file" accept=".pdf,application/pdf">
        <small class="input-hint">Maks. {{ $catalogUploadLimitMb }} MB. @if(isset($category) && $category->catalog_path)Kosongkan untuk mempertahankan PDF saat ini.@endif</small>
    </div>
    <div data-catalog-url>
        <label for="catalogUrl">Link katalog</label>
        <input id="catalogUrl" type="url" name="catalog_url" value="{{ old('catalog_url', $category->catalog_url ?? '') }}" placeholder="https://example.com/katalog.pdf" maxlength="2048">
    </div>
</div>
