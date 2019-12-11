<?php


namespace airBnB\System\Http;


use airBnB\Database\Model\Profile;
use airBnB\Database\Model\User;
use airBnB\Database\Repository\RepositoryManager;
use airBnB\Database\Repository\UserRepository;
use airBnB\System\Session\Session;

class Register
{
    public const USERNAME_MISSING = -1;
    public const USERNAME_EXIST = -3;
    public const EMAIL_MISSING = -3;
    public const EMAIL_EXIST = -4;
    public const BIRTH_DATE_MISSING = -5;
    public const BIRTH_DATE_BAD = -6;
    public const CITY_MISSING = -7;
    public const COUNTRY_MISSING = -8;
    public const PASSWORD_MISSING = -9;
    public const PASSWORD_CHECK_MISSING = -10;
    public const PASSWORD_CHECK_BAD = -11;

    public const PRICE_MISSING = -12;
    public const PRICE_BAD = -13;
    public const AREA_MISSING = -14;
    public const AREA_BAD = -15;
    public const DESCRIPTION_MISSING = -16;
    public const SLEEPING_NUM_MISSING = -17;
    public const SLEEPING_NUM_BAD = -18;
    public const RENTING_TYPE_ID_MISSING = -19;

    public const START_DATE_MISSING = -20;
    public const START_DATE_BAD = -21;
    public const END_DATE_MISSING = -22;
    public const END_DATE_BAD = -23;
    public const END_DATE_NOT_MATCH = -24;

    private $user = null;

    public function __construct() { $this->user = Session::get( Session::USER ); }

    public function checkRegister( array $data ): int
    {
        $email = 'email';
        $password = 'password';
        $password_check = 'password_check';
        $username = 'username';
        $birth_date = 'birth_date';
        $city = 'city';
        $country = 'country';
        $is_renter = 'is_renter';

        $repo = RepositoryManager::manager();

        $profile_repo = $repo->profileRepository();

        if( empty( $data[ $username ] ) ) {
            return self::USERNAME_MISSING;
        }
        else {
            if( ! is_null( $profile_repo->getByUsername( $data[ $username ] ) ) ) return self::USERNAME_EXIST;
        }

        $user_repo = $repo->userRepository();

        if( empty( $data[ $email ] ) ) {
            return self::EMAIL_MISSING;
        }
        else {
            if( ! is_null( $user_repo->getByEmail( $data[ $email ] ) ) ) return self::EMAIL_EXIST;
        }

        if( empty( $data[ $birth_date ] ) ) {
            return self::BIRTH_DATE_MISSING;
        }
        else {
            if( ! preg_match('/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/', $data[ $birth_date ] ) )
                return self::BIRTH_DATE_BAD;
        }

        if( empty( $data[ $city ] ) ) return self::CITY_MISSING;

        if( empty( $data[ $country ] ) ) return self::COUNTRY_MISSING;

        if( empty( $data[ $password ] ) ) return self::PASSWORD_MISSING;

        if( empty( $data[ $password_check ] ) ) {
            return self::PASSWORD_CHECK_MISSING;
        }
        else {
            if( $data[ $password ] !== $data[ $password_check ] )
                return self::PASSWORD_CHECK_BAD;
        }

        // Insertion du nouvel utilisateur
        // TODO: changer l'insertion en base dans le controller

        $role_repo = $repo->roleRepository();
        $role_id = $role_repo->getIdByLabel( $data[ $is_renter ] );

        if( $role_id === 0 ) {
            // TODO: erreur role (pas normal), logger
        }

        $explode_birth_date = explode('/', $data[ $birth_date ] );
        $reverse_birth_date = array_reverse( $explode_birth_date );
        $new_birth_date = implode('/', $reverse_birth_date );

        $data[ $birth_date ] =  $new_birth_date;

        $profile_id = $profile_repo->insert( new Profile( $data ) );

        if( $profile_id === 0 ) {
            // TODO: erreur d'insertion
        }

        $data[ 'role_id' ] = $role_id;
        $data[ 'profile_id' ] = $profile_id;
        $data[ $password ] = Auth::hashData( $data[ $password ] );

        $success = $user_repo->insert( new User( $data ) );

        if( $success === 0 ) {
            // TODO: erreur d'insertion
        }

        $user = $user_repo->getByEmail( $data[ $email ] );

        $user->password = null;

        Session::set( Session::USER, $user );

        return $user->role_id;
    }

    public function checkBookingFields( array $data ): int
    {
        $start_date = 'start_date';
        $end_date = 'end_date';

        if( empty( $data[ $start_date ] ) ) {
            return self::START_DATE_MISSING;
        }
        else {
            if( ! preg_match('/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/', $data[ $start_date ] ) )
                return self::START_DATE_BAD;
        }

        if( empty( $data[ $end_date ] ) ) {
            return self::END_DATE_MISSING;
        }
        else {
            if( ! preg_match('/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/', $data[ $end_date ] ) )
                return self::END_DATE_BAD;
        }

        // Vérification dates dans le bon ordre
        $explode_start_date = explode('/', $data[ $start_date ] );
        $reverse_start_date = array_reverse( $explode_start_date );
        $new_start_date = implode('-', $reverse_start_date );

        $new_start_date = date_create($new_start_date);

        $explode_end_date = explode('/', $data[ $end_date ] );
        $reverse_end_date = array_reverse( $explode_end_date );
        $new_end_date = implode('-', $reverse_end_date );

        $new_end_date = date_create($new_end_date);

        $interval = date_diff( $new_start_date, $new_end_date );

        if( $interval->invert === 1 || $interval->days === 0 ) return self::END_DATE_NOT_MATCH;

        return 0;
    }

    public function checkRentingFields ( array $data ): int
    {
        $renting_type_id = 'renting_type_id';
        $city = 'city';
        $country = 'country';
        $price = 'price';
        $area = 'area';
        $description = 'description';
        $sleeping_num = 'sleeping_num';

        if( empty( $data[ $renting_type_id ] ) ) return self::RENTING_TYPE_ID_MISSING;

        if( empty( $data[ $description ] ) ) return self::DESCRIPTION_MISSING;

        if( empty( $data[ $city ] ) ) return self::CITY_MISSING;

        if( empty( $data[ $country ] ) ) return self::COUNTRY_MISSING;

        if( empty( $data[ $price ] ) ) {
            return self::PRICE_MISSING;
        }
        else {
            if( ! preg_match('/^\d{1,6}$/', $data[ $price ] ) )
                return self::PRICE_BAD;
        }

        if( empty( $data[ $area ] ) ) {
            return self::AREA_MISSING;
        }
        else {
            if( ! preg_match('/^\d{1,6}$/', $data[ $area ] ) )
                return self::AREA_BAD;
        }

        if( empty( $data[ $sleeping_num ] ) ) {
            return self::SLEEPING_NUM_MISSING;
        }
        else {
            if( ! preg_match('/^\d{1,6}$/', $data[ $area ] ) )
                return self::SLEEPING_NUM_BAD;
        }

        return 0;
    }

}