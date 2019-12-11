<?php


namespace airBnB\Http\Controller;


use airbnb\Airbnb;
use airBnB\Database\Model\Booking;
use airBnB\Database\Repository\RepositoryManager;
use airBnB\System\Http\Controller;

use airBnB\System\Http\Register;
use airBnB\System\Session\FormStatus;
use airBnB\System\Session\Session;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;

class UserController extends Controller
{
    public function book( ServerRequest $request ): RedirectResponse
    {
        $post_data = $request->getParsedBody();

        $user_id = 'user_id';
        $renting_id = 'renting_id';
        $start_date = 'start_date';
        $end_date = 'end_date';

        $input_user_id = $post_data[ $user_id ] ?? null;
        $input_renting_id = $post_data[ $renting_id ] ?? null;
        $input_start_date = $post_data[ $start_date ] ?? null;
        $input_end_date = $post_data[ $end_date ] ?? null;

        $router = Airbnb::app()->getRouter();

        // TODO: manque un champ, logger
        if( is_null($input_user_id) || is_null($input_renting_id) || is_null($input_start_date) || is_null($input_end_date) )
            return new RedirectResponse( $router->url('home') );

        // Validation de la saisie
        $check_result = Airbnb::app()->getRegister()->checkBookingFields( $post_data );

        if( $check_result === 0 ) {
            Session::set( Session::FORM_STATUS, null );

            // Insertion dans la bdd
            $booking_repo = RepositoryManager::manager()->bookingRepository();

            $booking_success = $booking_repo->insert( new Booking( $request->getParsedBody() ) );

            if( $booking_success === 0 ) {
                // TODO: erreur d'insertion
            }

            return new RedirectResponse( $router->url( 'rent-list' ) );
        }

        // Gestion des erreurs du formulaire
        $form_status = new FormStatus();

        // Erreurs
        switch( $check_result ) {
            case Register::START_DATE_MISSING:
                $form_status->addError( $start_date, 'Veuillez indiquer la date de début.' );
                break;
              case Register::START_DATE_BAD:
                $form_status->addError( $start_date, 'Veuillez respecter le format jj/mm/aaa.' );
                break;
            case Register::END_DATE_MISSING:
                $form_status->addError( $end_date, 'Veuillez indiquer la date de fin.' );
                break;
            case Register::END_DATE_BAD:
                $form_status->addError( $end_date, 'Veuillez respecter le format jj/mm/aaa.' );
                break;
            case Register::END_DATE_NOT_MATCH:
                $form_status->addError( $end_date, 'La date de fin doit être après la date de début.' );
                break;

            default:
                // SHOULDDO: Log de l'erreur car anomalie
                break;
        }

        // Valeurs
        if( is_null( $form_status->getError( $start_date ) ) )
            $form_status->addValue( $start_date, $input_start_date );

        if( is_null( $form_status->getError( $end_date ) ) )
            $form_status->addValue( $end_date, $input_end_date );

        Session::set( Session::FORM_STATUS, $form_status );

        return new RedirectResponse( $router->url( 'rent-detail', [ 'id' => $input_renting_id ] ) );
    }

    public function booked(): void
    {
        $user_id = $_SESSION['user']->id;

        $role_label = RepositoryManager::manager()->roleRepository()->getById( $_SESSION['user']->role_id )->label;

        $booking_repo = RepositoryManager::manager()->bookingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();

        $bookings_data = $booking_repo->findByUserId( $user_id );

        foreach( $bookings_data as $key => $value ) {
            $bookings_data[$key][ 'renting_type_label' ] = $renting_type_repo->getLabelById( $value[ 'renting_type_id' ] );

            $explode_start_date = explode('-', $bookings_data[$key][ 'start_date' ] );
            $reverse_start_date = array_reverse( $explode_start_date );
            $new_start_date = implode('/', $reverse_start_date );

            $bookings_data[$key][ 'start_date' ] = $new_start_date;

            $explode_end_date = explode('-', $bookings_data[$key][ 'end_date' ] );
            $reverse_end_date = array_reverse( $explode_end_date );
            $new_end_date = implode('/', $reverse_end_date );

            $bookings_data[$key][ 'end_date' ] = $new_end_date;
        }

        echo $this->twig->render( 'user/rent-manager.twig', [
            'title' => 'Liste de mes locations',
            'bookings' => $bookings_data,
            'role_label' => $role_label
        ]);
    }

    public function index(): void
    {
        $role_label = RepositoryManager::manager()->roleRepository()->getById( $_SESSION['user']->role_id )->label;

        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();

        $rentings_data = $renting_repo->findAll();

        foreach( $rentings_data as $renting_data ) {
            $renting_data->renting_type_label = $renting_type_repo->getLabelById( $renting_data->renting_type_id );
        }

        echo $this->twig->render( 'user/rent-list.twig', [
            'title' => 'Liste des locations',
            'rentings' => $rentings_data,
            'role_label' => $role_label
        ]);
    }

    public function show( int $id ): void
    {
        $role_label = RepositoryManager::manager()->roleRepository()->getById( $_SESSION['user']->role_id )->label;
        $user_id = $_SESSION['user']->id;

        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();

        $renting_data = $renting_repo->getById( $id );

        $renting_data->renting_type_label = $renting_type_repo->getLabelById( $renting_data->renting_type_id );

        echo $this->twig->render( 'user/rent-detail.twig', [
            'title' => 'Détails de la location',
            'form_status' => Session::get( Session::FORM_STATUS ),
            'renting' => $renting_data,
            'equipments' => $renting_repo->findEquipmentsById( $renting_data->id ),
            'role_label' => $role_label,
            'user_id' => $user_id
        ]);
    }
}