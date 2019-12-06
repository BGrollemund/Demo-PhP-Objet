<?php


namespace airBnB\System;


class Settings
{
    private static $instance = null;

    private $settings;

    public static function instance(): self
    {
        if( is_null( self::$instance ) )
            self::$instance = new Settings();

        return self::$instance;
    }

    public function get( string $key ): string
    {
        if( array_key_exists( $key, $this->settings ) )
            return $this->settings[ $key ];

        return '';
    }

    private function __construct() { $this->settings = require ROOT_PATH . 'settings.php'; }
    private function __clone() {}
    private function __wakeup() {}
}