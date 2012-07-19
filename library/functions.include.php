<?
function forward( $url, $secure = 0, $external = 0 )
{
	$url = ( !$external ? ( $secure ? DOMAIN_SECURE : DOMAIN ) : "" ) . $url;
	header( "Location: " . $url );
	exit;
}

function delete_cache_file( $file )
{
	if( file_exists( ROOT . DS . CACHE_DIR . $file ) )
	{
		unlink( ROOT . DS . CACHE_DIR . $file );
	}
}

function log_error( $error )
{
	$handle = fopen( ROOT . DS . LOGS_DIR . LOG_CUST_ERR_FILE_NAME, 'a+' );
	if( $handle !== false )
	{
		fwrite( $handle, $error );
		fclose( $handle );
	}
}

function meta_set_url( $url = "" )
{
	if( $url == "" )
	{
		return DOMAIN . substr( $_SERVER['REQUEST_URI'], 1 );
	}
	return DOMAIN . $url;
}

function meta_set_image( $img = "" )
{
	if( $img == "" )
	{
		return META_IMAGE;
	}
	return $img;
}

function meta_set_description( $desc = "" )
{
	if( $desc == "" )
	{
		return META_DESCRIPTION;
	}
	return $desc;
}

function meta_set_title( $title = "" )
{
	if( $title == "" )
	{
		return META_TITLE;
	}
	return $title;
}

function errCheck( $errs, $keys, $msg = "", $att = "" )
{
	$errs = (array) $errs;
	$keys = (array) $keys;
	
	if( empty( $errs ) )
	{
		return false;
	}
	
	$found = false;
	foreach( $keys as $k )
	{
		if( array_key_exists( $k, $errs ) )
		{
			$found = true;
			break;
		}
	}
	
	if( !$found )
	{
		return false;
	}
	
	return "<div class='error'>" . ( $msg != "" ? $msg : "Required Field" ) . "</div>";
}