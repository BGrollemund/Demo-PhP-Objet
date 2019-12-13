<?php


namespace airBnB\TwigExtension;


use airBnB\System\Settings;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HTMLUtils extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('htmlTitle', [ $this, 'getHTMLTitle'] ),
            new TwigFunction('siteFullName', [ $this, 'getSiteFullName'] )
        ];
    }

    public function getHTMLTitle( $value ): string
    {
        return sprintf(
            '%s - %s',
            $value,
            Settings::instance()->get('site_full_name')
        );
    }

    public function getSiteFullName(): string
    {
        return Settings::instance()->get('site_full_name');
    }
}