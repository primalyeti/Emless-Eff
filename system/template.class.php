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
	function set_variables( $arr )
	{
		$this->_vars = $arr;
	}
	
	function set_views( $arr )
	{
		$this->_views = $arr;
	}
	
	public function load()
	{
		return $this->framework->load();
	}
	
	/** Set Variables **/
	function get( $name )
	{
		if( isset( $this->_vars[$name] ) )
		{
			return $this->_vars[$name];
		}
		
		return null;
	}
	
	public function __get( $name )
    {
	    $val = Registry::get( $name );
	    if( $val !== NULL )
	    {
		    return $val;
		}
		
		if( $this->load()->$name != null )
		{
			return $this->load()->$name;
		}
		
		return null;
    }
    
	/** Display Template **/
    function render()
	{	
		$this->load()->library( "html" );
		
		extract( $this->_vars );
		
		if( PRINT_GLOBALS )
		{
			echo "<br><pre>" . print_r( $_SESSION, true ) . "</pre><br />";
			echo "<pre>" . print_r( $_COOKIE, true ) . "</pre><br />";
		}
		
		foreach( $this->_views as $view )
		{
			$pathItems = explode( "/", $view );
			
			if( count( $pathItems ) == 1 )
			{
				$path = ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $view . '.php';
				if( $this->isAdmin == true )
				{
					$path = ROOT . DS . 'admin' . DS . 'views' . DS . $this->_controller . DS . $view . '.php';
				}
				
				if( file_exists( $path ) )
				{
					include( $path );
				}
				continue;
			}
			
			if( count( $pathItems ) > 1 )
			{
				$path = ROOT . DS . 'application' . DS . 'views' . DS . $view . '.php';
				if( $this->isAdmin == true )
				{
					$path = ROOT . DS . 'admin' . DS . 'views' . DS . $view . '.php';
				}
				
				if( file_exists( $path ) )
				{
					include( $path );
				}
			}
		}
    }

	function include_file( $file_name )
	{
		$this->load()->library( "html" );
	
		extract( $this->_vars );
		
		if( file_exists( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $file_name  ) )
		{
			include( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $file_name  );
		}
		else if( file_exists( ROOT . DS . 'application' . DS . 'views' . DS . $file_name ) )
		{
			include( ROOT . DS . 'application' . DS . 'views' . DS . $file_name );
		}
	}
	
	function module( $controller, $action )
	{	
		Framework::action( $controller, $action, array(), 1 );
	}
}