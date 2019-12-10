<?php


namespace airBnB\Http\Controller;


use airBnB\System\Http\Controller;
use airBnB\System\Session\Session;

class PageController extends Controller
{
    public function index(): void
    {
        echo $this->twig->render( 'visitor/home.twig', [
            'title' => 'Bienvenue sur AirBnB',
            'form_status' => Session::get( Session::FORM_STATUS )
            ]);
    }
}