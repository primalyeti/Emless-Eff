<?
class Global_link
{
	public $link;
	
	public function __construct( $global )
	{
		$this->link = $global;
	}
    
    public function __get( $name )
    {
	    if( array_key_exists( $name, $this->link ) )
	    {
		    return $this->link[$name];
		}
		return null;
    }
}