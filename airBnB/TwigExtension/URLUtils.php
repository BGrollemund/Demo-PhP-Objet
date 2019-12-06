<?php


namespace airBnB\TwigExtension;


use Airbnb\Airbnb;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class URLUtils extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [ $this, 'getAssetsUrl'] ),
            new TwigFunction('route', [ $this, 'getRouteUrl'] ),
        ];
    }

    public function getAssetsUrl( $value ): string
    {
        return sprintf(
            '%s://%s/assets/%s',
            $_SERVER['REQUEST_SCHEME'],
            $_SERVER['HTTP_HOST'],
            $value
        );
    }

    public function getRouteUrl( string $name, array $params = [] ): string
    {
        $router = Airbnb::app()->getRouter();

        return $router->url( $name, $params );
    }
}