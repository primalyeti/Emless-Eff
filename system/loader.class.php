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
		$class = ucfirst( $class );
		
		if( file_exists( $path ) && !isset( $this->loaded[strtolower($class)] ) )
		{
			include_once( $path );
			
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