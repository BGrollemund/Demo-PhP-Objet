<?php


namespace airbnb;


use airBnB\Http\Middleware\AuthMiddleware;
use airBnB\Http\Middleware\RenterMiddleware;
use airBnB\Http\Middleware\UserMiddleware;
use airBnB\Http\Middleware\VisitorMiddleware;
use airBnB\Http\Routes;
use airBnB\System\Http\Auth;
use airBnB\TwigExtension\HTMLUtils;
use airBnB\TwigExtension\URLUtils;

use Throwable;
use Zend\Diactoros\Response\HtmlResponse;

use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Router;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Airbnb
{
    private $auth = null;
    public function getAuth(): ?Auth { return $this->auth; }

    private $router = null;
    public function getRouter(): ?Router  { return $this->router; }

    private $twig = null;
    public function getTwig(): ?Environment  { return $this->twig; }

    private static $instance = null;

    public static function app(): self
    {
        if( is_null(self::$instance) )
            self::$instance = new Airbnb();

        return self::$instance;
    }

    public function start(): void
    {
        session_start();

        $this->loadAuth();
        $this->loadTwig();
        $this->loadRouter();
    }

    private function loadAuth(): void
    {
        $this->auth = new Auth();
    }

    private function loadRouter(): void
    {
        $this->router = new Router();

        $attr_renter = [
            'namespace' => 'AirBnB\Http\Controller',
            'middleware' => [ AuthMiddleware::class, RenterMiddleware::class ]
        ];

        $attr_subscriber = [
            'namespace' => 'AirBnB\Http\Controller'
        ];

        $attr_user = [
            'namespace' => 'AirBnB\Http\Controller',
            'middleware' => [ AuthMiddleware::class, UserMiddleware::class ]
        ];

        $attr_visitor = [
            'namespace' => 'AirBnB\Http\Controller',
            'middleware' => [ VisitorMiddleware::class ]
        ];

        $this->router
            ->group( $attr_renter, Routes::renter() )
            ->group( $attr_subscriber, Routes::subscriber() )
            ->group( $attr_user, Routes::user() )
            ->group( $attr_visitor, Routes::visitor() )

        ;

        try {
            $this->router->dispatch();
        }
        catch( RouteNotFoundException $e ) {
            $response = new HtmlResponse(URLUtils::get404(), 404);
            $this->router->getPublisher()->publish($response);
        }
        /*
        catch( Throwable $e ) {
            $response = new HtmlResponse( 'Server Error.', 500 );
            $this->router->getPublisher()->publish($response);
        }
        */
    }

    private function loadTwig(): void
    {
        $loader = new FilesystemLoader( [
            ROOT_PATH . 'views',
            ROOT_PATH . 'views/renter',
            ROOT_PATH . 'views/user',
            ROOT_PATH . 'views/visitor'
        ]);
        $this->twig = new Environment( $loader, [
            // 'cache' => ROOT_PATH . 'cache'
        ]);

        $this->twig->addExtension( new URLUtils() );
        $this->twig->addExtension( new HTMLUtils() );
    }

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}