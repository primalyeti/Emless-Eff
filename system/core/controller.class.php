<?
class Controller
{
	protected $_controller;		# controller name
	protected $_action;			# controllers action
	protected $_isAdmin;
	
	protected $_template;		# template
	protected $_views = array();# the view to load
	protected $_vars = array(); # template vars
	
	protected $render;			# render template or not
	protected $render_header;	# render header
	protected $render_footer;	# render footer
		
	final function __construct( $controller, $action, $render = 1, $isAdmin = false )
	{	
		$this->_controller 	= ucfirst( $controller );
		$this->_action 		= $action;
		$this->_isAdmin		= $isAdmin;
		
		$this->render = $render;
		$this->render_header = 1;
		$this->render_footer = 1;
		
		// track user
		if( TRACKER_TYPE == 'CONTROLLER' )
		{
			Registry::get("tracker")->push( Registry::get( "url" ) );
		}
	}
	
	final public function view( $view )
	{
		$this->append_view( $view );
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

	final public function remove_view( $view )
	{
		if( $key = array_search( $view, $this->_views ) )
		{
			unset( $this->_views[$key] );
			return true;
		}
		
		return false;
	}

	final public function clear_views()
	{
		$this->_views = array();
	}

	final private function append_view( $view, $first = false )
	{
		$viewArray 	= explode( '/', $view );
		$admin		= ( $this->_isAdmin ? 'admin' : '' );
		$base_path 	= ROOT . DS . 'application'. DS;
		$pathArray 	= array();
 
		// try the controller folder if no leading slash
		if( $viewArray[0] != '' )
		{
			array_push( $pathArray, $base_path . $admin . DS . 'views' . DS . $this->_controller . DS . $view . '.php' );
		}
		
		// if admin try the admin base view folder
		if( $this->_isAdmin )
		{
			array_push( $pathArray, $base_path . $admin . DS . 'views' . DS . $view . '.php' );
		}
		
		// try the absolute base path
		array_push( $pathArray, $base_path . 'views' . ( $viewArray[0] != '' ? DS : "" ) . $view . '.php' );
 
		foreach( $pathArray as $path )
		{
			if( file_exists( $path ) )
			{
				$callback = "array_" . ( $first ? "unshift" : "push" );
				$callback( $this->_views, $path );
				break;
			}
		}
	}
 
	final public function __destruct()
	{
		if( $this->render )
		{
			global $defaultViews;
		
			// track user
			if( TRACKER_TYPE == 'TEMPLATE' )
			{
				Registry::get( "tracker" )->push( Registry::get( "url" ) );
			}
		
			$this->_template = new Template( strtolower( $this->_controller ), strtolower( $this->_action ) );
			
			if( $this->render_header )
			{
				if( !empty( $defaultViews['beforeView'] ) )
				{
					foreach( array_reverse( $defaultViews['beforeView'] ) as $view )
					{
						$this->append_view( $view, true );
					}
				}
			}
			
			if( $this->render_footer )
			{
				if( !empty( $defaultViews['afterView'] ) )
				{
					foreach( $defaultViews['afterView'] as $view )
					{
						$this->append_view( $view );
					}
				}
			}
						
			$this->_template->set_variables( $this->_vars );
			$this->_template->set_views( $this->_views );
			
			$this->_template->render();
		}
	}
}