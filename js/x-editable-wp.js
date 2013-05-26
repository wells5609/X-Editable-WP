jQuery(document).ready(function() { 
	
	/*
	jQuery('#focus-list-submit').submit( function() {
		
		var values = jQuery(#form_id_to_target).serialize();
		
		// then send values (i.e. data: values) with dataType: 'json'
			
	});
	
	jQuery.find('input.focus-list-checkbox').each( function() {
			
		ids.push(jQuery(this).attr('data-post_id'));
			
	});
		
	var result = ids.join(',');
		
	$(this).attr('value', result);
	
	*/	
	
	jQuery('.x-editable-meta').each( function() {
	
		var action = "xeditable_meta_handler";
	
		var data_type = jQuery(this).data('type');
		var nonce = jQuery(this).data('nonce');
		var userrole = jQuery(this).data('role');
		var metaobject = jQuery(this).data('meta_object');
		var object = jQuery(this).data('object');
		var meta_tax = jQuery(this).data('meta_tax');
		
		
		// datepicker
		if ( data_type == 'date' ) {
			
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce
				},
				// datepicker options
				format: 'yyyy-mm-dd',
				viewformat: 'M. d, yyyy',
				datepicker: {
					weekStart: 0
				}
			});		
		
		}
		
		// userrole defined - means meta values are users
		if ( userrole ) {
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce,
					metaobject: metaobject
				},
				// define checklist options
				source: xeditable.ajaxurl+'?action=xeditable_user_options&role='+userrole,
			});
		}

		
		if ( data_type == 'checklist' ) {		
		}
				
		// SELECT elements
		if ( data_type == 'select' ) {
		
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce
				},
				inputclass: 'input-small',
				// define select options
				source: [
					{value: 0, text: 'No'},
					{value: 1, text: 'Yes'}
				]
				
			});	
					
			// otherwise not sure what options...
			
		}
		
		// TEXTAREA elements
		if ( data_type == 'textarea' ) {
			
			//success vars
			var post_id = jQuery(this).data('pk')
			var fieldName = jQuery(this).data('name')
			var into = jQuery('html').find( '#'+fieldName+'-content' )
			
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce
				},
				rows: 6,
				autoText: 'never',
				mode: 'inline',
				display: false,
				success: function() { 
					load_xe_field(post_id, fieldName, into)
				}	
			});
			
			if ( object ) {
				jQuery(this).editable({
					url: xeditable.ajaxurl,
					params: {
						action: action,
						nonce: nonce,
						object: object,
						meta_tax: meta_tax	
					}
				});	
			}
			
		}
		
		// everything else (e.g. text)
		else {
		
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce
				}	
			});	
		
		}
			
	});
	
	
	jQuery('.x-editable-tax').each( function() {
		
		var action = "xeditable_tax_handler";
		
		var data_type = jQuery(this).data('type');
		var nonce = jQuery(this).data('nonce');		
		var name = jQuery(this).data('name');
		var object_id = jQuery(this).data('pk');
		
		if ( 'typeahead' == data_type ) {

			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce
				},
				autotext: 'never',
				display: false,
				// define select options
				source: xeditable.ajaxurl+'?action=xeditable_tax_options&string=true&tax='+name,
				success: function() { 
					if ( jQuery(this).hasClass('edit-inline') ) {
						var into = jQuery(this);
						load_xe_terms_inline(object_id, name, into);
					}
					else {
						var into = jQuery('html').find( '#'+name+'-terms-content' );
						load_xe_terms(object_id, name, into);
					}
				}
			});	
			
		}
		else {
							
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce
				},
				display: false,
				// define select options
				source: xeditable.ajaxurl+'?action=xeditable_tax_options&tax='+name,
				success: function() { 
					if ( jQuery(this).hasClass('edit-inline') ) {
						var into = jQuery(this);
						load_xe_terms_inline(object_id, name, into);
					}
					else {
						var into = jQuery('html').find( '#'+name+'-terms-content' );
						load_xe_terms(object_id, name, into);
					}
				}
			});	
		
		}
		
	});
	
	// AJAX Post Meta loader function
	function load_xe_field(post_id, field, into) {
		
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_meta_load',
				field: field,
				post_id: post_id
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).html('<div id="'+field+'-content">' + data + '</div>');	
			}
		});	
	}
	// AJAX Post Terms loader function
	function load_xe_terms(object_id, tax, into) {
		
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_term_load',
				tax: tax,
				object_id: object_id
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).html('<div id="'+tax+'-terms-content">' + data + '</div>');	
			}
		});	
	}
	
	function load_xe_terms_inline(object_id, tax, into) {
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_term_load',
				tax: tax,
				object_id: object_id,
				inline: 'true'
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).html(data);	
			}
		});	
	}

});