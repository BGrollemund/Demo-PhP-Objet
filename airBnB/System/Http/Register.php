<?php


namespace airBnB\System\Http;


use airBnB\System\Session\Session;

class Register
{
    public const USERNAME_MISSING = -1;
    public const EMAIL_MISSING = -2;
    public const BIRTH_DATE_MISSING = -3;
    public const BIRTH_DATE_BAD = -4;
    public const CITY_MISSING = -5;
    public const COUNTRY_MISSING = -6;
    public const PASSWORD_MISSING = -7;
    public const PASSWORD_CHECK_MISSING = -8;
    public const PASSWORD_CHECK_BAD = -9;

    private $user = null;

    public function __construct() { $this->user = Session::get( Session::USER ); }

    public function checkRegister( string $username, string $email, string $birth_date, string $city, string $country, string $password, string $password_check ): int
    {
        if( empty( $username ) ) return self::USERNAME_MISSING;

        if( empty( $email ) ) return self::EMAIL_MISSING;

        if( empty( $birth_date ) ) {
            return self::BIRTH_DATE_MISSING;
        }
        else {
            if( ! preg_match('/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/', $birth_date ) )
                return self::BIRTH_DATE_BAD;
        }

        if( empty( $city ) ) return self::CITY_MISSING;

        if( empty( $country ) ) return self::COUNTRY_MISSING;

        if( empty( $password ) ) return self::PASSWORD_MISSING;

        if( empty( $password_check ) ) {
            return self::PASSWORD_CHECK_MISSING;
        }
        else {
            if( $password !== $password_check )
                return self::PASSWORD_CHECK_BAD;
        }

        return 0;
    }

}