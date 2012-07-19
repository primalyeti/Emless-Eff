<?php
class Template 
{
	protected $_controller;
	protected $_action;
	protected $variables = array();
	
	function __construct( $controller, $action )	
	{
		$this->_controller = $controller;
		$this->_action = $action;
	}

	/** Set Variables **/
	function set( $name, $value )
	{
		$this->variables[$name] = $value;
	}
	
	/** Set Variables **/
	function get( $name )
	{
		if( isset( $this->variables[$name] ) )
		{
			return $this->variables[$name];
		}
		
		return null;
	}		

	/** Display Template **/
    function render( $doNotRenderHeader = 0 )
	{	
		global $isAdmin;
	
		extract( $this->variables );
		
		if( PRINT_GLOBALS )
		{
			echo "<br><pre>" . print_r( $_SESSION ) . "</pre>" . "<br />";
			echo "<pre>" . print_r( $_COOKIE ) . "</pre>" . "<br />";
		}
		
		if( $doNotRenderHeader == 0 )
		{	
			if( file_exists( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php' ) )
			{
				include( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php' );
			}
			else
			{
				include( ROOT . DS . 'application' . DS . 'views' . DS . 'header.include.php' );
			}
		}
		
		if( file_exists( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php' ) )
		{
			include( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php' );
		}
			
		if( $doNotRenderHeader == 0 )
		{
			if( file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php' ) )
			{
				include( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php' );
			}
			else
			{
				include( ROOT . DS . 'application' . DS . 'views' . DS . 'footer.include.php' );
			}
		}
    }

	function includeFile( $file_name )
	{
		global $isAdmin;
		
		extract( $this->variables );
		
		if( file_exists( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $file_name  ) )
		{
			include( ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $file_name  );
		}
		else if( file_exists( ROOT . DS . 'application' . DS . 'views' . DS . $file_name ) )
		{
			include( ROOT . DS . 'application' . DS . 'views' . DS . $file_name );
		}
	}
	
	function requireFile( $file_name )
	{
		extract( $this->variables );
		
		if( file_exists( ROOT . DS . 'library' . DS . $file_name  ) )
		{
			require_once( ROOT . DS . 'library' . DS . $file_name  );
		}
	}
	
	function module( $controller, $action )
	{	
		performAction( $controller, $action, array(), 1 );
	}
}