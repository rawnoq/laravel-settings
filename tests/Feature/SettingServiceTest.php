<?php

namespace Rawnoq\Settings\Tests\Feature;

use Rawnoq\Settings\Models\Setting;
use Rawnoq\Settings\Services\SettingService;
use Rawnoq\Settings\Tests\TestCase;

class SettingServiceTest extends TestCase
{
    public function test_it_can_set_and_get_fixed_setting(): void
    {
        $service = $this->app->make(SettingService::class);

        $service->set('site_name', 'My Site');

        $setting = $service->get('site_name');

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertSame('site_name', $setting->key);
        $this->assertSame('My Site', $setting->resolved_value);
        $this->assertFalse($setting->is_translatable);
        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'fixed_value' => 'My Site',
            'is_translatable' => false,
        ]);
    }

    public function test_it_can_set_translatable_setting(): void
    {
        $service = $this->app->make(SettingService::class);

        $service->set('site_title', [
            'en' => 'Welcome',
            'AR' => 'مرحباً',
        ]);

        $setting = $service->get('site_title');

        $this->assertTrue($setting->is_translatable);
        $this->assertSame('Welcome', $setting->resolved_value);
        $this->assertDatabaseHas('setting_translations', [
            'setting_id' => $setting->id,
            'locale' => 'en',
            'value' => 'Welcome',
        ]);
        $this->assertDatabaseHas('setting_translations', [
            'setting_id' => $setting->id,
            'locale' => 'ar',
            'value' => 'مرحباً',
        ]);
    }

    public function test_it_merges_groups_when_setting_values(): void
    {
        $service = $this->app->make(SettingService::class);

        $service->set('site_name', 'My Site', 'general');
        $service->set('site_name', 'My Site', ['general', 'seo']);

        $setting = $service->get('site_name');

        $this->assertSame(['general', 'seo'], $setting->groups);
    }

    public function test_it_can_set_many_settings(): void
    {
        $service = $this->app->make(SettingService::class);

        $service->setMany([
            'site_name' => 'My Site',
            'site_email' => 'admin@example.com',
            'phone_numbers' => ['+1234567890', '+0987654321'],
        ]);

        $this->assertSame('My Site', $service->get('site_name')->resolved_value);
        $this->assertSame('admin@example.com', $service->get('site_email')->resolved_value);
        $this->assertSame(['+1234567890', '+0987654321'], $service->get('phone_numbers')->resolved_value);
    }

    public function test_it_can_get_settings_by_group_as_key_value(): void
    {
        $service = $this->app->make(SettingService::class);

        $service->set('site_name', 'My Site', 'general');
        $service->set('site_email', 'admin@example.com', 'general');

        $values = $service->getByGroupAsKeyValue('general');

        $this->assertSame([
            'site_name' => 'My Site',
            'site_email' => 'admin@example.com',
        ], $values);
    }

    public function test_it_can_load_settings_from_configuration(): void
    {
        $service = $this->app->make(SettingService::class);

        $service->load([
            'translatable' => [
                'base' => [
                    'site_title' => [
                        'en' => 'Base Title',
                        'fr' => 'Titre de base',
                    ],
                ],
                'seo' => [
                    '_include' => ['base'],
                ],
            ],
            'fixed' => [
                'general' => [
                    'site_email' => 'admin@example.com',
                ],
                'seo' => [
                    '_include' => ['general'],
                ],
            ],
        ]);

        $titleSetting = $service->get('site_title');
        $emailSetting = $service->get('site_email');

        $this->assertSame('Base Title', $titleSetting->resolved_value);
        $this->assertSame(['base', 'seo'], $titleSetting->groups);

        $this->assertSame('admin@example.com', $emailSetting->resolved_value);
        $this->assertSame(['general', 'seo'], $emailSetting->groups);
    }

    public function test_repository_get_all_returns_collection(): void
    {
        $service = $this->app->make(SettingService::class);
        $service->setMany([
            'site_name' => 'My Site',
            'site_email' => 'admin@example.com',
        ]);

        $repository = $this->app->make(\Rawnoq\Settings\Repositories\SettingRepository::class);
        $settings = $repository->getAll();

        $this->assertCount(2, $settings);
    }
}
