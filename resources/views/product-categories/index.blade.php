@extends('layouts.app')

@section('title', 'Kategori Produk')
@section('eyebrow', 'MASTER DATA')
@section('heading', 'Kategori produk')

@section('content')
@if($errors->any())
    <div class="error-box access-error"><strong>Periksa data berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="catalog-layout">
    <section class="panel catalog-panel category-panel">
        <div class="panel-heading">
            <div><p class="eyebrow">KATEGORI PRODUK</p><h3>{{ $categories->total() }} kategori tersimpan</h3></div>
            <form class="table-search"><input type="search" name="search" value="{{ $search }}" placeholder="Cari nama kategori..."><button aria-label="Cari kategori">&#8981;</button></form>
        </div>
        <div class="table-wrap"><table>
            <thead><tr><th>Nama kategori</th><th>Katalog</th><th>Jumlah part</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td><a href="{{ $category->catalogUrl() }}" target="_blank" rel="noopener noreferrer">{{ $category->catalog_path ? 'Buka PDF' : 'Buka link katalog' }}</a></td>
                    <td>{{ $category->products_count }}</td>
                    <td>@if(auth()->user()->canAccess('products.manage'))<div class="row-actions">
                        <a href="{{ route('product-categories.edit', $category) }}" class="action-button" title="Edit kategori">&#9998;</a>
                        <form method="POST" action="{{ route('product-categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')<button class="action-button danger" title="Hapus kategori">&times;</button></form>
                    </div>@endif</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty-state">Belum ada kategori produk.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="pagination">{{ $categories->links() }}</div>
    </section>
    @if(auth()->user()->canAccess('products.manage'))
    <aside class="panel add-product-panel">
        <p class="eyebrow">TAMBAH KATEGORI</p><h3>Kategori baru</h3><p>QR label membuka katalog dari kategori part yang dipilih.</p>
        <form method="POST" action="{{ route('product-categories.store') }}" enctype="multipart/form-data">
            @csrf
            @include('product-categories._fields', ['category' => null])
            <button class="button button-primary button-wide">Simpan kategori</button>
        </form>
    </aside>
    @endif
</div>
@endsection
