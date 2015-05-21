<?
Class Registry
{
	private static $objects = array();
	private static $locks = array();
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

	public static function get( $key )
	{
		return self::singleton()->getter( $key );
	}

	public static function set( $key, $instance, $locked = false )
	{
		if( self::singleton()->get_lock( $key ) == false )
		{
			if( $locked )
			{
				self::singleton()->set_lock( $key );
			}

			return self::singleton()->setter( $key, $instance );
		}

		return false;
	}

	protected function get_lock( $key )
	{
		if( isset( self::$locks[$key] ) )
		{
			return true;
		}

		return false;
	}

	protected function set_lock( $key )
	{
		self::$locks[$key] = true;
	}

	protected function getter( $key )
	{
		if( isset( self::$objects[$key] ) )
		{
			return self::$objects[$key];
		}

		return false;
	}

	protected function setter( $key, $val )
	{
		self::$objects[$key] = $val;
	}
}