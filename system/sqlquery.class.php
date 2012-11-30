<?
class SQLQuery
{
	protected $_dbObj = false;
    protected $_result;

	public function __construct()
	{
		if( DB_TYPE == "" || DB_TYPE == "PDO" )
		{
			$this->_dbObj = new PDOConn( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
		}
		
		if( DB_TYPE == "MYSQL" || $this->_dbObj->isValid() == false )
		{	
			$this->_dbObj = new MySQLConn( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		}
		
		if( $this->_dbObj->isValid() == false )
		{
			if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true )
			{
				throw new Exception( $this->_dbhObj->error() );
			}
			else
			{
				throw new Exception( "Could not connect to DB" );
			}
		}
	}
	
	public function clean( $string, $type = "str" )
	{
		return $this->_dbObj->clean( $string, $type );
	}
	
	public function query( $query, $params = null )
	{
		return $this->_dbObj->query( $query, $params );
	}
	
	public function id()
	{
		return $this->_dbObj->id();
	}
	
	public function errno()
	{
		return $this->_dbObj->errno();
	}
	
	public function error()
	{
		return $this->_dbObj->error();
	}
}

interface SQLConn
{
	public function connect( $host, $username, $password, $dbname );
	public function clean( $string );
	public function query( $query, $params = null );
	public function id();
	public function errno();
	public function error();
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
}

class MySQLConn extends SQLHandle
{
    /** Connects to database **/
    function connect( $host, $username, $password, $dbname )
	{
		$link = mysql_connect( $host, $username, $password );
		
		if( $link !== false )
		{	
			$this->_dbHandle = $link;
			mysql_select_db( $dbname, $this->_dbHandle );

			$this->_isConn = true;
		}
    }

	function clean( $string, $type = "str" )
	{
		return  "'" . mysql_real_escape_string( $string, $this->_dbHandle ) . "'";
	}

	function query( $query, $params = null )
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
			if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true )
			{
				echo $this->error() . " SQL STATEMENT:" . $query;
			}
			else
			{
				error_log( $this->error() . " SQL STATEMENT:" . $query );
			}
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
	
	function id()
	{
		return mysql_insert_id( $this->_dbHandle );
	}

    /** Get error string **/
    function error() {
        return mysql_error( $this->_dbHandle );
    }
    
     /** Get error numer **/
    function errno() {
        return mysql_errno( $this->_dbHandle );
    }
}

class PDOConn extends SQLHandle
{
    /** Connects to database **/
    function connect( $host, $username, $password, $dbname )
	{
		try
		{
			$this->_dbHandle = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $username, $password );
		    $this->_dbHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		    $this->_dbHandle->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
		}
		catch( PDOException $e )
		{
			$msg = $e->getMessage();
			if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true )
			{
				echo $msg;
			}
			else
			{
				log_error( $msg );
			}
			return false;
		}
		
		$this->_isConn = true;
    }
    
    function isValid()
    {
    	return $this->_isConn;
    }

	function clean( $string, $type = "str" )
	{
		switch( $type )
		{
			case "bool":
				$type = "PARAM_BOOL";
				break;
			case "null":
				$type = "PARAM_NULL";
				break;
			case "int":
				$type = "PARAM_INT";
				break;
			default:
			case "str":
				$type = "PARAM_STR";
				break;
		}
		
		return $this->_dbHandle->quote( $string, constant( "PDO::" . $type ) );
	}

	function query( $query, $params = null )
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
			$msg = $e->getMessage() . " SQL STATEMENT:" . $query;
			if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true )
			{
				echo $msg;
			}
			else
			{
				log_error( $msg );
			}
			return false;
		}
		
		if( $params != null && is_array( $params ) && count( $params ) > 0 )
		{
			for( $i = 1; $i <= count( $params ); $i++ )
			{
				$stmt->bindValue( $i, $params[$i-1] );
			}
		}
		
		try
		{
			$stmt->execute();
		}
		catch( PDOException $e )
		{
			$msg = $e->getMessage() . " SQL STATEMENT:" . $query;
			if( ENVIRONMENT != "LIVE" && DEVELOPMENT_ENVIRONMENT == true )
			{
				echo $msg;
			}
			else
			{
				log_error( $msg );
			}
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
	
	function id()
	{
		return $this->_dbHandle->lastInsertId();
	}

    /** Get error string **/
    function error()
    {
        return print_r( $this->_dbHandle->errorInfo(), true );
    }
    
     /** Get error numer **/
    function errno()
    {
        return ( $this->_dbHandle->errorCode() == "00000" ? false : $this->_dbHandle->errorCode() );
    }
}