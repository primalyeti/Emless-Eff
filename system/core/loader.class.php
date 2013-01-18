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
	
		foreach( array( "system", "application" ) as $location )
		{
			$path = ROOT . DS . $location . DS . 'helpers' . DS . $helper . ".php";
		
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
	
		foreach( array( "system", "application" ) as $location )
		{
			$path = ROOT . DS . $location . DS . 'library' . DS . $class . ".class.php";
			$class = ucfirst( $class );
			
			if( file_exists( $path ) )
			{
				include_once( $path );
				
				if( class_exists( $class ) )
				{
					$this->loaded[strtolower($class)] = new $class();
					return true;
				}
			}
		}
		
		return false;
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