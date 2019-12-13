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

    public function getImageUrl( $value ): string
    {}

    public function getRouteUrl( string $name, array $params = [] ): string
    {
        $router = Airbnb::app()->getRouter();

        return $router->url( $name, $params );
    }

    public static function get404(): string
    {
        // Lance le cache de sortie (output buffer)
        // Après cette ligne PHP va écrire dans le cache au lieu
        // d'écrire sur la page
        ob_start();

        include ROOT_PATH . 'views' . DS . 'errors' . DS . '404.php';

        // vide le contenu du cache en sortie de la méthode
        return ob_get_clean();
    }
}