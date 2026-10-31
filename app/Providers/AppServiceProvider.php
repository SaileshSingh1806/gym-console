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

        try {
            $storageDirs = [
                storage_path('app/public/members/photos'),
                storage_path('app/public/members/documents'),
                storage_path('app/public/logos'),
                storage_path('app/public/trainers'),
                storage_path('app/public/signatures'),
                storage_path('app/public/uploads/branding'),
            ];
            foreach ($storageDirs as $dir) {
                if (! file_exists($dir)) {
                    @mkdir($dir, 0775, true);
                }
            }

            $publicStorageLink = public_path('storage');
            $storageTarget = storage_path('app/public');
            if (! file_exists($publicStorageLink) && function_exists('symlink')) {
                @symlink($storageTarget, $publicStorageLink);
            }
        } catch (\Throwable) {
            // Silently continue if host restricts symlink
        }

        View::composer('*', function ($view) {
            try {
                $platform = Schema::hasTable('settings') ? Setting::getAllGlobal() : [];

                if (empty($platform['logo_url']) || (str_starts_with((string) $platform['logo_url'], '/storage/') && ! file_exists(public_path(ltrim($platform['logo_url'], '/'))))) {
                    $platform['logo_url'] = asset('images/gymdeskhub_horizontal_dark.png');
                }
                $platform['logo_url_dark'] = asset('images/gymdeskhub_horizontal_dark.png');
                $platform['logo_url_light'] = asset('images/gymdeskhub_horizontal_light.png');
                $platform['logo_icon'] = asset('images/gymdeskhub_icon_dark.png');

                if (empty($platform['favicon_url']) || (str_starts_with((string) $platform['favicon_url'], '/storage/') && ! file_exists(public_path(ltrim($platform['favicon_url'], '/'))))) {
                    $platform['favicon_url'] = asset('favicon.png');
                }
                if (empty($platform['app_name'])) {
                    $platform['app_name'] = 'GymDeskHub';
                }

                $view->with('platformSettings', $platform);

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
            } catch (\Throwable $e) {
                $view->with('platformSettings', [
                    'app_name' => 'GymDeskHub',
                    'logo_url' => asset('images/logo.png'),
                    'favicon_url' => asset('favicon.ico'),
                ]);
                $view->with('savedThemeSettings', null);
                $view->with('themeStorageKey', 'admin');
            }
        });
    }
}
