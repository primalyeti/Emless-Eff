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
		
		// track user
		if( $this->_controller != ucfirst( "errors" ) && TRACKER_TYPE == 'CONTROLLER' )
		{
			Registry::get("tracker")->push( Registry::get( "url" ) );
		}
	}
	
	final public function view( $view )
	{
		array_push( $this->_views, $view );
	}
	
	final public function action( $action, $controller, $query = null, $render = 0 )
	{
		return Framework::action( $action, $controller, $query, $render );
	}
	
	final public function set( $name, $value )
	{
		$this->_vars[$name] = $value;
	}
	
	final public function load()
	{
		return $this->framework->load();
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
		
		if( isset( $this->$name ) )
	    {
		    return $this->$name;
		}
		
		return false;
    }
    
    final public function enable_render()
    {
	    $this->render = 1;
    }
    
    final public function disable_render()
    {
	    $this->render = 0;
    }
    
    final public function enable_header()
    {
	    $this->render_header = 1;
    }
    
    final public function disable_header()
    {
	    $this->render_header = 0;
    }
    
    final public function enable_footer()
    {
	    $this->render_footer = 1;
    }
    
    final public function disable_footer()
    {
	    $this->render_footer = 0;
    }
    
    final public function enable_wrappers()
	{
		$this->render_header = 1;
		$this->render_footer = 1;
	}
    
	final public function disable_wrappers()
	{
		$this->render_header = 0;
		$this->render_footer = 0;
	}

	public function __destruct()
	{
		if( $this->render )
		{
			// track user
			if( $this->_controller != "errors" && TRACKER_TYPE == 'TEMPLATE' )
			{
				Registry::get("tracker")->push( Registry::get( "url" ) );
			}
		
			$this->_template = new Template( strtolower( $this->_controller ), strtolower( $this->_action ) );
			
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