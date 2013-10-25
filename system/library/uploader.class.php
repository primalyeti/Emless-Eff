<?
class Uploader extends Library
{
	protected $errCode;
	protected $errMsg;
	
	const ERR_IMAGE_UPLOAD_MAX_FILESIZE = -1;
	const ERR_IMAGE_MAX_FILE_SIZE = -2;
	const ERR_IMAGE_PARTIAL = -3;
	const ERR_IMAGE_TEMP_FOLDER = -6;
	const ERR_IMAGE_DISK_PERMS = -7;
	const ERR_IMAGE_EXTENSION = -8;
	const ERR_IMAGE_UNKNOWN = -9;
	const ERR_IMAGE_MIME_TYPES = -10;
	const ERR_IMAGE_NOT_IMAGE = -12;
	
	const ERR_FILE_EXTENSION = -14;
	
	const ERR_NO_FILE = -4;
	const ERR_MOVE_FILE = -13;

	public function file( $key, $options = array() )
	{
		$_options = $this->default_options( "file" );
		
		$options = array_merge( $_options, $options );
	
		if( !$this->check_for_file( $key ) )
		{
			return false;
		}
		
		$fileInfo = $this->get_filename( $key );
		$fileExtension 	= $fileInfo['ext'];
		$fileName 		= $fileInfo['filename'];
		
		if( $this->check_valid_extensions( $fileExtension, $options['validExtensions'] ) == false )
		{
			$this->set_error( self::ERR_FILE_EXTENSION, "Only " . implode( "/", $options['validExtensions'] ) . " files are supported" );
			return false;
		}
		
		return $this->save_file( $key, $options );
	}

	public function image( $key, $options = array() )
	{
		$_options = $this->default_options( "image" );
		
		$options = array_merge( $_options, $options );
	
		if( !$this->check_for_file( $key ) )
		{
			return false;
		}
		
		if( $_FILES[$key]["error"] > 0 )
		{
			switch( $_FILES[$key]["error"] )
			{
				case 1:
					$this->set_error( self::ERR_IMAGE_UPLOAD_MAX_FILESIZE, "Image is too big" );
					break;
					
				case 2:
					$this->set_error( self::ERR_IMAGE_MAX_FILE_SIZE, "Image is too big" );
					break;
					
				case 3:
					$this->set_error( self::ERR_IMAGE_PARTIAL, "Upload error" );
					break;
					
				case 4:
					$this->set_error( self::ERR_NO_FILE, "No file uploaded" );
					break;
					
				case 6:
					$this->set_error( self::ERR_IMAGE_TEMP_FOLDER, "No server storage" );
					break;
					
				case 7:
					$this->set_error( self::ERR_IMAGE_DISK_PERMS, "Couldn't save image" );
					break;
					
				case 8:
					$this->set_error( self::ERR_IMAGE_EXTENSION, "Server error" );
					break;
					
				default:
					$this->set_error( self::ERR_IMAGE_UNKNOWN, "Unknown error" );
					break;
			}
			
			return false;
		}
		
		$fileInfo = $this->get_filename( $key );
		$fileExtension 	= $fileInfo['ext'];
		$fileName 		= $fileInfo['filename'];
		
		if( $this->check_valid_extensions( $fileExtension, $options['validExtensions'] ) == false )
		{
			$this->set_error( self::ERR_IMAGE_EXTENSION, "Only " . implode( "/", $options['validExtensions'] ) . " images are supported" );
			return false;
		}
		
		if( @getimagesize( $_FILES[$key]["tmp_name"] ) === false )
		{
			$this->set_error( self::ERR_IMAGE_NOT_IMAGE, "Only " . implode( "/", $options['validExtensions'] ) . " images are supported" );
			return false;
		}
		
		return $this->save_file( $key, $options );
	}
	
	public function has_error()
	{
		return ( $this->errCode != null );
	}
	
	public function get_error_code()
	{
		if( !$this->has_error() )
		{
			return false;
		}
		
		return $this->errCode;
	}
	
	public function get_error_msg()
	{
		if( !$this->has_error() )
		{
			return false;
		}
		
		return $this->errMsg;
	}
	
	public function reset()
	{
		$this->errCode = null;
		$this->errMsg = null;
	}
	
	/* **********
	* PRIVATE FNS
	********** */
	
	protected function default_options( $type )
	{
		$options = array(
			"filenamePrepend" => "",
			"filenameAppend" => "",
			"rename" => true,
			"validExtensions" => array(),
		);
		
		switch( $type )
		{
			case "file":
				return $options;
			
			case "image":
				$options["validExtensions"] = array( "jpg", "jpeg", "gif", "png" );
				return $options;
		}
		
		return $options;
	}
	
	protected function check_for_file( $key )
	{
		if( !isset( $_FILES[$key] ) )
		{
			$this->set_error( self::ERR_NO_FILE, "File was not uploaded" );
			return false;
		}
		
		return true;
	}
	
	protected function save_file( $key, $options )
	{
		$fileInfo = $this->get_filename( $key );
		
		$newFileName = $fileInfo['filename'];
		if( $options['rename'] == true )
		{
			$newFilename = time() . uniqid( "", true );
		}
		
		$newFilename = $options['filenamePrepend'] . $newFilename . $options['filenameAppend'];
		$filename = $newFilename . "." . $fileInfo['ext'];;
		
		$path = ROOT . DS . FILE_DIR;
		$filePath = $path . $filename;

        if( move_uploaded_file( $_FILES[$key]['tmp_name'], $filePath ) == false )
        {
	        $this->set_error( self::ERR_MOVE_FILE, "Could not save file" );
	        return false;
        }
        
        return $filename;
	}
	
	protected function check_valid_extensions( $fileExtension, $validFileExtensions )
	{
		return ( empty( $validFileExtensions ) || in_array( $fileExtension, $validFileExtensions ) );
	}
	
	protected function get_filename( $key )
	{
		$extLoc 		= strrpos( $_FILES[$key]["name"], "." );
		$fileExtension 	= substr( $_FILES[$key]["name"], $extLoc + 1 );
		$fileName 		= substr( $_FILES[$key]["name"], 0, $extLoc );
		
		return array( "filename" => $fileName, "ext" => $fileExtension );
	}
	
	protected function set_error( $errCode, $errMsg )
	{
		$this->errCode = $errCode;
		$this->errMsg = $errMsg;
	}
}