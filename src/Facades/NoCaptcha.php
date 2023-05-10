<?php

namespace ZBrettonYe\NoCaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class NoCaptcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'NoCaptcha';
    }
}
