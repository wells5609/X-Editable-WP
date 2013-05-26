<?php

/* ================
		TAX		
  Template tags		
================ */

// Template tag (generic)
function xe_tax($taxonomy, $object_id = NULL, $input_type = 'checklist', $args = array() ) {
	
	if ( NULL === $object_id ) {
		global $post;
		$object_id = $post->ID;	
	}	
	
	$edit = new X_Editable_Element('tax', $input_type);
	
	$edit->setup($object_id, $taxonomy, true);
	$edit->set_external_container(true);
	
	if ( !empty($args) ) {
		$edit->set_args($args);	
	}
	
	$edit->html();
	
	//	xeditable_tax_html($taxonomy, $object_id, $input_type, $args);
	
}

// CHECKLIST element
function xe_tax_checklist($taxonomy, $object_id, $args = array() ) {
		
	xe_tax($taxonomy, $object_id, 'checklist', $args);
	
}

// SELECT element
function xe_tax_select($taxonomy, $object_id, $args = array() ) {
		
	xe_tax($taxonomy, $object_id, 'select', $args);
	
}

function xe_tax_inline($taxonomy, $object_id, $input_type = 'select', $args = array()) {
	
	$edit = new X_Editable_Element('tax', $input_type);
	
	$edit->setup($object_id, $taxonomy, false);
	$edit->set_external_container(false);
	
	$edit->set_args(array_merge( array('mode' => 'inline'), $args) );
	
	$edit->html();
	
}

?>