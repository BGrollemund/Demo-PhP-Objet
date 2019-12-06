<?php


namespace airBnB\System\Database;


use airBnB\System\Settings;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;

    public static function connection(): ?PDO
    {
        if( is_null( self::$instance ) )
            self::$instance = self::getPDO();

        return self::$instance;
    }

    private static function getPDO()
    {
        $settings = Settings::instance();

        $db_host = $settings->get( 'db_host' );
        $db_user = $settings->get( 'db_user' );
        $db_pass = $settings->get( 'db_pass' );
        $db_name = $settings->get( 'db_name' );

        $dsn = 'mysql:dbname='.$db_name.';host='.$db_host;
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $pdo = null;

        try {
            $pdo = new PDO( $dsn, $db_user, $db_pass, $options );
        }
        catch( PDOException $e ) {
            return null;
        }

        return $pdo;
    }

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}