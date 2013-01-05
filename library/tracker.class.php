<?
class Tracker
{	
	public static function add( $path, $url )
	{
		if( TRACKER_ISON === false )
		{
			return true;
		}
		
		// get either guest id or user id
		$file_name = $_SESSION[GUEST_SESSION_VAR]['id'];
		
		// open the tracker file
		$tracker_data = self::open( $file_name );
		// if file didnt exists
		if( $tracker_data === false )
		{
			$tracker_data = "<tracker></tracker>";
		}
		// convert xml to object
		$tracker = new SimpleXMLElement( $tracker_data );
		
		// its empty, so init it
		if( count( $tracker->children() ) === 0 )
		{
			self::init( $tracker, $file_name );
		}
		
		if( is_user_logged_in() && !self::is_user_tracker( $tracker ) )
		{
			$dbh = Registry::get("dbh");
		
			$amount = $dbh->query(
				"SELECT SUM( orders.o_total ) AS amount FROM orders WHERE o_user_id = ?", array( $_SESSION[USER_SESSION_VAR]['id'] )
			);
			
			self::change_owner( $_SESSION[USER_SESSION_VAR]['id'], $_SESSION[USER_SESSION_VAR]['email'], $amount[0]['']['amount'], $tracker );
		}
		else if( !is_user_logged_in() && self::is_user_tracker( $tracker ) )
		{
			$tracker->info->id = $_SESSION[GUEST_SESSION_VAR]['id'];
			
			unset( $tracker->info->email );
			unset( $tracker->info->amount );
		}
		
		// add an entry
		$entry = $tracker->addChild( "entry" );
		// add data to entry
		$entry->addChild( "loc", $path );
		$entry->addChild( "url", $url );
		$entry->addChild( "time", date( "H:i:s" ) );
		$entry->addChild( "date", date( "Y-m-d" ) );
		
		// save tracker to disk
		self::save( $file_name, $tracker );
	}
	
	public static function change_owner( $id, $email = "", $amount = "", $tracker = null )
	{
		if( TRACKER_ISON === false )
		{
			return true;
		}
				
		// get either guest id or user id
		$file_name = $_SESSION[GUEST_SESSION_VAR]['id'];
		if( $tracker == null )
		{
			// open the tracker file
			$tracker_data = self::open( $file_name );
			
			// conver xml to object
			$tracker = new SimpleXMLElement( $tracker_data );
		}
		
		// change the id
		$tracker->info->id = $id;
		
		// add user data if set
		if( $email != "" )
		{
			if( !isset( $tracker->info->email ) )
			{
				$tracker->info->addChild( "email" );
			}
			$tracker->info->email = $email;
		}
		else if( isset( $tracker->info->email ) )
		{
			unset( $tracker->info->email );
		}
		
		if( $amount != "" )
		{
			if( !isset( $tracker->info->amount ) )
			{
				$tracker->info->addChild( "amount" );
			}
			$tracker->info->amount = $amount;
		}
		else if( isset( $tracker->info->amount ) )
		{
			unset( $tracker->info->amount );
		}
		
		// save to file
		self::save( $file_name, $tracker );
	}
		
	public static function has_tracker( $file_name )
	{
		return file_exists( self::filepath( $file_name ) );
	}
	
	protected function init( $tracker, $id )
	{
		// create info node
		$info = $tracker->addChild("info");
		// add data to info node
		$info->addChild( "id", $id );
		$info->addChild( "filename", $id );
		$info->addChild( "system", $_SERVER['HTTP_USER_AGENT'] );
		$info->addChild( "referer", "<![CDATA[" . htmlentities( $_SESSION[GUEST_SESSION_VAR]['referer'] ) . "]]>" );
		$info->addChild( "ip", $_SESSION[GUEST_SESSION_VAR]['ip'] );
	}
	
	protected function is_user_tracker( $tracker )
	{
		return ( isset( $tracker->info->email ) && isset( $tracker->info->amount ) );
	}
	
	protected function filepath( $file_name )
	{
		return ROOT . DS . TRACKER_DIR . $file_name . ".xml";
	}
	
	protected function open( $file_name )
	{
		if( self::has_tracker( $file_name ) )
		{
			return file_get_contents( self::filepath( $file_name ) );
		}
		return false;
	}
	
	protected function save( $file_name, $tracker )
	{
		// declare full path of file
		$filepath = self::filepath( $file_name );
	
		$handle = fopen( $filepath, 'w+' );
		fwrite( $handle, $tracker->asXML() );
		fclose( $handle );
	}
}