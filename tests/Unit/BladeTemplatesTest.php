<?php

namespace YourName\SafeEmailNotifications\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Framework-agnostic checks: our Blade templates exist and contain expected structure
 * (no message body, include "View conversation"). Actual rendering is tested in FreeScout.
 */
class BladeTemplatesTest extends TestCase
{
    private string $viewsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewsPath = dirname(__DIR__, 2) . '/resources/views/emails/';
    }

    /** @test */
    public function html_template_exists_and_has_no_message_body_placeholder(): void
    {
        $path = $this->viewsPath . 'notification.blade.php';
        self::assertFileExists($path);
        $content = file_get_contents($path);
        self::assertStringNotContainsString('$thread->body', $content, 'Template must not output raw thread body');
        self::assertStringContainsString('view_conversation', $content, 'Template should use view_conversation message key or link');
    }

    /** @test */
    public function text_template_exists_and_has_no_message_body_placeholder(): void
    {
        $path = $this->viewsPath . 'notification_text.blade.php';
        self::assertFileExists($path);
        $content = file_get_contents($path);
        self::assertStringNotContainsString('Html2Text', $content, 'Template must not convert thread body to text');
        self::assertStringNotContainsString('$thread->body', $content, 'Template must not output raw thread body');
        self::assertStringContainsString('view_conversation', $content);
    }
}
