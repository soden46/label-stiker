<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\LabelBrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LabelSettingController extends Controller
{
    public function edit(LabelBrandingService $branding): View
    {
        return view('settings.label', [
            'logoDataUri' => $branding->logoDataUri(),
            'logoPath' => $branding->logoPath(),
            'faviconDataUri' => $branding->faviconDataUri(),
            'faviconPath' => $branding->faviconPath(),
            'appName' => $branding->appName(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:60'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,jpg,jpeg,webp', 'max:1024'],
        ], [
            'app_name.required' => 'Judul aplikasi wajib diisi.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, JPEG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'favicon.mimes' => 'Favicon harus berformat PNG, ICO, JPG, JPEG, atau WEBP.',
            'favicon.max' => 'Ukuran favicon maksimal 1 MB.',
        ]);

        AppSetting::put('app_name', $validated['app_name']);

        if ($request->hasFile('logo')) {
            $this->replaceFile('label_logo_path', $request->file('logo')->store('branding', 'public'));
        }

        if ($request->hasFile('favicon')) {
            $this->replaceFile('favicon_path', $request->file('favicon')->store('branding', 'public'));
        }

        return back()->with('success', 'Pengaturan branding berhasil diperbarui.');
    }

    public function destroy(): RedirectResponse
    {
        $this->removeFile('label_logo_path');

        return back()->with('success', 'Logo custom dihapus. Aplikasi kembali memakai logo bawaan.');
    }

    public function destroyFavicon(): RedirectResponse
    {
        $this->removeFile('favicon_path');

        return back()->with('success', 'Favicon custom berhasil dihapus.');
    }

    private function replaceFile(string $key, string $newPath): void
    {
        $oldPath = AppSetting::value($key);
        AppSetting::put($key, $newPath);

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    private function removeFile(string $key): void
    {
        $path = AppSetting::value($key);

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        AppSetting::query()->where('key', $key)->delete();
    }
}
