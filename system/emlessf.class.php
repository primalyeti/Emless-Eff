<?
class EmlessF
{
	protected $_url;
	protected $_default;
	protected $_routing;
	
	public $post;
	public $get;
	public $session;
	public $cookies;
	public $files;

	public function __construct()
	{
		global $url, $default, $routing;
		
		$this->_url = $url;
		$this->_default = $default;
		$this->_routing = $routing;
				
		$this->set_reporting();
		$this->remove_magic_quotes();
		$this->register_globals_to_framework();
		$this->unregister_globals();
	}
	
	public function init()
	{
		$queryString = array();
		
		if( !isset( $this->_url ) )
		{
			$controller = $this->_default['controller'];
			$action = $this->_default['action'];
		}
		else
		{
			$url = $this->routeURL( $this->_url );
			$urlArray = explode( "/", $url );
			
			// get controller
			$controller = $urlArray[0];
			array_shift( $urlArray );
			
			// get action
			$action = $urlArray[0];
			array_shift( $urlArray );
			
			// query string
			$queryString = $urlArray;
		}
		
		$controllerName = ucfirst( $controller ) . 'Controller';
			
		if( !class_exists( $controllerName ) || !method_exists( $controllerName, $action ) )
		{
			$controllerName = "ErrorsController";
			$controller 	= "errors";
			$action 		= "index";
		}
		
		if( DEVELOPMENT_ENVIRONMENT == true )
		{
			echo $controllerName . " C: " . $controller . " A: " . $action . "<br>";
		}
		
		$dbh = new SQLQuery();
		Registry::set( "dbh", $dbh );
		
		$dispatch = new $controllerName( $controller, $action );
		
		if( !is_null( Registry::get( "EmlessF" ) ) && file_exists( ROOT . DS . 'config' . DS . "init_hooks.php" ) )
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
	
	public function __get( $name )
    {
	    if( isset( $this->$name ) )
	    {
		    return $this->$name;
	    }
	    
	    return null;
    }
	
	static public function action( $controller, $action, $queryString = null, $render = 0 )
	{
		$controllerName = ucfirst( $controller ) . 'Controller';
		$dispatch = new $controllerName( $controller, $action, $render );
		return call_user_func_array( array( $dispatch, $action ), $queryString );
	}
		
	/** Check if environment is development and display errors **/
	protected function set_reporting()
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
	protected function strip_slashes_deep( $value )
	{
		$value = is_array( $value ) ? array_map( array( "this", 'strip_slashes_deep' ), $value ) : stripslashes( $value );
		return $value;
	}
	
	protected function register_globals_to_framework()
	{
		$this->post 	= $_POST;#new Global_link( $_POST );
		$this->get 		= $_GET;#new Global_link( $_GET );
		$this->session 	= $_SESSION;#new Global_link( $_SESSION );
		$this->cookies 	= $_COOKIE;#new Global_link( $_COOKIE );
		$this->files 	= $_FILES;#new Global_link( $_FILES );
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
		foreach( $this->_routing as $pattern => $result )
		{
			if( preg_match( $pattern, $url ) )
			{
				return preg_replace( $pattern, $result, $url );
			}
		}
	
		return $url;
	}
}