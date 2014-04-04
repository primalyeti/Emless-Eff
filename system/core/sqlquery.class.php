<?
class SQLQuery
{
	protected $_dbObj = false;
	protected $_result;

	public function __construct( $db_host, $db_user, $db_password, $db_name )
	{
		$this->_dbObj = new PDOConn( $db_host, $db_user, $db_password, $db_name );

		if( $this->_dbObj->isValid() == false )
		{
			echo "Could not connect to DB";
			return;
		}
	}

	public function beginTransaction()
	{
		return $this->_dbObj->beginTransaction();
	}

	public function commit()
	{
		return $this->_dbObj->commit();
	}

	public function rollBack()
	{
		return $this->_dbObj->rollBack();
	}

	public function clean( $string, $type = "str" )
	{
		return $this->_dbObj->clean( $string, $type );
	}

	public function query_obj( $query )
	{
		$params = null;
		if( func_get_args() > 1 )
		{
			$params = func_get_args();
			array_shift( $params );
		}

		Registry::get("_profiler")->start_time( "mysql" );
		$return = $this->_dbObj->query( $query, $params );
		Registry::get("_profiler")->stop_time( "mysql", $query . "\nParams: \n" . print_r( $params, true ) );

		return $return;
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
		$return = $this->_dbObj->query( $query, $params );
		Registry::get("_profiler")->stop_time( "mysql", $query . "\nParams: \n" . print_r( $params, true ) );

		return $return;
	}

	public function id()
	{
		return $this->_dbObj->id();
	}

	public function error()
	{
		return $this->_dbObj->error();
	}

	public function errno()
	{
		return $this->_dbObj->errno();
	}

	public function errdesc()
	{
		return $this->_dbObj->errdesc();
	}
}

interface SQLConn
{
	function connect( $host, $username, $password, $dbname );
	function isValid();
	function clean( $string );
	function query( $query, $params );
	function id();
	function error();
	function errno();
	function errdesc();
}

abstract class SQLHandle implements SQLConn
{
	protected $_dbHandle = false;
	protected $_result;
	protected $_isConn = false;

	public function __construct( $host, $username, $password, $dbname )
	{
		$this->connect( $host, $username, $password, $dbname );
	}

	public function isValid()
	{
		return $this->_isConn;
	}

	protected function put_error( $msg )
	{
		if( ENVIRONMENT == "LIVE" )
		{
			error_log( $msg );
		}
		else
		{
			error_log( $msg );
		}

		return;
	}
}

class PDOConn extends SQLHandle
{
	/** Connects to database **/
	public function connect( $host, $username, $password, $dbname )
	{
		try
		{
			$this->_dbHandle = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $username, $password );
		}
		catch( PDOException $e )
		{
			$this->put_error( $e->getMessage() );
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

	public function query( $query, $params )
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
					error_log($e);
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

		if( preg_match( "/^select/im", $query ) && $results->get_stmt()->rowCount() > 0 )
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

	/** Get error string **/
	public function error()
	{
		return ( $this->_dbHandle->errorCode() != "00000" );
	}

	 /** Get error number **/
	public function errno()
	{
		return $this->_dbHandle->errorCode();
	}

	/** Get error desc **/
	public function errdesc()
	{
		return print_r( $this->_dbHandle->errorInfo(), true );
	}
}

class SQLResult
{
	protected $_isValid 		= false;
	protected $_query 			= null;
	protected $_params 			= null;
	protected $_stmt 			= null;
	protected $_results			= array();
	protected $_pos 			= -1;
	protected $_wasSerialized 	= false;

	public function __construct(){}

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

	/**
	*
	* PUBLIC USE
	*
	**/
	public function isValid()
	{
		return $this->_isValid;
	}

	public function first()
	{
		if( !isset( $this->_results[0] ) )
		{
			return false;
		}

		return $this->_results[0];
	}

	public function last()
	{
		if( $this->length() == 0 )
		{
			return false;
		}

		return $this->_results[$this->length()-1];
	}

	public function curr()
	{
		$pos = ( $this->_pos == -1 && $this->length() > 0 ? 0 : -1 );
		if( !isset( $this->_results[$pos] ) )
		{
			return false;
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

	public function all()
	{
		return $this->_results;
	}

	public function reset()
	{
		$this->_pos = -1;
		return true;
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
			if( isset( $row->$table->$field ) && ( $val = $row->$table->$field ) != "" )
			{
				$newVal = call_user_func_array( $functionName, array_merge( array( $row->$table->$field ), $additionalParams ) );

				if( $newVal === false )
				{
					return false;
				}

				$row->$table->$field = $newVal;
			}
		}

		$this->_pos = $pos;

		return true;
	}

	public function shuffle()
	{
		$this->results = shuffle( $this->_results );

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

	public function position()
	{
		return $this->_pos;
	}

	public function error()
	{
		if( $this->wasSerialized() && $this->isValid() )
		{
			return false;
		}
		else if(
			is_null( $this->_stmt )
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
		if( is_null( $this->_stmt ) || $this->has_dbh_error() )
		{
			return Registry::get("_dbh")->errno();
		}
		else if( $this->has_statement_error() )
		{
			return $this->_stmt->errorCode();
		}

		return 0;
	}

	public function errdesc()
	{
		if( is_null( $this->_stmt ) || $this->has_dbh_error() )
		{
			return Registry::get("_dbh")->errdesc();
		}
		else if( $this->has_statement_error() )
		{
			return print_r( $this->_stmt->errorInfo(), true );
		}

		return "";
	}

	public function as_array()
	{
		$resultArray = array();
		foreach( $this->_results as $result )
		{
			array_push( $resultArray, $result->as_array() );
		}

		return $resultArray;
	}


	/**
	*
	* SETTERS
	*
	**/
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
			$row = new SQLRow( $result );
			array_push($this->_results, $row );
		}

		$this->_isValid = true;
	}

	/**
	*
	* GETTERS
	*
	**/
	public function get_query()
	{
		return $this->_query . " - Params: " . print_r( $params, true );
	}

	public function get_stmt()
	{
		return $this->_stmt;
	}

	public function __get( $key )
	{
		if( !isset( $this->$key ) && $this->first() !== false )
		{
			return $this->first()->$key;
		}

		return null;
	}

	/**
	*
	* PRIVATE
	*
	**/
	protected function has_statement_error()
	{
		return ( $this->_stmt->errorCode() != "00000" );
	}

	protected function has_dbh_error()
	{
		return Registry::get("_dbh")->error();
	}

	protected function wasSerialized()
	{
		return $this->_wasSerialized;
	}
}

class SQLRow
{
	protected $_row;

	public function __construct( $val )
	{
		$this->_row = $this->init( $val );
	}

	public function __get( $key )
	{
		if( isset( $this->_row->$key ) || method_exists( $this->_row, $key ) )
		{
			return $this->_row->$key;
		}

		return null;
	}

	public function __set( $key, $value )
	{
		$this->_row->$key = $value;
	}

	private function init( $arr )
	{
		if( $arr instanceof SQLRow || $arr instanceof SQLResult )
		{
			return $arr;
		}

		if( is_array( $arr ) )
		{
			$arr = (object) $arr;
		}

		if( is_object( $arr ) )
		{
			$new = new stdClass();

			foreach( $arr as $key => $val )
			{
				$new->{$key} = $this->init( $val );
			}
		}
		else
		{
			$new = $arr;
		}

		return $new;
	}

	public function row()
	{
		return $this->_row;
	}

	public function as_array()
	{
		return $this->internal_as_array( $this->_row );
	}

	protected function internal_as_array( $vals )
	{
		$array = array();

		foreach( $vals as $key => $value )
		{
			if( method_exists( $value, "as_array" ) )
			{
				$value = $value->as_array();
			}
			else if( is_array( $value ) )
			{
				$value = $this->internal_as_array( $value );
			}

			$array[$key] = (array) $value;
		}

		return $array;
	}
}