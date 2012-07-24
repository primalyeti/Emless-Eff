<?php
class Template 
{
	protected $_controller;
	protected $_view;
	protected $variables = array();
	
	public $load = null;
	
	function __construct( $controller, $action )	
	{
		$this->_controller = $controller;
		$this->_view = $action;
	}

	private function view( $view )
	{
		$this->_view = $view;
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
	
	public function __get( $name )
    {
	    if( array_key_exists( $name, $this->variables ) )
		{
			return $this->variables[$name];
		}
		
		if( $this->load->$name !== NULL )
		{
			return $this->load->$name;
		}
		
		$EmlessF = Registry::get("EmlessF");
		$val = $EmlessF->$name;
	    if( $val !== NULL )
	    {
		    return $val;
		}
		
		$val = Registry::get( $name );
	    if( $val !== NULL )
	    {
		    return $val;
		}
		
		return null;
    }
    
    public function set_loader( $loader )
    {
	    $this->load = $loader;
    }

	/** Display Template **/
    function render( $render_wrappers = 0 )
	{	
		$this->load->library("html");
		
		extract( $this->variables );
		$EmlessF = Registry::get("EmlessF");
		
		if( PRINT_GLOBALS )
		{
			echo "<br><pre>" . print_r( $_SESSION, true ) . "</pre><br />";
			echo "<pre>" . print_r( $_COOKIE, true ) . "</pre><br />";
		}
		
		if( $render_wrappers == 1 )
		{	
			$headerPath = ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php';
			if( file_exists( $headerPath ) )
			{
				include( $headerPath );
			}
			else
			{
				include( ROOT . DS . 'application' . DS . 'views' . DS . 'header.include.php' );
			}
		}
		
		$viewPath = ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_view . '.php';
		if( file_exists( $viewPath ) )
		{
			include( $viewPath );
		}
			
		if( $render_wrappers == 1 )
		{
			$footerPath = ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php';
			if( file_exists( $footerPath ) )
			{
				include( $footerPath );
			}
			else
			{
				include( ROOT . DS . 'application' . DS . 'views' . DS . 'footer.include.php' );
			}
		}
    }

	function include_file( $file_name )
	{
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
	
	function module( $controller, $action )
	{	
		EmlessF::action( $controller, $action, array(), 1 );
	}
}