<?
/** Honey pot trigger url (trap) */
define( 'HONEYPOT_URL', "scripts/honeypot.bottrap.php" );

/** Honey Pot trapped url */
define( 'HONEYPOT_TRAPPED_URL', "scripts/honeypot.trapped.php" );

/** Honey Pot Robot List Filename */
define( 'HONEYPOT_FILENAME', "honeypot.xml" );

/** Honey Pot Session Variable Name */
define( 'HONEYPOT_SESSION_VAR', "framework_honeypot" );

function bot_honey_trap_is_active()
{
	return HONEYPOT_ACTIVE;
}

function bot_honey_trap_link()
{
	if( bot_honey_trap_is_active() === false )
	{
		return;
	}
	
	echo Registry::get( "_framework" )->load()->html->link( HONEYPOT_URL, "This link will get you banned", array( "style" => "display: none; position: absolute; top:0; left: 0; margin-left: -99%;" ) );
}

function bot_honey_trap_init()
{
	if( bot_honey_trap_is_active() === false )
	{
		return;
	}

	if( !isset( $_SESSION[HONEYPOT_SESSION_VAR] ) )
	{
		$_SESSION[HONEYPOT_SESSION_VAR]['isBot'] = false;
		bot_honey_trap_scan();
	}
	
	bot_honey_trap_is_set();
}

function bot_honey_trap_is_set()
{
	if( bot_honey_trap_is_active() === false )
	{
		return;
	}
	
	if( isset( $_SESSION[HONEYPOT_SESSION_VAR] ) && $_SESSION[HONEYPOT_SESSION_VAR]['isBot'] == true && $_SERVER['REQUEST_URI'] != BASE_PATH . HONEYPOT_TRAPPED_URL )
	{
		forward( HONEYPOT_TRAPPED_URL );
	}
}

function bot_honey_trap_scan()
{
	if( bot_honey_trap_is_active() === false )
	{
		return;
	}
	
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
				$_SESSION[HONEYPOT_SESSION_VAR]['isBot'] = true;
				break;
			}
		}
	}
}