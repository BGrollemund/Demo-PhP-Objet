<?php


namespace airBnB\Http\Controller;


use airbnb\Airbnb;
use airBnB\Database\Model\Booking;
use airBnB\Database\Repository\RepositoryManager;
use airBnB\System\Http\Controller;

use airBnB\System\Http\Register;
use airBnB\System\Session\FormStatus;
use airBnB\System\Session\Session;
use airBnB\System\Util\DateManager;
use airBnB\System\Util\FieldChecker;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;

class UserController extends Controller
{
    #region Affichage

    public function booked(): void
    {
        Session::set( Session::FORM_STATUS, null );

        $booking_repo = RepositoryManager::manager()->bookingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $role_repo = RepositoryManager::manager()->roleRepository();

        $bookings_data = $booking_repo->findByUserId( Session::get( Session::USER )->id );

        foreach( $bookings_data as $key => $value ) {
            $bookings_data[$key][ 'renting_type_label' ] = $renting_type_repo->findLabelById( $value[ 'renting_type_id' ] );

            $bookings_data[$key][ 'start_date' ] = DateManager::invertDateFormat( $bookings_data[$key][ 'start_date' ], '-', '/'  );
            $bookings_data[$key][ 'end_date' ] = DateManager::invertDateFormat( $bookings_data[$key][ 'end_date' ], '-', '/'  );
        }

        echo $this->twig->render( 'user/rent-manager.twig', [
            'title' => 'Liste de mes locations',
            'bookings' => $bookings_data,
            'role_label' => $role_repo->findById( Session::get( Session::USER )->role_id )->label
        ]);
    }

    public function index(): void
    {
        Session::set( Session::FORM_STATUS, null );

        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $role_repo = RepositoryManager::manager()->roleRepository();

        $rentings_data = $renting_repo->findAll();

        foreach( $rentings_data as $renting_data ) {
            $renting_data->renting_type_label = $renting_type_repo->findLabelById( $renting_data->renting_type_id );

            $medium_infos = $renting_repo->findMediumById( $renting_data->id );

            if( ! is_null( $medium_infos ) ) {
                $renting_data->medium_bind = 'img'.DS.'users'.DS.$renting_data->renter_id.DS.$medium_infos->filename;
            }
            else {
                $renting_data->medium_bind = null;
            }
        }

        echo $this->twig->render( 'user/rent-list.twig', [
            'title' => 'Liste des locations',
            'rentings' => $rentings_data,
            'role_label' => $role_repo->findById( Session::get( Session::USER )->role_id )->label
        ]);
    }

    public function show( int $id ): void
    {
        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $role_repo = RepositoryManager::manager()->roleRepository();

        $renting_data = $renting_repo->findById( $id );
        $renting_data->renting_type_label = $renting_type_repo->findLabelById( $renting_data->renting_type_id );

        $medium_infos = $renting_repo->findMediumById( $renting_data->id );

        if( ! is_null( $medium_infos ) ) {
            $renting_data->medium_bind = 'img'.DS.'users'.DS.$renting_data->renter_id.DS.$medium_infos->filename;
        }
        else {
            $renting_data->medium_bind = null;
        }

        echo $this->twig->render( 'user/rent-detail.twig', [
            'title' => 'Détails de la location',
            'form_status' => Session::get( Session::FORM_STATUS ),
            'renting' => $renting_data,
            'equipments' => $renting_repo->findEquipmentsById( $renting_data->id ),
            'role_label' => $role_repo->findById( Session::get( Session::USER )->role_id )->label,
            'user_id' => Session::get( Session::USER )->id
        ]);
    }

    #endregion Affichage

    #region Traitement

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
        $check_result = FieldChecker::checkBookingFields( $post_data );

        if( $check_result === 0 ) {
            Session::set( Session::FORM_STATUS, null );

            // Insertion dans la bdd
            $post_data[ $start_date ] = DateManager::invertDateFormat( $post_data[ $start_date ], '/', '/'  );
            $post_data[ $end_date ] = DateManager::invertDateFormat( $post_data[ $end_date ], '/', '/'  );

            $booking_repo = RepositoryManager::manager()->bookingRepository();

            $booking_success = $booking_repo->insert( new Booking( $post_data ) );

            if( $booking_success === 0 ) {
                // TODO: erreur d'insertion
            }

            return new RedirectResponse( $router->url( 'rent-list' ) );
        }

        // Gestion des erreurs du formulaire
        $form_status = new FormStatus();

        // Erreurs
        switch( $check_result ) {
            case FieldChecker::START_DATE_MISSING:
                $form_status->addError( $start_date, 'Veuillez indiquer la date de début.' );
                break;
            case FieldChecker::START_DATE_BAD:
                $form_status->addError( $start_date, 'Veuillez respecter le format jj/mm/aaa.' );
                break;
            case FieldChecker::END_DATE_MISSING:
                $form_status->addError( $end_date, 'Veuillez indiquer la date de fin.' );
                break;
            case FieldChecker::END_DATE_BAD:
                $form_status->addError( $end_date, 'Veuillez respecter le format jj/mm/aaa.' );
                break;
            case FieldChecker::END_DATE_NOT_MATCH:
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

    #endregion Traitement
}