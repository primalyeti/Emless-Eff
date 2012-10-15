<?
class Controller
{
	protected $_controller;		# controller name
	protected $_action;			# controllers action
	
	protected $_template;		# template
	protected $_views = array();# the view to load
	protected $_vars = array(); # template vars
	
	protected $render;			# render template or not
	protected $render_header;	# render header
	protected $render_footer;	# render footer
		
	function __construct( $controller, $action, $render = 1 )
	{	
		$this->_controller 	= ucfirst( $controller );
		$this->_action 		= $action;
		$this->_view 		= $action;
		
		$this->render = $render;
		$this->render_header = 1;
		$this->render_footer = 1;
	}
	
	public function view( $view )
	{
		array_push( $this->_views, $view );
	}
	
	public function action( $action, $controller, $query = null, $render = 0 )
	{
		$EmlessF = Registry::get("framework");
		$EmlessF::action( $action, $controller, $query, $render );
	}
	
	public function set( $name, $value )
	{
		$this->_vars[$name] = $value;
	}
	
	public function load()
	{
		return Registry::get( "framework" )->load();
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
		
		if( isset( $this->$name ) )
	    {
		    return $this->$name;
		}
		
		return null;
    }
    
    public function enable_header()
    {
	    $this->render_header = 1;
    }
    
    public function disable_header()
    {
	    $this->render_header = 0;
    }
    
    public function enable_footer()
    {
	    $this->render_footer = 1;
    }
    
    public function disable_footer()
    {
	    $this->render_footer = 0;
    }
    
    public function enable_wrappers()
	{
		$this->render_header = 1;
		$this->render_footer = 1;
	}
    
	public function disable_wrappers()
	{
		$this->render_header = 0;
		$this->render_footer = 0;
	}

	public function __destruct()
	{
		if( $this->render )
		{
			$this->_template = new Template( $this->_controller, $this->_action );
			
			if( $this->render_header )
			{
				array_unshift( $this->_views, "/header" );
			}
			
			if( $this->render_footer )
			{
				array_push( $this->_views, "/footer" );
			}
						
			$this->_template->set_variables( $this->_vars );
			$this->_template->set_views( $this->_views );
			
			$this->_template->render();
		}
	}
}