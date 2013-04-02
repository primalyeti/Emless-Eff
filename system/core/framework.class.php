<?
class Framework
{
	protected $_url;		# unparsed url
	protected $_loader;
	
	final public function __construct( $url )
	{		
		if( !$this->verify_settings() )
		{
			die();
		}
		
		if( empty( $url ) )
		{
			$url = "";
		}
		
		// set url in registry
		Registry::set( "url", $url, true );
		$this->_url 	= $url;
		
		// start profiling
		$this->set_profiler();
		
		// set loader
		$this->_loader 	= new Loader();
		
		// set tracker
		$tracker = new Tracker();
		Registry::set( "tracker", $tracker, true );
		
		// clean and prep everything
		$this->set_reporting();
		$this->remove_magic_quotes();
		$this->register_globals_to_framework();
		$this->unregister_globals();
	}
	
	final public function __destruct()
	{
		Registry::get("profiler")->stop_time( "page" );
		Registry::get("profiler")->log_data();
	}
	
	final public function init()
	{
		// load defaults
		global $defaultPage;
		
		$queryString = array();
		
		$controller = $defaultPage['controller'];
		$action 	= $defaultPage['action'];
		
		// if its not defaults
		if( isset( $this->_url ) )
		{
			// route the url
			$url = $this->route_url( $this->_url );
			$urlArray = explode( "/", $url );
			
			//clear all bogus ones
			for( $i = 0; $i < count( $urlArray ); $i++ )
			{
				if( $urlArray[$i] == "" )
				{
					unset( $urlArray[$i] );
				}
			}
			
			// get controller
			if( isset( $urlArray[0] ) )
			{
				$controller = preg_replace( "/[^a-z0-9\/]/i", "_", $urlArray[0] );
				
				// pop off for action call
				array_shift( $urlArray );
			
				// if is admin
				if( $controller == ADMIN_ALIAS )
				{
					// set in registry
					Registry::set( "isAdmin", true, true );
					
					// load default admin
					$controller = $defaultPage['admin']['controller'];
					$action 	= $defaultPage['admin']['action'];
				}
				else if( $controller == AJAX_ALIAS )
				{
					// set in registry
					Registry::set( "isAjax", true, true );
					
					$controller = $defaultPage['controller'];
				}
				else if( $controller == SCRIPTS_ALIAS )
				{
					Registry::set( "isScript", true, true );
				}
				
				if( Registry::get( "isAdmin"  ) || Registry::get( "isAjax"  ) )
				{
					// get admin controller
					if( isset( $urlArray[0] ) )
					{
						$controller = $urlArray[0];
						array_shift( $urlArray );
					}
				}
			}
			
			// get action
			if( isset( $urlArray[0] ) )
			{
				$action = preg_replace( "/[^a-z0-9\/]/i", "_", $urlArray[0] );
				array_shift( $urlArray );
			}
			
			// query string
			$queryString = $urlArray;
		}
		
		// set admin
		Registry::set( "isAdmin", false );
		
		// init dbh
		$dbh = new SQLQuery( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
		Registry::set( "dbh", $dbh, true );
		
		// is script
		if( Registry::get( "isScript" ) )
		{
			$newUrl = explode( "/", $this->_url );
			array_shift( $newUrl );
			
			$scripts_dir = ROOT . DS . 'application' . DS . 'scripts' . DS;
			$script_file = implode( "/", $newUrl );
			$script_path = $scripts_dir . $script_file;
			
			if( file_exists( $script_path  ) && is_file( $script_path ) )
			{
				$pwd = getcwd();
				chdir( dirname( $script_path ) );
				
				require_once( $script_path );
				
				chdir( $pwd );
			}
			else
			{
				echo "Script not found ";
			}
		}
		else
		{
			$controllerName = ucfirst( $controller ) . 'Controller';
			if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true && DEVELOPMENT_SHOW_CONTROLLER == true )
			{
				echo "Original: " . $controllerName . " C: " . $controller . " A: " . $action . " Q: " . implode( ",", $queryString ) . "<br>";
			}
				
			if( !class_exists( $controllerName ) || !method_exists( $controllerName, $action ) )
			{
				$controllerName = "ErrorsController";
				$controller 	= "errors";
				$action 		= "index";
				
				Registry::get("tracker")->set_enabled( false );
			}
			
			if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true && DEVELOPMENT_SHOW_CONTROLLER == true )
			{
				echo "After Check: " . $controllerName . " C: " . $controller . " A: " . $action . " Q: " . implode( ",", $queryString ) . "<br>";
			}
			
			// init controller, if its an ajax call, do not render
			$dispatch = new $controllerName( $controller, $action, !Registry::get( "isAjax"  ) );
			
			require_once( ROOT . DS . 'application' . DS . "config" . DS . "init_hooks.php" );
			
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
	}
	
	final public function load()
	{
		return $this->_loader;
	}
	
	final public function __get( $name )
    {
	    if( isset( $this->$name ) )
	    {
		    return $this->$name;
	    }
	    
	    return false;
    }
	
	final public static function action( $controller, $action, $queryString = null, $render = 0 )
	{
		if( $queryString === null )
		{
			$queryString = array();
		}
	
		$controllerName = ucfirst( $controller ) . 'Controller';
		$dispatch = new $controllerName( $controller, $action, $render );
		return call_user_func_array( array( $dispatch, $action ), $queryString );
	}
		
	/** Check if environment is development and display errors **/
	final protected function set_reporting()
	{
		error_reporting( E_ALL /* | E_STRICT */ );
		ini_set( 'log_errors', 'On' );
		ini_set( 'error_log', ROOT . DS . 'application' . DS . LOGS_DIR . LOG_FILE_NAME );
		
		if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true )
		{
			ini_set( 'display_errors', 'On' );
		}
		else
		{
			ini_set( 'display_errors', 'Off' );
		}
	}
	
	/** Check for Magic Quotes and remove them **/
	final protected function strip_slashes_deep( $value )
	{
		if( empty( $value ) )
		{
			$value = "";
		}
		else if ( is_array( $value ) )
		{
			$value = array_map( array( "self", "strip_slashes_deep" ), $value );
		}
		else
		{
			$value = stripslashes( $value );
		}
		
		return $value;
	}
	
	final protected function register_globals_to_framework()
	{
		Registry::set( "post", ( isset( $_POST ) ? $_POST : array() ), true );
		Registry::set( "get", ( isset( $_GET ) ? $_GET : array() ), true );
		Registry::set( "session", ( isset( $_SESSION ) ? $_SESSION : array() ), true );
		Registry::set( "cookie", ( isset( $_COOKIE ) ? $_COOKIE : array() ), true );
		Registry::set( "files", ( isset( $_FILES ) ? $_FILES : array() ), true );
	}
	
	final protected function remove_magic_quotes()
	{
		if( get_magic_quotes_gpc() )
		{
			$_GET    = $this->strip_slashes_deep( $_GET );
			$_POST   = $this->strip_slashes_deep( $_POST );
			$_COOKIE = $this->strip_slashes_deep( $_COOKIE );
		}
	}
	
	/** Check register globals and remove them **/
	final protected function unregister_globals()
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
	final protected function route_url( $url )
	{
		global $routing;
		
		if( !empty( $routing ) )
		{
			foreach( $routing as $pattern => $result )
			{
				if( preg_match( $pattern, $url ) )
				{
					return preg_replace( $pattern, $result, $url );
					break;
				}
			}
		}
		
		return $url;
	}
	
	final public function set_profiler()
	{
		global $profilerIgnoreList;
		
		$profiler = new Profiler();
		Registry::set( "profiler", $profiler, true );
		
		if( in_array( Registry::get( "url" ), $profilerIgnoreList ) )
		{
			Registry::get( "profiler" )->set_profiler( false );
		}
		
		Registry::get( "profiler" )->start_time( "page" );
	}
	
	/** Check Constants **/
	final protected function verify_settings()
	{
		$vars = array(
			// ** ENVIRONMENT and MySQL SETTINGS ** //
			"SITE_TITLE" => array( "", "%" ),
			"TIMEZONE" => array( "%" ),
			"ENVIRONMENT" => array( "LOCAL", "DEV", "TEST", "LIVE" ),
			"BASE_PATH" => array( "%" ),
			
			"DB_TYPE" => array( "PDO", "MYSQL" ),
			"DB_NAME" => array( "", "%" ),
			"DB_USER" => array( "", "%" ),
			"DB_PASSWORD" => array( "", "%" ),
			"DB_HOST" => array( "", "%" ),
			"DOMAIN" => array( "%" ),
			"DOMAIN_SECURE" => array( "", "%" ),
			"AUTH_KEY" => array( "", "%" ),
			
			// ** DEVELOPMENT VARIABLES ** //
			"DEVELOPMENT_ENVIRONMENT" => array( true, false ),
			"DEVELOPMENT_PRINT_GLOBALS" => array( true, false ),
			"DEVELOPMENT_SHOW_CONTROLLER" => array( true, false ),
			"LOG_FILE_NAME" => array( "%" ),
			"LOG_CUST_ERR_FILE_NAME" => array( "%" ),
			
			// ** PATH VARIABLES ** //
			"FILE_DIR" => array( "%" ),
			"FILE_TEMP_DIR" => array( "%" ),
			"TEMP_DIR" => array( "%" ),
			"CACHE_DIR" => array( "%" ),
			"LOGS_DIR" => array( "%" ),
			"SESSION_DIR" => array( "%" ),
			"TRACKER_DIR" => array( "%" ),
			
			// ** MISCELLANEOUS VARIABLES ** //
			"CACHE_ISON" => array( true, false ),
			"CACHE_DEFAULT_LIFESPAN" => array( "%" ),
			"PROFILER_ISON" => array( true, false ),
			"TRACKER_ISON" => array( true, false ),
			"TRACKER_TYPE" => array( 'CONTROLLER', 'TEMPLATE' ),
			"TRACKER_SESSION_VAR" => array( "%" ),
			"HONEYPOT_ACTIVE" => array( true, false ),
			"ADMIN_SESSION_VAR" => array( "%" ),
			"ADMIN_ALIAS" => array( "%" ),
			"SCRIPTS_ALIAS" => array( "%" ),
			"AJAX_ALIAS" => array( "%" ),
		);
		
		$valid = true;
		foreach( $vars as $var => $rules )
		{
			if( !defined( $var ) )
			{
				echo $var . " is not defined<br>";
				$valid = false;
				continue;
			}
			
			// "" is allowed to be empty
			// "%" is allowed to be anything
			// "wtv" needs to be that 
			
			$constVal = constant( $var );
			
			// var is blank, and has blank rule
			if( empty( $constVal ) && in_array( "", $rules ) )
			{
				continue;
			}
			
			// var is not empty but has anythign rule, we're good
			if( !empty( $constVal ) && in_array( "%", $rules ) )
			{
				continue;
			}
			
			// var is not empty and has specific values
			if( in_array( $constVal, $rules ) )
			{
				continue;
			}
			
			// it failed
			echo $var . " does not have a valid value<br>";
			$valid = false;
		}
		
		if( !$valid )
		{
			echo "One or more required config settings were missing. Cannot run framework.";
		}
		
		return $valid;
	}
}
