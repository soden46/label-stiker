<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Storage;

class LabelBrandingService
{
    public function appName(): string
    {
        return AppSetting::value('app_name', config('app.name', 'Labelin'));
    }

    public function logoPath(): ?string
    {
        return AppSetting::value('label_logo_path');
    }

    public function logoDataUri(): ?string
    {
        return $this->dataUri($this->logoPath());
    }

    public function publicLabelLogoDataUri(): ?string
    {
        $path = public_path('logo.png');

        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    public function faviconPath(): ?string
    {
        return AppSetting::value('favicon_path');
    }

    public function faviconDataUri(): ?string
    {
        return $this->dataUri($this->faviconPath());
    }

    public function dataUriForPath(?string $path): ?string
    {
        return $this->dataUri($path);
    }

    private function dataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }
}
