<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;
use App\Services\WhatsappService;
use App\Services\EmailService;
use App\Services\SmsService;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register WhatsApp Service as singleton
        $this->app->singleton(WhatsappService::class, function ($app) {
            return new WhatsappService();
        });

        // Register Email Service as singleton
        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService();
        });

        // Register SMS Service as singleton
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });

        // Register Notification Service with dependencies
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(WhatsappService::class),
                $app->make(EmailService::class),
                $app->make(SmsService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../../config/notification.php' => config_path('notification.php'),
        ], 'notification-config');
    }
}
