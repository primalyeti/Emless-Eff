<?
require_once( ROOT . DS . 'config' . DS . 'config.php' );
require_once( ROOT . DS . 'config' . DS . 'vars.php' );
require_once( ROOT . DS . 'config' . DS . 'routing.php' );
require_once( ROOT . DS . 'config' . DS . 'inflection.php' );

session_start();

$emlessf = New EmlessF();
Registry::set( "EmlessF", $emlessf );
$emlessf->init();






/** Autoload any classes that are required **/
function __autoload( $className )
{
	// load classes
	if( file_exists( ROOT . DS . 'system' . DS . strtolower( $className ) . '.class.php' ) )
	{
		require_once( ROOT . DS . 'system' . DS . strtolower( $className ) . '.class.php' );
		return null;
	}
	
	// load classes
	if( file_exists( ROOT . DS . 'system' . DS . strtolower( $className ) . '.php' ) )
	{
		require_once( ROOT . DS . 'system' . DS . strtolower( $className ) . '.php' );
		return null;
	}
	
	// load all contorllers
	if( file_exists( ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower( $className ) . '.php' ) ) 
	{
		require_once( ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower( $className ) . '.php' );
		return null;
	}
	
	return null;
}