<?php

namespace App\Providers;

use Illuminate\Routing\Route;
use Laravel\Passport\Passport;
use App\Http\Services\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Illuminate\Pagination\LengthAwarePaginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('strong_pass', function($attribute, $value, $parameters, $validator) {
            return is_string($value);
        });

        Passport::routes();

        /*ADD THIS LINES*/
        $this->commands([
            InstallCommand::class,
            ClientCommand::class,
            KeysCommand::class,
        ]);

        if (function_exists('bcscale')) {
            bcscale(8);
        }
        if(DB::connection()->getDatabaseName()) {
            if (Schema::hasTable('admin_settings')) {
                $adm_setting = allsetting();

                $capcha_site_key = isset($adm_setting['NOCAPTCHA_SITEKEY']) ? $adm_setting['NOCAPTCHA_SITEKEY'] : env('NOCAPTCHA_SITEKEY');
                $capcha_secret_key = isset($adm_setting['NOCAPTCHA_SECRET']) ? $adm_setting['NOCAPTCHA_SECRET'] : env('NOCAPTCHA_SECRET');

                config(['captcha.sitekey' => $capcha_site_key]);
                config(['captcha.secret' => $capcha_secret_key]);
            }
        }
        Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new Paginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
