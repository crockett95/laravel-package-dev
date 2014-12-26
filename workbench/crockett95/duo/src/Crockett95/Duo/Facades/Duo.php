<?php

namespace Crockett95\Duo\Facades;

use Illuminate\Support\Facades\Facade;

class Duo extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'duo';
    }

}
