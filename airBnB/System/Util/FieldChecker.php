<?php


namespace airBnB\System\Util;


use airBnB\Database\Repository\RepositoryManager;

abstract class FieldChecker
{
    public const AREA_BAD = -1;
    public const AREA_MISSING = -2;
    public const BIRTH_DATE_BAD = -3;
    public const BIRTH_DATE_MISSING = -4;
    public const CITY_MISSING = -5;
    public const COUNTRY_MISSING = -6;
    public const DESCRIPTION_MISSING = -7;
    public const EMAIL_EXIST = -8;
    public const EMAIL_MISSING = -9;
    public const END_DATE_BAD = -10;
    public const END_DATE_MISSING = -11;
    public const END_DATE_NOT_MATCH = -12;
    public const PASSWORD_CHECK_MISSING = -13;
    public const PASSWORD_CHECK_BAD = -14;
    public const PASSWORD_MISSING = -15;
    public const PRICE_BAD = -16;
    public const PRICE_MISSING = -17;
    public const RENTING_TYPE_ID_MISSING = -18;
    public const SLEEPING_NUM_BAD = -19;
    public const SLEEPING_NUM_MISSING = -20;
    public const START_DATE_BAD = -21;
    public const START_DATE_MISSING = -22;
    public const USERNAME_EXIST = -23;
    public const USERNAME_MISSING = -24;
    public const WRONG_INTERVAL_BOOKING = -25;
    public const WRONG_INTERVAL_UNAVAILABILITY = -26;

    public static function checkDatesFields(array $data ): int
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
        $interval = DateManager::diffInvertDateFormat( $data[ $start_date ], $data[ $end_date], '/' );

        if( $interval->invert === 1 || $interval->days === 0 ) return self::END_DATE_NOT_MATCH;

        return 0;
    }

    public static function checkDatesFieldsWithDB(array $data ): int
    {
        $start_date = 'start_date';
        $end_date = 'end_date';
        $renting_id = 'renting_id';

        $unavailabilities = RepositoryManager::manager()->unavailabilityRepository()->findByRentingId( (int) $data[$renting_id] );

        foreach( $unavailabilities as $unavailability ) {
            $unavailability->start_date = DateManager::invertDateFormat( $unavailability->start_date, '-', '/' );
            $unavailability->end_date = DateManager::invertDateFormat( $unavailability->end_date, '-', '/' );

            $isOnInterval = DateManager::isOnDateInterval( $unavailability->start_date, $unavailability->end_date, $data[$start_date], $data[$end_date], '/' );

            if( $isOnInterval ) return self::WRONG_INTERVAL_UNAVAILABILITY;
        }

        $bookings = RepositoryManager::manager()->bookingRepository()->findByRentingId( (int) $data[$renting_id] );

        foreach( $bookings as $booking ) {
            $booking->start_date = DateManager::invertDateFormat( $booking->start_date, '-', '/' );
            $booking->end_date = DateManager::invertDateFormat( $booking->end_date, '-', '/' );

            $isOnInterval = DateManager::isOnDateInterval( $booking->start_date, $booking->end_date, $data[$start_date], $data[$end_date], '/' );

            if( $isOnInterval ) return self::WRONG_INTERVAL_BOOKING;
        }

        return 0;
    }

    public static function checkRegisterFields( array $data ): int
    {
        $email = 'email';
        $password = 'password';
        $password_check = 'password_check';
        $username = 'username';
        $birth_date = 'birth_date';
        $city = 'city';
        $country = 'country';

        $repo = RepositoryManager::manager();

        $profile_repo = $repo->profileRepository();

        if( empty( $data[ $username ] ) ) {
            return self::USERNAME_MISSING;
        }
        else {
            if( ! is_null( $profile_repo->findByUsername( $data[ $username ] ) ) ) return self::USERNAME_EXIST;
        }

        $user_repo = $repo->userRepository();

        if( empty( $data[ $email ] ) ) {
            return self::EMAIL_MISSING;
        }
        else {
            if( ! is_null( $user_repo->findByEmail( $data[ $email ] ) ) ) return self::EMAIL_EXIST;
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

        return 0;
    }

    public static function checkRentingFields ( array $data ): int
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
            if( ! preg_match('/^\d{1,6}$/', $data[ $sleeping_num ] ) )
                return self::SLEEPING_NUM_BAD;
        }

        return 0;
    }

    private function __construct() {}
}