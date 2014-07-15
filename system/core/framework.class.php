<?
class Framework
{
	protected $_url;		# unparsed url

	protected $_controller;
	protected $_action;
	protected $_controllerName;
	protected $_queryString = array();

	protected $_isAdmin = false;
	protected $_isAjax = false;
	protected $_isScript = false;
	protected $_defaultPage = array(
		"controller" 		=> "",
		"action" 			=> "",
		"admin"	=> array(
			"controller" 	=> "",
			"action" 		=> "",
		),
	);

	protected $_loader;
	protected $_dbh;
	protected $_tracker;
	protected $_profiler;

	final public function __construct( $url )
	{
		if( !$this->verify_settings() )
		{
			die();
		}

		// load defaults
		global $defaultPage;

		if( !isset( $defaultPage ) )
		{
			die();
		}

		$this->_defaultPage = $defaultPage;

		if( empty( $url ) )
		{
			$url = "";
		}

		// set url in registry
		$this->_url 	= $url;

		// start profiling
		$this->init_addons();

		// clean and prep everything
		$this->set_reporting();
		$this->remove_magic_quotes();
		$this->unregister_globals();
		$this->parse_url();
		$this->init_registry();
	}

	final public function __destruct()
	{
		$this->_profiler->stop_time( "page" );
		$this->_profiler->log_data();
	}

	final public function run()
	{
		// is script
		if( $this->_isScript )
		{
			$this->run_as_script();
			return;
		}

		// init controller, if its an ajax call, do not render
		$dispatch = new $this->_controllerName( $this->_controller, $this->_action, !$this->__isAjax, $this->_isAdmin );

		if( $this->_isAjax )
		{
			$this->_tracker->set_enabled( false );
			$this->load()->library( "ajax" );
			require_once( ROOT . DS . 'application' . DS . "config" . DS . "ajax_hooks.php" );
		}
		else
		{
			require_once( ROOT . DS . 'application' . DS . "config" . DS . "init_hooks.php" );
		}

		if( (int) method_exists( $this->_controllerName, $this->_action ) )
		{
			call_user_func_array( array( $dispatch, "beforeAction" ), $this->_queryString );
			call_user_func_array( array( $dispatch, $this->_action ), $this->_queryString );
			call_user_func_array( array( $dispatch, "afterAction" ), $this->_queryString );
		}
		else
		{
			die( "Error 0: Framework could not init" );
		}
	}

	final public function run_as_script()
	{
		$newUrl = explode( "/", $this->_url );
		array_shift( $newUrl );

		$scripts_dir = ROOT . DS . 'application' . DS . 'scripts' . DS;
		$script_file = implode( "/", $newUrl );
		$script_path = $scripts_dir . $script_file;

		if( !file_exists( $script_path  ) || !is_file( $script_path ) )
		{
			echo "Script not found";
			return;
		}

		$pwd = getcwd();
		chdir( dirname( $script_path ) );

		require_once( $script_path );

		chdir( $pwd );
	}

	final public function action( $controller, $action, $queryString = null, $render = 0, $isAdmin = false )
	{
		$oldTrackerVal = $this->_tracker->is_enabled();

		$this->_tracker->set_enabled( false );

		if( $queryString === null )
		{
			$queryString = array();
		}

		$controllerName = ucfirst( $controller ) . 'Controller';
		$dispatch = new $controllerName( $controller, $action, $render, $isAdmin );

		$this->_tracker->set_enabled( $oldTrackerVal );

		return call_user_func_array( array( $dispatch, $action ), $queryString );
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

	final protected function init_addons()
	{
		$this->_loader 	= new Loader();

		$this->init_profiler();

		// set tracker
		$this->_tracker = new Tracker();

		// init dbh
		$this->_dbh = new SQLConn( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	}

	final protected function init_profiler()
	{
		global $profilerIgnoreList;

		$this->_profiler = new Profiler();

		if( in_array( $this->_url, $profilerIgnoreList ) )
		{
			$this->_profiler->set_profiler( false );
		}

		$this->_profiler->start_time( "page" );
	}

	final protected function init_registry()
	{
		Registry::set( "_url", $this->_url, true );

		Registry::set( "_controller", $this->_controller, true );
		Registry::set( "_action", $this->_action, true );

		Registry::set( "_isAdmin", $this->_isAdmin, true );
		Registry::set( "_isScript", $this->_isScript, true );
		Registry::set( "_isAjax", $this->_isAjax, true );

		Registry::set( "_dbh", $this->_dbh, true );
		Registry::set( "dbh", $this->_dbh, true );

		Registry::set( "_tracker", $this->_tracker, true );
		Registry::set( "_profiler", $this->_profiler, true );

		Registry::set( "_framework", $this, true );
	}

	final protected function parse_url()
	{
		$queryString = array();

		$controller = $this->_defaultPage['controller'];
		$action 	= $this->_defaultPage['action'];

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
					$this->_isAdmin = true;
					Registry::set( "_isAdmin", $this->_isAdmin );

					// load default admin
					$controller = $this->_defaultPage['admin']['controller'];
					$action 	= $this->_defaultPage['admin']['action'];
				}
				else if( $controller == AJAX_ALIAS )
				{
					// set in registry
					$this->_isAjax = true;

					$controller = $this->_defaultPage['controller'];
				}
				else if( $controller == SCRIPTS_ALIAS )
				{
					$this->_isScript = true;
				}

				if( $this->_isAdmin || $this->_isAjax )
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

			$this->_tracker->set_enabled( false );
		}

		$this->_controllerName = $controllerName;
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_queryString = $queryString;

		if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true && DEVELOPMENT_SHOW_CONTROLLER == true )
		{
			echo "After Check: " . $controllerName . " C: " . $controller . " A: " . $action . " Q: " . implode( ",", $queryString ) . "<br>";
		}
	}

	/** Check if environment is development and display errors **/
	final protected function set_reporting()
	{
		error_reporting( E_ALL /* | E_STRICT */ );
		ini_set( 'log_errors', 'On' );
		ini_set( 'error_log', LOGS_DIR . LOG_FILE_NAME );

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
			"PROFILER_IDENTIFIER" => array( "%" ),
			"PROFILER_RATE" => array( "#" ),
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
			// "#" is numbers
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

			// var is not empty but has a number rule, we're good
			if( !empty( $constVal ) && in_array( "#", $rules ) && is_int( $constVal ) )
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
