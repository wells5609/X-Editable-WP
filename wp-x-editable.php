<?php
/*
Plugin Name: WP X-Editable
Plugin URI: 
Description: Edit from the front end using X-Editable. Currently you can edit meta (i.e. custom fields) for posts and users.
Version: 1.1
Author: Wells Peterson
Author URI: http://wellspeterson.com/
License: GPL
Copyright: Wells Peterson
*/

/**
* Setup the WP X-Editable plugin
*
* Use add_theme_support( 'x-editable' ); in theme functions.php to enable.
*/

add_action( 'init', 'wpx_editable' );

function wpx_editable() {
	
	if ( ! current_theme_supports( 'x-editable' ) ) {
		return false;
	}
	
	new X_Editable_WP();
	
	
	// X-Editable Element class
	
	require_once('x-editable-element.class.php');
	
	
	// inputs
	
	include_once( 'inputs/text.input.php' );
	include_once( 'inputs/textarea.input.php' );
	include_once( 'inputs/date.input.php' );
	include_once( 'inputs/select.input.php' );
	include_once( 'inputs/checklist.input.php' );
	
	// deprecated
	
	// tax.php
	// meta.php
	// class.x-editable-element.php
	// deprecated.php

}

// Plugin class
class X_Editable_WP {

	protected $version;
	
	function __construct() {		
		$this->version = '1.1';
		$this->scripts();
		$this->setup_hooks();
		$this->enqueue();
	}
	
	private function scripts() {
		
		// Bootstrap editable css
		wp_register_style('x-editable', plugins_url( 'css/bootstrap-editable.css' , __FILE__ ), array('bootstrap'), '1.4.4', all );
		
		// Bootstrap editable js
		wp_register_script('x-editable', plugins_url( 'js/bootstrap-editable.min.js' , __FILE__ ), array('jquery'), '1.4.4', true );
		
		
		// WYSIHTML5
		wp_register_script('wysihtml5', plugins_url('js/wysihtml5-0.3.0.min.js', __FILE__ ), array('jquery', 'x-editable-wp'), '0.3.0', true);
		
		// Bootstrap (WYSIWYG) WYSIHTML5 
		wp_register_script('bootstrap-wysihtml5-js', plugins_url('js/bootstrap-wysihtml5.js', __FILE__ ), array('jquery', 'x-editable-wp'), $this->version, true);
		wp_register_style('bootstrap-wysihtml5-css', plugins_url('css/bootstrap-wysihtml5.css', __FILE__ ), array('x-editable'), $this->version);
		
		// X-Editable for Bootstrap WYSIHTML5
		wp_register_script('x-editable-wysihtml5', plugins_url('js/x-editable-wysihtml5.js', __FILE__), array('jquery', 'x-editable-wp'), '0.2.0', true);
		
		
		// X-Editable WP
		wp_register_script('x-editable-wp', plugins_url('js/x-editable-wp.js', __FILE__ ), array('jquery', 'x-editable'), $this->version, true );
		
	}
	
	private function setup_hooks() {
		
		// Meta edit handler 
		add_action( 'wp_ajax_xeditable_meta_handler', array($this, 'meta_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_handler', array($this, 'must_log_in') );
		
		// Meta load handler
		add_action( 'wp_ajax_xeditable_meta_load', array($this, 'load_meta') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_load', array($this, 'load_meta') );
		
		// Gets user options (for meta)
		add_action( 'wp_ajax_xeditable_user_options', array($this, 'user_options') );
		add_action( 'wp_ajax_nopriv_xeditable_user_options', array($this, 'must_log_in') );
		
		// Gets taxonomy options (terms)
		add_action( 'wp_ajax_xeditable_tax_options', array($this, 'tax_options') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_options', array($this, 'must_log_in') );
		
		// Taxonomy edit handler
		add_action( 'wp_ajax_xeditable_tax_handler', array($this, 'tax_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_handler', array($this, 'must_log_in') );
		
		// Term load handler
		add_action( 'wp_ajax_xeditable_term_load', array($this, 'load_terms') );
		add_action( 'wp_ajax_nopriv_xeditable_term_load', array($this, 'must_log_in') );
			
	}
	
	private function enqueue() {
		wp_enqueue_style('x-editable');
		wp_enqueue_script('x-editable-wp');
		wp_localize_script(	'x-editable-wp', 'xeditable', array( 'ajaxurl' => admin_url('admin-ajax.php') ) );	
	}
	
	
	/* ===================
		AJAX HANDLERS
	=================== */
	
	// user not logged in
	public function must_log_in() {
		echo 'You must log in to use this feature.';	
	}
	
	// Taxonomy terms (add/remove to/from an object)
	public function tax_handler() {
		
		$object_id = $_POST['pk']; // the POST ID.
		$taxonomy = trim($_POST['name']); // will be the taxonomy name
		$value = $_POST['value'];
		
		// nonce must match name.
		if ( ! wp_verify_nonce( $_POST['nonce'], $taxonomy )) {
			header('HTTP 400 Bad Request', true, 400);
			exit('Wise guy, huh?');
		}
		
		if ($value) {
			
			if ( is_string($value) ) {
				
				$exists = term_exists($value, $taxonomy);
				
				if ( ! $exists ) {
					$term = wp_insert_term($value, $taxonomy);
					$term_id = (int) $term['term_id'];
				}
				else {
					$term = get_term_by('name', $value, $taxonomy);	
					$term_id = (int) $term->term_id;
				}
				
				$terms = array($term_id);
					
			}
			
			elseif ( is_array($value) ) {
				
				$terms = array();
				
				foreach($value as $val) {
					$terms[] = (int) $val;	
				}
			}
			else {
				$terms = array((int) $value);
			}
					
			wp_set_object_terms($object_id, $terms, $taxonomy, false);
	
		}
		
		die();	
		
	}
	
	// Handles HTTP GET requests for tax terms (used to populate dropdowns/checkboxes).
	public function tax_options() {
		
		$tax = trim($_REQUEST['tax']); 
		$string = $_REQUEST['string'];
		
		if ( ! is_null($tax) ) {
			
			$terms = get_terms($tax, array('hide_empty=0') );
			
			if ( $terms ) {
				
				$options = array();
				
				foreach ($terms as $term) :
					
					if ( true != $string) {
						$options[$term->term_id] = $term->name;
					}
					else {
						$options[] = $term->name;	
					}
					
				endforeach;
				
				echo json_encode($options);
	
			}
			
		}
		
		die();
	}
	
	// user options
	public function user_options() {
		
		$role = trim($_REQUEST['role']); 
		
		if ( ! is_null($role) ) {
			
			$users = get_users( 'role=' . $role );
			
			if ( $users ) {
				
				$options = array();
				
				foreach ($users as $user) :
					
					$options[$user->ID] = $user->display_name;
					
				endforeach;
				
				echo json_encode($options);
	
			}
			
		}
		
		die();
	}
	
	// Load terms
	public function load_terms() {
		
		$tax = $_REQUEST['tax'];
		$object_id = $_REQUEST['object_id'];
		$inline = $_POST['inline'];
		
		$terms = get_the_terms($object_id, $tax);
		
		if ($terms) {
			
			if ( true == $inline ) {
				if ( count($terms) > 1 ) {
					foreach($terms as $term) :
						echo $term->name . ', ';
					endforeach;
				}
				else {
					foreach($terms as $term) :
						echo $term->name;
					endforeach;
				}
			}
			else {
				echo '<ul class="terms unstyled">';
				foreach($terms as $term) :
					echo '<li>' . $term->name . '</li>';
				endforeach;
				echo '</ul>';
			}
			
		}
				
		die();
			
	}
	
	
	// load meta handler (called on edit success)
	public function load_meta() {
		
		$post_id = $_REQUEST['post_id'];
		$field = $_REQUEST['field'];
		
		if (function_exists('get_field')) {
			$value = get_field($field, $post_id);	
		}
		else {
			$value = get_post_meta($post_id, $field);
		}
		
		echo $value;
		
		die();
			
	}

	// Meta AJAX handler function
	public function meta_handler() {
	
		$object_id = $_POST['pk']; // post id.
		$name = trim($_POST['name']); // the ACF field key
		$value = $_POST['value']; // uses "data-value" if present, otherwise html contents.
		$metaobject = $_POST['metaobject'];
			   
		// nonce must match name.
		if ( ! wp_verify_nonce( $_POST['nonce'], $name )) {
			header('HTTP 400 Bad Request', true, 400);
			exit('Wise guy, huh?');
		}
		
		// If value is not blank, update post meta
		// Using !is_null() allows us to post '0' or 'false' as the value.
		if ( ! is_null($value) ) {
						
			if (function_exists('update_field')) {
				if ( is_array($value) ) {
					
					$array_vals = array();
					
					foreach($value as $val) :
						$array_vals[] = $val;
					endforeach;
					
					update_field($name, $array_vals, $object_id);
				
				}
				elseif ( 'user' == $metaobject ) {
					update_field($name, array($value), $object_id);	
				}
				else {
					update_field( $name, $value, $object_id );
				}
			}
			else {
				
				if ( is_array($value) ) {
					
					foreach($value as $val) :
						update_post_meta($object_id, $name, $val);
					endforeach;	
				
				}
				else {
					// THIS WILL ONLY WORK FOR POST OBJECTS !!
					update_post_meta($object_id, $name, $value);
				}
			}
			
			print_r($_POST);	
		
		}
		else {
			header('HTTP 400 Bad Request', true, 400);
			exit("I think you broke it");
		}
		
		die();	
	}

	
} // delete this and puppies get neglected.


?>