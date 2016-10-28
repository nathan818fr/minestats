<?php

namespace MineStats\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('order_in', function ($attribute, $value, $parameters, $validator) {
            if (starts_with($value, '-')) {
                $value = substr($value, 1);
            }

            return in_array($value, $parameters);
        });

        Validator::extend('numeric_array', function ($attribute, $values, $parameters) {
            if (!is_array($values)) {
                return false;
            }
            foreach ($values as $v) {
                if (!is_numeric($v)) {
                    return false;
                }
            }

            return true;
        });

        Validator::extend('host', function ($attribute, $value, $parameters) {
            if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                return true;
            }

            // Ref: http://stackoverflow.com/questions/106179/regular-expression-to-match-dns-hostname-or-ip-address
            return preg_match('#^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$#',
                $value);
        });

        /*
         * Validate simple datetime represended like "YYYY-MM-DD hh:mm:ss"
         */
        Validator::extend('datetime', function ($attribute, $value, $parameters) {
            $r = date_parse_from_format("Y-m-d H:i:s", $value);

            return ($r['error_count'] === 0 && $r['warning_count'] === 0);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        require base_path('routes/breadcrumbs.php');
    }
}
