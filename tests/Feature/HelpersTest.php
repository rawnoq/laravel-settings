<?php

namespace Rawnoq\Settings\Tests\Feature;

use Rawnoq\Settings\Services\SettingService;
use Rawnoq\Settings\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_setting_helper_returns_service_when_no_arguments(): void
    {
        $service = setting();

        $this->assertInstanceOf(SettingService::class, $service);
    }

    public function test_setting_helper_can_set_and_get_values(): void
    {
        setting('site_name', 'My Site');
        setting('site_email', 'admin@example.com', 'general');

        $this->assertSame('My Site', setting('site_name'));
        $this->assertSame('admin@example.com', setting('site_email'));

        $service = setting();
        $this->assertSame(['general'], $service->get('site_email')->groups);
    }

    public function test_setting_helper_handles_translatable_values(): void
    {
        setting('site_title', [
            'en' => 'Title',
            'es' => 'Título',
        ]);

        $service = setting();
        $setting = $service->get('site_title');

        $this->assertTrue($setting->is_translatable);
        $this->assertSame('Title', setting('site_title'));
        $this->assertDatabaseHas('setting_translations', [
            'setting_id' => $setting->id,
            'locale' => 'es',
            'value' => 'Título',
        ]);
    }

    public function test_settings_helper_can_perform_bulk_operations(): void
    {
        $result = settings([
            'contact_phone' => '+1234567890',
            'contact_email' => 'contact@example.com',
        ]);

        $this->assertInstanceOf(SettingService::class, $result);
        $this->assertSame('+1234567890', setting('contact_phone'));
        $this->assertSame('contact@example.com', setting('contact_email'));

        $all = settings();
        $this->assertCount(2, $all);
    }
}
