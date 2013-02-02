<?
class HTML
{
	public function doctype( $type = "" )
	{
		switch( $type )
		{
			case "html4-strict":
				return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">";
				break;
				
			case "html4-trans":
				return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
				break;
				
			case "html4-frame":
				return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">";
				break;
				
			default:
			case "html5":
				return "<!DOCTYPE>";
				break;
				
			case "xhtml-strict":
				return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";
				break;
				
			case "xhtml-trans":
				return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
				break;
				
			case "xhtml-frame":
				return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">";
				break;
				
			case "xhtml11":
				return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
				break;
		}
	}
	
	public function title( $title = "" )
	{
		return "<title>" . ( !empty( $title ) ? $title . " : " : "" ) . SITE_TITLE . "</title>";
	}
	
	public function charset( $charset = "utf-8" )
	{
		return "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $charset . "\" />";
	}
	
	public function meta( $type, $content )
	{
		return "<meta name=\"" . $type . "\" content=\"" . $content . "\" />";
	}
	
	public function icon( $url, $type )
	{
		return "<link rel=\"icon\" type=\"" . $type . "\" href=\"" . BASE_PATH . $url . "\" />";
	}
	
	public function author( $url = "humans.txt" )
	{
		return "<link rel=\"author\" type=\"text/plain\" href=\"" . BASE_PATH . $url . "\" />";
	}

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
		
		return '<a href="' . addslashes( $href ) . urlencode( $query ) . '"' . $attributes . ">" . $text . "</a>";
	}
	
	public function js( $file_name, $options = array() )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$attributes = " ";
		
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
		
		$cache_prevension = "";
		if( DEVELOPMENT_ENVIRONMENT == true )
		{
			$cache_prevension = "?d=" . uniqid();
		}
		else if( CACHE_ISON == true )
		{
			$cache_prevension = "?v=" . VERSION;
		}
	
		return "<script type='text/javascript' src='" . BASE_PATH . "js/" . $file_name . ".js" . $cache_prevension . "'"  . $attributes . "></script>" . "\r\n";
	}
	
	public function jquery()
	{
		return $this->js( "jquery" );
	}
	
	public function jquery_ui()
	{
		return $this->js( "jquery-ui" );
	}
	
	public function modernizr()
	{
		return $this->js( "modernizr" );
	}
	
	public function css( $file_name, $options = array() )
	{
		$options = ( is_array( $options ) ? $options : array( 0 => $options ) );
		$attributes = " ";
		
		foreach( $options as $a => $k )
		{
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
		
		$cache_prevension = "";
		if( DEVELOPMENT_ENVIRONMENT == true )
		{
			$cache_prevension = "?d=" . uniqid();
		}
		else if( CACHE_ISON == true )
		{
			$cache_prevension = "?v=" . VERSION;
		}
		
		return "<link type='text/css' rel='stylesheet' href='" . BASE_PATH . "css/" . $file_name . ".css" . $cache_prevension . "'"  . $attributes . "/>";
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
	
	public function form_open( $action = "", $options = array() )
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
			$attributes .= $a . '="' . addslashes( $k ) . '" ';
		}
		
		if( $action == "" )
		{
			$action = $this->url;
		}
		
		$form = "<form action='" . BASE_PATH . $action . "'" . $attributes . ">";
		
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