<?
class Form_manager
{
	protected $fields;
	protected $tags = array( "<p>", "</p>" );
	
	public function set_rule( $field, $rules = "", $display_name = "" )
	{
		if( !isset( $this->fields[$field] ) )
		{
			$this->create_field_node( $field );
		}
		
		$this->fields[$field]["rules"] = explode( "|", $rules );
		
		if( $display_name != "" )
		{
			$this->fields[$field]["display_name"] = $display_name;
		}
	}
	
	public function set_rules( $arr )
	{
		foreach( $arrs as $rule )
		{
			if( !empty( $rule['field'] ) && !empty( $rule['rules'] ) )
			{
				$this->set_rule( $rule['field'], $rule['rules'] );
			}
		}
	}
	
	public function set_message( $field, $message )
	{
		if( !isset( $this->fields[$field] ) )
		{
			$this->create_field_node( $field );
		}
		
		$this->fields[$field]["message"] = $message;
	}
	
	public function set_messages( $arr )
	{
		foreach( $arrs as $message )
		{
			if( !empty( $rule['$field'] ) && !empty( $rule['message'] ) )
			{
				$this->set_message( $rule['field'], $rule['message'] );
			}
		}
	}
		
	public function set_value( $field, $value )
	{
		if( !isset( $this->fields[$field] ) )
		{
			$this->create_field_node( $field );
		}
		
		$this->fields[$field]["value"] = $value;
	}
	
	public function get_value( $field )
	{
		if( !empty( $this->fields[$field]["value"] ) )
		{
			return $this->fields[$field]["value"];
		}
		
		return "";
	}
	
	public function get_select( $field, $value, $default = false )
	{
		if( !empty( $this->fields[$field]["value"] ) && $this->fields[$field]["value"] == $value || 
			( $default == true && empty( $this->fields[$field]["value"] ) )
		)
		{
			return "selected=\"selected\"";
		}
	}
	
	public function set_error_tags( $open, $close )
	{
		$this->tags = array( $open, $close );
	}
	
	public function get_error( $field, $newTags = array() )
	{	
		if( $this->fields[$field]["valid"] == false && !empty( $this->fields[$field]["message"] ) )
		{
			$tags = ( count( $newTags ) == 2 ? $newTags : $this->tags );
			echo $this->tags[0] . $this->fields[$field]["message"] . $this->tags[1];
		}
		
		return false;
	}
	
	public function get_errors()
	{
		if( !empty( $this->fields ) )
		{
			foreach( $this->fields as $fieldName => $fieldData )
			{
				$this->get_error( $fieldName );
			}
		}
	}
	
	public function get_display_name( $field )
	{
		if( !empty( $this->fields[$field]["display_name"] ) )
		{
			return $this->fields[$field]["display_name"];
		}
		
		return $field;
	}
	
	public function validate( $data )
	{
		/* LIST OF RULES
		
		required					if empty
		matches[field_name]			if matches another form element
		is_unique[table.column]		if exists in db
		min_length[length]			if longer than or equal to
		max_length[length]			if less than or equal to
		exact_length[length]		if is length
		greater_than[value]			if is greater than
		less_than[value]			if is less than
		alpha						if is letter
		alpha_numeric				if is letter or number
		alpha_dash					if is letter, number, udnerscore or dash
		numeric						if is number
		integer						if is integer
		decimal						if is decimal
		valid_email					if is valid email
		valid_phone					if is valid phone number
		valid_ip					if is valid ipv4 or ipv6
		callback[function]			use callback function
		*/
		
		if( empty( $data ) )
		{
			return null;
		}
		
		$isValid = true;
		
		foreach( $this->fields as $localFieldName => &$localFieldData )
		{
			$sentFieldData = "";
			if( isset( $data[$localFieldName] ) )
			{
				$sentFieldData = $data[$localFieldName];
			}
			
			if( is_string( $sentFieldData ) )
			{
				$sentFieldData = trim( $sentFieldData );
			}
			else if( is_array( $sentFieldData ) )
			{
				foreach( $sentFieldData as &$trimItem )
				{
					$trimItem = trim( $trimItem );
				}
			}
			
			$this->set_value( $localFieldName, $sentFieldData );
			
			if( $localFieldData["valid"] == false )
			{
				next;
			}
			
			// go through each rule
			foreach( $localFieldData['rules'] as $rule )
			{
				preg_match( "/\[([\d\w\"\._]*)\]/i", $rule, $sub );
				$rule = preg_replace( "/\[([\d\w\"\._]*)\]/i", "", $rule );
				
				if( method_exists( $this, "validate_" . $rule ) )
				{
					$localFieldData["valid"] = call_user_func_array( array( $this, "validate_" . $rule ), array( $localFieldName, $localFieldData, $sentFieldData, $data, $sub ) );
				}
								
				if( $localFieldData["valid"] == false )
				{
					$isValid = false;
				}
			}
		}

		return $isValid;
	}
	
	protected function validate_required( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( empty( $sentFieldData ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " is required." );
			}
			return 0;
		}
		
		return  true;
	}
	
	protected function validate_matches( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'matches[]'";
			return 0;
		}
		
		if( $sentFieldData != trim( $data[$sub[1]] ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must match " . ucwords( $sub[1] ) . "." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_is_unique( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'is_unique'";
			return 0;
		}
	
		$dbh = Registry::get("dbh");
		
		$dbParams = explode( ".". $sub[1] );
		$check = $dbh->query(
			"SELECT COUNT( * ) AS `count`
			FROM " . $dbParams[0] . "
			WHERE `" . $dbParams . "` = ?",
			array( $dbParams[1] )
		);
		
		if( count( $check ) != 1 )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be unique." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_min_length( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( strlen( $sentFieldData ) < $sub[1] || ( is_array( $sentFieldData ) && count( $sentFieldData ) < $sub[1] ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " minimum length is " . $sub[1] . "." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_max_length( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( strlen( $sentFieldData ) > $sub[1] || ( is_array( $sentFieldData ) && count( $sentFieldData ) > $sub[1] ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " maximum length is " . $sub[1] . "." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_exact_length( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( strlen( $sentFieldData ) != $sub[1] || ( is_array( $sentFieldData ) && count( $sentFieldData ) != $sub[1] ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " length must be exactly " . $sub[1] . "." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_greater_than( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'greater_than[]'";
			return 0;
		}
	
		if( $sentFieldData < $sub[1] )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be greater than " . $sub[1] . "." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_less_than( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'less_than[]'";
			return 0;
		}
		
		if( $sentFieldData > $sub[1] )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be less than " . $sub[1] . "." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_alpha( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'alpha'";
			return 0;
		}
		
		if( preg_match( "/[^a-z]*/i", $sentFieldData ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be an alphabetical character." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_alpha_numeric( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'alpha_numeric'";
			return 0;
		}
		
		if( preg_match( "/[^a-z\d]*/i", $sentFieldData ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be an alphanumeric character." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_alpha_dash( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'alpha_dash'";
			return 0;
		}
		
		if( preg_match( "/[^\w\d]*/i", $sentFieldData ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be an alphanumeric character, hyphen or underscore." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_integer( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'integer'";
			return 0;
		}
		
		if( preg_match( "/[^\d]*/i", $sentFieldData ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be a whole number." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_decimal( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'decimal'";
			return 0;
		}
		
		if( preg_match( "/[^\d\.]*/i", $sentFieldData ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be a decimal." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_valid_email( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'valid_email'";
			return 0;
		}
		
		if( !preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $sentFieldData ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be a valid email." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_valid_ip( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		if( is_array( $sentFieldData ) )
		{
			echo "Form array can not be validated against 'valid_ip'";
			return 0;
		}
		
		if( !filter_var( $sentFieldData, FILTER_FLAG_IPV4 ) && !filter_var( $sentFieldData, FILTER_FLAG_IPV6 ) )
		{
			if( !isset( $localFieldData["message"] ) )
			{
				$this->set_message( $localFieldName, $this->get_display_name( $localFieldName ) . " must be a valid ip." );
			}
			return 0;
		}
		
		return 1;
	}
	
	protected function validate_callback( $localFieldName, $localFieldData, $sentFieldData, $data, $sub )
	{
		
	}
	
	protected function create_field_node( $name )
	{
		$this->fields[$name] = array(
			"display_name" => "",
			"rules" => array(),
			"valid" => true,
		);
	}
}
?>