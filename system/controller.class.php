<?
class Controller
{
	protected $_controller;		# controller name
	protected $_action;			# controllers action
	protected $_template;		# template
	protected $_url;			# url that was passed
	protected $_view;			# the view to load
	
	protected $render;			# render template or not
	protected $render_wrappers;	# render header and footer
	
	protected $load;

	function __construct( $controller, $action, $render = 1 )
	{	
		$this->_controller 	= ucfirst( $controller );
		$this->_action 		= $action;
		$this->_view 		= $action;
		
		$globs = Registry::get("vars");
		$this->_url = $globs["unroutedURL"];
		
		$this->render = $render;
		$this->render_wrappers = 1;
		
		$this->load			= new Loader();
		
		// template stuff\
		if( $render == true )
		{
			$this->_template = new Template( $controller, $action );
		}
	}
	
	public function view( $name )
	{
		if( !empty( $this->_template ) )
		{
			$this->_template->view( $name );
		}
	}
	
	public function set( $name, $value )
	{
		if( !empty( $this->_template ) )
		{
			$this->_template->set( $name, $value );
		}
	}
	
	public function __get( $name )
    {
	    if( isset( $this->$name ) )
	    {
		    return $this->$name;
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
    
	public function disable_header()
	{
		$this->toggle_header( 1 );
	}
	
	public function enable_header()
	{
		$this->toggle_header( 0 );
	}

	public function toggle_header( $toggle = -1 )
	{
		$this->render_wrappers ^= 1;
		if( $toggle > -1 )
		{
			$this->render_wrappers = $toggle;
		}
	}

	public function __destruct()
	{
		if( $this->render && isset( $this->_template ) )
		{
			$this->_template->set_loader( $this->load );
			$this->_template->render( $this->render_wrappers );
		}
	}
}