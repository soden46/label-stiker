@extends('layouts.app')

@section('title', 'Pengaturan')
@section('eyebrow', 'SYSTEM SETTINGS')
@section('heading', 'Pengaturan branding')

@section('content')
<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="settings-layout">
    @csrf
    @method('PUT')

    <section class="panel settings-main">
        <div class="panel-heading"><div><p class="eyebrow">IDENTITAS APLIKASI</p><h3>Nama dan aset brand</h3></div><span class="range-pill">Global</span></div>

        @if($errors->any())
            <div class="error-box settings-error"><strong>Periksa pengaturan berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="settings-body">
            <div class="field settings-name-field">
                <label>Judul aplikasi <b>*</b></label>
                <input name="app_name" value="{{ old('app_name', $appName) }}" maxlength="60" required>
                <small class="input-hint">Dipakai pada judul browser, halaman login, dan sidebar.</small>
            </div>
            <div class="delivery-address-grid">
                <p class="eyebrow">PROFIL PERUSAHAAN</p><h4>Data pengirim untuk Delivery Order</h4>
                <p class="input-hint">Disimpan di pengaturan aplikasi dan digunakan pada shipping sticker, bukan di-hardcode pada PDF.</p>
                <div class="form-grid">
                    <div class="field"><label>Nama perusahaan</label><input name="company_name" value="{{ old('company_name', $companyProfile['name']) }}" maxlength="160"></div>
                    <div class="field"><label>Telepon</label><input name="company_phone" value="{{ old('company_phone', $companyProfile['phone']) }}" maxlength="80"></div>
                    <div class="field field-span-2"><label>Alamat</label><textarea name="company_address" rows="3" maxlength="2000">{{ old('company_address', $companyProfile['address']) }}</textarea></div>
                    <div class="field"><label>Kota</label><input name="company_city" value="{{ old('company_city', $companyProfile['city']) }}" maxlength="100"></div>
                    <div class="field"><label>Kode pos</label><input name="company_postal_code" value="{{ old('company_postal_code', $companyProfile['postal_code']) }}" maxlength="30"></div>
                    <div class="field"><label>Negara</label><input name="company_country" value="{{ old('company_country', $companyProfile['country']) }}" maxlength="100"></div>
                </div>
            </div>

            <div class="settings-assets">
                <div class="asset-setting">
                    <div><p class="eyebrow">LOGO UTAMA</p><h4>Logo aplikasi & label</h4><p>Dipakai pada login, sidebar, preview, PDF satuan, dan bulk print.</p></div>
                    <label class="asset-dropzone">
                        <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp" data-logo-input>
                        <div class="asset-preview logo-asset-preview" data-logo-preview>
                            @if($logoDataUri)<img src="{{ $logoDataUri }}" alt="Logo aktif">@else<span>{{ strtoupper(mb_substr($appName, 0, 1)) }}</span>@endif
                        </div>
                        <strong>Pilih logo baru</strong><small>PNG transparan disarankan · maks. 2 MB</small>
                    </label>
                </div>

                <div class="asset-setting">
                    <div><p class="eyebrow">FAVICON</p><h4>Ikon browser</h4><p>Ikon kecil yang tampil pada tab browser dan bookmark.</p></div>
                    <label class="asset-dropzone compact">
                        <input type="file" name="favicon" accept=".png,.ico,.jpg,.jpeg,.webp" data-favicon-input>
                        <div class="asset-preview favicon-asset-preview" data-favicon-preview>
                            @if($faviconDataUri)<img src="{{ $faviconDataUri }}" alt="Favicon aktif">@else<span>{{ strtoupper(mb_substr($appName, 0, 1)) }}</span>@endif
                        </div>
                        <strong>Pilih favicon baru</strong><small>PNG/ICO persegi · maks. 1 MB</small>
                    </label>
                </div>
            </div>
        </div>

        <div class="settings-actions"><button class="button button-primary">✓ Simpan semua pengaturan</button></div>
    </section>

    <aside class="panel settings-preview">
        <p class="eyebrow">LIVE PREVIEW</p><h3>Tampilan brand</h3>
        <div class="brand-preview-sidebar">
            <span class="brand-mark {{ $logoDataUri ? 'has-image' : '' }}" data-brand-mark>@if($logoDataUri)<img src="{{ $logoDataUri }}" alt="Logo">@else{{ strtoupper(mb_substr($appName, 0, 1)) }}@endif</span>
            <div><strong data-brand-name>{{ $appName }}</strong><small>Barcode workspace</small></div>
        </div>
        <div class="brand-preview-tab"><span data-favicon-mini>@if($faviconDataUri)<img src="{{ $faviconDataUri }}" alt="">@else{{ strtoupper(mb_substr($appName, 0, 1)) }}@endif</span><strong><i data-tab-title>{{ $appName }}</i> · Dashboard</strong><b>×</b></div>
        <p>Perubahan logo berlaku dinamis, termasuk saat mencetak ulang label lama.</p>

        @if($logoPath || $faviconPath)
            <div class="asset-remove-list">
                @if($logoPath)<button type="submit" form="removeLogoForm" class="asset-remove">× Hapus logo custom</button>@endif
                @if($faviconPath)<button type="submit" form="removeFaviconForm" class="asset-remove">× Hapus favicon custom</button>@endif
            </div>
        @endif
    </aside>
</form>

@if($logoPath)<form id="removeLogoForm" method="POST" action="{{ route('settings.logo.destroy') }}" onsubmit="return confirm('Hapus logo custom dan kembali ke logo bawaan?')">@csrf @method('DELETE')</form>@endif
@if($faviconPath)<form id="removeFaviconForm" method="POST" action="{{ route('settings.favicon.destroy') }}" onsubmit="return confirm('Hapus favicon custom?')">@csrf @method('DELETE')</form>@endif
@endsection
