<?
interface SQL
{
	public function __construct( $host, $username, $password, $dbname );
	public function connect( $host, $username, $password, $dbname );
	public function clean( $string, $type );
	public function query( $query, $params );
	public function id();
	public function errno();
	public function error();
}

abstract class SQLConn implements SQL
{
	protected $_dbHandle = null;
    protected $_result;

	public function __construct( $host, $username, $password, $dbname )
	{
		$this->connect( $host, $username, $password, $dbname );
	}
}

class SQLQuery extends SQLConn
{
    /** Connects to database **/
    function connect( $host, $username, $password, $dbname )
	{
		try {
			$this->_dbHandle = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $username, $password );
		    $this->_dbHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		    $this->_dbHandle->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
		} catch( PDOException $e )
		{
			echo 'ERROR: ' . $e->getMessage();
			return 0;
		}
		return 1;
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
			if( DEVELOPMENT_ENVIRONMENT == true )
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
		
		$stmt->execute();
		
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