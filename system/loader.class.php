<?
class Loader
{
	public $loaded = array();

	public function helper( $helper )
	{
		$path = ROOT . DS . 'helpers' . DS . $helper . ".php";
	
		if( file_exists( $path ) )
		{
			include( $path );
		}
	}
	
	public function library( $class )
	{
		$path = ROOT . DS . 'library' . DS . $class . ".class.php";
	
		if( file_exists( $path ) )
		{
			include( $path );
			$class = ucfirst( $class );
			
			if( class_exists( $class ) )
			{
				$this->loaded[strtolower($class)] = new $class();
			}
		}
		
		return null;
	}
	
	public function __get( $name )
    {
    	if( isset( $this->loaded[$name] ) )
	    {
		    return $this->loaded[$name];
		}
		
		return null;
    }

}