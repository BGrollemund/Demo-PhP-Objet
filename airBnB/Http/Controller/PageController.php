<?php


namespace airBnB\Http\Controller;


use airBnB\System\Http\Controller;
use airBnB\System\Session\Session;

class PageController extends Controller
{
    public function aboutUs(): void
    {
        echo $this->twig->render( 'visitor/about.twig', [
            'title' => 'A propos de nous',
            ]);
    }

    public function index(): void
    {
        echo $this->twig->render( 'visitor/home.twig', [
            'title' => 'Bienvenue sur AirBnB',
            'form_status' => Session::get( Session::FORM_STATUS )
        ]);
    }

    public function legalMentions(): void
    {
        echo $this->twig->render( 'visitor/legal.twig', [
            'title' => 'Mentions Légales'
        ]);
    }

    public function termsUse(): void
    {
        echo $this->twig->render( 'visitor/cgu.twig', [
            'title' => 'Conditions générales d\'utilisation'
        ]);
    }
}