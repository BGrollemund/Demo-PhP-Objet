<?php


namespace airBnB\Http\Controller;


use airbnb\Airbnb;
use airBnB\Database\Model\Role;
use airBnB\System\Http\Auth;
use airBnB\System\Http\Controller;
use airBnB\System\Session\FormStatus;
use airBnB\System\Session\Session;

use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;

class AuthController extends Controller
{
    public static function getRedirectRoute( int $role_id ): string
    {
        $redirect_route = 'home';

        switch( $role_id ) {
            case Role::RENTER:
                $redirect_route = 'my-rent-list';
                break;

            case Role::USER:
                $redirect_route = 'rent-list';
                break;

            // Si jamais le rôle de l'utilisateur n'est pas géré par l'application
            // On doit adopter un comportement lisible pour l'utilisateur
            // - Déconnexion
            // - Renvoi en page d'accueil
            // Tout en gérant des logs à destination des développeurs en phase de débug
            default:
                Session::set( Session::USER, null );
                // TODO: gérer des logs pour signaler l'incohérence du rôle
                break;
        }

        return $redirect_route;
    }

    public function auth( ServerRequest $request )
    {
        $post_data = $request->getParsedBody();

        $name_email = 'email';
        $name_password = 'password';

        $input_email = $post_data[ $name_email ] ?? null;
        $input_password = $post_data[ $name_password ] ?? null;

        $router = Airbnb::app()->getRouter();

        if( is_null($input_email) || is_null($input_password) )
            // TODO: log mauvais formulaire
            return new RedirectResponse( $router->url('home') );

        // TODO: Validation de la saisie (format de l'email, etc.)

        $check_result = Airbnb::app()->getAuth()->checkLogin( $input_email, $input_password );

        $redirect_route = 'login';

        if( $check_result > 0 ) {
            Session::set( Session::FORM_STATUS, null );

            $redirect_route = self::getRedirectRoute( $check_result );

            return new RedirectResponse( $router->url( $redirect_route ) );
        }

        // Gestion des erreurs du formulaire
        $form_status = new FormStatus();

        // Erreurs
        switch( $check_result ) {
            case Auth::ERROR_EMAIL_MISSING:
                $form_status->addError( $name_email, 'Veuillez renseigner une adresse email.' );
                break;
            case Auth::ERROR_EMAIL_BAD:
                $form_status->addError( $name_email, 'Adresse email inconnue.' );
                break;
            case Auth::ERROR_PASSWORD_MISSING:
                $form_status->addError( $name_password, 'Veuillez renseigner un mot de passe.' );
                break;
            case Auth::ERROR_PASSWORD_BAD:
                $form_status->addError( $name_password, 'Mot de passe incorrect.' );
                break;
            default:
                // SHOULDDO: Log de l'erreur car anomalie
                break;
        }

        // Valeurs
        if( is_null($form_status->getError( $name_email ) ) )
            $form_status->addValue( $name_email, $input_email );

        if( is_null($form_status->getError( $name_password ) ) )
            $form_status->addValue( $name_password, $input_password );

        Session::set( Session::FORM_STATUS, $form_status );

        return new RedirectResponse( $router->url( $redirect_route ) );
    }

    public function index(): void
    {
        echo $this->twig->render( 'visitor/login.twig', [
            'title' => 'Connexion',
            'form_status' => Session::get( Session::FORM_STATUS )
        ]);
    }

    public function logout()
    {
        Session::unset();
        $router = Airbnb::app()->getRouter();

        return new RedirectResponse( $router->url('home') );
    }
}