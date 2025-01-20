<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Schema;

class LoginSystemsConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (env('DB_HOST') == 'image_build') {
            return;
        }
        if (!Schema::hasTable('general_options')) {
            return;
        }

        Cache::flush();
        if (!Cache::has('parameters_login_systems')) {
            $parametersLoginSystems = GeneralOptionsModel::whereIn('option_name', [
                'google_login_active',
                'google_client_id',
                'google_client_secret',
                'facebook_login_active',
                'facebook_login_active',
                'facebook_client_id',
                'facebook_client_secret',
                'twitter_login_active',
                'twitter_client_id',
                'twitter_client_secret',
                'linkedin_login_active',
                'linkedin_client_id',
                'linkedin_client_secret'
            ])->get()->toArray();

            $config = [];

            foreach ($parametersLoginSystems as $parameterLoginSystem) {
                $config[$parameterLoginSystem['option_name']] = $parameterLoginSystem['option_value'];
            }

            Cache::put('parameters_login_systems', $config, 60 * 24); // Cache for 24 hours
        }

        $parametersLoginSystems = Cache::get('parameters_login_systems');

        config([
            'services.google.client_id' => $parametersLoginSystems['google_client_id'],
            'services.google.client_secret' => $parametersLoginSystems['google_client_secret'],
            'services.google.redirect' => env('GOOGLE_REDIRECT_URL'),

            'services.facebook.client_id' => $parametersLoginSystems['facebook_client_id'],
            'services.facebook.client_secret' => $parametersLoginSystems['facebook_client_secret'],
            'services.facebook.redirect' => env('FACEBOOK_REDIRECT_URL'),

            'services.twitter.client_id' => $parametersLoginSystems['twitter_client_id'],
            'services.twitter.client_secret' => $parametersLoginSystems['twitter_client_secret'],
            'services.twitter.redirect' => env('TWITTER_REDIRECT_URL'),

            'services.linkedin-openid.client_id' => $parametersLoginSystems['linkedin_client_id'],
            'services.linkedin-openid.client_secret' => $parametersLoginSystems['linkedin_client_secret'],
            'services.linkedin-openid.redirect' => env('LINKEDIN_REDIRECT_URL'),
        ]);
    }
}
