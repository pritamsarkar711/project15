<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guards the bugs that took huvanti.com down with a site-wide HTTP 500.
 */
class Error500RegressionTest extends TestCase
{
    public function test_admin_login_post_route_is_not_double_prefixed(): void
    {
        $source = file_get_contents(base_path('routes/web.php'));
        $this->assertIsString($source);
        $this->assertStringContainsString("->name('login.post')", $source);
        $this->assertStringNotContainsString("->name('admin.login.post')", $source);
    }

    public function test_home_controller_defines_search_before_compact(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Frontend/HomeController.php'));
        $this->assertIsString($source);
        $this->assertMatchesRegularExpression(
            '/\$search\s*=\s*\$request->query\(\'q\'\);/',
            $source
        );
    }

    public function test_layouts_use_safe_vite_helper(): void
    {
        $app = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $login = file_get_contents(resource_path('views/admin/auth/login.blade.php'));
        $this->assertStringContainsString('ViteAssets::tags', (string) $app);
        $this->assertStringContainsString('ViteAssets::tags', (string) $login);
        $this->assertStringNotContainsString('@vite(', (string) $app);
        $this->assertStringNotContainsString('@vite(', (string) $login);
    }
}
