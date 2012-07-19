<?
class Cache
{
	protected $_cacheHistory = array();
	
	function get( $name, $properties = null )
	{
		// cache is disables
		if( CACHE_ISON === false )
		{
			return false;
		}
	
		// no name
		if( empty( $name ) || !is_string( $name ) )
		{
			return false;
		}
		
		$historyItem = array(
			"name" => $name,
			"properties" => $this->initProperties(),
		);

		if( is_array( $properties ) && !empty( $properties ) )
		{
			foreach( $properties as $n => $v )
			{
				if( isset( $historyItem["properties"][$n] ) )
				{
					$historyItem["properties"][$n] = $v;
				}
			}
		}
		
		// set history
		array_push( $this->_cacheHistory, $historyItem );
		
		// no file
		$fileName = $this->filename( $name );
		if( !file_exists( $fileName ) )
		{	
			return false;
		}
		
		// file is expired
		if( ( time() - filemtime( $fileName ) ) > $historyItem["properties"]["lifetime"] )
		{
			return false;
		}
				
		$handle = fopen( $fileName, 'r' );							# open the file in read mode
		if( $handle == false )
		{
			return false;
		}
		
		if( flock( $handle, LOCK_SH ) )
		{
			$content = fread( $handle, filesize( $fileName ) );		# get content
			fclose( $handle );												

			$unserialized = unserialize( $content );				# unserialize it, it might be an array
			return $unserialized;									# return unserialized content
		}
		return false;
	}
	
	function __call( $method, $arguments )
	{
		if( $method == "set" )
		{
			switch( count( $arguments ) )
			{
				case 1:
					return call_user_func_array( array( $this, "setWithoutKey" ), $arguments );
					break;
				case 2:
					return call_user_func_array( array( $this, "setWithKey" ), $arguments );
					break;
			}
		}
	}
	
	function setWithKey( $name, $value )
	{
		if( CACHE_ISON === false )
		{
			return true;
		}
	
		if( empty( $name ) || !is_string( $name ) || empty( $value ) )
		{
			return false;
		}
		
		$handle = fopen( $this->filename( $name ), "w" );	# open file
		
		if( $handle == false )
		{
			return false;
		}
		
		if( flock( $handle, LOCK_EX ) )						# lock File, error if unable to lock
		{
			$value = serialize( $value );
			
			fwrite( $handle, $value );						# write the content
			flock( $handle, LOCK_UN );						# unlock the file
		}
		
		fclose( $handle );
		return true;
	}
	
	function setWithoutKey( $value )
	{
		if( CACHE_ISON === false )
		{
			return true;
		}
		
		if( empty( $value ) )
		{
			return false;
		}
		
		$historyItem = end( $this->_cacheHistory );
		$name = $historyItem["name"];
		
		$handle = fopen( $this->filename( $name ), "w" );	# open file
		
		if( $handle == false )
		{
			return false;
		}
		
		if( flock( $handle, LOCK_EX ) )						# lock File, error if unable to lock
		{
			$value = serialize( $value );
			
			fwrite( $handle, $value );						# write the content
			flock( $handle, LOCK_UN );						# unlock the file
		}
		
		fclose( $handle );
		return true;
	}
	
	////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////	
	private function initProperties()
	{
		return array(
			"lifetime"	=> 60 * 5,
		);
	}
		
	private function filename( $fileName )
	{
		return ROOT . DS . CACHE_DIR . $fileName . ".txt";
	}
	
	private function isValid()
	{
		$mdate = filemtime( $this->filename() );
		$now = time();
		
		return ( ( $now - $mdate ) < $expiry );
	}

	public function debug()
	{
		echo "<pre>";
		print_r( get_object_vars( $this ) );
		echo "</pre>";
	}
}