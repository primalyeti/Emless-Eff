<?
define( 'DS', DIRECTORY_SEPARATOR );
define( 'ROOT', dirname( dirname( __FILE__ ) ) );

session_start();

require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'core.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'user_vars.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'schema.php' );
require_once( ROOT . DS . 'system' . DS . 'core' . DS . 'functions.include.php' );

date_default_timezone_set( TIMEZONE );

$url = "";
if( isset( $_GET['url'] ) )
{
	$url = $_GET['url'];
}

require_once( ROOT . DS . 'system' . DS . 'core' . DS . 'bootstrap.php' );
?>