<?php

// If you're not sure, just don't touch it. Otherwise, back it up (not like that).


class XE_Element {
		
	/* ==================
		  VARIABLES
	 ================== */
	
	protected
		$input_type,
		$meta_or_tax,
		
	// regardless if meta or tax
		
		$is_single_value,
		
		$data_args,
		
		// display vars
		$show_external,
		$show_label,
		
		$element_label,
		
		// what object are we editing (post, term, user, etc.)
		$queried_object,
		$queried_object_id,
		$queried_object_class,
				
	// META vars
		
		$meta_key,
		$meta_value,
		$meta_name,
		// are the meta VALUES an object? if so, what type
		$meta_value_object,
		
		
	// TAX vars
		
		$taxonomy,
		$taxonomy_terms,
		
	// HTML display vars
		$html_class,
		$html_label,
		$html_external,
		$html_value,
		$html_display;
		
		private 
			$generic_name,
			$nonce,
			
			$values_as_ul,
			$ul_css_class;
	
	
	/* ==================
		Constructor
	 ================== */
		
		function __construct() {
			
			$this->queried_object = get_queried_object();
			
			$this->queried_object_class = get_class($this->queried_object);
		}
		
		
	/* ==================
		Public Methods
	 ================== */
		 
		/**	meta_args($meta_key, $object_id, $is_single_value = false, $meta_value_object = NULL)
		 *	
		 *	Sets required parameters for editable meta (REQUIRED)
		 *
		 *	@param meta_key				(string)		the meta key (use the actual KEY if using ACF)
		 *	@param object_id			(int)			the queried object id
		 *	@param is_single_value		(bool)			whether we should only disply 1 value
		 *	@param meta_value_object	(string)		(optional) the object type of the values (e.g. user, term, post)
		 *	
		**/
		
		public function meta_args($meta_key, $object_id, $is_single_value = false, $meta_value_object = NULL) {
			
			$this->meta_key = $meta_key;
			$this->queried_object_id = $object_id;
			$this->is_single_value = $is_single_value;
			$this->meta_value_object = $meta_value_object;
					
		}
		
		
		/**	tax_args($taxonomy, $object_id, $is_single_value = false) 
		 *		
		 *	Sets required parameters for editable taxonomy (REQUIRED)
		 *
		 *	@param taxonomy				(string)		a taxonomy slug
		 *	@param object_id			(int)			the queried object id (always a post for taxonomies, right?)
		 *	@param is_single_value		(bool)			whether we should only disply 1 term	
		 *	
		**/
		
		public function tax_args($taxonomy, $object_id, $is_single_value = false) {
			
			$this->taxonomy = $taxonomy;
			$this->queried_object_id = $object_id;
			$this->is_single_value = $is_single_value;
				
		}
		
		/**	data_args($args)
		 *	
		 *	Sets data-* attributes like: data-{{key}}="{{value}}"
		 *
		 *	@param args		(array)			key|value pairs 
		 *	
		**/
		
		public function data_args($args) {
			$this->data_args = $args;
		}
		
		
		/**	set_show_label($bool)
		 *
		 *	whether to show a label
		 *	
		 *	@param bool		(bool)
		 *
		**/
		
		public function set_show_label($bool) {
			$this->show_label = $bool;	
		}
		
		
		/**	set_show_external($bool)
		 *
		 *	whether to show the values (i.e. meta values or terms) in an external container
		 *	
		 *	@param bool		(bool)
		 *
		**/
		
		public function set_show_external($bool) {
			$this->show_external = $bool;	
		}
			
		
		/**	set_is_single_value($bool)
		 *
		 *	whether value is single (meta/term) - can be set from input class
		 *	
		 *	@param bool		(bool)
		 *
		**/
		
		public function set_is_single_value($bool) {
			$this->is_single_value = $bool;	
		}
		
			
		/**	set_is_single_value($bool)
		 *
		 *	whether value is single (meta/term) - can be set from input class
		 *	
		 *	@param bool		(bool)
		 *
		**/
		
		public function set_values_as_ul($bool, $class = '') {
			$this->values_as_ul = $bool;
			$this->ul_css_class = $class;
		}
		
		
	/* ==================
	   Private Methods
	 ================== */	
	
		
		// Called whether meta or tax - defines values and output
		private function define_settings() {
			
			
			if ( 'meta' === $this->meta_or_tax ) {
				
				$this->define_meta_values();
				$this->define_meta_output();
			}
			
			elseif ( 'tax' === $this->meta_or_tax ) {
				
				$this->define_tax_values();
				$this->define_tax_output();
			}
			
			
			$this->define_name();
			
			
			if ( true === $this->show_label ) {
				$this->define_label();
			}
			
			if ( true === $this->show_external ) {
				$this->define_external();		
			}
			
			
			$this->define_class();
			
			$this->create_nonce();
			
		}
		
		// Defines generic_name (used in data-name) - either meta_name or taxonomy object label
		private function define_name() {
			
			// if meta 
			if ( 'meta' === $this->meta_or_tax ) {
				
				// set generic_name to meta name
				$this->generic_name = $this->meta_name;
			
			}
			
			// if tax
			elseif ( 'tax' === $this->meta_or_tax ) {
				
				// get the taxonomy object
				$this->taxonomy_object = get_taxonomy($this->taxonomy);
				
				if ( false == $this->taxonomy_object )
					return;
				
				// set generic_name to taxonomy
				$this->generic_name = $this->taxonomy;
				
				// set the label here (NOTE: for meta, this is defined in define_meta_values() )	
				$this->element_label = $this->taxonomy_object->labels->name;
	
			}
				
		}
	
		
	/* ====================
	   Protected Methods
	 ==================== */	
	
		protected function define_label() {
						
			$this->html_label = '<span class="x-editable ' . $this->meta_or_tax . '-label ' . ' for-' . $this->generic_name . '">' . $this->element_label . '</span> ';
						
		}
		
		protected function define_external() {
			
			if ( 'meta' === $this->meta_or_tax ) {
				$this->html_external = '<div id="' . $this->meta_name . '-content">' . $this->html_display . '</div>';
			}
			
			elseif ( 'tax' === $this->meta_or_tax ) {
				$this->html_external =  '<div id="' . $this->taxonomy . '-terms-content">' . $this->html_display . '</div>';
			}
						
		}
		
		protected function define_class() {
			
			$class = 'x-editable-';
			$class .= $this->meta_or_tax;
			
			$id = 'xe-';
			$id .= $this->generic_name;
			
					
			if ( true === $this->show_external ) {
				$class .= ' values-external';
			}
			
			if ( true === $this->show_label ) {
				$class .= ' labeled';
			}
			
			
			$this->html_class = $class;
			$this->html_id = $id;
				
		}
	
	// META
		
		protected function define_meta_values() {
			
			/**
			 *	Set prefixes for get_field_object()
			**/	
			
			// if we're editing a post
			if ( 'WP_Post' == $this->queried_object_class ) {
				
				$prefix = '';
							
			}
			
			// if we're editing a user
			elseif ( 'WP_User' == $this->queried_object_class ) {
				
				$prefix = 'user_';
			
			}
			
			// if we're editing a term
			elseif ( 'WP_Term' == $this->queried_object_class ) {
				
				// TO-DO: this. is definitely wrong!
				$tax = $this->queried_object->taxonomy;
				
				$prefix = $tax . '_';
			
			}
			
					
			/**
			 *	Get field object and assign vars
			**/
	
				$field = get_field_object($this->meta_key, $prefix . $this->queried_object_id);
				
				$this->meta_value = $field['value'];
				$this->meta_name = $field['name'];
				$this->element_label = $field['label'];
				
			/**
			 *	Set html_value depending on meta value and other options
			 *
			**/	
			
			// string
			if ( is_string($this->meta_value) ) {
				
				$formatted_value = $this->meta_value;	
			
			}
			
			// Object
			elseif ( is_object($this->meta_value) ) {
				
				$formatted_value = $this->format_object_values($this->meta_value);
							
			} // if is_object
			
			
			// Array
			elseif ( is_array($this->meta_value) ) {
			
				// only single value is desired	
				if ( true === $this->is_single_value ) {
					
					$this->single_meta_value = array_shift($this->meta_value);
					
					if ( is_object($this->single_meta_value) ) {
						$formatted_value = $this->format_object_value($this->single_meta_value);	
					}
					else {
						$formatted_value = $this->single_meta_value;	
					}
													
				}
				// otherwise loop em
				else {
					
					$array_values = array();
					
					foreach($this->meta_value as $value) :
						
						// have array of objects
						if ( is_object($value) ) {
							
							$array_values[] = $this->format_object_value($value);	
						}
						else {
							$array_values[] = $value;
						}
											
					endforeach;
					
					$formatted_value = implode(',', $array_values);
						
				}
			
			} // if is_array
			
				
			$this->html_value = apply_filters( 'xe_meta_values', $formatted_value );
			
		}
		
		
		protected function define_meta_output() {
			
			// string
			if ( is_string($this->meta_value) ) {
				
				$formatted_output = $this->meta_value;	
			
			}
			
			// Object
			elseif ( is_object($this->meta_value) ) {
				
				$formatted_output = $this->format_object_output($this->meta_value);
							
			}
			
			// Array
			elseif ( is_array($this->meta_value) || isset($this->single_meta_value) ) {
				
				// only single value is desired	
				if ( true === $this->is_single_value ) {
					
					if ( isset($this->single_meta_value) ) {
						$maybe_formatted_output = $this->single_meta_value;
					}
					else {
						$maybe_formatted_output = array_shift($this->meta_value);	
					}
					
					if ( is_object($maybe_formatted_output) ) {
						$this->format_object_output($maybe_formatted_output);	
					}
					else {
						$formatted_output = $maybe_formatted_output;	
					}
					
											
				}
				// otherwise loop em
				else {
					
					$array_values = array();
					
					foreach($this->meta_value as $value) :
						
						// have array of objects
						if ( is_object($value) ) {
							
							$array_values[] = $this->format_object_output($value);	
						}
						else {
							$array_values[] = $value;
						}
											
					endforeach;
					
					// TO-DO: option for UL or inline csv
					$formatted_output = implode(', ', $array_values);
						
				}
		
			} // if is_array
			
			
			$this->html_display = $formatted_output;
					
		}
		
	// TAX
	
		protected function define_tax_values() {
			// to-do
			
			/**
			 *	Get taxonomy terms
			**/
	
				$terms = get_the_terms($this->queried_object_id, $this->taxonomy);
				
				$this->taxonomy_terms = $terms;
				
				
			/**
			 *	Set html_value depending on meta value and other options
			 *
			**/	
			
			// string
			if ( is_string($this->taxonomy_terms) ) {
							
				$formatted_value = $this->taxonomy_terms;	
			
			}
			
			// Object
			elseif ( is_object($this->taxonomy_terms) ) {
				
				$formatted_value = $this->format_object_values($terms);
							
			} // if is_object
			
			
			// Array
			elseif ( is_array($this->taxonomy_terms) ) {
			
				// only single value is desired	
				if ( true === $this->is_single_value ) {
					
					$this->single_tax_term = array_shift($this->taxonomy_terms);
					
					if ( is_object($this->single_tax_term) ) {
						$formatted_value = $this->format_object_value($this->single_tax_term);	
					}
					else {
						$formatted_value = $this->single_tax_term;	
					}
													
				}
				// otherwise loop em
				else {
					
					$array_values = array();
					
					foreach($this->taxonomy_terms as $term) :
						
						// have array of objects
						if ( is_object($term) ) {
							
							$array_values[] = $this->format_object_value($term);	
						}
						else {
							$array_values[] = $term;
						}
											
					endforeach;
					
					$formatted_value = implode(',', $array_values);
						
				}
			
			} // if is_array
			
			$this->html_value = apply_filters( 'xe_tax_values', $formatted_value );
			
			
		}
		
		protected function define_tax_output() {
			// to-do
			
			// string
			if ( is_string($this->taxonomy_terms) ) {
				
				$formatted_output = $this->taxonomy_terms;	
			
			}
			
			// Object
			elseif ( is_object($this->taxonomy_terms) ) {
				
				$formatted_output = $this->format_object_output($this->taxonomy_terms);
							
			}
			
			// Array
			elseif ( is_array($this->taxonomy_terms) || isset($this->single_tax_term) ) {
				
				// only single value is desired	
				if ( true === $this->is_single_value ) {
					
					if ( isset($this->single_tax_term) ) {
						$maybe_formatted_output = $this->single_tax_term;
					}
					else {
						$maybe_formatted_output = array_shift($this->taxonomy_terms);	
					}
					
					if ( is_object($maybe_formatted_output) ) {
						$this->format_object_output($maybe_formatted_output);	
					}
					else {
						$formatted_output = $maybe_formatted_output;	
					}
					
											
				}
				// otherwise loop em
				else {
					
					$array_values = array();
					
					foreach($this->taxonomy_terms as $term) :
						
						// have array of objects
						if ( is_object($term) ) {
							
							$array_values[] = $this->format_object_output($term);	
						}
						else {
							$array_values[] = $term;
						}
											
					endforeach;
					
					// TO-DO: option for UL or inline csv
					
					if ( true === $this->values_as_ul ) {
						
						$formatted_output = '<ul class="x-editable-ul ' . $this->ul_css_class . ' ' . $this->meta_or_tax . '">';
						
						foreach($array_values as $array_val) :
						
							$formatted_output .= '<li>' . $array_val . '</li>';
						
						endforeach;
						
						$formatted_output .= '</ul>';
						
					}
					else {
									
						$formatted_output = implode_nice($array_values, ', ', ' and ');
					}
					
					
				}
		
			} // if is_array
			
			
			$this->html_display = $formatted_output;
		}
	
	
	// Private callback functions for formatting
		
		// Format values when value(s) = object(s)
		private function format_object_value($object) {
			
			if ( 'term' === $this->meta_value_object || 'tax' === $this->meta_or_tax ) {
				// format values where meta values are TERM Object(s)  
				$return_value = $object->term_id;
			}
					
			elseif ( 'user' === $this->meta_value_object ) {
				// format values where meta values are USER Object(s)  
				$return_value = $object->ID;
			}
					
			else {
				// default formatting (Post) of Object meta values
				$return_value = $object->ID;
			}
			
			return $return_value;
		}
		
		// Format output when value(s) = object(s)
		private function format_object_output($object) {
			
			if ( 'term' === $this->meta_value_object || 'tax' === $this->meta_or_tax ) {
				// format output where meta values are TERM Object(s)  
				$return_value = $object->name;
			}
					
			elseif ( 'user' === $this->meta_value_object ) {
				// format output where meta values are USER Object(s)  
				$return_value = $object->display_name;
			}
					
			else {
				// default formatting (Post) of Object meta values
				$return_value = $object->post_title;
			}
			
			return $return_value;
		}
		
		
		private function create_nonce() {
			$this->nonce = wp_create_nonce( $this->generic_name );
		}
		
	
	// public html output
	
	public function html() {
		
		// define all the values and such
		$this->define_settings();
		
		$html = '';
		
		// LABEL
		if ( $this->show_label ) {
			$html .= $this->html_label;	
		}
		
		// Class and href="#" (for older safari and some other browser)
		$html .= '<a href="#" class="' . $this->html_class . '" ';
		
		// INPUT TYPE
		$html .= 'data-type="'. $this->input_type .'" ';
		
		//	user-defined data-* options
		if ( isset($this->data_args) ) {
			if ( is_array($this->data_args) ) {
				foreach ($this->data_args as $data_option => $data_value) :
					$html .= 'data-' . $data_option . '="' . $data_value . '" ';
				endforeach;
			}
		}
		
		// Object type
		if ( isset($this->queried_object_class) ) {
			$html .= 'data-object_type="' . $this->queried_object_class . '" ';
		}
		// Meta Object type
		if ( ! is_null($this->meta_value_object) ) {	
			$html .= 'data-meta_object="' . $this->meta_value_object . '" ';
		}
			
		// Pop-up title	
		$html .= 'data-original-title="Edit: '. $this->element_label .'" ';
		
		// element ID - format: xe-{{meta_name or taxonomy}}
		$html .= 'id="' . $this->html_id . '" ';
		
		// NAME - IMPORTANT - submitted to server
		$html .= 'data-name="'. $this->generic_name .'" ';
		
		// NONCE
		$html .= 'data-nonce="'. $this->nonce .'" ';
		
		//	VALUE
		if ( 'date' === $this->input_type ) {
			// don't print date values to preserve formatting  (done in js)
			$html .= 'data-value=""';	
		}
		else
			$html .= 'data-value="' . $this->html_value . '" ';
		
		//	PK (primary key)
		$html .= 'data-pk="'. $this->queried_object_id .'">';
		
		//	TEXT TO DISPLAY
		if ( true === $this->show_external ) {
			
			$edit_text = 'Edit';
			
			$html .= apply_filters( 'xe_edit_text', $edit_text );
			
		}
		else {
			
			$html .= $this->html_display;
			
		}
		
		$html .= '</a>';
		
		// Display content container for fields with "Edit" buttons
		if ( true === $this->show_external ) {
			
			$html .= $this->html_external;
			
		}
		
		// show the editable element
		echo $html;		
		
	}
	
	
}

?>