@extends('layouts.app')

@section('title', 'Edit Kategori Produk')
@section('eyebrow', 'MASTER DATA')
@section('heading', 'Edit kategori produk')

@section('content')
<div class="edit-product-layout">
    <form method="POST" action="{{ route('product-categories.update', $category) }}" enctype="multipart/form-data" class="panel edit-product-form">
        @csrf @method('PUT')
        <div class="panel-heading"><h3>{{ $category->name }}</h3><a href="{{ $category->catalogUrl() }}" class="button button-ghost" target="_blank" rel="noopener noreferrer">Buka katalog</a></div>
        <div class="edit-form-body category-form">
            @if($errors->any())<div class="error-box"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @include('product-categories._fields')
        </div>
        <div class="edit-form-actions"><a href="{{ route('product-categories.index') }}" class="button button-ghost">Batal</a><button class="button button-primary">Simpan perubahan</button></div>
    </form>
    <aside class="panel edit-product-note"><h3>Katalog untuk label baru</h3><p>Perubahan katalog berlaku untuk label baru. QR pada label lama tetap memakai link katalog yang tersimpan saat label dibuat.</p></aside>
</div>
@endsection
