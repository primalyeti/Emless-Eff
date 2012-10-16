<?
if( empty( $_GET['n'] ) )
{
	die( "No Name provided" );
}

$name = strtolower( trim( urldecode( $_GET['n'] ) ) );

if( !preg_match( "/^[a-z0-9_]*$/i", $name ) )
{
	die( "Invalid name" );
}

define( 'DS', DIRECTORY_SEPARATOR );
define( 'ROOT', dirname( dirname( __FILE__ ) ) );
require_once( ROOT . DS . 'config' . DS . 'config.php' );
require_once( ROOT . DS . 'config' . DS . 'vars.php' );
require_once( ROOT . DS . 'config' . DS . 'routing.php' );
require_once( ROOT . DS . 'config' . DS . 'inflection.php' );
require_once( ROOT . DS . 'system' . DS . 'functions.include.php' );

// make controller file
ob_start();

echo "class " . ucfirst( $name ) . "Controller extends Controller" . "\n";
echo "{" . "\n";
echo "	function beforeAction ()" . "\n";
echo "	{" . "\n";
echo "	\n";
echo "	}" . "\n";
echo "	\n";
echo "	function index()" . "\n";
echo "	{" . "\n";
echo "	\n";
echo "	}" . "\n";
echo "	\n";
echo "	function afterAction()" . "\n";
echo "	{" . "\n";
echo "	\n";
echo "	}" . "\n";
echo "}" . "\n";
echo "\n";

$controller = ob_get_clean();

$newController = fopen( ROOT . DS . "application" . DS . "controllers" . DS . $name . "controller.php" . DS . "index.php", "w+" );
if( $newController === false )
{
	die( "Could not create controller" );
}
fwrite( $newController, $controller );
fclose( $newController );

// make view folder
if( mkdir( ROOT . DS . "application" . DS . "views" . DS . $name ); === false )
{
	die( "Could not create view folder" );
}

// make viewo folder index file
$newView = fopen( ROOT . DS . "application" . DS . "views" . DS . $name . DS . "index.php", "w+" );
if( $newView === false )
{
	die( "Could not create view index" );
}
fclose( $newView );