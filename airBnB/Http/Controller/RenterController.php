<?php


namespace airBnB\Http\Controller;


use airbnb\Airbnb;
use airBnB\Database\Model\Renting;
use airBnB\Database\Repository\RepositoryManager;
use airBnB\System\Http\Controller;

use airBnB\System\Http\Register;
use airBnB\System\Session\FormStatus;
use airBnB\System\Session\Session;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;

class RenterController extends Controller
{
    public function index(): void
    {
        $renter_id = $_SESSION['user']->id;

        $role_label = RepositoryManager::manager()->roleRepository()->getById( $_SESSION['user']->role_id )->label;

        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();

        $rentings_data = $renting_repo->findByRenterId( $renter_id );

        foreach( $rentings_data as $renting_data ) {
            $renting_data->renting_type_label = $renting_type_repo->getLabelById( $renting_data->renting_type_id );
        }

        echo $this->twig->render( 'renter/my-rent-list.twig', [
            'title' => 'Liste de mes annonces',
            'rentings' => $rentings_data,
            'role_label' => $role_label
        ]);
    }

    public function add(): void
    {
        $renter_id = $_SESSION['user']->id;

        $role_label = RepositoryManager::manager()->roleRepository()->getById( $_SESSION['user']->role_id )->label;

        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $equipment_repo = RepositoryManager::manager()->equipmentRepository();

        echo $this->twig->render( 'renter/my-rent-add.twig', [
            'title' => 'Ajouter une annonce',
            'form_status' => Session::get( Session::FORM_STATUS ),
            'equipments' => $equipment_repo->findAll(),
            'renting_types' => $renting_type_repo->findAll(),
            'renter_id' => $renter_id,
            'role_label' => $role_label
        ] );
    }

    public function booked(): void
    {
        $renter_id = $_SESSION['user']->id;

        $role_label = RepositoryManager::manager()->roleRepository()->getById( $_SESSION['user']->role_id )->label;

        $booking_repo = RepositoryManager::manager()->bookingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();

        $bookings_data = $booking_repo->findByRenterId( $renter_id );

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
            'title' => 'Mes réservations en cours',
            'bookings' => $bookings_data,
            'role_label' => $role_label
        ]);
    }

    public function insert( ServerRequest $request ): RedirectResponse
    {
        $post_data = $request->getParsedBody();

        $city = 'city';
        $country = 'country';
        $price = 'price';
        $renting_type_id = 'renting_type_id';
        $area = 'area';
        $description = 'description';
        $sleeping_num = 'sleeping_num';
        $bound_equipments = 'bound_equipments';

        $input_city = $post_data[ $city ] ?? null;
        $input_country = $post_data[ $country ] ?? null;
        $input_price = $post_data[ $price ] ?? null;
        $input_area = $post_data[ $area ] ?? null;
        $input_description = $post_data[ $description ] ?? null;
        $input_sleeping_num = $post_data[ $sleeping_num ] ?? null;
        $input_bound_equipments = $post_data[ $bound_equipments ] ?? [];

        $router = Airbnb::app()->getRouter();

        // TODO: manque un champ, logger
        if( is_null($input_city) || is_null($input_country) || is_null($input_price) || is_null($input_area) || is_null($input_description) || is_null($input_sleeping_num) )
            return new RedirectResponse( $router->url('home') );

        // Validation de la saisie
        $check_result = Airbnb::app()->getRegister()->checkRentingFields( $post_data );

        if( $check_result === 0 ) {
            Session::set( Session::FORM_STATUS, null );

            // Insertion dans la bdd
            $renting_repo = RepositoryManager::manager()->rentingRepository();

            $renting_success = $renting_repo->insert( new Renting( $request->getParsedBody() ) );

            if( $renting_success === 0 ) {
                // TODO: erreur d'insertion
            }

            $equipment_success = $renting_repo->bindEquipments( $renting_success, $input_bound_equipments);

            if( $equipment_success === 0 ) {
                // TODO: erreur d'insertion
            }

            return new RedirectResponse( $router->url( 'my-rent-list' ) );
        }

        // Gestion des erreurs du formulaire
        $form_status = new FormStatus();

        // Erreurs
        switch( $check_result ) {
            case Register::RENTING_TYPE_ID_MISSING:
                $form_status->addError( $renting_type_id, 'Veuillez indiquer le type de location.' );
                break;
             case Register::DESCRIPTION_MISSING:
                $form_status->addError( $description, 'Veuillez indiquer la description.' );
                break;
            case Register::CITY_MISSING:
                $form_status->addError( $city, 'Veuillez indiquer la ville.' );
                break;
            case Register::COUNTRY_MISSING:
                $form_status->addError( $country, 'Veuillez indiquer le pays.' );
                break;
            case Register::PRICE_MISSING:
                $form_status->addError( $price, 'Veuillez indiquer le prix.' );
                break;
            case Register::PRICE_BAD:
                $form_status->addError( $price, 'Le prix doit être un nombre.' );
                break;
            case Register::AREA_MISSING:
                $form_status->addError( $area, 'Veuillez indiquer la surface.' );
                break;
            case Register::AREA_BAD:
                $form_status->addError( $area, 'La surface doit être un nombre.' );
                break;
            case Register::SLEEPING_NUM_MISSING:
                $form_status->addError( $sleeping_num, 'Veuillez indiquer le nombre de couchage.' );
                break;
            case Register::SLEEPING_NUM_BAD:
                $form_status->addError( $sleeping_num, 'Le nombre de couchage doit être un nombre.' );
                break;

            default:
                // SHOULDDO: Log de l'erreur car anomalie
                break;
        }

        // Valeurs
        if( is_null( $form_status->getError( $city ) ) )
            $form_status->addValue( $city, $input_city );

        if( is_null( $form_status->getError( $country ) ) )
            $form_status->addValue( $country, $input_country );

        if( is_null( $form_status->getError( $price ) ) )
            $form_status->addValue( $price, $input_price );

        if( is_null( $form_status->getError( $area ) ) )
            $form_status->addValue( $area, $input_area );

        if( is_null( $form_status->getError( $description ) ) )
            $form_status->addValue( $description, $input_description );

        if( is_null( $form_status->getError( $sleeping_num) ) )
            $form_status->addValue( $sleeping_num, $input_sleeping_num);

        Session::set( Session::FORM_STATUS, $form_status );

        return new RedirectResponse( $router->url( 'my-rent-add' ) );
    }
}