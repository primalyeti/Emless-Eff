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

if( isset( $_GET['url'] ) )
{
	$url = $_GET['url'];
}

$urlArr = explode( "/", $url );
if( $urlArr[0] == "scripts" )
{
	array_shift( $urlArr );
	$include = implode( "/", $urlArr );
	unset( $urlArr );
	if( file_exists( ROOT . DS . 'scripts' . DS . $include  ) && is_file( ROOT . DS . 'scripts' . DS . $include ) )
	{
		require_once( ROOT . DS . 'scripts' . DS . $include );
	}
}
else
{
	unset( $urlArr );
	require_once( ROOT . DS . 'system' . DS . 'bootstrap.php' );
}
?>