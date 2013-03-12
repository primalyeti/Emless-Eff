<?
function forward( $url, $secure = 0, $external = 0 )
{
	$url = ( !$external ? ( $secure ? DOMAIN_SECURE : BASE_PATH ) : "" ) . $url;
	header( "Location: " . $url );
	exit;
}

function aforward( $url, $secure = 0 )
{
	$url = ( $secure ? DOMAIN_SECURE : BASE_PATH ) . ADMIN_ALIAS . DS . $url;
	header( "Location: " . $url );
	exit;
}

function delete_cache_file( $file )
{
	if( file_exists( ROOT . DS . 'application' . DS . CACHE_DIR . $file ) )
	{
		unlink( ROOT . DS . 'application' . DS . CACHE_DIR . $file );
	}
}

function log_error( $error )
{
	$handle = fopen( ROOT . DS . 'application' . DS . LOGS_DIR . LOG_CUST_ERR_FILE_NAME, 'a+' );
	if( $handle !== false )
	{
		fwrite( $handle, $error );
		fclose( $handle );
	}
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

function exceptions_error_handler( $severity, $message, $filename, $lineno )
{
	if( error_reporting() != 0 & $severity )
	{
		throw new ErrorException($message, 0, $severity, $filename, $lineno);
	}
	return;
}