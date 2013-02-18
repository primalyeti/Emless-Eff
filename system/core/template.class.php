<?php
class Template 
{
	protected $_controller;
	protected $_views;
	protected $_vars = array();
	
	function __construct( $controller, $action )	
	{
		$this->_controller = $controller;
		$this->_view = $action;
	}

	/** Set Variables **/
	final public function set_variables( $arr )
	{
		$this->_vars = $arr;
	}
	
	final public function set_views( $arr )
	{
		$this->_views = $arr;
	}
	
	final public function load()
	{
		return $this->framework->load();
	}
	
	/** Set Variables **/
	final public function get( $name )
	{
		if( isset( $this->_vars[$name] ) )
		{
			return $this->_vars[$name];
		}
		
		return false;
	}
	
	public function __get( $name )
    {
	    $val = Registry::get( $name );
	    if( $val !== false )
	    {
		    return $val;
		}
		
		if( $this->load()->$name != false )
		{
			return $this->load()->$name;
		}
		
		return true;
    }
    
	/** Display Template **/
    final public function render()
	{	
		$this->load()->library( "html" );
		
		extract( $this->_vars );
		
		if( DEVELOPMENT_PRINT_GLOBALS )
		{
			echo "<br><pre>" . print_r( $_SESSION, true ) . "</pre><br />";
			echo "<pre>" . print_r( $_COOKIE, true ) . "</pre><br />";
		}
		
		foreach( $this->_views as $view )
		{
			$pathItems = explode( "/", $view );
			
			if( count( $pathItems ) == 1 )
			{
				$path = ROOT . DS . 'application' . DS . ( Registry::get("isAdmin") == true ? 'admin' . DS : "" ) . 'views' . DS . $this->_controller . DS . $view . '.php';

				if( file_exists( $path ) )
				{
					include( $path );
				}
				continue;
			}
			
			if( count( $pathItems ) > 1 )
			{
				$path = ROOT . DS . 'application' . DS . ( Registry::get("isAdmin") == true ? 'admin' . DS : "" ) . 'views' . DS . $view . '.php';
				
				if( file_exists( $path ) )
				{
					include( $path );
				}
			}
		}
    }

	final public function include_file( $file_name )
	{
		$this->load()->library( "html" );
	
		extract( $this->_vars );
		
		$path = ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $file_name;
		$pathUp = ROOT . DS . 'application' . DS . 'views' . DS . $file_name;
		
		if( file_exists( $path ) )
		{
			include( $path  );
		}
		else if( file_exists( $pathUp ) )
		{
			include( $pathUp );
		}
	}
	
	final public function module( $controller, $action, $query = null )
	{	
		return Framework::action( $controller, $action, $query, 1 );
	}
}