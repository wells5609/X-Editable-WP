<?php

// Renders an (X-)editable HTML element
// $args accepts keys: 'label' (bool), 'data_options' (array)
function xeditable_meta_html($meta_key, $object_id, $input_type = 'text', $args = array() ) {
	
	// Set defaults for all passed options
	$defaults = array(
        'label' => true,
    );
    $options = array_merge($defaults, $args);
	
	// valid input_types:
	if ( ! in_array($input_type, array('text', 'textarea', 'select', 'checklist', 'date', 'email', 'tel', 'url', 'number') ) ) {
		return;	
	}
	
	$nonce = wp_create_nonce($meta_key);
	
	$field = get_field_object($meta_key, $object_id);
	
	$value = $field['value'];
	$name = $field['name'];
	$field_label = $field['label'];
		
	// CUSTOM DISPLAY VARS
		
		// if this is a boolean datatype
		if ( $options['data_options']['opts'] == 'bool' ) {
			// set the display
			if ( 1 === $value ) {
				$display = 'Yes';	
			}
			if ( 0 === $value ) {
				$display = 'No';	
			}
			else {
				$display = 'N/A';	
			}
		}
		// is it a textarea?
		if ( 'textarea' == $input_type ) {
			$display = '<small>Edit</small>';
		}
		// am I a date?
		if ( 'date' == $input_type ) {
			$display = xe_format_date($value);	
		}
	
	// te gusta label?
	if ( true === $options['label'] ) {
		echo '<span class="x-editable meta-label">' . $field_label . '</span> ';	
	}
	
	// the HTML output
	
	$html = '<a href="#" class="x-editable-meta" ';
	$html .= 'data-type="'. $input_type .'" ';
	
	// $html .= 'data-field_name="' . $name . '" ';
	
	// don't input the value for dates (preserves formatting)
	if ( 'date' == $input_type ) {
		$html .= 'data-value=""';
	}
	else {
		$html .= 'data-value="' . $value . '" ';
	}
	
	// user-defined data-* options
	if ( $options['data_options'] ) {
		
		// user passed an array of options
		if ( is_array($options['data_options']) ) {
			
			foreach ($options['data_options'] as $data_option => $data_value) :
				
			/*
				if ( 'source' == $data_option && is_array($data_value) ) {
					$data_value = json_encode($data_value);
				}
			*/
			
				$html .= 'data-' . $data_option . '="' . $data_value . '" ';
			
			endforeach;
		
		}
	
	}
	
	// loose html5 url input type validation
	if ( 'url' == $input_type ) {
		$html .= 'pattern="https?://.+" ';
	}
	
	$html .= 'data-original-title="Edit: '. $field_label .'" ';
	$html .= 'id="' . $meta_key .'" ';
	$html .= 'data-name="'. $meta_key .'" ';
	$html .= 'data-nonce="'. $nonce .'" ';
	$html .= 'data-pk="'. $object_id .'">';
	
	if ( isset($display) ) {
		$html .= $display;
	}
	elseif ( empty($value) ) {
		$html .= 'N/A';
	}
	else {
		$html .= $value;	
	}
	
	$html .= '</a>';
	
	// Display content container for fields with "Edit" buttons
	if ( $input_type == 'textarea' ) {
		echo '<div id="' . $name . '-content">' . $value . '</div>';
	}
	
	
	echo $html;
	
		
}

// Renders an editable HTML element
function xeditable_tax_html($taxonomy, $object_id, $input_type = 'checklist', $args = array() ) {
	
	// Set defaults for options
	$defaults = array(
        'label' => true,
    );
    $options = array_merge($defaults, $args);
	
	// Get the taxonomy
	$taxObj = get_taxonomy($taxonomy);
	if ( false == $taxObj ) {
		return;
	}
	// the tax name (used for label and popover title)
	$taxName = $taxObj->labels->name;
	
	// nonce creation
	$nonce = wp_create_nonce($taxonomy);
	
	// Terms
	$terms = get_the_terms($object_id, $taxonomy);
	if ( is_wp_error($terms) ) {
		return;	
	}
	
	if ( is_array($terms) ) {
		
		$vals = array();
		$names = array();

		foreach ( $terms as $term ):
		
			$vals[] = $term->term_id;
			$names[] = $term->name;
		
		endforeach;
	
	}
	elseif ( !is_null($terms) ){
		
		$vals = $terms;	
		
		if ( is_string($terms) ) {
			$names = $vals;
		}
		
	}
	
	// Display edit button if more than one term
	if ( is_array($names) ) {
		$display = '<small>Edit</small>';
	}
	
	// Label?
	if ( true === $options['label'] ) {
		echo '<span class="x-editable tax-label">' . $taxName . '</span> ';	
	}

	// The HTML element
	$element = '<a href="#" class="x-editable-tax" ';
	$element .= 'data-type="'. $input_type .'" ';
	
	// user-defined data-* options
	if ( $options['data_options'] ) {
		
		// user passed an array of options
		if ( is_array($options['data_options']) ) {
			
			foreach ($options['data_options'] as $data_option => $data_value) :
			
				$element .= 'data-' . $data_option . '="' . $data_value . '" ';
			
			endforeach;
		
		}
	
	}
	
	$element .= 'data-original-title="Edit: '. $taxName .'" ';
	$element .= 'id="' . $taxonomy .'" ';
	$element .= 'data-name="'. $taxonomy .'" ';
	$element .= 'data-nonce="'. $nonce .'" ';
	
	if ( is_array($vals) ) {
		$element .= 'data-value="';
		foreach($vals as $v) {
			$element .= $v . ',';
		}
		$element .= '" ';
	}
	else {
		$element .= 'data-value="' . $vals . '" ';
	}
	
	$element .= 'data-pk="'. $object_id .'">';
	
	// multiple terms - show edit button
	if ( isset($display) ) {
		$element .= $display;
	}
	else {
		$element .= $names;
	}
	
	$element .= '</a>';
	
	echo $element;
	
	if ( is_array($names) ) {
		echo '<div id="' . $taxonomy . '-terms-content">';
		echo '<ul class="terms unstyled">';
		foreach($names as $n) {
			echo '<li>' . $n . '</li>';	
		}
		echo '</ul></div>';
	}
}


?>