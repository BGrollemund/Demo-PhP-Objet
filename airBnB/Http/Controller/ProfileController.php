<?php


namespace airBnB\Http\Controller;


use airBnB\System\Http\Controller;

class ProfileController extends Controller
{
    public function index(): void
    {
        echo $this->twig->render( 'user/profile.twig', ['title' => 'Votre Profil'] );
    }
}