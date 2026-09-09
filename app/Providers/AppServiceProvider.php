<?php

namespace App\Providers;

use App\Models\Setting;
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
