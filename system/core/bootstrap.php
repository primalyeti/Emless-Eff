<?
if( strpos( php_sapi_name(), 'cli' ) === false && ( ( version_compare( phpversion(), '5.4.0', '>=' ) && session_status() === PHP_SESSION_NONE ) || session_id() === '' ) )
{
	session_start();
}

require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'core.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'user_vars.php' );
require_once( ROOT . DS . 'application' . DS . 'config' . DS . 'schema.php' );
require_once( ROOT . DS . 'system' . DS . 'core' . DS . 'functions.include.php' );

date_default_timezone_set( TIMEZONE );

/** Autoload any classes that are required **/
function __autoload( $className )
{
	// load classes
	if( file_exists( ROOT . DS . 'system' . DS . 'core' . DS . strtolower( $className ) . '.class.php' ) )
	{
		require_once( ROOT . DS . 'system' . DS . 'core' . DS . strtolower( $className ) . '.class.php' );
		return false;
	}

	if( Registry::get( "_isAdmin" ) == true && file_exists( ROOT . DS . 'application' . DS . 'admin' . DS . 'controllers' . DS . strtolower( $className ) . '.php' ) )
	{
		require_once( ROOT . DS . 'application' . DS . 'admin' . DS . 'controllers' . DS . strtolower( $className ) . '.php' );
		return false;
	}

	// load all contorllers
	if( file_exists( ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower( $className ) . '.php' ) )
	{
		require_once( ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower( $className ) . '.php' );
		return false;
	}

	return true;
}