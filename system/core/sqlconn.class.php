<?
class SQLConn
{
	protected $_dbHandle = false;
	protected $_isConn = false;
	protected $_result;

	public function __construct( $db_host, $db_user, $db_password, $db_name )
	{
		try
		{
			$this->_dbHandle = new PDO( 'mysql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_password );
		}
		catch( PDOException $e )
		{
			$this->put_error( $e->getMessage() );
			echo "Could not connect to DB";
			return false;
		}

		$this->_dbHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->_dbHandle->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );

		$this->_isConn = true;
	}

	public function isValid()
	{
		return $this->_isConn;
	}

	protected function put_error( $msg )
	{
		error_log( $msg );
		return;
	}

	public function beginTransaction()
	{
		return $this->_dbHandle->beginTransaction();
	}

	public function commit()
	{
		return $this->_dbHandle->commit();
	}

	public function rollBack()
	{
		return $this->_dbHandle->rollBack();
	}

	public function clean( $string, $type = "str" )
	{
		$toReturn = $this->_dbHandle->quote( $string, PDO::PARAM_STR );

		switch( $type )
		{
			case "bool":
			case "null":
			case "int":
			case "noquote":
				$toReturn = substr( $toReturn, 1, -1 );
				break;
			default:
			case "str":
				$toReturn = $toReturn;
				break;
		}

		return $toReturn;
	}

	public function query( $query )
	{
		$params = null;
		if( func_get_args() > 1 )
		{
			$params = func_get_args();
			array_shift( $params );
		}

		Registry::get("_profiler")->start_time( "mysql" );
		$return = $this->_query( $query, $params );
		Registry::get("_profiler")->stop_time( "mysql", $query . "\nParams: \n" . print_r( $params, true ) );

		return $return;
	}

	protected function _query( $query, $params )
	{
		$query = trim( $query );

		$results = new SQLResult();

		// no query called
		if( $query == "" || !is_string( $query ) || $this->_dbHandle == null )
		{
			return $results;
		}

		$results->set_query( $query, $params );

		try
		{
			$stmt = $this->_dbHandle->prepare( $query );
		}
		catch( PDOException $e )
		{
			$this->put_error( $e->getMessage() . " SQL STATEMENT:" . $query );
			return $results;
		}

		$results->set_stmt( $stmt );
		if( $params != null && is_array( $params ) && !empty( $params ) )
		{
			// make sure they're all the same length
			$lens = array();
			foreach( $params as $key => $param )
			{
				if( !array( $param ))
				{
					$this->put_error( "Parameter " . $key . " is not an array. SQL STATEMENT:" . $query );
					return $results;
				}
				array_push( $lens, count( $param ) );
			}
			unset( $param, $key );
			$lens = array_unique( $lens );

			if( count( $lens ) != 1 )
			{
				$this->put_error( "Statement contains both question mark and named placeholders. SQL STATEMENT:" . $query );
				return $results;
			}
			$len = $lens[0];

			// go through each param
			for( $i = 1; $i <= count( $params ); $i++ )
			{
				$key = $i-1;
				$parameter 	= $i;
				$value 		= $params[$key][0];
				$data_type 	= $params[$key][1];

				// named placeholders
				if( $len == 3 )
				{
					$parameter 	= $params[$key][0];
					$value 		= $params[$key][1];
					$data_type 	= $params[$key][2];

					if( !in_array( $data_type, array( "bool", "null", "int", "str", "noquote" ) ) )
					{
						$data_type = "str";
					}
				}
				try
				{
					$test = $results->get_stmt()->bindValue( $parameter, $value, constant( "PDO::PARAM_" . strtoupper( $data_type ) ) );
					if( $results->get_stmt()->bindValue( $parameter, $value, constant( "PDO::PARAM_" . strtoupper( $data_type ) ) ) === false )
					{
						$this->put_error( "Statement parameter " . $parameter . " ( " . $parameter . "," . $value . "," . $data_type . " ) is invalid. SQL STATEMENT:" . $query );
						return $results;
					}
				}
				catch( Exception $e )
				{
					$this->put_error( $e );
				}
			}
		}

		try
		{
			$results->get_stmt()->execute();
		}
		catch( PDOException $e )
		{
			$this->put_error( $e->getMessage() . " SQL STATEMENT:" . $query );
			return $results;
		}

		$result = array();
		$table = array();
		$field = array();
		$tempResults = array();

		if( preg_match( "/^(\()?(\s)?select/im", $query ) && $results->get_stmt()->rowCount() > 0 )
		{
			$numOfFields = $results->get_stmt()->columnCount();
			for( $i = 0; $i < $numOfFields; ++$i )
			{
				$meta = $results->get_stmt()->getColumnMeta( $i );

				array_push( $field, $meta['name'] );

				if( empty( $meta['table'] ) )
				{
					array_push( $table, "fn" );
				}
				else
				{
					array_push( $table, $meta['table'] );
				}
			}

			while( $row = $results->get_stmt()->fetch( PDO::FETCH_NUM ) )
			{
				for( $i = 0; $i < $numOfFields; ++$i )
				{
					$table[$i] = Inflection::singularize( $table[$i] );
					$tempResults[$table[$i]][$field[$i]] = $row[$i];
				}
				array_push( $result, $tempResults );
			}
		}

		$results->set_results( $result );

		return $results;
	}

	public function id()
	{
		return $this->_dbHandle->lastInsertId();
	}

	public function error()
	{
		return ( $this->_dbHandle->errorCode() != "00000" );
	}

	public function errno()
	{
		return $this->_dbHandle->errorCode();
	}

	public function errdesc()
	{
		return print_r( $this->_dbHandle->errorInfo(), true );
	}
}