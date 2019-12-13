<?php


namespace airBnB\Http\Controller;


use airbnb\Airbnb;
use airBnB\Database\Model\Renting;
use airBnB\Database\Repository\RepositoryManager;
use airBnB\System\Http\Controller;

use airBnB\System\Http\Register;
use airBnB\System\Session\FormStatus;
use airBnB\System\Session\Session;
use airBnB\System\Util\DateManager;
use airBnB\System\Util\FieldChecker;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;

class RenterController extends Controller
{
    #region Affichage

    public function add(): void
    {
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $equipment_repo = RepositoryManager::manager()->equipmentRepository();

        echo $this->twig->render( 'renter/my-rent-add.twig', [
            'title' => 'Ajouter une annonce',
            'form_status' => Session::get( Session::FORM_STATUS ),
            'equipments' => $equipment_repo->findAll(),
            'renting_types' => $renting_type_repo->findAll(),
            'renter_id' => Session::get( Session::USER )->id,
        ] );
    }

    public function booked(): void
    {
        Session::set( Session::FORM_STATUS, null );

        $booking_repo = RepositoryManager::manager()->bookingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();

        $bookings_data = $booking_repo->findByRenterId( Session::get( Session::USER )->id );

        foreach( $bookings_data as $key => $value ) {
            $bookings_data[$key][ 'renting_type_label' ] = $renting_type_repo->findLabelById( $value[ 'renting_type_id' ] );

            $bookings_data[$key][ 'start_date' ] = DateManager::invertDateFormat( $bookings_data[$key][ 'start_date' ], '-', '/'  );
            $bookings_data[$key][ 'end_date' ] = DateManager::invertDateFormat( $bookings_data[$key][ 'end_date' ], '-', '/'  );
        }

        echo $this->twig->render( 'renter/my-rent-manager.twig', [
            'title' => 'Mes réservations en cours',
            'bookings' => $bookings_data
        ]);
    }

    public function edit( ServerRequest $request ): void
    {
        if( isset( $request->getParsedBody()['renting_id'] ) ) {
            $renting_id = $request->getParsedBody()['renting_id'];
            Session::set( 'renting_id', $renting_id );
            Session::set( Session::FORM_STATUS, null );
        }
        else {
            $renting_id = Session::get( Session::RENTING_ID );
        }

        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();
        $equipment_repo = RepositoryManager::manager()->equipmentRepository();

        $bound_equipments = [];

        foreach( $equipment_repo->findByRentingId( (int) $renting_id ) as $equipment ) {
            $bound_equipments[] = $equipment->id;
        }

        echo $this->twig->render( 'renter/my-rent-edit.twig', [
            'title' => 'Modifier une annonce',
            'form_status' => Session::get( Session::FORM_STATUS ),
            'equipments' => $equipment_repo->findAll(),
            'renting' => $renting_repo->findById( $renting_id ),
            'renting_types' => $renting_type_repo->findAll(),
            'bound_equipments' => $bound_equipments,
            'renter_id' => Session::get( Session::USER )->id
        ] );
    }

    public function index(): void
    {
        Session::set( Session::FORM_STATUS, null );

        $renting_repo = RepositoryManager::manager()->rentingRepository();
        $renting_type_repo = RepositoryManager::manager()->rentingTypeRepository();

        $rentings_data = $renting_repo->findByRenterId( Session::get( Session::USER )->id );

        foreach( $rentings_data as $renting_data ) {
            $renting_data->renting_type_label = $renting_type_repo->findLabelById( $renting_data->renting_type_id );
        }

        echo $this->twig->render( 'renter/my-rent-list.twig', [
            'title' => 'Liste de mes annonces',
            'rentings' => $rentings_data
        ]);
    }

    #endregion Affichage

    #region Traitement

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
        $input_renting_type_id = $post_data[ $renting_type_id ] ?? null;
        $input_area = $post_data[ $area ] ?? null;
        $input_description = $post_data[ $description ] ?? null;
        $input_sleeping_num = $post_data[ $sleeping_num ] ?? null;
        $input_bound_equipments = $post_data[ $bound_equipments ] ?? [];

        $router = Airbnb::app()->getRouter();

        // TODO: manque un champ, logger
        if( is_null($input_city) || is_null($input_country) || is_null($input_price) || is_null($renting_type_id) || is_null($input_area) || is_null($input_description) || is_null($input_sleeping_num) )
            return new RedirectResponse( $router->url('home') );

        // Validation de la saisie
        $check_result = FieldChecker::checkRentingFields( $post_data );

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
            case FieldChecker::RENTING_TYPE_ID_MISSING:
                $form_status->addError( $renting_type_id, 'Veuillez indiquer le type de location.' );
                break;
            case FieldChecker::DESCRIPTION_MISSING:
                $form_status->addError( $description, 'Veuillez indiquer la description.' );
                break;
            case FieldChecker::CITY_MISSING:
                $form_status->addError( $city, 'Veuillez indiquer la ville.' );
                break;
            case FieldChecker::COUNTRY_MISSING:
                $form_status->addError( $country, 'Veuillez indiquer le pays.' );
                break;
            case FieldChecker::PRICE_MISSING:
                $form_status->addError( $price, 'Veuillez indiquer le prix.' );
                break;
            case FieldChecker::PRICE_BAD:
                $form_status->addError( $price, 'Le prix doit être un nombre.' );
                break;
            case FieldChecker::AREA_MISSING:
                $form_status->addError( $area, 'Veuillez indiquer la surface.' );
                break;
            case FieldChecker::AREA_BAD:
                $form_status->addError( $area, 'La surface doit être un nombre.' );
                break;
            case FieldChecker::SLEEPING_NUM_MISSING:
                $form_status->addError( $sleeping_num, 'Veuillez indiquer le nombre de couchage.' );
                break;
            case FieldChecker::SLEEPING_NUM_BAD:
                $form_status->addError( $sleeping_num, 'Le nombre de couchage doit être un nombre.' );
                break;

            default:
                // TODO: Log de l'erreur car anomalie
                break;
        }

        // Valeurs
        if( is_null( $form_status->getError( $city ) ) )
            $form_status->addValue( $city, $input_city );

        if( is_null( $form_status->getError( $country ) ) )
            $form_status->addValue( $country, $input_country );

        if( is_null( $form_status->getError( $price ) ) )
            $form_status->addValue( $price, $input_price );

        if( is_null( $form_status->getError( $renting_type_id ) ) )
            $form_status->addValue( $renting_type_id, $input_renting_type_id );

        if( is_null( $form_status->getError( $area ) ) )
            $form_status->addValue( $area, $input_area );

        if( is_null( $form_status->getError( $description ) ) )
            $form_status->addValue( $description, $input_description );

        if( is_null( $form_status->getError( $sleeping_num) ) )
            $form_status->addValue( $sleeping_num, $input_sleeping_num);

        Session::set( Session::FORM_STATUS, $form_status );

        return new RedirectResponse( $router->url( 'my-rent-add' ) );
    }

    public function update( ServerRequest $request ): RedirectResponse
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
        $old_bound_equipments = 'old_bound_equipments';

        $input_city = $post_data[ $city ] ?? null;
        $input_country = $post_data[ $country ] ?? null;
        $input_price = $post_data[ $price ] ?? null;
        $input_renting_type_id = $post_data[ $renting_type_id ] ?? null;
        $input_area = $post_data[ $area ] ?? null;
        $input_description = $post_data[ $description ] ?? null;
        $input_sleeping_num = $post_data[ $sleeping_num ] ?? null;
        $input_bound_equipments = $post_data[ $bound_equipments ] ?? [];
        $input_old_bound_equipments = $post_data[ $old_bound_equipments ] ?? null;

        $router = Airbnb::app()->getRouter();

        // TODO: manque un champ, logger
        if( is_null($input_city) || is_null($input_country) || is_null($input_price) || is_null($renting_type_id) || is_null($input_area) || is_null($input_description) || is_null($input_sleeping_num) )
            return new RedirectResponse( $router->url('home') );

        // Validation de la saisie
        $check_result = FieldChecker::checkRentingFields( $post_data );

        if( $check_result === 0 ) {
            Session::set( Session::FORM_STATUS, null );

            // Insertion dans la bdd
            $renting_repo = RepositoryManager::manager()->rentingRepository();

            $renting_success = $renting_repo->save( new Renting( $post_data ) );

            if( $renting_success === 0 ) {
                // TODO: erreur d'insertion
            }

            $input_old_bound_equipments = explode(',', $input_old_bound_equipments );

            // Attachement des compétences cochées
            $to_bind = array_diff( $input_bound_equipments, $input_old_bound_equipments );

            if( ! empty($to_bind) ) {
                $bind_success = $renting_repo->bindEquipments( $post_data[ 'id' ], $to_bind );

                if( ! $bind_success ) {
                    // TODO: erreur d'insertion
                }
            }

            // Détachement des compétences non cochées
            $to_unbind = array_diff( $input_old_bound_equipments, $input_bound_equipments );

            if( ! empty( $to_unbind ) ) {
                $unbind_success = $renting_repo->unbindEquipments( $post_data[ 'id' ], $to_unbind );

                if( ! $unbind_success ) {
                    // TODO: erreur d'insertion
                }
            }

            return new RedirectResponse( $router->url( 'my-rent-list' ) );
        }

        // Gestion des erreurs du formulaire
        $form_status = new FormStatus();

        // Erreurs
        switch( $check_result ) {
            case FieldChecker::RENTING_TYPE_ID_MISSING:
                $form_status->addError( $renting_type_id, 'Veuillez indiquer le type de location.' );
                break;
            case FieldChecker::DESCRIPTION_MISSING:
                $form_status->addError( $description, 'Veuillez indiquer la description.' );
                break;
            case FieldChecker::CITY_MISSING:
                $form_status->addError( $city, 'Veuillez indiquer la ville.' );
                break;
            case FieldChecker::COUNTRY_MISSING:
                $form_status->addError( $country, 'Veuillez indiquer le pays.' );
                break;
            case FieldChecker::PRICE_MISSING:
                $form_status->addError( $price, 'Veuillez indiquer le prix.' );
                break;
            case FieldChecker::PRICE_BAD:
                $form_status->addError( $price, 'Le prix doit être un nombre.' );
                break;
            case FieldChecker::AREA_MISSING:
                $form_status->addError( $area, 'Veuillez indiquer la surface.' );
                break;
            case FieldChecker::AREA_BAD:
                $form_status->addError( $area, 'La surface doit être un nombre.' );
                break;
            case FieldChecker::SLEEPING_NUM_MISSING:
                $form_status->addError( $sleeping_num, 'Veuillez indiquer le nombre de couchage.' );
                break;
            case FieldChecker::SLEEPING_NUM_BAD:
                $form_status->addError( $sleeping_num, 'Le nombre de couchage doit être un nombre.' );
                break;

            default:
                // TODO: Log de l'erreur car anomalie
                break;
        }

        // Valeurs
        if( is_null( $form_status->getError( $city ) ) )
            $form_status->addValue( $city, $input_city );

        if( is_null( $form_status->getError( $country ) ) )
            $form_status->addValue( $country, $input_country );

        if( is_null( $form_status->getError( $price ) ) )
            $form_status->addValue( $price, $input_price );

        if( is_null( $form_status->getError( $renting_type_id ) ) )
            $form_status->addValue( $renting_type_id, $input_renting_type_id );

        if( is_null( $form_status->getError( $area ) ) )
            $form_status->addValue( $area, $input_area );

        if( is_null( $form_status->getError( $description ) ) )
            $form_status->addValue( $description, $input_description );

        if( is_null( $form_status->getError( $sleeping_num) ) )
            $form_status->addValue( $sleeping_num, $input_sleeping_num);

        $form_status->addArray( $bound_equipments, $input_bound_equipments );

        Session::set( Session::FORM_STATUS, $form_status );

        return new RedirectResponse( $router->url( 'my-rent-edit', [ 'renting_id' => $post_data['id'] ] ) );
    }

    #endregion Traitement
}