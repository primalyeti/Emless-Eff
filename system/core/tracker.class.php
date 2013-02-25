<?
class Tracker
{
	protected $_locs = null;
	protected $_maxItems = 5;
	
	final public function __construct()
	{
		if( isset( $_SESSION[TRACKER_SESSION_VAR] ) )
		{
			$this->_locs = $_SESSION[TRACKER_SESSION_VAR];
			return;
		}
		
		$this->_locs = array();
	}
	
	public function __destruct()
	{
		$_SESSION[TRACKER_SESSION_VAR] = $this->_locs;
	}
	
	final public function push( $loc )
	{
		if( !$this->isEnabled() || $this->top() === $loc )
		{
			echo "false";
			return false;
		}
		
		array_unshift( $this->_locs, $loc );
	
		if( $this->length() > $this->_maxItems )
		{
			array_pop( $this->_locs );
		}
	}
	
	final public function top()
	{
		if( !$this->isEnabled() )
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
		if( !$this->isEnabled() )
		{
			return false;
		}
	
		if( isset( $this->_locs[$key] ) )
		{
			return $this->_locs[$key];
		}
		
		return false;
	}
	
	final protected function isEnabled()
	{
		return TRACKER_ISON;
	}
	
	final protected function length()
	{
		return count( $this->_locs );
	}
}