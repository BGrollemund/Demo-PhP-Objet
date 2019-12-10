<?php


namespace airBnB\Http\Middleware;


use airbnb\Airbnb;
use airBnB\System\Session\Session;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

use MiladRahimi\PhpRouter\Middleware;

class AuthMiddleware implements Middleware
{
    public function handle( ServerRequestInterface $request, Closure $next )
    {
        $session_user = Session::get( Session::USER );

        if( ! is_null( $session_user ) )
            return $next( $request );

        $router = Airbnb::app()->getRouter();

        return new RedirectResponse( $router->url( 'login' ) );
    }
}