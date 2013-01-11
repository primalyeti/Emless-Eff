<?
//require_once( 'bootstrap.inc.php' );

$framework = New Framework();
Registry::set( "framework", $framework, true );
$framework->init();

/** Autoload any classes that are required **/
function __autoload( $className )
{
	// load classes
	if( file_exists( ROOT . DS . 'system' . DS . strtolower( $className ) . '.class.php' ) )
	{
		require_once( ROOT . DS . 'system' . DS . strtolower( $className ) . '.class.php' );
		return null;
	}
	
	if( Registry::get( "isAdmin" ) == true && file_exists( ROOT . DS . 'admin' . DS . 'controllers' . DS . strtolower( $className ) . '.php' ) )
	{
		require_once( ROOT . DS . 'admin' . DS . 'controllers' . DS . strtolower( $className ) . '.php' );
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