<?php


namespace airbnb;


use airBnB\Http\Routes;

use airBnB\TwigExtension\HTMLUtils;
use airBnB\TwigExtension\URLUtils;
use MiladRahimi\PhpRouter\Router;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Airbnb
{
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

        $this->loadTwig();
        $this->loadRouter();
    }

    private function loadRouter(): void
    {
        $this->router = new Router();

        $attr_visitor = [
            'namespace' => 'AirBnB\Http\Controller'
        ];

        $this->router
            ->group( $attr_visitor, Routes::visitor() );

        // TODO: Gérer erreur 404
        $this->router->dispatch();
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