<?
class Controller
{
	protected $_controller;		# controller name
	protected $_action;			# controllers action
	protected $_template;		# template
	protected $_url;			# url that was passed
	
	protected $render;
	protected $doNotRenderHeader;

	function __construct( $controller, $action, $render = 1 )
	{	
		$this->_controller = ucfirst( $controller );
		$this->_action = $action;
		
		$globs = Registry::get("vars");
		$this->_url = $globs["unroutedURL"];
		
		$this->render = $render;
		$this->doNotRenderHeader = 0;
		
		// template stuff
		$this->_template = new Template( $controller, $action );
	}
	
	function set( $name, $value )
	{
		$this->_template->set( $name, $value );
	}

	function toggleHeader()
	{
		$this->doNotRenderHeader ^= 1;
	}

	function __destruct()
	{
		if( $this->render )
		{
			$this->_template->render( $this->doNotRenderHeader );
		}
	}
}