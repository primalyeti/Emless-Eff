<?
class Loader
{
	public $loaded = array();
	public $included = array();

	public function helper( $helper )
	{
		if( isset( $this->included[strtolower($helper)] ) )
		{
			return true;
		}
		
		$locations = $this->get_locations();
		
		foreach( $locations as $loc )
		{
			$path = ROOT . DS . $loc . DS . "helpers" . DS . $helper . ".php";
	
			if( file_exists( $path ) )
			{
				include( $path );
				$this->included[strtolower($helper)] = true;
				
				return true;
			}
		}
		
		return false;
	}
	
	public function library( $class )
	{
		if( isset( $this->loaded[strtolower($class)] ) )
		{
			return $this->loaded[strtolower($class)];
		}
	
		$locations = $this->get_locations();
	
		foreach( $locations as $location )
		{
			$path = ROOT . DS . $location . DS . 'library' . DS . $class . ".class.php";
			$class = ucfirst( $class );
			
			if( file_exists( $path ) )
			{
				include_once( $path );
				
				if( class_exists( $class ) )
				{
					$newObj = new $class();
					if( !is_subclass_of( $newObj, 'Library' ) )
					{
						unset( $newObj );
						return false;
					}
					
					$this->loaded[strtolower($class)] = $newObj;
					return $this->loaded[strtolower($class)];
				}
			}
		}
		
		return false;
	}
	
	public function unload( $key )
    {
	    if( isset( $this->loaded[$name] ) )
	    {
		    unset( $this->loaded[$name] );
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