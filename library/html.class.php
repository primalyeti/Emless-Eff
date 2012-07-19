<?
class HTML
{
	function urlsafe( $string )
	{
		return htmlspecialchars( preg_replace( "/\s/i", "-", preg_replace( "/[^a-z0-9\s]/i", "", $string ) ) );
	}
	
	function shortenUrls( $data )
	{
		return preg_replace_callback( "@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@", array( get_class( $this ), "_fetchTinyUrl" ), $data );
	}

	private function _fetchTinyUrl( $url )
	{ 
		$ch = curl_init(); 
		$timeout = 5; 
		curl_setopt( $ch, CURLOPT_URL, "http://tinyurl.com/api-create.php?url=" . $url[0] ); 
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout ); 
		$data = curl_exec( $ch ); 
		curl_close( $ch ); 
		return self::_link( $data, $data, "", true );
	}

	function link( $path, $text, $att = "" )
	{
		return self::_link( $path, $text, $att );
	}
	
	function elink( $path, $text, $att = "" )
	{
		return self::_link( $path, $text, $att, true );
	}

	private function _link( $path, $text, $att, $external = false )
	{
		return  "<a href='" . ( $external == false ? BASE_PATH : "" ) . $path . "' " . $att . ">" . $text . "</a>";
	}

	function form( $action, $method = "post", $att = "" )
	{
		return "<form action='" . BASE_PATH . $action . "' method='" . $method . "' " . $att . " >";
	}
	
	function js( $file_name )
	{
		return "<script type='text/javascript' src='" . BASE_PATH . "js/" . $file_name . ".js'></script>" . "\r\n";
	}
	
	function ejs( $file_name )
	{
		return "<script type='text/javascript' src='" . $file_name . "'></script>" . "\r\n";
	}
	
	function css( $file_name, $media = "screen", $id = "" )
	{
		return "<link type='text/css' rel='stylesheet' href='" . BASE_PATH . "css/" . $file_name . ".css' media='" . $media . "'" . ( !empty( $id ) ? " id='" . $id . "'" : "" ) . " />";
	}
	
	function ecss( $file_name, $media = "screen", $id = "" )
	{
		return "<link type='text/css' rel='stylesheet' href='" . $file_name . "' media='" . $media . "'" . ( !empty( $id ) ? " id='" . $id . "'" : "" ) . " />";
	}
	
	function img( $file_name, $att = "" )
	{
		return "<img src='" . BASE_PATH . "img/" . $file_name . "' " . $att . " />";
	}
}