<?php

/* usage:

Method 1: Long way
	
	$edit = new X_Editable_Element('meta',  'text');
		$edit->set_meta_key( 'field_key' );	
		$edit->set_object_type( 'user' );	(optional - defaults to 'post')
		$edit->set_object_id( $post->ID );
		$edit->set_args( array( 'label' => false, 'opts' => 'bool', 'me' => 'wells' ) );
	
		OR
	
	$edit = new X_Editable_Element('tax', 'select' );
		$edit->set_taxonomy( 'my-tax' );
		...
		
	Rather than specifying 2nd parameter of X_Editable_Element class,
	can also use set_input_type();

Method 2: Short way

	$edit = new X_Editable_Element('meta', 'text');
		$edit->setup($user_id, 'custom_field_key', 'user');
	
		OR
	
	$edit = new X_Editable_Element('tax', 'checklist');
		$edit->setup($post_id, 'custom-tax', 'post');
	
*/

class X_Editable_Element{

	protected
		$meta_or_tax,
		$show_label,
		$nonce,
		$display,
		$external_container,
		$is_wysiwyg,
		$term_meta_tax;
		
	private
		$input_type,
		$object_id,
		$object_type,
		$key,
		$value,
		$args,
		$options,
		$name,
		$element_label;
		
	
	//  $meta_or_tax accepts 'meta' or 'tax', $input_type accepts many...
	function __construct($meta_or_tax = 'meta', $input_type = 'text') {
		
		$this->meta_or_tax = $meta_or_tax;
		$this->input_type = $input_type;
		
		// default vars
		$this->object_type = 'post';
		$this->args = array();
		
		if ( $this->is_input('textarea') || $this->is_input('wysihtml5') ) {
			$this->external_container = true;
		}
		if ( $this->is_input('wysihtml5') ) {
			$this->wysiwyg();
		}
		
	}
	
	public function wysiwyg(){
		
		wp_enqueue_script('wysihtml5');
		
		wp_enqueue_script('bootstrap-wysihtml5-js');
		wp_enqueue_style('bootstrap-wysihtml5-css');
		
		wp_enqueue_script('x-editable-wysihtml5');
			
	}
	
	/* ====================
	 	PUBLIC SETTERS
	 =================== */
	 
	// quick method - sets 2 required and 2 optional vars
	public function setup($object_id, $key, $label = true, $object_type = 'post') {
		$this->object_id = $object_id;
		$this->key = $key;	
		$this->show_label = $label;
		$this->object_type = $object_type;
	}
		// REQUIRED (all) - sets object_id 
		public function set_object_id($id) {
			$this->object_id = $id;
		}
		// REQUIRED (meta only) - sets meta key
		public function set_meta_key($key) {
			$this->key = $key;	
		}
		// REQUIRED (tax only) - sets taxonomy 
		public function set_taxonomy($tax) {
			$this->key = $tax;	
		}		
		// (optional) overrides default object type (post)
		public function set_object_type($type) {
			$this->object_type = $type;
		}
	
	public function set_term_meta_tax($tax) {
		$this->term_meta_tax = $tax;
	}
		
	// (optional) overrides the type of input to use (default set on construct)
	public function set_input_type($input_type) {
		$this->input_type = $input_type;	
	}	
	// sets args (optional)
	public function set_args($args) {
		$this->args = $args;	
	}
	public function set_external_container($bool){
		$this->external_container = $bool;	
	}
	public function set_show_label($bool) {
		$this->show_label = $bool;	
	}
	public function set_meta_object($object) {
		$this->meta_object = $object;
		$this->args['meta_object'] = $this->meta_object;
	}
	
	/* ====================
	 	PRIVATE HELPERS
	==================== */
	private function is_meta() {
		if ( 'meta' === $this->meta_or_tax ) {
			return true;	
		}
		else
			return false;
	}
	private function is_tax() {
		if ( 'tax' === $this->meta_or_tax ) {
			return true;	
		}
		else
			return false;
	}
	
	private function is_input($type) {
		if ( $type == $this->input_type ) {
			return true;	
		}
		else	
			return false;
	}
	
	private function show_label() {
		
		if ( false != $this->show_label ) {
			return true;
		}
		else
			return false;
			
	}
	
	
	/* ====================
	 	PRIVATE SETTERS
	==================== */
	private function set_options() {
		
		// Set defaults for all passed options
		$defaults = array(
			'label' => true,
		);	
		$this->options = array_merge($defaults, $this->args);	
	}
	
	private function set_attributes() {
		
		// meta
		if ( $this->is_meta() ) {
			
			$object = $this->object_type;
			
			// post is default
			if ( 'post' === $object ) {
				$prefix = '';	
			}
			// if this is user meta
			elseif ( 'user' === $object ) {
				$prefix = 'user_';
				$this->args['object'] = 'user';
			}
			// if its term meta
			elseif ( 'term' === $object) {
				if ( ! isset($this->term_meta_tax) ) {
					throw new Exception('taxonomy is required for term meta - use set_term_meta_tax()');
				}
				$prefix = $this->term_meta_tax . '_';
				$this->args['object'] = 'term';
				$this->args['meta_tax'] = $this->term_meta_tax;
			}
			
			$field = get_field_object($this->key, $prefix . $this->object_id);
			
			$this->value = $field['value'];
			$this->name = $field['name'];
			$this->element_label = $field['label'];
			
			$this->args['meta_name'] = $this->name;
				
		}
		// tax
		elseif ( $this->is_tax() ) {

			// Get the taxonomy
			$taxObj = get_taxonomy($this->key);
			
			if ( false == $taxObj ) {
				return;
			}
		
			$this->name = $this->key;	
			$this->value = get_the_terms($this->object_id, $this->key);
			$this->element_label = $taxObj->labels->name;

		}	
	}
	
	private function create_nonce() {
		$this->nonce = wp_create_nonce( $this->key );
	}

	/* ============================
		Alter the display output
	============================ */
	private function display() {
		
		// Display edit button if tax or textarea
		if ( true === $this->external_container ) {
			$this->display = '<small>Edit</small>';
		}
		
		// its empty
		elseif ( empty($this->value) ) {
			$this->display = 'N/A';
		}
				
		// am I a date?
		elseif ( $this->is_input('date') ) {
			$this->display = xe_format_date($this->value);
		}
		
		// if this is a boolean datatype
		elseif ( $this->args['bool'] == true ) {
			// set the display
			if ( 1 == $this->value ) {
				$this->display = 'Yes';	
			}
			if ( 0 == $this->value ) {
				$this->display = 'No';	
			}
			else {
				$this->display = 'N/A';	
			}
		}
				
		// Array of values w/o external container
		elseif ( true !== $this->external_container ) {
			
			if ( isset($this->names) ) {
				
				if ( count($this->names) > 1 ) {
					
					$this->display = '';
					foreach($this->names as $n) :	
						$this->display .= $n . ', ';
					endforeach;
				}
				else {
					$this->display = maybe_array_shift($this->names);
				}
				
			}
			
		}
		
		else {
			$this->display = $this->value;	
		}
		
		return $this->display;
			
		
	}
	
	private function set_values() {
		
		// Value is an array
		if ( is_array($this->value) ) {
	
			$this->vals = array();
			$this->names = array();
			
			// if its a taxonomy
			if ( $this->is_tax() ) {
				
				foreach ( $this->value as $term ) :
					// simply get term_id and name
					$this->vals[] = $term->term_id;
					$this->names[] = $term->name;
				endforeach;
							
			}
			// if its a meta...
			elseif ( $this->is_meta() ) {
				
				if ( isset($this->meta_object) ) {
						
					foreach($this->value as $meta_value) :
					
						// meta_object determines what data to get	
						
						if ( 'user' == $this->meta_object ) {
							$this->vals[] = $meta_value['ID'];
							$this->names[] = $meta_value['display_name'];
						}
						elseif ( 'post' == $this->meta_object ) {
							$this->vals[] = $meta_value['ID'];
							$this->names[] = $meta_value['post_title'];
						}
						elseif ( 'term' == $this->meta_object ) {
							$this->vals[] = $meta_value['term_id'];
							$this->names[] = $meta_value['name'];
						}
												
					endforeach;
				
				} // if isset(meta_object)
				
				elseif( 'typeahead' === $this->input_type ) {
					$this->vals[] = $this->names[] = $meta_value['name'];	
				}
				
				else {
					// $this->vals[] = $this->names[] = $meta_value;	
				}
				
			} // end if ( is_meta() )
			
		} // end if (is_array(this->value)
		
	}
	
	
	/* ====================
	 	OUTPUT (public)
	==================== */
	public function html() {
		
		$html = '';
		
		$this->set_attributes();
		$this->set_options();
		$this->create_nonce();
		$this->set_values();
		
		
		// elseif ( $this->is_tax() && !is_null($this->value) ){
			
		//	$vals = $this->value;	
			
		//	if ( is_string($this->value) ) {
				// $names = $vals;
		//	}
			
		// }
		
		
			
		if ( $this->show_label() ) {
			$html .= '<span class="x-editable ' . $this->meta_or_tax . '-label ' . ' for-' . $this->name . '">' . $this->element_label . '</span> ';	
		}
		
			
		/* ================
			The element
		================ */
		
		if ( ! $this->show_label() && false === $this->external_container ) {
			$is_inline = 'edit-inline';
		}
		
		$html .= '<a href="#" class="x-editable-' . $this->meta_or_tax . ' ' . $is_inline . '" ';
		$html .= 'data-type="'. $this->input_type .'" ';
		
	//	user-defined data-* options
		if ( isset($this->args) ) {
			if ( is_array($this->args) ) {
				foreach ($this->args as $data_option => $data_value) :
					$html .= 'data-' . $data_option . '="' . $data_value . '" ';
				endforeach;
			}
		}
				
		$html .= 'data-original-title="Edit: '. $this->element_label .'" ';
		$html .= 'id="xe-' . $this->key .'" ';
		$html .= 'data-name="'. $this->key .'" ';
		$html .= 'data-nonce="'. $this->nonce .'" ';
		
	//	VALUE
		if ( 'date' == $this->input_type ) {
			$html .= 'data-value=""';	
		}
		elseif ( isset($this->vals) ) {
			
			$html .= 'data-value="';
			
			foreach($this->vals as $v) :
				$html .= $v . ',';
			endforeach;
						
			$html .= '" ';
		
		}
		else {
			$html .= 'data-value="' . $this->value . '" ';
		}
		
	//	PK
		$html .= 'data-pk="'. $this->object_id .'">';
		
	//	TEXT TO DISPLAY
		$html .= $this->display();
		
		$html .= '</a>';
		
		// Display content container for fields with "Edit" buttons
		if ( $this->external_container === true ) {
			
			if ( $this->is_meta() ) {
				echo '<div id="' . $this->name . '-content">' . $this->value . '</div>';
			}
			
		}
		
		// show the editable element
		echo $html;
		
		// Show tax terms
		if ( $this->external_container === true ) {
			
			if ( $this->is_tax() ) {
				echo '<div id="' . $this->name . '-terms-content">';	
		
				// show values if necessary
				if ( is_array($this->value) ) {
					echo '<ul class="terms unstyled">';
					foreach($this->value as $term) :
						echo '<li>' . $term->name . '</li>';	
					endforeach;
					echo '</ul>';
				}
				
				echo '</div>';
		
			}
		}
		
	}
	
} // delete this and puppies go hungry.


// Date format helper function
function xe_format_date($date, $format = 'M. j, Y', $given_format = 'Y-m-d') {
	
	$dateTime = DateTime::createFromFormat($given_format, $date);
	
	if ( is_object($dateTime) ) {
		
		$formatted_date = $dateTime->format($format);
		
		return $formatted_date;
	
	}
	else
		return $date;
	
	
}


?>