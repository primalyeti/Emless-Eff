<?
class SQLCell
{
	protected $_val;

	public function __construct( $val = "" )
	{
		$this->_val = $val;
	}

	public function __set( $key, $value )
	{
		$this->_val = $value;
	}

	public function __get( $key )
	{
		return $this->_val;
	}

	public function __isset( $key )
	{
		return true;
	}

	public function __toString()
	{
		return (string) $this->_val;
	}

	public function as_array()
	{
		return $this->_val;
	}
}