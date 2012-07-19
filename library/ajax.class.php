<?
class Ajax
{
	private function send_headers()
	{
		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 				// Date in the past
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" ); 	// always modified
		header( "Cache-Control: no-cache, must-revalidate" ); 			// HTTP/1.1
		header( "Pragma: no-cache" );										// HTTP/1.0
	}
	
	function send_xml_headers()
	{
		self::send_headers();
		header( "Content-type: text/xml" );
	}
	
	function send_json_headers()
	{
		self::send_headers();
		header( "Content-type: application/json");
	}
	
	function send_html_headers()
	{
		self::send_headers();
		header( "Content-type: text/html" );
	}
	
	function send_text_headers()
	{
		self::send_headers();
		header( "Content-type: text/plain" );
	}
	
	function init_xml( $input )
	{
		Registry::set( "render", true );
		self::send_xml_headers();
		$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes' ?>\n";
		$xml .= "<response>\n";
		$xml .= $input . "\n";
		$xml .= "</response>\n";
				
		return $xml;
	}
	
	function init_json( $input )
	{
		Registry::set( "render", true );
		self::send_json_headers();
		return json_encode( $input );
	}	
}