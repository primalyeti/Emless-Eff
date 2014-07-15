<?
class SQLTable
{
	protected $_cells = array();

	public function __construct( $cells = array() )
	{
		if( !empty( $cells ) && is_array( $cells ) )
		{
			foreach( $cells as $key => $value )
			{
				$this->$key = $value;
			}
		}
	}

	public function __set( $key, $value )
	{
		#echo var_export( $value );
		if( $value instanceof SQLResult )
		{
			$this->_cells[$key] = $value;
		}
		else if( $value instanceof SQLRow || $value instanceof SQLTable )
		{
			#echo "+++";
			$this->_cells[$key] = $value;
		}
		else
		{
			$this->_cells[$key] = $value;
		}
	}

	public function __get( $key )
	{
		if( !isset( $this->_cells[$key] ) )
		{
			return null;
		}

		return $this->_cells[$key];
	}

	public function __isset( $key )
	{
		$val = $this->$key;

		return ( $val !== null );
	}

	public function __toString()
	{
		return print_r( $this->as_array(), true );
	}

	public function length()
	{
		return count( $this->_cells );
	}

	public function as_array()
	{
		$cellsArr = array();
		foreach( $this->_cells as $key => $cell )
		{
			$cellsArr[$key] = $cell;
		}

		return $cellsArr;
	}
}