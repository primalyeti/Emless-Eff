<?
/* require_once( ROOT . DS . 'system' . DS . 'bootstrap.php' ); */
define( 'DS', DIRECTORY_SEPARATOR );
define( 'ROOT', dirname( dirname( __FILE__ ) ) );

require_once( ROOT . DS . 'config' . DS . 'config.php' );
require_once( ROOT . DS . 'config' . DS . 'vars.php' );
require_once( ROOT . DS . 'config' . DS . 'routing.php' );
require_once( ROOT . DS . 'config' . DS . 'inflection.php' );
require_once( ROOT . DS . 'system' . DS . 'functions.include.php' );

session_start();
date_default_timezone_set( TIMEZONE );

if( isset( $_GET['url'] ) )
{
	$url = $_GET['url'];
}

require_once( ROOT . DS . 'system' . DS . 'bootstrap.php' );
?>