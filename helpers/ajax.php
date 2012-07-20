<?
function ajax_send_headers()
{
	header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 				// Date in the past
	header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" ); 	// always modified
	header( "Cache-Control: no-cache, must-revalidate" ); 			// HTTP/1.1
	header( "Pragma: no-cache" );										// HTTP/1.0
}

function ajax_send_xml_headers()
{
	ajax_send_headers();
	header( "Content-type: text/xml" );
}

function ajax_send_json_headers()
{
	ajax_send_headers();
	header( "Content-type: application/json");
}

function ajax_send_html_headers()
{
	ajax_send_headers();
	header( "Content-type: text/html" );
}

function ajax_send_text_headers()
{
	ajax_send_headers();
	header( "Content-type: text/plain" );
}

function ajax_init_xml( $input )
{
	ajax_send_xml_headers();
	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes' ?>\n";
	$xml .= "<response>\n";
	$xml .= $input . "\n";
	$xml .= "</response>\n";
			
	return $xml;
}

function ajax_init_json( $input )
{
	ajax_send_json_headers();
	return json_encode( $input );
}	