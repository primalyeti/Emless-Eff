<?
class SQLQuery
{
	protected $_dbObj = false;
    protected $_result;

	public function __construct( $db_type, $db_host, $db_user, $db_password, $db_name )
	{
		if( $db_type == "" || $db_type == "PDO" )
		{
			$this->_dbObj = new PDOConn( $db_host, $db_user, $db_password, $db_name );
		}
		
		if( $db_type == "MYSQL" || $this->_dbObj->isValid() == false )
		{	
			$this->_dbObj = new MySQLConn( $db_host, $db_user, $db_password, $db_name );
		}
		
		if( $this->_dbObj->isValid() == false )
		{
			echo "Could not connect to DB";
			return;
		}
	}
	
	public function clean( $string, $type = "str" )
	{
		return $this->_dbObj->clean( $string, $type );
	}
	
	public function query( $query )
	{
		$params = null;
		if( func_get_args() > 1 )
		{
			$params = func_get_args();
			array_shift( $params );
		}
		return $this->_dbObj->query( $query, $params );
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
			echo $msg;
		}
		
		return;
    }
}

class MySQLConn extends SQLHandle
{
    /** Connects to database **/
    public function connect( $host, $username, $password, $dbname )
	{
		$link = mysql_connect( $host, $username, $password );
		
		if( $link !== false )
		{	
			$this->_dbHandle = $link;
			mysql_select_db( $dbname, $this->_dbHandle );

			$this->_isConn = true;
		}
		
		return $link;
    }

	public function clean( $string, $type = "str" )
	{
		$toReturn = mysql_real_escape_string( $string, $this->_dbHandle );
	
		switch( $type )
		{
			case "bool":
			case "null":
			case "int":
				$toReturn = $toReturn;
				break;
			default:
			case "str":
				$toReturn = "'" . $toReturn . "'";
				break;
		}
		
		return $toReturn;
	}

	public function query( $query, $params )
	{
		$query = trim( $query );
		
		// no query called
		if( $query == "" || !is_string( $query ) || $this->_dbHandle == null )
		{
			return false;
		}
		
		$result = array();
		$table = array();
		$field = array();
		$tempResults = array();
		
		$this->_result = mysql_query( $query, $this->_dbHandle );
				
		if( !is_resource( $this->_result ) ) # substr_count( strtoupper( $query ), "SELECT " ) > 0
		{
			$this->put_error( $this->error() . " SQL STATEMENT:" . $query );
			return false;
		}
		else
		{
			if( mysql_num_rows( $this->_result ) > 0 )
			{
				$numOfFields = mysql_num_fields( $this->_result );
				for( $i = 0; $i < $numOfFields; ++$i )
				{
					array_push( $table, mysql_field_table( $this->_result, $i ) );
					array_push( $field, mysql_field_name( $this->_result, $i ) );
				}
				while( $row = mysql_fetch_row( $this->_result ) )
				{
					for( $i = 0;$i < $numOfFields; ++$i )
					{
						$table[$i] = Inflection::singularize( $table[$i] );
						$tempResults[$table[$i]][$field[$i]] = $row[$i];
					}
					array_push( $result, $tempResults );
				}
			}
			mysql_free_result( $this->_result );
		}	
		
		return $result;
	}
	
	public function id()
	{
		return mysql_insert_id( $this->_dbHandle );
	}

    /** Get error string **/
    public function error() {
        return ( mysql_errno( $this->_dbHandle ) != 0 );
    }
    
     /** Get error numer **/
    public function errno() {
        return mysql_errno( $this->_dbHandle );
    }
    
    /** Get error string **/
    public function errdesc() {
        return mysql_error( $this->_dbHandle );
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

	public function clean( $string, $type = "str" )
	{
		$toReturn = $this->_dbHandle->quote( $string, PDO::PARAM_STR );
	
		switch( $type )
		{
			case "bool":
			case "null":
			case "int":
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
		
		// no query called
		if( $query == "" || !is_string( $query ) || $this->_dbHandle == null )
		{
			return false;
		}
		
		try
		{
			$stmt = $this->_dbHandle->prepare( $query );
		}
		catch( PDOException $e )
		{
			$this->put_error( $e->getMessage() . " SQL STATEMENT:" . $query );
			return false;
		}
		
		if( $params != null && is_array( $params ) && !empty( $params ) )
		{
			// make sure they're all the same length
			$lens = array();
			foreach( $params as $key => $param )
			{
				if( !array( $param ))
				{
					$this->put_error( "Parameter " . $key . " is not an array. SQL STATEMENT:" . $query );
					return false;
				}
				array_push( $lens, count( $param ) );
			}
			unset( $param, $key );
			$lens = array_unique( $lens );
			
			if( count( $lens ) != 1 )
			{
				$this->put_error( "Statement contains both question mark and named placeholders. SQL STATEMENT:" . $query );
				return false;
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
					
					if( !in_array( $data_type, array( "bool", "null", "int", "str" ) ) )
					{
						$data_type = "str";
					}
				}
				
				if( $stmt->bindValue( $parameter, $value, constant( "PDO::PARAM_" . strtoupper( $data_type ) ) ) === false )
				{
					$this->put_error( "Statement parameter " . $parameter . " ( " . $parameter . "," . $value . "," . $data_type . " ) is invalid. SQL STATEMENT:" . $query );
					return false;
				}
			}	
		}
		
		try
		{
			$stmt->execute();
		}
		catch( PDOException $e )
		{
			$this->put_error( $e->getMessage() . " SQL STATEMENT:" . $query );
			return false;
		}
		
		$result = array();
		$table = array();
		$field = array();
		$tempResults = array();
				
		if( preg_match( "/^select/im", $query ) && $stmt->rowCount() > 0 )
		{
			$numOfFields = $stmt->columnCount();
			for( $i = 0; $i < $numOfFields; ++$i )
			{
				$meta = $stmt->getColumnMeta( $i );
				array_push( $table, $meta['table'] );
				array_push( $field, $meta['name'] );
			}
			
			while( $row = $stmt->fetch( PDO::FETCH_NUM ) )
			{
				for( $i = 0; $i < $numOfFields; ++$i )
				{
					$table[$i] = Inflection::singularize( $table[$i] );
					$tempResults[$table[$i]][$field[$i]] = $row[$i];
				}
				array_push( $result, $tempResults );
			}
		}
		
		return $result;
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