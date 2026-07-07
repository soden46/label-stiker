<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LabelSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_replace_and_remove_the_label_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'app_name' => 'WAF Label Center',
            'logo' => UploadedFile::fake()->image('brand-logo.png', 300, 200),
            'favicon' => UploadedFile::fake()->image('favicon.png', 32, 32),
        ])->assertRedirect();

        $firstPath = AppSetting::value('label_logo_path');
        $faviconPath = AppSetting::value('favicon_path');
        $this->assertSame('WAF Label Center', AppSetting::value('app_name'));
        Storage::disk('public')->assertExists($firstPath);
        Storage::disk('public')->assertExists($faviconPath);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('WAF Label Center');

        $this->actingAs($user)->put(route('settings.update'), [
            'app_name' => 'WAF Label Center',
            'logo' => UploadedFile::fake()->image('new-logo.png', 400, 240),
        ])->assertRedirect();

        $newPath = AppSetting::value('label_logo_path');
        $this->assertNotSame($firstPath, $newPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($newPath);

        $this->actingAs($user)->delete(route('settings.logo.destroy'))->assertRedirect();
        Storage::disk('public')->assertMissing($newPath);
        $this->assertNull(AppSetting::value('label_logo_path'));

        $this->actingAs($user)->delete(route('settings.favicon.destroy'))->assertRedirect();
        Storage::disk('public')->assertMissing($faviconPath);
        $this->assertNull(AppSetting::value('favicon_path'));
    }
}
