<?
// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// | Class: Mailer
// | Version: 1.1.1.2
// | Last Minor Update: January 16, 2012
// | Last Major Update: November 18, 2011
// | Change Log:
// |	2012-01-19
// |		- Updated send_mail, fixed sytax bug that returned a warning when from name was blank
// |	2012-01-16
// |		- Updated error_trigger to only display errors if debug mode is enabled, else return false
// |	2012-01-02
// |		- Updated build_email_string to properly build emails without a name
// |	2011-12-19
// |		- Removed error messages from debugger
// | 		- Updated 'build_email_string', not checks if name isset and not blank
// |	2011-11-29
// |		- Updated send_mail method, now returns true on success, or false
// |		- Removed htmlentities call from text messages
// |		- Misc bug fixes
// |	2011-11-23
// |		- Added support for CC and BCC fields
// |		- Added support for mutliple to, fro, cc, bcc and reply to address/names
// |		- Added support for multiple reply to addresses
// |		- Added attachment support
// |		- Added documentation of all public methods
// |		- Changed error management, error messages are now easier to understand.
// |			Default errors are now NOTICES instead of WARNINGS
// |	2011-11-18
// |		- initial release
// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//
// DOCUMENTATION
//	Public method calls
//		__construct( (string) to, (string) from, (string) subject, [(string) to name, [(string) from name]] )
//		send_mail()
//		set_to( (string) email, [(string) name] )
//		set_reply_to( (string) email, [(string) name] )
//		set_cc( (string) email, [(string) name] )
//		set_bcc( (string) email, [(string) name] )
//		set_text_message( (string) message )
//		set_html_message( (string) message )
//		set_attachment( (string) file name, (string) file type, (string) file path )
//		set( (string) key, (mixed) value )
//		get( (string) key )
//		toggle_debug()
//		debug()
//
// TYPICAL USAGE
//	$mailer = new Mailer( "john@doe.com", "me@myself.com", "Sample Email" );
//	$mailer->set_text_message( "Hey John, this is me" );
//	$mailer->send_mail();

class Mailer
{	
// ##########################################################################################################################################################################
// ############################## ---------------------------------------- VARIABLES / CONSTANTS
// ##########################################################################################################################################################################
	protected $from 		= array();						# from email address
	protected $to 			= array();						# to email address
	protected $cc			= array();						# cc email ddresses
	protected $bcc			= array();						# bcc email addresses
	protected $reply_to		= array();						# reply-to email address
	
	protected $subject 		= "";							# email subject
	protected $txt_message 	= "";							# text body of the email
	protected $html_message = "";							# html body of the email
	
	protected $attachments 	= array();						# attachments

	protected $BOUNDARY		= "Alternative_Boundary_";		# semi rand boundary for html/txt
	protected $BOUNDARY2	= "Mixed_Boundary_";			# semi rand boundary for attachments
	
	protected $vars 		= array();						# additional variables, user defined
	protected $debug 		= false;						# development environment
	
	//////////////////////////////////////////////////
	# ERROR VARS
	//////////////////////////////////////////////////
	# error level constants
	const ERR_LEVEL_NOTICE			= 1;	# error notice, script continues
	const ERR_LEVEL_WARNING			= 2;	# error warning, script dies
	#-------------------------------------------------
	# field errors
	const TO_INVALID 				= 1;	# to input is invalid
	const FROM_INVALID				= 2;	# to name input is invalid
	const CC_INVALID				= 3;	# cc input invalid
	const BCC_INVALID				= 4;	# bcc input invalid
	const REPLY_TO_INVALID			= 5;	# reply to input invalid
	const SUBJECT_INVALID			= 6;	# subject input invalid
	
	# message errors
	const MESSAGE_INVALID			= 20;	# no message set
	const TXT_MESSAGE_INVALID		= 21;	# text message input invalid
	const HTML_MESSAGE_INVALID		= 22;	# html message input invalid

	# attachment errors
	const ATTACHMENT_INVALID		= 30;	# attachment file is invalid/not found
	const ATTACHMENT_DATA_INVALID	= 31;	# attachment file is corrupt or inaccessible
	
	# php errors
	const VARS_KEY_NOT_SET			= 50;	# vars key is undefined
	const EMPTY_ARRAY				= 51;	# array passed has no values
	
	# class errors
	const FORCE_KILL				= 98;	# errors occured too soon, killing script
	const MAIL_SEND_FAILED 			= 99;	# email failed to send
	
	// error messages, keys are error number
	protected $ERROR_MESSAGES = array(
					1	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: TO string is invalid" ),
					2	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: FROM string is invalid" ),
					3	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: CC string is invalid" ),
					4	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: BCC string is invalid" ),
					5	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: REPLY TO string is invalid" ),
					6	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: SUBJECT string is invalid" ),
					
					20	=>	array( self::ERR_LEVEL_WARNING, "Warning: no valid messages found" ),
					21	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: no TEXT message found" ),
					22	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: no HTML message found" ),
					
					30	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: attachment file is invalid or not found" ),
					31	=>	array( self::ERR_LEVEL_WARNING, "Warning: attachment file is corrupt or inaccessible." ),
										
					50	=>	array( self::ERR_LEVEL_WARNING, "Warning: array key undefined" ),
					51	=>	array( self::ERR_LEVEL_NOTICE, 	"Notice: Array is empty" ),
					
					98	=>	array( self::ERR_LEVEL_WARNING, "Critical errors have occured: script killed" ),
					99 	=> 	array( self::ERR_LEVEL_WARNING, "Warning: email failed to send" ),
				);
	
	protected $err_stack = array();			# stack of errors that have occured
	
// ##########################################################################################################################################################################
// ############################## ---------------------------------------- DO'ERS
// ##########################################################################################################################################################################
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	construct
	# DESCRIPTION:	creates a basic email to be sent
	# PARAMETERS:	(string) to email
	#				(string) to name
	#				(string) from email
	#				(string) from name
	#				(string) email subject
	# RETURNED:		
	#__________________________________________________
	public function __construct( $to, $from, $subject, $to_name = "", $from_name = "" )
	{
		// setup boundary
		$this->init_boundary();
		
		// make sure none of the fields are blank
		( trim( $subject ) 	== "" 	? $this->error_trigger( self::SUBJECT_INVALID )		: "" );
		
		// set class vars
		$this->set_from( $from, $from_name );
		$this->set_to( $to, $to_name );
		
		$this->subject		= $subject;
		
		if( $this->has_errors() )
		{
			$this->error_trigger( self::FORCE_KILL );
		}
	}
			
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	send_mail
	# DESCRIPTION:	send the email
	# PARAMETERS:	
	# RETURNED:		true of success, false of failure
	#__________________________________________________
	public function send_mail()
	{
		// build to
		$to = $this->build_email_string( $this->to );
		// setup reply to
		if( empty( $this->reply_to ) )
		{
			$this->set_reply_to( $this->from[0]['email'], ( isset( $this->from[0]['name'] ) ? $this->from[0]['name'] : "" ) );
		}
		// init header
		$header = $this->set_header();
		// build message
		$body = $this->set_body();
		
		// send the email
		if( !$this->has_errors() && !mail( $to, $this->subject, $body, $header ) )
		{
			$this->error_trigger( self::MAIL_SEND_FAILED );
			return false;
		}
		else if( $this->has_errors() )
		{
			ob_start();
			
				echo "Errors have occured, email not sent. \n\n";
				print_r( array( $header, $body ) );
		
			$toPrint = ob_get_contents();
			ob_end_clean();
			
			echo "<pre>";
			echo htmlspecialchars( $toPrint );
			echo "</pre>";
			
			if( $this->debug == true )
			{
				$this->debug();
			}
			return false;
		}
		return true;
	}
	
// ##########################################################################################################################################################################
// ############################## ---------------------------------------- INIT / VALIDATION
// ##########################################################################################################################################################################
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	init_boundary
	# DESCRIPTION:	sets up email boundary variable
	# PARAMETERS:	
	# RETURNED:		
	#__________________________________________________
	private function init_boundary()
	{
		$this->BOUNDARY = $this->BOUNDARY . md5( time() );
		$this->BOUNDARY2 = $this->BOUNDARY2 . md5( time() );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	is_valid_email
	# DESCRIPTION:	checks if email is valid
	# PARAMETERS:	(string) email address
	# RETURNED:		True if email is valid, false if invalid
	#__________________________________________________
	protected function is_valid_email( $email )
	{
		return preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email );
	}
	
	
// ##########################################################################################################################################################################
// ############################## ---------------------------------------- SETTERS/GETTERS
// ##########################################################################################################################################################################
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	to
	# DESCRIPTION:	adds email receiver
	# PARAMETERS:	(string) email
	#				[(string) name]
	# RETURNED:		false if fail, true if success
	#__________________________________________________
	public function set_to( $email, $name = "" )
	{
		return $this->do_add( "to", $email, $name );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	add_from
	# DESCRIPTION:	adds email sender
	# PARAMETERS:	(string) email
	#				[(string) name]
	# RETURNED:		false if fail, true if success
	#__________________________________________________
	public function set_reply_to( $email, $name = "" )
	{
		return $this->do_add( "reply_to", $email, $name );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	add_from
	# DESCRIPTION:	adds email sender
	# PARAMETERS:	(string) email
	#				[(string) name]
	# RETURNED:		false if fail, true if success
	#__________________________________________________
	public function set_cc( $email, $name = "" )
	{
		return $this->do_add( "cc", $email, $name );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	add_from
	# DESCRIPTION:	adds email sender
	# PARAMETERS:	(string) email
	#				[(string) name]
	# RETURNED:		false if fail, true if success
	#__________________________________________________
	public function set_bcc( $email, $name = "" )
	{
		return $this->do_add( "bcc", $email, $name );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	add_from
	# DESCRIPTION:	adds email sender
	# PARAMETERS:	(string) email
	#				[(string) name]
	# RETURNED:		false if fail, true if success
	#__________________________________________________
	private function set_from( $email, $name = "" )
	{
		return $this->do_add( "from", $email, $name );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	set_text_message
	# DESCRIPTION:	sets internal text message variable
	# PARAMETERS:	(string) message
	# RETURNED:		
	#__________________________________________________
	public function set_text_message( $string )
	{
		$this->set_message( "txt", $string );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	set_html_message
	# DESCRIPTION:	sets internal text message variable
	# PARAMETERS:	(string) message
	# RETURNED:		
	#___________________________________________________
	public function set_html_message( $string )
	{
		$this->set_message( "html", $string );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	set_message
	# DESCRIPTION:	private set message call for settign internal variables
	# PARAMETERS:	(string) message type
	#				(string) message
	# RETURNED:		
	#__________________________________________________
	protected function set_message( $type, $string )
	{
		if( trim( $string ) == "" )
		{
			$this->error_trigger( constant( "self::" . strtoupper( $type ) . "_MESSAGE_INVALID" ) );
		}
		
		$this->{$type . "_message"} = $string;
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	set_attachment
	# DESCRIPTION:	adds an attachment to the email
	# PARAMETERS:	(string) file name
	#				(string) file type
	#				(string) file path
	# RETURNED:		true on succes, false on error
	#__________________________________________________
	public function set_attachment( $filename, $filetype, $filepath )
	{
		$file = fopen( $filepath, 'r' );
		if( $file === false )
		{
			$this->error_trigger( self::ATTACHMENT_INVALID );
			return false;
		}
		$data = fread( $file, filesize( $filepath ) ); 
		if( $data === false )
		{
			$this->error_trigger( self::ATTACHMENT_DATA_INVALID );
			return false;
		}
		fclose( $file );
		
		$data = chunk_split( base64_encode( $data ) );
		
		$att['name'] = $filename;
		$att['type'] = $filetype;
		$att['data'] = $data;
		array_push( $this->attachments, $att );
		
		return true;
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	set_header
	# DESCRIPTION:	sets the header
	# PARAMETERS:	
	# RETURNED:		
	#__________________________________________________
	protected function set_header()
	{
		$header = "";
		// set from
		$from = $this->build_email_string( $this->from );
		$reply_to = $this->build_email_string( $this->reply_to );
		$cc = 	( !empty( $this->cc ) 	? $cc = $this->build_email_string( $this->cc )		: "" );
		$bcc =	( !empty( $this->bcc ) 	? $bcc = $this->build_email_string( $this->bcc )	: "" );
		
		$header .= "From: " . $from . "\r\n";
		( $cc != ""  ? $header .= "Cc: " . $cc . "\r\n" : "" );
		( $bcc != "" ? $header .= "Bcc: " . $bcc . "\r\n" : "" );
		#$header .= "Subject: " . $this->subject . "\r\n";		
		$header .= "Reply-To: " . $reply_to . "\r\n";
		$header .= "Return-path: " . $from . "\r\n";	
		$header .= "MIME-Version: 1.0" . "\r\n";
		$header .= "X-Sender: " . $from . "\r\n";
		$header .= "X-Priority: 3" . "\r\n";
		$header .= "X-Mailer: PHP/" . phpversion() . "\r\n";
		
		$header .= "Content-Type: multipart/";
		if( empty( $this->attachments ) )
		{
			$header .= "alternative; boundary=\"" . $this->BOUNDARY . "\";\r\n\r\n";
		}
		else
		{
			$header .= "mixed; boundary=\"" . $this->BOUNDARY2 . "\";\r\n\r\n";
		}
		
		return $header;
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	set_body
	# DESCRIPTION:	sets up the email body
	# PARAMETERS:	(string) text message
	#				[(string) html message]
	# RETURNED:		
	#__________________________________________________
	protected function set_body()
	{
		// make sure both fields arent blank, we have at least 1 thing to work with
		if( $this->txt_message == "" && $this->html_message == "" )
		{
			$this->error_trigger( self::MESSAGE_INVALID );
		}
		
		// init blank message
		$message  = ""; 
		
		if( !empty( $this->attachments ) )
		{
			$message .= "--" . $this->BOUNDARY2 . "\n";
			$message .= "Content-Type: multipart/alternative; boundary=\"" . $this->BOUNDARY . "\"\n\n";
		}
		
		// there is some text to work with
		if( $this->txt_message != "" )
		{
			$message .= "--" . $this->BOUNDARY . "\n"; 
			$message .= "Content-Type: text/plain" . "\n";             // Plain text section 
			$message .= "Content-Transfer-Encoding: 7bit" . "\n\n";    // 7bit because it has no ascii characters 
			$message .= $this->txt_message . "\n\n"; 
		}
		
		// there is an hmtl message
		if( $this->html_message != "" )
		{		
			$message .= "--" . $this->BOUNDARY . "\n"; 
			$message .= "Content-Type: text/html" . "\n";             // HTML section 
			$message .= "Content-Transfer-Encoding: 7bit" . "\n\n";   // must be 8bit cause it could have ascii characters 
			$message .= $this->html_message . "\n\n";
		}
		
		// there is an attachment
		if( !empty( $this->attachments ) )
		{
			$message .= "--" . $this->BOUNDARY . "\n\n";
			foreach( $this->attachments as $att )
			{
				$message .= "--" . $this->BOUNDARY2 . "\n";
				$message .= "Content-Type: " . $att['type'] . "; name=\"" . $att['name'] . "\"\n";
				$message .= "Content-Transfer-Encoding: base64\n";
				$message .= "Content-Disposition: attachment; filename=\"" . $att['name'] . "\"\n\n";
				$message .= $att['data'] . "\n\n";
			}
		}
		
		// add seperator to message
		if( empty( $this->attachments ) )
		{
			$message .= "--" . $this->BOUNDARY . "--\n\n\n";
		}
		else
		{
			$message .= "--" . $this->BOUNDARY2 . "--\n\n\n";
		}
		// set class var
		return $message;
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	set
	# DESCRIPTION:	sets user defined variable in vars array
	# PARAMETERS:	(string | int) array key
	#				(string) value
	# RETURNED:		
	#__________________________________________________
	public function set( $name, $value )
	{
		$this->vars[$name] = $value;
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	get
	# DESCRIPTION:	gets user defined variable in vars array
	# PARAMETERS:	(string | int) array key
	# RETURNED:		returns value from array, or null if array key doesn't exist
	#__________________________________________________
	public function get( $name )
	{
		// if key exists
		if( array_key_exists( $name, $this->vars ) )
		{
			return $this->vars[$name];
		}
		$this->error_trigger( self::VARS_KEY_NOT_SET );
		return null;
	}
	
// ##########################################################################################################################################################################
// ############################## ---------------------------------------- BUILDER FUNCTIONS
// ##########################################################################################################################################################################
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	do_add
	# DESCRIPTION:	adds email to or from
	# PARAMETERS:	(string) add type
	#				(string) email
	#				[(string) name]
	# RETURNED:		false if fail, true if success
	#__________________________________________________
	protected function do_add( $type, $email, $name )
	{
		( trim( $email ) == "" 	? $this->error_trigger(	constant("self::" . strtoupper( $type ) . "_INVALID") ) : "" );
		
		// check to email, make sure it's valid
		if( !$this->is_valid_email( $email ) )
		{
			$this->error_trigger( constant("self::" . strtoupper( $type ) . "_INVALID") );
			return false;
		}
		
		$tmp['email'] = $email;
		( $name != "" ? $tmp['name'] = $name : "" );
		
		$this->{$type}[] = $tmp;
		return true;
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	build_email_string
	# DESCRIPTION:	builds name/email string ex: John <john@email.com>
	# PARAMETERS:	(array) array of names and emails
	# RETURNED:		false if failed, a string if successful
	#__________________________________________________
	protected function build_email_string( $array )
	{
		if( empty( $array ) )
		{
			$this->error_trigger( self::EMPTY_ARRAY );
			return false;
		}
		$to_return = "";
		foreach( $array as $item )
		{
			$tmp = $item['email'] . ", ";
			if( isset( $item['name'] ) && $item['name'] != "" )
			{
				$tmp = $item['name'] . " <" . $item['email'] . ">, ";
			}
			$to_return .= $tmp;
		}
		return substr( $to_return, 0, -2 );
	}
	
// ##########################################################################################################################################################################
// ############################## ---------------------------------------- ERROR MANAGEMENT
// ##########################################################################################################################################################################
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	error_trigger
	# DESCRIPTION:	triggers an error message
	# PARAMETERS:	(int) error number
	#				(int) error level
	# RETURNED:		true if successful, false if failed
	#__________________________________________________
	protected function error_trigger( $err )
	{
		if( $this->debug != true )
		{
			return false;
		}
		array_push( $this->err_stack, $err );
		if( isset( $this->ERROR_MESSAGES[$err] ) )
		{
			echo "<strong>Error:</strong> [" . $err . "] " . $this->ERROR_MESSAGES[$err][1] . "<br/>";
			if( $this->ERROR_MESSAGES[$err][0] == self::ERR_LEVEL_WARNING )
			{
				echo "Killing Script<br>";
				foreach( $this->err_stack as $item )
				{
					echo "[" . $item . "] => " . $this->ERROR_MESSAGES[$item][0] . ", " . $this->ERROR_MESSAGES[$item][1] . "<br>";
				}
				die();
			}
			return true;
		}
		echo "<pre>";
		foreach( $this->err_stack as $item )
		{
			print_r( $this->ERROR_MESSAGES[$err] );
		}
		echo "</pre>";
		die( "Warning: Undefined error, killing script" );
		
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	has_errors
	# DESCRIPTION:	checks if an error has occured during runtime
	# PARAMETERS:	
	# RETURNED:		true if an error occured, false if not
	#__________________________________________________
	protected function has_errors()
	{
		if( $this->debug == true )
		{
			return true;
		}
		return !empty( $this->err_stack );
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	toggle_debug
	# DESCRIPTION:	turns debug on and off
	# PARAMETERS:	
	# RETURNED:		
	#__________________________________________________
	public function toggle_debug()
	{
		$this->debug ^= 1;
	}
	
	#^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
	# FUNCTION: 	debug
	# DESCRIPTION:	prints out all variable values
	# PARAMETERS:	
	# RETURNED:		
	#__________________________________________________
	public function debug()
	{
		$debug = get_object_vars( $this );
		unset( $debug['ERROR_MESSAGES'] );
		
		ob_start();
		
			print_r( $debug );
		
		$toPrint = ob_get_contents();
		ob_end_clean();
			
		echo "<pre>";
		echo htmlspecialchars( $toPrint );
		echo "</pre>";
	}	
}