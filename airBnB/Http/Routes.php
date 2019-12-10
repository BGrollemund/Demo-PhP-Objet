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

                ->name( 'register' )
                ->post( '/register', 'RegisterController@register', [])

                ->name('login')
                ->get('/connexion', 'AuthController@index')

                ->name('auth')
                ->post('/auth', 'AuthController@auth', [])

            ;
        };
    }

    public static function renter(): Closure
    {
        return function ( Router $router )
        {
            $router
                ->name('my-rent-list')
                ->get('/mes-locations', 'RenterController@index')

            ;
        };
    }

    public static function subscriber(): Closure
    {
        return function ( Router $router )
        {
            $router
                ->name( 'logout' )
                ->get( '/deconnexion', 'AuthController@logout' )
            ;
        };
    }

    public static function user(): Closure
    {
        return function ( Router $router )
        {
            $router
                ->name('rent-list')
                ->get('/locations', 'UserController@index')

            ;
        };
    }
}