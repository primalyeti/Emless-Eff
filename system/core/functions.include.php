<?
function is_dev()
{
	return ( ENVIRONMENT == 'LOCAL' || ENVIRONMENT == 'DEV' );
}

function is_test()
{
	return ( ENVIRONMENT == 'TEST' );
}

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

function err_check( $errs, $keys, $msg = "", $options = "" )
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

	$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
	$defaults = array(
		"class" => "error",
	);

	$options = array_merge( $defaults, $options );

	$attributes = "";
	foreach( $options as $a => $k )
	{
		$attributes .= $a . '="' . addcslashes( $k, '"' ) . '" ';
	}

	return "<div " . $attributes . ">" . ( $msg != "" ? $msg : "Required Field" ) . "</div>";
}

function exceptions_error_handler( $severity, $message, $filename, $lineno )
{
	if( error_reporting() != 0 & $severity )
	{
		throw new ErrorException($message, 0, $severity, $filename, $lineno);
	}
	return;
}

function status_array( $status, $msg = "", $code = "", $data = array() )
{
	// VALID STAUS'
	#	error 	= an error was produced
	#	failed	= the request failed or did not meet criteria
	#	success = it worked
	#	success_with_warnings	= it worked but warnings were produced

	$statuses = array( -1 => "error", 0 => "failed", 1 => "success", 2 => "success_with_warnings" );
	if( !in_array( $status, $statuses ) && !array_key_exists( $status, $statuses ) )
	{
		return false;
	}

	if( is_int( $status ) )
	{
		$status = $statuses[$status];
	}

	return array( "status" => $status, "code" => $code, "msg" => $msg, "data" => $data );
}

function pre()
{
	foreach( func_get_args() as $arr )
	{
		echo "<pre>" . print_r( $arr, true ) . "</pre>";
	}
}