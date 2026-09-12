<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('feature', function (string $featureCode) {
            $user = auth()->user();
            if (! $user) {
                return false;
            }

            return $user->hasFeature($featureCode);
        });

        Blade::if('hasanyfeature', function (array $featureCodes) {
            $user = auth()->user();
            if (! $user) {
                return false;
            }

            return $user->hasAnyFeature($featureCodes);
        });

        // Dynamically apply database-stored Mail / SMTP settings if present
        try {
            if (Schema::hasTable('settings')) {
                $mailMailer = Setting::getGlobal('mail_mailer');
                if (! empty($mailMailer)) {
                    config(['mail.default' => $mailMailer]);
                }

                $mailHost = Setting::getGlobal('mail_host');
                if (! empty($mailHost)) {
                    config(['mail.mailers.smtp.host' => $mailHost]);
                }

                $mailPort = Setting::getGlobal('mail_port');
                if (! empty($mailPort)) {
                    config(['mail.mailers.smtp.port' => (int) $mailPort]);
                }

                $mailUsername = Setting::getGlobal('mail_username');
                if (! empty($mailUsername)) {
                    config(['mail.mailers.smtp.username' => $mailUsername]);
                }

                $mailPassword = Setting::getGlobal('mail_password');
                if (! empty($mailPassword)) {
                    config(['mail.mailers.smtp.password' => $mailPassword]);
                }

                $mailEncryption = Setting::getGlobal('mail_encryption');
                if (! empty($mailEncryption)) {
                    config(['mail.mailers.smtp.encryption' => $mailEncryption === 'none' ? null : $mailEncryption]);
                }

                $fromAddress = Setting::getGlobal('mail_from_address');
                if (! empty($fromAddress)) {
                    config(['mail.from.address' => $fromAddress]);
                }

                $fromName = Setting::getGlobal('mail_from_name');
                if (! empty($fromName)) {
                    config(['mail.from.name' => $fromName]);
                }
            }
        } catch (\Throwable) {
            // DB not ready or not migrated yet
        }

        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $view->with('platformSettings', Setting::getAllGlobal());

                    $user = auth()->user();
                    $savedTheme = null;
                    $themeKey = 'admin';

                    if ($user) {
                        if ($user->isSuperAdmin()) {
                            $savedTheme = Setting::getGlobal('admin_theme_settings', null);
                            $themeKey = 'admin';
                        } elseif ($user->tenant_id) {
                            $savedTheme = Setting::get('theme_settings', null, $user->tenant_id);
                            $themeKey = 'gym_'.$user->tenant_id;
                        }
                    }

                    $view->with('savedThemeSettings', $savedTheme);
                    $view->with('themeStorageKey', $themeKey);
                }
            } catch (\Throwable $e) {
                $view->with('platformSettings', []);
                $view->with('savedThemeSettings', null);
                $view->with('themeStorageKey', 'admin');
            }
        });
    }
}
