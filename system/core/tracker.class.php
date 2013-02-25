<?
class Tracker
{
	protected $_locs = null;
	protected $_maxItems = 5;
	protected $_loc = null;
	protected $_enabled = null;
	
	final public function __construct()
	{
		$this->_locs = array();
		if( isset( $_SESSION[TRACKER_SESSION_VAR] ) )
		{
			$this->_locs = $_SESSION[TRACKER_SESSION_VAR];
		}
		
		$this->set_enabled( TRACKER_ISON );	
	}
	
	public function __destruct()
	{
		if( $this->is_set() )
		{
			array_unshift( $this->_locs, $this->_loc );
	
			if( $this->length() > $this->_maxItems )
			{
				array_pop( $this->_locs );
			}
			
			$_SESSION[TRACKER_SESSION_VAR] = $this->_locs;
		}
	}
	
	final public function push( $loc )
	{
		if( !$this->is_enabled() || $this->is_set() || $this->last() === $loc )
		{
			return false;
		}
		
		$this->_loc = $loc;
		
		return true;
	}
	
	final public function last()
	{
		if( !$this->is_enabled() )
		{
			return false;
		}
	
		if( $this->length() > 0 )
		{
			return $this->get( 0 );
		}
		
		return false;
	}
	
	final public function get( $key )
	{
		if( !$this->is_enabled() )
		{
			return false;
		}
	
		if( isset( $this->_locs[$key] ) )
		{
			return $this->_locs[$key];
		}
		
		return false;
	}
	
	final public function set_enabled( $val )
	{
		$this->_enabled = $val;
	}
	
	final protected function is_set()
	{
		return !( $this->_loc === null );
	}
	
	final protected function is_enabled()
	{
		return $this->_enabled;
	}
	
	final protected function length()
	{
		return count( $this->_locs );
	}
}