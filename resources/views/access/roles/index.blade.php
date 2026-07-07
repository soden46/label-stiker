@extends('layouts.app')
@section('title','Role & Hak Akses') @section('eyebrow','ACCESS CONTROL') @section('heading','Role & hak akses')
@section('content')
<section class="hero-row"><div><h2>Hak akses per modul.</h2><p>Role Back Office dan POS dipisahkan agar izin tidak pernah menyeberang portal.</p></div><a href="{{ route('roles.create') }}" class="button button-primary">＋ Buat role</a></section>
@if($errors->any())<div class="error-box access-error">{{ $errors->first() }}</div>@endif
<div class="role-grid">@foreach($roles as $role)<article class="panel role-card"><div><span class="role-portal {{ $role->portal }}">{{ $role->portal }}</span>@if($role->is_super_admin)<span class="role-super">FULL ACCESS</span>@endif</div><h3>{{ $role->name }}</h3><p>{{ $role->description ?: 'Tanpa deskripsi.' }}</p><dl><div><dt>Pengguna</dt><dd>{{ $role->users_count }}</dd></div><div><dt>Hak akses</dt><dd>{{ $role->is_super_admin ? 'Semua' : $role->permissions_count }}</dd></div></dl><div class="role-actions">@if(!$role->is_super_admin)<a href="{{ route('roles.edit',$role) }}" class="button button-ghost">Edit akses</a><form method="POST" action="{{ route('roles.destroy',$role) }}" onsubmit="return confirm('Hapus role ini?')">@csrf @method('DELETE')<button class="action-button danger">×</button></form>@else<span>Role sistem terkunci</span>@endif</div></article>@endforeach</div>
@endsection
