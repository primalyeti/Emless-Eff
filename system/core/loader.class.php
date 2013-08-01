<?
class Loader
{
	public $loaded = array();
	public $included = array();

	public function helper( $helper )
	{
		$helper = strtolower( $helper );
		
		if( isset( $this->included[$helper] ) )
		{
			return true;
		}
		
		$locations = $this->get_locations();
		
		foreach( $locations as $loc )
		{
			$path = ROOT . DS . $loc . DS . "helpers" . DS . $helper . ".php";
	
			if( file_exists( $path ) )
			{
				require_once( $path );
				$this->included[$helper] = true;
				
				return true;
			}
		}
		
		return false;
	}
	
	public function library( $class, $args = array(), $include_only = false )
	{
		$class = strtolower( $class );
		
		if( isset( $this->loaded[$class] ) )
		{
			return $this->loaded[$class];
		}
	
		$locations = $this->get_locations();
	
		foreach( $locations as $location )
		{
			$path = ROOT . DS . $location . DS . 'library' . DS . $class . ".class.php";
			
			if( file_exists( $path ) )
			{
				require_once( $path );
				
				$this->loaded[$class] = null;
				
				if( $include_only == true )
				{
					return true;
				}
				
				$class_name = ucfirst( $class );
				
				if( class_exists( $class_name ) )
				{
					if( empty( $args ) )
					{
						$newObj = new $class_name();
					}
					else
					{
						$r = new ReflectionClass( $class_name );
						$newObj = $r->newInstanceArgs( $args );
						unset( $r );
					}
					
					if( !is_subclass_of( $newObj, 'Library' ) )
					{
						unset( $newObj );
						return false;
					}
					
					$this->loaded[$class] = $newObj;
					return $this->loaded[$class];
				}
			}
		}
		
		return false;
	}
	
	public function unload( $key )
    {
	    if( isset( $this->loaded[$key] ) )
	    {
		    unset( $this->loaded[$key] );
		    return true;
	    }
	    
	    return false;
    }
	
	protected function get_locations()
	{
		$locations = array(
			"system",
			"application",
		);
		
		if( Registry::get( "isAdmin" ) )
		{
			array_splice( $locations, 1, 0, array( "application" . DS . "admin" ) );
		}
		
		return $locations;
	}
	
	public function __get( $name )
    {
    	if( isset( $this->loaded[$name] ) )
	    {
		    return $this->loaded[$name];
		}
		
		return false;
    }

}