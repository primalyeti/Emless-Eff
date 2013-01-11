<?
$blacklistPath = ROOT . DS . 'application' . FILE_DIR . HONEYPOT_FILENAME;
$ip = $_SERVER['REMOTE_ADDR'];
$uagent = $_SERVER['HTTP_USER_AGENT'];

// if file exists, get its contents
if( file_exists( $blacklistPath ) )
{
	$data = file_get_contents( $blacklistPath );		
}
// file doesnt exist, create it
else
{
	$data = "<blacklist></blacklist>";
}

$theList = new SimpleXMLElement( $data );	

$exists = false;
if( count( $theList->children() ) > 0 )
{
	for( $i = 0; $i < count( $theList->children() ); $i++ )
	{
		if( $theList->bot[$i]->ip == $ip )
		{
			$exists = true;
			break;
		}
	}
}

if( $exists == false )
{
	$bot = $theList->addChild( "bot" );
	$bot->addChild( "ip", $ip );
	$bot->addChild( "user-agent", $uagent );
	$bot->addChild( "date", date( "Y-m-d H:i:s" ) );
	
	$handle = fopen( $blacklistPath, 'w+' );
	fwrite( $handle, $theList->asXML() );
	fclose( $handle );
}

$_SESSION[HONEYPOT_SESSION_VAR]['isBot'] = true;

forward( HONEYPOT_TRAPPED_URL );