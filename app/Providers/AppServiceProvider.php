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
    }
}
