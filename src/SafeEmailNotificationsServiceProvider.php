<?php

namespace Freescout\SafeEmailNotifications;

use Illuminate\Support\ServiceProvider;

class SafeEmailNotificationsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'safeemailnotifications');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'safeemailnotifications');

        \Eventy::addFilter('email.user_notification.template_name_html', function ($default) {
            return 'safeemailnotifications::emails.notification';
        }, 20, 1);

        \Eventy::addFilter('email.user_notification.template_name_text', function ($default) {
            return 'safeemailnotifications::emails.notification_text';
        }, 20, 1);

        \Eventy::addFilter('email.user_notification.subject', function ($default, $conversation) {
            $result = (new SubjectBuilder())->build($conversation);
            if ($result === null) {
                return $default;
            }
            return $result->format(function ($key, $replace) {
                return __($key, $replace);
            });
        }, 20, 2);

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/safeemailnotifications'),
        ], 'safe-email-notifications-lang');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
