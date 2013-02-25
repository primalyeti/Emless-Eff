<?
define( 'DS', DIRECTORY_SEPARATOR );
define( 'ROOT', dirname( dirname( __FILE__ ) ) );

session_start();
date_default_timezone_set( TIMEZONE );

require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'config.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'vars.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'routing.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'inflection.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'profiler.php' );
require_once( ROOT . DS . 'system' . DS . 'core' . DS . 'functions.include.php' );

if( isset( $_GET['url'] ) )
{
	$url = $_GET['url'];
}

require_once( ROOT . DS . 'system' . DS . 'core' . DS . 'bootstrap.php' );
?>