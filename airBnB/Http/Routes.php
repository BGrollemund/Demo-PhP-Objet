<?php


namespace airBnB\Http;


use Closure;

use MiladRahimi\PhpRouter\Router;

abstract class Routes
{
    private $router = null;

    public function __construct( Router $router ) { $this->router = $router; }

    public static function visitor(): Closure
    {
        return function ( Router $router )
        {
            $router
                ->name('home')
                ->get('/', 'PageController@index')

            ;
        };
    }
}