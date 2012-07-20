<?
class Form_validation
{
	protected $fields;
	protected $delim = array( "<p>", "</p>" );
	
	public function set_rule( $field, $rules )
	{
		$this->fields[$field] = array(
			"rules" => explode( "|", $rules ),
			"valid" => true,
		);
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
		if( isset( $this->fields[$field] ) )
		{
			$this->fields[$field]["message"] = $message;
		}
	}
	
	public function set_messages( $arr )
	{
		foreach( $arrs as $message )
		{
			if( !empty( $rule['$field'] ) && !empty( $rule['message'] ) )
			{
				$this->set_message( $rule['$field'], $rule['message'] );
			}
		}
	}
	
	public function set_error_delim( $open, $close )
	{
		$this->delim = array( $open, $close );
	}
	
	public function run( $data )
	{
		/* LIST OF RULES
		
		required					if empty
		matches["field_name"]		if matches another form element
		is_unique["table.column"]	if exists in db
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
		
		*/		
		foreach( $this->fields as $name => &$field )
		{
			// go through each rule
			foreach( $field['rules'] as $field => &$local )
			{
				$fieldData = trim( $data[$field] );
				$sub = preg_match( "/\[([\d\w\"\._]*)\]/i", $local["rule"], $match );
				preg_replace( "/\[([\d\w\"\._]*)\]/i", $local["rule"] );
				
				switch( $local )
				{
					case "required":
						if( empty( $fieldData ) )
						{
							$local["valid"] = false;
						}
						break;
					case "matches":
						if( $fieldData != trim( $data[$sub] ) )
						{
							$local["valid"] = false;
						}
						break;
					case "is_unique":
						//
						break;
					case "min_length":
						if( strlen( $fieldData ) < $sub)
						{
							$local["valid"] = false;
						}
						break;
					case "max_length":
						if( strlen( $fieldData ) > $sub)
						{
							$local["valid"] = false;
						}
						break;
					case "exact_length":
						if( strlen( $fieldData ) != $sub)
						{
							$local["valid"] = false;
						}
						break;
					case "greater_than":
						if( $fieldData < $sub)
						{
							$local["valid"] = false;
						}
						break;
					case "less_than":
						if( $fieldData > $sub)
						{
							$local["valid"] = false;
						}
						break;
					case "alpha":
						if( preg_match( "/[^a-z]*/i", $fieldData ) )
						{
							$local["valid"] = false;
						}
						break;
					case "alpha_numeric":
						if( preg_match( "/[^a-z\d]*/i", $fieldData ) )
						{
							$local["valid"] = false;
						}
						break;
					case "alpha_dash":
						if( preg_match( "/[^\w\d]*/i", $fieldData ) )
						{
							$local["valid"] = false;
						}
						break;
					case "integer":
						if( preg_match( "/[^\d]*/i", $fieldData ) )
						{
							$local["valid"] = false;
						}
						break;
					case "decimal":
						if( preg_match( "/[^\d\.]*/i", $fieldData ) )
						{
							$local["valid"] = false;
						}
						break;
					case "valid_email":
						if( !preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $fieldData ) )
						{
							$local["valid"] = false;
						}
						break;
					case "valid_ip":
						if( !filter_var( $fieldData, FILTER_FLAG_IPV4 ) && !filter_var( $fieldData, FILTER_FLAG_IPV6 ) )
						{
							$local["valid"] = false;
						}
						break;
					case "callback":
						
						break;
				}
			}
		}
	}
}
?>