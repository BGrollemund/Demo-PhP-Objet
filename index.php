<?php


use airbnb\Airbnb;

define( 'DS', DIRECTORY_SEPARATOR );
define( 'ROOT_PATH', dirname( __FILE__ ) . DS );

spl_autoload_register();

require_once 'vendor/autoload.php';

Airbnb::app()->start();