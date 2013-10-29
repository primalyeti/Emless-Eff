<?
abstract class Library
{
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
    
    final public function load()
	{
		return $this->_framework->load();
	}
}