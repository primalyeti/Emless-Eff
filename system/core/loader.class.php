<?
class Loader
{
	public $loaded = array();
	public $included = array();

	public function helper( $helper )
	{
		$path = ROOT . DS . 'system' . DS . 'helpers' . DS . $helper . ".php";
	
		if( file_exists( $path ) && !isset( $this->included[strtolower($helper)] ) )
		{
			include( $path );
			$this->included[strtolower($helper)] = true;
			
			return true;
		}
		
		return false;
	}
	
	public function library( $class )
	{
		$path = ROOT . DS . 'system' . DS . 'library' . DS . $class . ".class.php";
		$class = ucfirst( $class );
				
		if( file_exists( $path ) && !isset( $this->loaded[strtolower($class)] ) )
		{
			include_once( $path );
			
			if( class_exists( $class ) )
			{
				$this->loaded[strtolower($class)] = new $class();
				return true;
			}
		}
		else if( isset( $this->loaded[strtolower($class)] ) )
		{
			return $this->loaded[strtolower($class)];
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