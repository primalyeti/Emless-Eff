<?
class Ahtml extends Html
{
	protected function link_form( $url, $options, $secure )
	{
		$url = ( is_array( $url ) ? $url : array( 0 => $url ) );
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );

		$href = ( $secure ? DOMAIN_SECURE : BASE_PATH ) . ADMIN_ALIAS . DS;
		$query = "?";
		$attributes = " ";
		
		foreach( $url as $n => $v )
		{
			if( !empty( $n ) )
			{
				$query .= $n . "=" . $v . "&";
				continue;
			}
			
			$href .= $v . "/";
			
		}
		$query = substr( $query, 0, -1 );
		$href = substr( $href, 0, -1 );
		
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addcslashes( $k, '"' ) . '" ';
		}
		
		return '<a href="' . addslashes( $href ) . urlencode( $query ) . '"' . $attributes . ">";
	}
			
	protected function form_form( $action, $options, $secure )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$defaults = array(
			"method" => "post",
			"accept-charset" => "utf-8",
			"name" => uniqid(),
		);
		
		$options = array_merge( $defaults, $options );
		$attributes = "";
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addcslashes( $k, '"' ) . '" ';
		}
		
		return "<form action='" . ( $secure ? DOMAIN_SECURE : BASE_PATH ) . ( $action == "" ? Registry::get("_url") : ADMIN_ALIAS . DS ) . $action . "'" . $attributes . ">";
	}
}