<?php
class Template 
{
	protected $_controller;
	protected $_views;
	protected $_vars = array();
	
	final function __construct( $controller, $action )	
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
	
	final public function __get( $name )
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
		if( Registry::get( "_isAdmin" ) )
		{
			$this->load()->library( "ahtml" );
		}
		
		extract( $this->_vars );
		
		if( DEVELOPMENT_PRINT_GLOBALS )
		{
			echo "<br><pre>" . print_r( $_SESSION, true ) . "</pre><br />";
			echo "<pre>" . print_r( $_COOKIE, true ) . "</pre><br />";
			echo "<pre>" . print_r( $_GET, true ) . "</pre><br />";
			echo "<pre>" . print_r( $_POST, true ) . "</pre><br />";
		}
		
		foreach( $this->_views as $view )
		{
			include( $view );
		}
    }

	final public function module( $controller, $action, $query = null, $isAdmin = false )
	{	
		return Framework::action( $controller, $action, $query, 1, $isAdmin );
	}
}