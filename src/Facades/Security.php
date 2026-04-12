<?php

declare(strict_types = 1);

namespace Centrex\Security\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\Security\Security
 */
class Security extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Centrex\Security\Security::class;
    }
}
