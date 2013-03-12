<?
class Ajax extends Library
{	
	protected function send_headers()
	{
		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 				// Date in the past
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" ); 	// always modified
		header( "Cache-Control: no-cache, must-revalidate" ); 				// HTTP/1.1
		header( "Pragma: no-cache" );										// HTTP/1.0
	}
	
	protected function handle_output( $output, $return )
	{
		if( $return === true )
		{
			return $output;
		}
		
		echo $output;
		return;
	}
	
	public function xml( $input, $return = false )
	{
		if( $return == false )
		{
			self::send_headers();
			header( "Content-type: text/xml" );
		}
		
		$xml = new SimpleXMLElement( '<response/>' );
		array_walk_recursive( $input, array( $xml, 'addChild' ) );
		
		$output = $xml->asXML();
		
		return self::handle_output( $output, $return );
	}
	
	public function json( $input, $return = false )
	{
		if( $return == false )
		{
			self::send_headers();
			header( "Content-type: application/json");
		}
		
		$output = json_encode( $input );
		
		return self::handle_output( $output, $return );
	}
	
	public function plain( $input, $return = false )
	{
		if( $return == false )
		{
			self::send_headers();
			header( "Content-type: text/xml" );
		}
		
		$output = $input;
		
		return self::handle_output( $output, $return );
	}
	
	public function html( $input, $return = false )
	{
		if( $return == false )
		{
			self::send_headers();
			header( "Content-type: text/html" );
		}
		
		$output = $input;
		
		return self::handle_output( $output, $return );
	}
	
	public function other( $contentType, $input, $return )
	{
		if( $return == false )
		{
			self::send_headers();
			header( "Content-type: " . $contentType );
		}
		
		$output = $input;
		
		return self::handle_output( $output, $return ); 
	}
}