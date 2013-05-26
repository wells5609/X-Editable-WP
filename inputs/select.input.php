<?php

/**
 *	Select input	
 *
 *	updated 5/25
 *
 *	Required scripts: 	none
 *	Not for: 			none
 *
**/

class X_Editable_Select extends XE_Element {


	function __construct( $meta_or_tax ) {
		
		$this->input_type = 'select';
		$this->meta_or_tax = $meta_or_tax;
	
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
		parent::set_show_external(true);
		
		parent::set_is_single_value(true);	
	
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
	 
	function xe_field_select($field, $object_id, $args = array() ) {
		
		$select = new X_Editable_Select('meta');
		
		if ( function_exists('get_field_key') ) {
			
			$key = get_field_key($field);
			
			if ( false != $key ) {
				$field = $key;	
			}
			
		}
		
		$select->meta_args($field, $object_id);
		
		if ( isset($args) ) {
			$select->data_args($args);
		}
		
		return $select->html();
		
	}
	
	function xe_field_select_src($field, $object_id, $source, $args = array() ) {
		
		$select = new X_Editable_Select('meta');
		
		if ( function_exists('get_field_key') ) {
			
			$key = get_field_key($field);
			
			if ( false != $key ) {
				$field = $key;	
			}
			
		}
		
		$select->meta_args($field, $object_id);
		
		if ( isset($args) ) {
			$select->data_args($args);
		}
		
		$select->set_source($source);
		
		return $select->html();
		
	}


/**
 *	Tax
**/
	 
	function xe_tax_select($taxonomy, $object_id, $args = array()) {
		
		$select = new X_Editable_Select('tax');
		
		$select->tax_args($taxonomy, $object_id);
		
		if ( isset($args) ) {
			$select->data_args($args);
		}
		
		return $select->html();
		
	}
	
	
	function xe_tax_select_src($taxonomy, $object_id, $source, $args = array() ) {
		
		$select = new X_Editable_Select('tax');
		
		$select->tax_args($taxonomy, $object_id);
		
		if ( isset($args) ) {
			$select->data_args($args);
		}
		
		$select->set_source($source);
		
		return $select->html();
		
	}

?>