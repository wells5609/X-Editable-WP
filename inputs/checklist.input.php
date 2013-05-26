<?php

/**
 *	Checklist input	
 *
 *	updated 5/25
 *
 *	Required scripts: 	none
 *	Not for: 			none
 *
**/

class X_Editable_Checklist extends XE_Element {


	function __construct( $meta_or_tax ) {
		
		$this->input_type = 'checklist';
		$this->meta_or_tax = $meta_or_tax;
	
		// don't remove
		parent::__construct();
				
		$this->scripts();
		
		$this->setup_defaults();
		
	}
	
	private function scripts() {
		// enqueue scripts we need for this input
	}
	
	private function setup_defaults() {
	
		$this->show_label(true);
	
		$this->show_external(true);	
	
	}
	
	
	public function show_label($bool) { 
	
		parent::set_show_label($bool);
	
	}
	
	public function show_external($bool) { 
	
		parent::set_show_external($bool);
	
	}
	
	// Public set_source()  -- sets data-source as attribute
	// expects array of text => value pairs
	
	public function set_source($array) {
		
		// emulating a javascript object
		
		$source = "[";
			
			foreach($array as $text => $value) :
				
				$source .= "{value: " . $value . ", text: " . "'" . $text . "'}";
			
			endforeach;
		
		$source .= "]";
		
		// pass as 'source'
		if ( isset($this->data_args) ) {
			
			$this->data_args( array_merge($this->data_args), array('source'=>$source) );
		
		}
		else {
			
			$this->data_args( array('source'=>$source) );
				
		}
		
	}
	
}


//	Template tags
 

/**
 *	Meta
**/
 
function xe_field_checklist($field, $object_id, $args = array(), $show_label = true ) {
	
	$text = new X_Editable_Checklist('meta');
	
	if ( function_exists('get_field_key') ) {
		
		$key = get_field_key($field);
		
		if ( false != $key ) {
			$field = $key;	
		}
		
	}
	
	$text->meta_args($field, $object_id);
	
	if ( true !== $show_label ) {
	
		$text->show_label($show_label);
	
	}
	
	
	if ( isset($args) ) {
		$text->data_args($args);
	}
	
	return $text->html();
	
}


/**
 *	Tax
**/
 
function xe_tax_checklist($taxonomy, $object_id, $args = array(), $show_label = true, $is_single_value = false) {
	
	$text = new X_Editable_Checklist('tax');
	
	$text->tax_args($taxonomy, $object_id, $is_single_value);
	
	$text->set_values_as_ul(true, $taxonomy . '-terms');
	
	if ( true !== $show_label ) {
	
		$text->show_label($show_label);
	
	}
	
	
	if ( isset($args) ) {
		$text->data_args($args);
	}
	
	return $text->html();
	
}


?>