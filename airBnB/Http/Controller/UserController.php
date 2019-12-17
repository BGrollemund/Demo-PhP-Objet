<?php


namespace airBnB\Http\Controller;


use airbnb\Airbnb;
use airBnB\Database\Model\Booking;
use airBnB\Database\Repository\RepositoryManager;
use airBnB\System\Http\Controller;

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
        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $role_repo = RepositoryManager::manager()->roleRepository();

        $bookings_data = $booking_repo->findByUserId( Session::get( Session::USER )->id );

        foreach( $bookings_data as $key => $value ) {
            $bookings_data[$key][ 'renting_type_label' ] = $renting_type_repo->findLabelById( $value[ 'renting_type_id' ] );

            $bookings_data[$key][ 'start_date' ] = DateManager::invertDateFormat( $bookings_data[$key][ 'start_date' ], '-', '/'  );
            $bookings_data[$key][ 'end_date' ] = DateManager::invertDateFormat( $bookings_data[$key][ 'end_date' ], '-', '/'  );

            $interval = DateManager::diffInvertDateFormat( $bookings_data[$key][ 'start_date' ], $bookings_data[$key][ 'end_date' ], '/' );

            $bookings_data[$key][ 'msg_price_total' ] =
                'soit '.$interval->days.' jour(s) x '.$bookings_data[$key][ 'price' ]. ' € = '.
                ( $bookings_data[$key][ 'price' ] * $interval->days ).' €';

            $medium_infos = $renting_repo->findMediumById( $bookings_data[$key][ 'renting_id' ] );

            if( ! is_null( $medium_infos ) ) {
                $bookings_data[$key][ 'medium_bind' ] = 'img'.DS.'users'.DS.$bookings_data[$key][ 'renter_id' ].DS.$medium_infos->filename;
            }
            else {
                $bookings_data[$key][ 'medium_bind' ] = null;
            }
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

        $equipment_repo = RepositoryManager::manager()->equipmentRepository();
        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $role_repo = RepositoryManager::manager()->roleRepository();

        $rentings_data = $renting_repo->findAllFiltered( $_GET );

        foreach( $rentings_data as $renting_data ) {
            $renting_data->renting_type_label = $renting_type_repo->findLabelById( $renting_data->renting_type_id );

            $medium_infos = $renting_repo->findMediumById( $renting_data->id );

            if( ! is_null( $medium_infos ) ) {
                $renting_data->medium_bind = 'img'.DS.'users'.DS.$renting_data->renter_id.DS.$medium_infos->filename;
            }
            else {
                $renting_data->medium_bind = null;
            }

            $renting_data->is_favorite = $renting_repo->isFavoriteById( (int) Session::get( Session::USER )->id, $renting_data->id );;
        }

        echo $this->twig->render( 'user/rent-list.twig', [
            'title' => 'Liste des locations',
            'rentings' => $rentings_data,
            'equipments' => $equipment_repo->findAll(),
            'renting_types' => $renting_type_repo->findAll(),
            'role_label' => $role_repo->findById( Session::get( Session::USER )->role_id )->label,
            'show_menu_sort' => true
        ]);
    }

    public function favorites(): void
    {
        Session::set( Session::FORM_STATUS, null );

        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $role_repo = RepositoryManager::manager()->roleRepository();

        $rentings_data = $renting_repo->findFavoriteByUserId( (int) Session::get( Session::USER )->id );

        foreach( $rentings_data as $renting_data ) {
            $renting_data->renting_type_label = $renting_type_repo->findLabelById( $renting_data->renting_type_id );

            $medium_infos = $renting_repo->findMediumById( $renting_data->id );

            if( ! is_null( $medium_infos ) ) {
                $renting_data->medium_bind = 'img'.DS.'users'.DS.$renting_data->renter_id.DS.$medium_infos->filename;
            }
            else {
                $renting_data->medium_bind = null;
            }

            $renting_data->is_favorite = $renting_repo->isFavoriteById( (int) Session::get( Session::USER )->id, $renting_data->id );;
        }

        echo $this->twig->render( 'user/rent-favorites.twig', [
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

        $unavailability_repo = RepositoryManager::manager()->unavailabilityRepository();
        $unavailabilities = [];

        foreach ($unavailability_repo->findByRentingId( $id ) as $unavailability) {
            $unavailability->msg =  'Du ' . DateManager::invertDateFormat( $unavailability->start_date, '-', '/' ) .
                ' au ' . DateManager::invertDateFormat( $unavailability->end_date, '-', '/' );

            $unavailabilities[] = $unavailability->msg;
        }

        $booking_repo = RepositoryManager::manager()->bookingRepository();
        $bookings = [];

        foreach ($booking_repo->findByRentingId( $id ) as $booking) {
            $booking->msg =  'Du ' . DateManager::invertDateFormat( $booking->start_date, '-', '/' ) .
                ' au ' . DateManager::invertDateFormat( $booking->end_date, '-', '/' );

            $bookings[] = $booking->msg;
        }

        echo $this->twig->render( 'user/rent-detail.twig', [
            'title' => 'Détails de la location',
            'form_status' => Session::get( Session::FORM_STATUS ),
            'renting' => $renting_data,
            'equipments' => $renting_repo->findEquipmentsById( $renting_data->id ),
            'role_label' => $role_repo->findById( Session::get( Session::USER )->role_id )->label,
            'user_id' => Session::get( Session::USER )->id,
            'unavailabilities' => $unavailabilities,
            'bookings' => $bookings
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
        $interval_booking = 'interval_booking';
        $interval_unavailability = 'interval_unavailability';

        $input_user_id = $post_data[ $user_id ] ?? null;
        $input_renting_id = $post_data[ $renting_id ] ?? null;
        $input_start_date = $post_data[ $start_date ] ?? null;
        $input_end_date = $post_data[ $end_date ] ?? null;

        $router = Airbnb::app()->getRouter();

        // TODO: manque un champ, logger
        if( is_null($input_user_id) || is_null($input_renting_id) || is_null($input_start_date) || is_null($input_end_date) )
            return new RedirectResponse( $router->url('home') );

        // Validation de la saisie
        $check_result = FieldChecker::checkDatesFields( $post_data );
        $check_result_with_db = 1;

        if( $check_result === 0 ) {
            $check_result_with_db = FieldChecker::checkDatesFieldsWithDB( $post_data );

            if( $check_result_with_db === 0 ) {
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

        switch( $check_result_with_db ) {
            case FieldChecker::WRONG_INTERVAL_BOOKING:
                $form_status->addError( $interval_booking, 'Il y a une réservation à cette période.' );
                break;
            case FieldChecker::WRONG_INTERVAL_UNAVAILABILITY:
                $form_status->addError( $interval_unavailability, 'Il y a une indisponibilité à cette période.' );
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

    public function favoriteAdd( int $id ): RedirectResponse
    {
        // TODO: vérification pas déjà lié (passage en force car bouton ne doit pas être présent)

        $user_id = (int) Session::get(Session::USER)->id;
        $renting_id = $id;

        $bind_success = RepositoryManager::manager()->userRepository()->bindFavoriteById( $user_id, $renting_id );

        if( ! $bind_success ) {
            // TODO: erreur d'insertion
        }

        $router = Airbnb::app()->getRouter();

        return new RedirectResponse( $router->url( 'rent-list' ) );
    }

    public function favoriteRemove( int $id ): RedirectResponse
    {
        // TODO: vérification déjà pas lié (passage en force car bouton ne doit pas être présent)

        $user_id = (int) Session::get(Session::USER)->id;
        $renting_id = $id;

        $bind_success = RepositoryManager::manager()->userRepository()->unbindFavoriteById( $user_id, $renting_id );

        if( ! $bind_success ) {
            // TODO: erreur d'insertion
        }

        $router = Airbnb::app()->getRouter();

        return new RedirectResponse( $router->url( 'rent-list' ) );
    }

    #endregion Traitement
}