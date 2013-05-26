<?php

/**
 *	Text input	
 *
 *	updated 5/25
 *
 *	Required scripts: 	none
 *	Not for: 			taxonomy terms
 *
**/

class X_Editable_Text extends XE_Element {


	function __construct() {
		
		$this->input_type = 'text';
		$this->meta_or_tax = 'meta';
	
		// don't remove
		parent::__construct();
				
		$this->scripts();
		
		$this->settings();
		
	}
	
	private function scripts() {
		// enqueue scripts we need for this input
	}
	
	private function settings() {
		parent::set_show_label(true);
		parent::set_show_external(false);	
	}
		
	
}

/**
 *	Template tags
 *
 */
 
function xe_field_text($field, $object_id, $args = array() ) {
	
	$text = new X_Editable_Text;
	
	if ( function_exists('get_field_key') ) {
		
		$key = get_field_key($field);
		
		if ( false != $key ) {
			$field = $key;	
		}
		
	}
	
	$text->meta_args($field, $object_id);
	
	if ( isset($args) ) {
		$text->data_args($args);
	}
	
	return $text->html();
	
}


?>