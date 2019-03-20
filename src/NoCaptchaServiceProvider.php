<?php

namespace Misechow\NoCaptcha;

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
    public function boot()
    {
        $app = $this->app;

        $this->bootConfig();

        $app['validator']->extend('captcha', function ($attribute, $value) use ($app) {
            return $app['captcha']->verifyResponse($value, $app['request']->getClientIp());
        });

        if ($app->bound('form')) {
            $app['form']->macro('captcha', function ($attributes = []) use ($app) {
                return $app['captcha']->display($attributes, $app->getLocale());
            });
        }
    }

    /**
     * Booting configure.
     */
    protected function bootConfig()
    {
        $path = __DIR__.'/config/config.php';

        $this->mergeConfigFrom($path, 'reCaptcha');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('reCaptcha.php')]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('captcha', function ($app) {
            if ($app['config']['reCaptcha.server-get-config']) {
                $googleCaptcha = \App\Components\CaptchaVerify::googleCaptchaGetConfig();
                return new NoCaptcha(
                    $googleCaptcha['secret'],
                    $googleCaptcha['sitekey'],
                    $googleCaptcha['options']
                );
            } else {
                return new NoCaptcha(
                    $app['config']['reCaptcha.secret'],
                    $app['config']['reCaptcha.sitekey'],
                    $app['config']['reCaptcha.options']
                );
            }
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['captcha'];
    }
}
