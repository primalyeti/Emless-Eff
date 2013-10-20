<?
class Ahtml extends Html
{
	protected function link_form( $url, $options, $secure )
	{
		$url = ( is_array( $url ) ? $url : array( 0 => $url ) );

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
		
		$attributes = $this->generate_options( $options );
		
		return '<a href="' . addslashes( $href ) . urlencode( $query ) . '"' . $attributes . ">";
	}
			
	protected function form_form( $action, $options, $secure )
	{
		$defaults = array(
			"method" => "post",
			"accept-charset" => "utf-8",
			"name" => uniqid(),
		);
		
		$attributes = $this->generate_options( $options, $defaults );
				
		if( $action == "" )
		{
			$action = Registry::get("_url");
		}
		
		return "<form action='" . ( $secure ? DOMAIN_SECURE : BASE_PATH ) . ( $action == "" ? Registry::get("_url") : ADMIN_ALIAS . DS ) . $action . "'" . $attributes . ">";
	}
}