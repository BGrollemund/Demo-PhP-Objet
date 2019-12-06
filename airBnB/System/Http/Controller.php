<?php


namespace airBnB\System\Http;


use Airbnb\Airbnb;

class Controller
{
    protected $twig = null;

    public function __construct()
    {
        $this->twig = Airbnb::app()->getTwig();
    }
}