<?
define( 'DS', DIRECTORY_SEPARATOR );
define( 'ROOT', dirname( dirname( __FILE__ ) ) );

$url = "";
if( isset( $_GET['url'] ) )
{
	$url = $_GET['url'];
}

require_once( ROOT . DS . 'system' . DS . 'core' . DS . 'bootstrap.php' );
?>