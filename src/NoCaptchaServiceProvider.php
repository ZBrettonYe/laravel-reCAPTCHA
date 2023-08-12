<?php

namespace ZBrettonYe\NoCaptcha;

use Illuminate\Support\ServiceProvider;

class NoCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $app = $this->app;

        $this->bootConfig();

        $app['validator']->extend('NoCaptcha', function ($attribute, $value) use ($app) {
            return $app['NoCaptcha']->verifyResponse($value, $app['request']->getClientIp());
        });

        if ($app->bound('form')) {
            $app['form']->macro('NoCaptcha', function ($attributes = []) use ($app) {
                return $app['NoCaptcha']->display($attributes, $app->getLocale());
            });
        }
    }

    /**
     * Booting configure.
     */
    protected function bootConfig(): void
    {
        $path = __DIR__.'/config/recaptcha.php';

        $this->mergeConfigFrom($path, 'NoCaptcha');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('NoCaptcha.php')]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton('NoCaptcha', function ($app) {
            if ($app['config']['NoCaptcha.get_config_method']) {
                $googleCaptcha = \App\Components\CaptchaVerify::googleCaptchaGetConfig();

                return new NoCaptcha(
                    $googleCaptcha['secret'],
                    $googleCaptcha['sitekey'],
                    $googleCaptcha['options']
                );
            }

            return new NoCaptcha(
                $app['config']['NoCaptcha.secret'],
                $app['config']['NoCaptcha.sitekey'],
                $app['config']['NoCaptcha.options']
            );
        });

        $this->app->alias('NoCaptcha', NoCaptcha::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['NoCaptcha'];
    }
}
