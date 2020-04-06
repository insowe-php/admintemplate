<?php

namespace Insowe\AdminTemplate\Facades;

use Illuminate\Support\Facades\Facade;

class AdminTemplate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'admintemplate';
    }
}
