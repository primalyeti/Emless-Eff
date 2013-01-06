<?
class Framework
{
	protected $_url;		# unparsed url
	protected $_loader;
	
	public function __construct()
	{
		global $url;
		
		Registry::set( "url", $url, true );
		
		$this->_url 	= $url;
		$this->_loader 	= new Loader();
		
		$this->set_reporting();
		$this->remove_magic_quotes();
		$this->register_globals_to_framework();
		$this->unregister_globals();
	}
	
	public function init()
	{
		global $default;
	
		$queryString = array();
		
		$controller = $default['controller'];
		$action 	= $default['action'];
		
		if( isset( $this->_url ) )
		{
			$url = $this->routeURL( $this->_url );
			$urlArray = explode( "/", $url );
			
			for( $i = 0; $i < count( $urlArray ); $i++ )
			{
				if( empty( $urlArray[$i] ) )
				{
					unset( $urlArray[$i] );
				}
			}
			
			// get controller
			if( isset( $urlArray[0] ) )
			{
				$controller = $urlArray[0];
				array_shift( $urlArray );
			}
			
			// if is admin
			if( $controller == ADMIN_ALIAS )
			{
				Registry::set( "isAdmin", true, true );
				$controller = $default['admin']['controller'];
				$action 	= $default['admin']['action'];
				
				// get controller
				if( isset( $urlArray[0] ) )
				{
					$controller = $urlArray[0];
					array_shift( $urlArray );
				}
			}
			
			// get action
			if( isset( $urlArray[0] ) )
			{
				$action = $urlArray[0];
				array_shift( $urlArray );
			}
			
			// query string
			$queryString = $urlArray;
		}
		
		Registry::set( "isAdmin", false );
		
		$controllerName = ucfirst( $controller ) . 'Controller';
		if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true && DEVELOPMENT_SHOW_CONTROLLER == true )
		{
			echo "Original: " . $controllerName . " C: " . $controller . " A: " . $action . "<br>";
		}
			
		if( !class_exists( $controllerName ) || !method_exists( $controllerName, $action ) )
		{
			$controllerName = "ErrorsController";
			$controller 	= "errors";
			$action 		= "index";
		}
		
		if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true && DEVELOPMENT_SHOW_CONTROLLER == true )
		{
			echo $controllerName . " C: " . $controller . " A: " . $action . "<br>";
		}
		
		$dbh = new SQLQuery( DB_TYPE, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
		Registry::set( "dbh", $dbh, true );
		
		$dispatch = new $controllerName( $controller, $action );
		
		if( !is_null( Registry::get( "framework" ) ) && file_exists( ROOT . DS . 'config' . DS . "init_hooks.php" ) )
		{
			include( ROOT . DS . "config" . DS . "init_hooks.php" );
		}
		
		if( (int) method_exists( $controllerName, $action ) )
		{
			call_user_func_array( array( $dispatch, "beforeAction" ), $queryString );
			call_user_func_array( array( $dispatch, $action ), $queryString );
			call_user_func_array( array( $dispatch, "afterAction" ), $queryString );
		}
		else
		{
			die( "Error 0: Framework could not init" );
		}
	}
	
	public function load()
	{
		return $this->_loader;
	}
	
	public function __get( $name )
    {
	    if( isset( $this->$name ) )
	    {
		    return $this->$name;
	    }
	    
	    return null;
    }
	
	public static function action( $controller, $action, $queryString = null, $render = 0 )
	{
		$controllerName = ucfirst( $controller ) . 'Controller';
		$dispatch = new $controllerName( $controller, $action, $render );
		return call_user_func_array( array( $dispatch, $action ), $queryString );
	}
		
	/** Check if environment is development and display errors **/
	protected function set_reporting()
	{
		error_reporting( E_ALL /* | E_STRICT */ );
		#set_error_handler('exceptions_error_handler'); 
		
		if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true )
		{
			ini_set( 'display_errors', 'On' );
		}
		else
		{
			ini_set( 'display_errors', 'Off' );
			ini_set( 'log_errors', 'On' );
			ini_set( 'error_log', ROOT . DS . LOGS_DIR . LOG_FILE_NAME );
		}
	}
	
	/** Check for Magic Quotes and remove them **/
	protected function strip_slashes_deep( $value )
	{
		if( empty( $value ) )
		{
			$value = "";
		}
		else if ( is_array( $value ) )
		{
			$value = array_map( array( "this", "strip_slashes_deep" ), $value );
		}
		else
		{
			$value = stripslashes( $value );
		}
		
		return $value;
	}
	
	protected function register_globals_to_framework()
	{
		Registry::set( "post", $_POST, true );
		Registry::set( "get", $_GET, true );
		Registry::set( "session", $_SESSION, true );
		Registry::set( "cookie", $_COOKIE, true );
		Registry::set( "files", $_FILES, true );
	}
	
	protected function remove_magic_quotes()
	{
		if( get_magic_quotes_gpc() )
		{
			$_GET    = $this->strip_slashes_deep( $_GET );
			$_POST   = $this->strip_slashes_deep( $_POST );
			$_COOKIE = $this->strip_slashes_deep( $_COOKIE );
		}
	}
	
	/** Check register globals and remove them **/
	protected function unregister_globals()
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
	
	/** Routing **/
	protected function routeURL( $url )
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
}