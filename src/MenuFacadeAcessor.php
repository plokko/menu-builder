<?php

namespace plokko\MenuBuilder;

class MenuFacadeAcessor
{
    public function create()
    {
        return new MenuBuilder();
    }
}
