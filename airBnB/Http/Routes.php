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
                ->post( '/register', 'RegisterController@register')

                ->name('login')
                ->get('/connexion', 'AuthController@index')

                ->name('auth')
                ->post('/auth', 'AuthController@auth')

            ;
        };
    }

    public static function renter(): Closure
    {
        return function ( Router $router )
        {
            $router
                ->name('my-rent-list')
                ->get('/mes-annonces', 'RenterController@index')

                ->name('my-rent-add')
                ->get('/ajouter-annonce', 'RenterController@add')

                ->name('my-rent-insert')
                ->post('/ajouter-annonce', 'RenterController@insert')

                ->name('my-rent-edit')
                ->get('/modifier-annonce', 'RenterController@edit')

                ->name('my-rent-edit')
                ->post('/modifier-annonce', 'RenterController@edit')

                ->name('my-rent-update')
                ->post('/modification-annonce', 'RenterController@update')

                ->name('my-rent-manager')
                ->get('/gestion-annonces', 'RenterController@booked')

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
                ->name('rent-book')
                ->post('/locations', 'UserController@book')

                ->name('rent-detail')
                ->get('/locations/{id}', 'UserController@show')

                ->name('rent-list')
                ->get('/locations', 'UserController@index')

                ->name('rent-manager')
                ->get('/mes-locations', 'UserController@booked')
            ;
        };
    }
}