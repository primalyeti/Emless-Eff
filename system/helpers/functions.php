<?
function status_array( $status, $msg = "", $code = "" )
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
	
	return array( "status" => $status, "code" => $code, "msg" => $msg );
}