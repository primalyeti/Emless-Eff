<?
/** Main Call Function **/
function init()
{
	global $url;
	global $default;
	global $queryString;
	
	$unroutedURL = $url;
	$queryString = array();
	
	if( !isset( $url ) )
	{
		$controller = $default['controller'];
		$action = $default['action'];
	}
	else
	{
		$url = routeURL( $url );
		$urlArray = explode( "/", $url );
		
		$controller = $urlArray[0];
		array_shift( $urlArray );
		$action = $urlArray[0];
		array_shift( $urlArray );
		$queryString = $urlArray;
	}
	
	$controllerName = ucfirst( $controller ) . 'Controller';
		
	if( !class_exists( $controllerName ) || !method_exists( $controllerName, $action ) )
	{
		$controllerName = "ErrorsController";
		$controller 	= "errors";
		$action 		= "index";
	}

	#echo $controllerName . " C: " . $controller . " A: " . $action . "<br>";
	
	// create global vars vars
	if( Registry::get( "vars" ) == NULL )
	{
		Registry::set( "vars", array(
			"queryString" => $queryString,
			"unroutedURL" => $unroutedURL,
		) );
	}
	
	$dbh = new SQLQuery( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	Registry::set( "dbh", $dbh );
	
	$cache = new Cache();
	Registry::set( "cache", $cache );
	
	$dispatch = new $controllerName( $controller, $action );
	Registry::set( "dispatch", $dispatch );
	
	init_guest();
	init_cart();
	
	if( (int) method_exists( $controllerName, $action ) )
	{
		call_user_func_array( array( $dispatch, "beforeAction" ), $queryString );
		call_user_func_array( array( $dispatch, $action ), $queryString );
		call_user_func_array( array( $dispatch, "afterAction" ), $queryString );
	}
	else
	{
		/* Error Generation Code Here */
	}
}

/** Check if environment is development and display errors **/
function setReporting()
{
	if( DEVELOPMENT_ENVIRONMENT == true )
	{
		error_reporting( E_ALL );
		ini_set( 'display_errors', 'On' );
	}
	else
	{
		error_reporting( E_ALL );
		ini_set( 'display_errors', 'Off' );
		ini_set( 'log_errors', 'On' );
		ini_set( 'error_log', ROOT . DS . LOGS_DIR . LOG_FILE_NAME );
	}
}

/** Check for Magic Quotes and remove them **/
function stripSlashesDeep( $value )
{
	$value = is_array( $value ) ? array_map( 'stripSlashesDeep', $value ) : stripslashes( $value );
	return $value;
}

function removeMagicQuotes() {
	if( get_magic_quotes_gpc() )
	{
		$_GET    = stripSlashesDeep( $_GET );
		$_POST   = stripSlashesDeep( $_POST );
		$_COOKIE = stripSlashesDeep( $_COOKIE );
	}
}

/** Check register globals and remove them **/
function unregisterGlobals()
{
    if( ini_get( 'register_globals' ) )
	{
        $array = array( '_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES' );
        foreach( $array as $value )
		{
            foreach( $GLOBALS[$value] as $key => $var )
			{
                if( $var === $GLOBALS[$key] )
				{
                    unset( $GLOBALS[$key] );
                }
            }
        }
    }
}

/** Secondary Call Function **/
function performAction( $controller, $action, $queryString = null, $render = 0 )
{
	$controllerName = ucfirst( $controller ) . 'Controller';
	$dispatch = new $controllerName( $controller, $action, $render );
	return call_user_func_array( array( $dispatch, $action ), $queryString );
}

/** Routing **/
function routeURL( $url )
{
	global $routing;

	foreach( $routing as $pattern => $result )
	{
		if( preg_match( $pattern, $url ) )
		{
			return preg_replace( $pattern, $result, $url );
		}
	}

	return $url;
}

/** Autoload any classes that are required **/
function __autoload( $className )
{
	// load all classes
	if( file_exists( ROOT . DS . 'library' . DS . strtolower( $className ) . '.class.php' ) )
	{
		require_once( ROOT . DS . 'library' . DS . strtolower( $className ) . '.class.php' );
	}
	// load all contorllers
	else if( file_exists( ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower( $className ) . '.php' ) ) 
	{
		require_once( ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower( $className ) . '.php' );
	}
	else
	{
		/* Error Generation Code Here */
		die( "Could not load: " . $className );
	}
}

setReporting();
removeMagicQuotes();
unregisterGlobals();
session_start();
init();
?>