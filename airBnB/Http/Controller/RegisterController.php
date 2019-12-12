<?php


namespace airBnB\Http\Controller;


use airbnb\Airbnb;
use airBnB\Database\Model\Profile;
use airBnB\Database\Model\User;
use airBnB\Database\Repository\RepositoryManager;
use airBnB\System\Http\Auth;
use airBnB\System\Http\Controller;
use airBnB\System\Http\Register;
use airBnB\System\Session\FormStatus;
use airBnB\System\Session\Session;

use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;

class RegisterController extends Controller
{
    public function register( ServerRequest $request ): RedirectResponse
    {
        $post_data = $request->getParsedBody();

        $email = 'email';
        $password = 'password';
        $password_check = 'password_check';
        $username = 'username';
        $birth_date = 'birth_date';
        $city = 'city';
        $country = 'country';
        $is_renter = 'is_renter';

        $input_email = $post_data[ $email ] ?? null;
        $input_password = $post_data[ $password ] ?? null;
        $input_password_check = $post_data[ $password_check ] ?? null;
        $input_username = $post_data[ $username ] ?? null;
        $input_birth_date = $post_data[ $birth_date ] ?? null;
        $input_city = $post_data[ $city ] ?? null;
        $input_country = $post_data[ $country ] ?? null;
        $input_is_renter = $post_data[ $is_renter ] ?? null;

        $router = Airbnb::app()->getRouter();

        if( is_null($input_email) || is_null($input_password) || is_null($input_password_check) || is_null($input_username) || is_null($input_birth_date) || is_null($input_city) || is_null($input_country)|| is_null($input_is_renter) )
            return new RedirectResponse( $router->url('home') );

        // Validation de la saisie
        $check_result = Airbnb::app()->getRegister()->checkRegister( $post_data );

        if( $check_result === 0 ) {
            Session::set( Session::FORM_STATUS, null );

            // Insertion dans la bdd
            $repo = RepositoryManager::manager();

            $role_repo = $repo->roleRepository();
            $role_id = $role_repo->getIdByLabel( $input_is_renter );

            if( $role_id === 0 ) {
                // TODO: erreur role (pas normal), logger
            }

            // TODO: fonction modif Date
            $explode_birth_date = explode('/', $input_birth_date );
            $reverse_birth_date = array_reverse( $explode_birth_date );
            $new_birth_date = implode('/', $reverse_birth_date );

            $post_data[ $birth_date ] =  $new_birth_date;

            $profile_id = $repo->profileRepository()->insert( new Profile( $post_data ) );

            if( $profile_id === 0 ) {
                // TODO: erreur d'insertion
            }

            $post_data[ 'role_id' ] = $role_id;
            $post_data[ 'profile_id' ] = $profile_id;
            $post_data [ $password ] = Auth::hashData( $post_data[ $password ] );

            $success = $repo->userRepository()->insert( new User( $post_data ) );

            if( $success === 0 ) {
                // TODO: erreur d'insertion
            }

            $user = $repo->userRepository()->getByEmail( $post_data[ $email ] );

            $user->password = null;

            Session::set( Session::USER, $user );

            return new RedirectResponse( $router->url( 'my-rent-list' ) );
        }

        // Gestion des erreurs du formulaire
        $form_status = new FormStatus();

        // Erreurs
        switch( $check_result ) {
            case Register::USERNAME_MISSING:
                $form_status->addError( $username, 'Veuillez indiquer votre nom d\'utilisateur.' );
                break;
            case Register::USERNAME_EXIST:
                $form_status->addError( $username, 'Ce nom d\'utilisateur est déjà utilisé.' );
                break;
            case Register::EMAIL_MISSING:
                $form_status->addError( $email, 'Veuillez indiquer votre adresse email.' );
                break;
            case Register::EMAIL_EXIST:
                $form_status->addError( $email, 'Cet email est déjà utilisé.' );
                break;
            case Register::BIRTH_DATE_MISSING:
                $form_status->addError( $birth_date, 'Veuillez indiquer votre date de naissance.' );
                break;
            case Register::BIRTH_DATE_BAD:
                $form_status->addError( $birth_date, 'Veuillez respecter le format jj/mm/aaa.' );
                break;
            case Register::CITY_MISSING:
                $form_status->addError( $city, 'Veuillez indiquer votre ville.' );
                break;
            case Register::COUNTRY_MISSING:
                $form_status->addError( $country, 'Veuillez indiquer votre pays.' );
                break;
            case Register::PASSWORD_MISSING:
                $form_status->addError( $password, 'Veuillez choisir votre mot de passe.' );
                break;
            case Register::PASSWORD_CHECK_MISSING:
                $form_status->addError( $password_check, 'Veuillez retaper votre mot de passe.' );
                break;
            case Register::PASSWORD_CHECK_BAD:
                $form_status->addError( $password_check, 'Veuillez taper le même mot de passe.' );
                break;

            default:
                // SHOULDDO: Log de l'erreur car anomalie
                break;
        }

        // Valeurs
        if( is_null( $form_status->getError( $username ) ) )
            $form_status->addValue( $username, $input_username );

        if( is_null( $form_status->getError( $email ) ) )
            $form_status->addValue( $email, $input_email );

        if( is_null( $form_status->getError( $birth_date ) ) )
            $form_status->addValue( $birth_date, $input_birth_date );

        if( is_null( $form_status->getError( $city ) ) )
            $form_status->addValue( $city, $input_city );

        if( is_null( $form_status->getError( $country ) ) )
            $form_status->addValue( $country, $input_country );

        if( is_null( $form_status->getError( $password ) ) )
            $form_status->addValue( $password, $input_password );

        if( is_null( $form_status->getError( $password_check ) ) )
            $form_status->addValue( $password_check, $input_password_check );

        $form_status->addValue( $is_renter, $input_is_renter );

        Session::set( Session::FORM_STATUS, $form_status );

        return new RedirectResponse( $router->url( 'home' ) );
    }
}