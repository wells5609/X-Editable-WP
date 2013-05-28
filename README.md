# X-Editable-WP

X-Editable-WP is a (developer-focused) plugin that allows front-end editing of meta and taxonomy terms in WordPress. 


## Installation

* Install and activate plugin
* Add theme support in functions.php (see main plugin loader file)

## Documentation

Take a look at the PHP files for use instructions.

## Features

* Edit meta (custom fields) or taxonomy terms for posts, users, and terms.
* 3 flavors: Bootstrap, jQuery UI, and jQuery-only (with Poshytip)
* Built-in support for Advanced Custom Fields (actually works better with ACF)
* Working on adding filters and such

## Contributing

Everyone is welcome to help contribute and improve this project (please).

## Support

None provided

### Filters (known)
* 'xe_data_value_{{meta_or_tax}}'
'''
$html .= 'data-value="' . apply_filters( 'xe_data_value_'.$this->meta_or_tax, $this->html_value, $this->input_type, $this->generic_name ) . '" ';
'''
