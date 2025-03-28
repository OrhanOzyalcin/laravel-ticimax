<?php

namespace LaravelTicimax\Ticimax\Facades;

use Illuminate\Support\Facades\Facade;

class TicimaxFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ticimax';
    }
}