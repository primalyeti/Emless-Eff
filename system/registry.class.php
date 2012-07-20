<?
Class Registry
{
	private static $objects = array();
	private static $instance;
	
	private function __construct()
	{
	}
	
	private function __clone()
	{
	}
	
	public static function singleton()
	{
		if( !isset( self::$instance ) )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	static function get( $key )
	{
		return self::singleton()->getter( $key );
    }
	
	static function set( $key, $instance )
	{
		return self::singleton()->setter( $key, $instance );
    }
    
    protected function getter( $key )
	{
		if( isset( self::$objects[$key] ) )
		{
			return self::$objects[$key];
        }
		return NULL;
	}
	
	protected function setter( $key, $val )
	{
		self::$objects[$key] = $val;
	}
}