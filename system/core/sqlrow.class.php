<?
class SQLRow
{
	protected $_row = array();

	public function __construct( $tables = array() )
	{
		if( !empty( $tables ) )
		{
			foreach( $tables as $key => $value )
			{
				$this->$key = $value;
			}
		}
	}

	public function __set( $key, $value )
	{
		if( is_array( $value ) )
		{
			$cell = new SQLTable( $value );
			$this->_row[$key] = $cell;
		}
		else if( $value instanceof SQLResult )
		{
			$this->_row[$key] = $value;
		}
		else if( $value instanceof SQLRow || $value instanceof SQLTable )
		{
			$this->_row[$key] = $value;
		}
	}

	public function __get( $key )
	{
		if( !isset( $this->_row[$key] ) )
		{
			return null;
		}

		return $this->_row[$key];
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

	public function as_array()
	{
		$tableArr = array();
		foreach( $this->_row as $key => $table )
		{
			$tableArr[$key] = $table->as_array();
		}

		return $tableArr;
	}

	public function as_object( $arr )
	{
		if( is_array( $arr ) )
		{
			$arr = (object) $arr;
		}

		if( is_object( $arr ) )
		{
			$new = new stdClass();

			foreach( $arr as $key => $val )
			{
				$new->{$key} = $this->as_object( $val );
			}
		}
		else
		{
			$new = $arr;
		}

		return $new;
	}
}