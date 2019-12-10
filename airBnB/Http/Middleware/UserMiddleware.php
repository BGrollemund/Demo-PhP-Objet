<?php


namespace airBnB\Http\Middleware;


use airbnb\Airbnb;
use airBnB\Database\Model\Role;
use airBnB\System\Session\Session;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

use MiladRahimi\PhpRouter\Middleware;


class UserMiddleware implements Middleware
{
    public function handle( ServerRequestInterface $request, Closure $next )
    {
        $session_user = Session::get( Session::USER );

        $granted_roles = [
            Role::RENTER,
            Role::USER
        ];

        if( in_array( (int) $session_user->role_id, $granted_roles ) )
            return $next( $request );

        $router = Airbnb::app()->getRouter();

        return new RedirectResponse( $router->url( 'home' ) );
    }
}