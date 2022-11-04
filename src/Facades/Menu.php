<?php

namespace plokko\MenuBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use plokko\MenuBuilder\MenuBuilder;

class Menu extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MenuBuilder::class;
    }

}
