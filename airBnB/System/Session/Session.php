<?php


namespace airBnB\System\Session;


abstract class Session
{
    public const USER = 'user';
    public const RENTING_ID = 'renting_id';
    public const FORM_STATUS = 'form_status';

    public static function get( string $session_name )
    {
        if( empty( $_SESSION[ $session_name ] ) )
            return null;

        return $_SESSION[ $session_name ];
    }

    public static function set( string $session_name, $value ): void
    {
        $_SESSION[ $session_name ] = $value;
    }

    public static function unset(): void
    {
        session_destroy();
        unset( $_SESSION );
    }
}