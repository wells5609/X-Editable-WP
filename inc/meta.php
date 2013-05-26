<?php

/* ========
	META
======== */

// (Generic) template tag
function xe_field($key, $object_id, $type = 'text', $args = array() ){
	
	$edit = new X_Editable_Element('meta', $type);
	$edit->setup($object_id, $key);
	if ( !empty($args) ) {
		$edit->set_args($args);	
	}
	$edit->html();
	
}

// inline
function xe_field_inline($key, $object_id, $type = 'text', $args = array() ) {
	
	$edit = new X_Editable_Element('meta', $type);
	// 3rd argument for setup() is label
	$edit->setup($object_id, $key, false);
	$edit->set_args( array_merge(array('mode' => 'inline'), $args) );
	$edit->html();
	
}

/*	Input-type tags
 *
 *	syntax: xe_field_{$inputtype}()
 */
function xe_field_text($key, $object_id, $args = array() ){
	xe_field($key, $object_id, 'text', $args);
}
function xe_field_select($key, $object_id, $args = array() ){
	xe_field($key, $object_id, 'select', $args);
}
function xe_field_checklist($key, $object_id, $args = array() ){
	xe_field($key, $object_id, 'checklist', $args);	
}
function xe_field_date($key, $object_id, $args = array() ){
	xe_field($key, $object_id, 'date', $args);	
}
function xe_field_url($key, $object_id, $args = array() ) {
	xe_field($key, $object_id, 'url', $args);	
}
// textarea
function xe_field_textarea($key, $object_id, $args = array() ){
	
	$edit = new X_Editable_Element('meta', 'textarea');
	$edit->setup($object_id, $key, false);
	if ( !empty($args) ) {
		$edit->set_args($args);	
	}
	$edit->html();
}
// bootstrap-wysihtml5
function xe_field_wysiwyg($key, $object_id, $args = array() ) {
		
	$edit = new X_Editable_Element('meta', 'wysihtml5');
	$edit->setup($object_id, $key);
	if ( !empty($args) ) {
		$edit->set_args($args);	
	}
	$edit->html();
		
}

// Tags when VALUES are Users - (NOT for user meta)
function xe_field_users($key, $object_id, $type = 'select', $args = array() ) {
	
	$edit = new X_Editable_Element('meta', $type);
	$edit->setup($object_id, $key);
	if ( !empty($args) ) {
		$edit->set_args($args);	
	}
	$edit->set_meta_object('user');
	$edit->html();
	
}
	// input tags when users as values	
	function xe_field_checklist_users($key, $object_id, $args = array() ) {
		xe_field_users($key, $object_id, 'checklist', $args);
	}
	function xe_field_select_users($key, $object_id, $args = array() ) {
		xe_field_users($key, $object_id, 'select', $args);
	}

// inline tag when users as values
function xe_field_users_inline($key, $object_id, $type, $args = array() ) {
	
	$edit = new X_Editable_Element('meta', $type);
	$edit->setup($object_id, $key, false);
	$edit->set_args( array_merge(array('mode' => 'inline', 'showbuttons' => 'false'), $args) );
	$edit->set_meta_object('user');
	$edit->html();
		
}

// for TERM (object) meta
function xe_term_field($key, $object_id, $taxonomy, $type, $args = array() ) {
	
	$edit = new X_Editable_Element('meta', $type);
	$edit->setup($object_id, $key, false, 'term');
	if ( !empty($args) ) {
		$edit->set_args($args);	
	}
	$edit->set_term_meta_tax($taxonomy);
	$edit->html();
	
}
