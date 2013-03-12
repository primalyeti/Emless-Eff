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
		
		if( isset( $this->$name ) )
	    {
		    return $this->$name;
		}
		
		return false;
    }
}