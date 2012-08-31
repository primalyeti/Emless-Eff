<?
class HTML
{
	public function urlsafe( $string )
	{
		return htmlspecialchars( preg_replace( "/\s/i", "-", preg_replace( "/[^a-z0-9\s]/i", "", $string ) ) );
	}
	
	public function shortenUrls( $data )
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
	
	public function link( $url, $text = "", $options = array() )
	{
		$url = ( is_array( $url ) ? $url : array( 0 => $url ) );
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );

		$href = BASE_PATH;
		$query = "?";
		$attributes = " ";
		
		foreach( $url as $n => $v )
		{
			if( !empty( $n ) )
			{
				$query .= $n . "=" . $v . "&";
			}
			else
			{
				$href .= $v . "/";
			}
		}
		$query = substr( $query, 0, -1 );
		$href = substr( $href, 0, -1 );
		
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
		
		return '<a href="' . addslashes( $href ) . urlencode( $query ) . '"' . $attributes . ">" . htmlentities( $text ) . "</a>";
	}
	
	public function js( $file_name, $options = array() )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$attributes = " ";
		
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
	
		return "<script type='text/javascript' src='" . BASE_PATH . "js/" . $file_name . ".js'"  . $attributes . "></script>" . "\r\n";
	}
	
	public function jquery()
	{
		$this->js( "jquery" );
	}
	
	public function modernizr()
	{
		$this->js( "modernizr" );
	}
	
	public function css( $file_name, $options = array() )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$attributes = " ";
		
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
		
		return "<link type='text/css' rel='stylesheet' href='" . BASE_PATH . "css/" . $file_name . ".css'"  . $attributes . "/>";
	}
	
	public function img( $file_name, $options = array() )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$attributes = " ";
		
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
		
		return "<img src='" . BASE_PATH . "img/" . $file_name . "'"  . $attributes . "/>";
	}
	
	public function form_open( $action, $options = array() )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$defaults = array(
			"method" => "post",
			"accept-charset" => "utf-8",
		);
		
		$options = array_merge( $defaults, $options );
		$attributes = "";
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
		
		$form = "<form action='" . BASE_PATH . urlencode( $action ) . "'" . $attributes . ">";
		
		if( !empty( $formElemets ) )
		{
			$form .= $this->form_expand( $formElemets );
		}
		
		return $form;
	}
	
	public function form_open_multipart( $action, $options = array(), $formElemets = array() )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$options = array_merge( array( "enctype" => "multipart/form-data" ), $options );
		
		return $this->form_open( $action, $options, $formElemets );
	}
	
	public function form_close()
	{
		return "</form>";
	}
}