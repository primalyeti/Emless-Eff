<?
class SQLResult
{
	protected $_results			= array();
	protected $_isValid 		= false;
	protected $_query 			= null;
	protected $_params 			= null;
	protected $_stmt 			= null;
	protected $_pos 			= -1;
	protected $_wasSerialized 	= false;

	public function __contruct(){}

	public function __sleep()
	{
		return array(
			"_isValid",
			"_query",
			"_params",
			"_results",
			"_pos",
		);
	}

	public function __wakeup()
	{
		$this->_wasSerialized = true;
	}

	/* INITIALIZATION */
	/* ****************************** */
		// SETTERS
		public function set_query( $query, $params )
		{
			$this->_query = $query;
			$this->_params = $params;
		}

		public function set_stmt( $stmt )
		{
			if( get_class( $stmt ) != "PDOStatement" )
			{
				throw new Exception( "SQLResult: not a valid PDOStatement object" );
			}

			$this->_stmt = $stmt;
		}

		public function set_results( $results )
		{
			if( !is_array( $results ) )
			{
				throw new Exception( "SQLResult: not a valid result array" );
			}

			foreach( $results as $result )
			{
				$this->set_result( $result );
			}

			$this->_isValid = true;
		}

		public function set_result( $result )
		{
			$row = new SQLRow( $result );
			array_push( $this->_results, $row );
		}

		// GETTERS
		public function get_query()
		{
			return $this->_query . " - Params: " . print_r( $this->_params, true );
		}

		public function get_stmt()
		{
			return $this->_stmt;
		}

		public function get_results()
		{
			return $this->_results;
		}

	/* PRIVATE */
	/* ****************************** */
		protected function has_statement_error()
		{
			return ( $this->get_stmt()->errorCode() != "00000" );
		}

		protected function has_dbh_error()
		{
			return Registry::get("_dbh")->error();
		}

		protected function wasSerialized()
		{
			return $this->_wasSerialized;
		}

	/* ERROR DETAILS */
	/* ****************************** */
		public function isValid()
		{
			return $this->_isValid;
		}

		public function error()
		{
			if( $this->wasSerialized() && $this->isValid() )
			{
				return false;
			}
			else if(
				is_null( $this->get_stmt() )
				|| $this->has_statement_error()
				|| $this->has_dbh_error()
			)
			{
				return true;
			}

			return false;
		}

		public function errno()
		{
			if( is_null( $this->get_stmt() ) || $this->has_dbh_error() )
			{
				return Registry::get("_dbh")->errno();
			}
			else if( $this->has_statement_error() )
			{
				return $this->get_stmt()->errorCode();
			}

			return 0;
		}

		public function errdesc()
		{
			if( is_null( $this->get_stmt() ) || $this->has_dbh_error() )
			{
				return Registry::get("_dbh")->errdesc();
			}
			else if( $this->has_statement_error() )
			{
				return print_r( $this->get_stmt()->errorInfo(), true );
			}

			return "";
		}

		public function as_array()
		{
			$rowsArray = array();
			foreach( $this->_results as $key => $row )
			{
				if( method_exists( $row, "as_array" ) )
				{
					$rowsArray[$key] = $row->as_array();
					continue;
				}

				$rowsArray[$key] = $row;
			}

			return $rowsArray;
		}

	/* SETTERS, GETTERS */
	/* ****************************** */
		public function __get( $key )
		{
			if( !isset( $this->$key ) && $this->first() !== false )
			{
				return $this->first()->$key;
			}

			return null;
		}

		public function __set( $key, $value )
		{
			if( $this->first() === false )
			{
				return null;
			}

			$this->first()->$key = $value;
		}

	/* TRAVERSAL */
	/* ****************************** */
		public function first()
		{
			if( !isset( $this->_results[0] ) )
			{
				return null;
			}

			return $this->_results[0];
		}

		public function last()
		{
			if( $this->length() == 0 )
			{
				return null;
			}

			return $this->_results[$this->length()-1];
		}

		public function curr()
		{
			$pos = ( $this->_pos == -1 && $this->length() > 0 ? 0 : -1 );
			if( !isset( $this->_results[$pos] ) )
			{
				return curr;
			}

			return $this->_results[ $this->_pos ];
		}

		public function next()
		{
			if( !isset( $this->_results[$this->_pos+1] ) )
			{
				return false;
			}
			return $this->_results[++$this->_pos];
		}

		public function prev()
		{
			if( !isset( $this->_results[$this->_pos-1] ) )
			{
				return false;
			}

			return $this->_results[--$this->_pos];
		}

		public function reset()
		{
			$this->_pos = -1;
			return true;
		}

		public function position()
		{
			return $this->_pos;
		}

	/* USABILITY */
	/* ****************************** */
		public function all()
		{
			return $this->_results;
		}

		public function length()
		{
			return count( $this->_results );
		}

		public function search( $table, $field = "" )
		{
			// get position, then go to the beginning
			$pos = $this->_pos;
			$this->reset();

			// store the values
			$vals = array();
			while( $row = $this->next() )
			{
				if( isset( $row->$table->$field ) && ( $val = $row->$table->$field ) != "" )
				{
					array_push( $vals, $val );
				}
			}

			$this->_pos = $pos;

			return $vals;
		}

		public function apply_function_to_cells( $table, $field, $functionName, $additionalParams = array() )
		{
			if( !function_exists( $functionName ) )
			{
				return false;
			}

			// get position, then go to the beginning
			$pos = $this->_pos;
			$this->reset();

			// store the values
			while( $row = $this->next() )
			{
				$newVal = @call_user_func_array( $functionName, array_merge( array( $row->$table->$field ), $additionalParams ) );

				if( $newVal === false )
				{
					return false;
				}

				$row->$table->$field = $newVal;
			}

			$this->_pos = $pos;

			return true;
		}

		public function shuffle()
		{
			shuffle( $this->_results );

			return $this;
		}

		public function reverse()
		{
			$this->_results = array_reverse( $this->_results );

			return $this;
		}

		public function slice( $start, $end = -1 )
		{
			if( $end == -1 )
			{
				$end = $this->lenght();
			}

			$this->reset();

			while( $row = $this->next() )
			{
				if( $this->position() < $start || $this->position() >= $end )
				{
					unset( $this->_results[$this->position()] );
				}
			}

			$this->reset();

			return $this;
		}

		public function merge( $results2 )
		{
			if( $results2 instanceof SQLResult === false )
			{
				return false;
			}

			$new = new SQLResult();

			$rows = $this->_results;
			$rows2 = $results2->_results;

			print_r( $rows );
			print_r( $rows2 );

			$newRows = array_merge( $rows, $rows2 );

			$new->_results = $newRows;

			return $new;
		}
}