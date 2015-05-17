<?php
if ( class_exists( 'acf_Field' ) && ! class_exists( 'acf_field_validated_field' ) ):
class acf_field_validated_field extends acf_field {
	//static final NL = "\n";
	// vars
	var $slug,
		$config,
		$settings,					// will hold info such as dir / path
		$defaults,					// will hold default field options
		$sub_defaults,				// will hold default sub field options
		$debug,						// if true, don't use minified and confirm form submit					
		$drafts,
		$frontend;

	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	function __construct(){
		// vars
		$this->slug 	= 'acf-validated-field';
		$this->strbool 	= array( 'true' => true, 'false' => false );
		$this->config 	= array(
			'acf_vf_debug' => array(
				'type' 		=> 'checkbox',
				'default' 	=> 'false',
				'label'  	=> __( 'Enable Debug', 'acf_vf' ),
				'help' 		=> __( 'Check this box to turn on debugging for Validated Fields.', 'acf_vf' ),
			),
			'acf_vf_drafts' => array(
				'type' 		=> 'checkbox',
				'default' 	=> 'true',
				'label'  	=> __( 'Enable Draft Validation', 'acf_vf' ),
				'help' 		=> __( 'Check this box to enable Draft validation globally, or uncheck to allow it to be set per field.', 'acf_vf' ),
			),
			'acf_vf_frontend' => array(
				'type' 		=> 'checkbox',
				'default' 	=> 'true',
				'label'  	=> __( 'Enable Front-End Validation', 'acf_vf' ),
				'help'		=> __( 'Check this box to turn on validation for front-end forms created with', 'acf_vf' ) . ' <code>acf_form()</code>.',
			),
			'acf_vf_frontend_css' => array(
				'type' 		=> 'checkbox',
				'default' 	=> 'true',
				'label'  	=> __( 'Enqueue Admin CSS on Front-End', 'acf_vf' ),
				'help' 		=> __( 'Uncheck this box to turn off "colors-fresh" admin theme enqueued by', 'acf_vf' ) . ' <code>acf_form_head()</code>.',
			),
		);
		$this->name		= 'validated_field';
		$this->label 	= __( 'Validated Field', 'acf_vf' );
		$this->category	= __( 'Basic', 'acf' );
		$this->drafts	= $this->option_value( 'acf_vf_drafts' );
		$this->frontend = $this->option_value( 'acf_vf_frontend' );
		$this->frontend_css = $this->option_value( 'acf_vf_frontend_css' );
		$this->debug 	= $this->option_value( 'acf_vf_debug' );

		$this->defaults = array(
			'read_only' => false,
			'mask'		=> '',
			'function'	=> 'none',
			'pattern'	=> '',
			'message'	=>  __( 'Validation failed.', 'acf_vf' ),
			'unique'	=> 'non-unique',
			'unique_statuses' => apply_filters( 'acf_vf/unique_statuses', array( 'publish', 'future' ) ),
			'drafts'	=> true,
		);

		$this->sub_defaults = array(
			'type'		=> '',
			'key'		=> '',
			'name'		=> '',
			'_name'		=> '',
			'id'		=> '',
			'value'		=> '',
			'field_group' => '',
			'readonly' => '',
			'disabled' => '',
		);

		$this->input_defaults = array(
			'id'		=> '',
			'value'		=> '',
		);

		// do not delete!
		parent::__construct();

		// settings
		$this->settings = array(
			'path'		=> apply_filters( 'acf/helpers/get_path', __FILE__ ),
			'dir'		=> apply_filters( 'acf/helpers/get_dir', __FILE__ ),
			'version'	=> ACF_VF_VERSION,
		);

		if ( is_admin() || $this->frontend ){ // admin actions

			// bug fix for acf with backslashes in the content.
			add_filter( 'content_save_pre', array( $this, 'fix_post_content' ) );
			add_filter( 'acf/get_valid_field', array( $this, 'fix_upgrade' ) );

			// override the default ajax actions to provide our own messages since they aren't filtered
			add_action( 'init', array( $this, 'override_acf_ajax_validation' ) );

			if ( ! is_admin() && $this->frontend ){
				if ( ! $this->frontend_css ){
					add_action( 'acf/input/admin_enqueue_scripts',  array( $this, 'remove_acf_form_style' ) );
				}

				add_action( 'wp_head', array( $this, 'set_post_id_to_acf_form' ) );
				add_action( 'wp_head', array( $this, 'input_admin_enqueue_scripts' ), 1 );
			}
			if ( is_admin() ){
				add_action( 'admin_init', array( $this, 'admin_register_settings' ) );
				add_action( 'admin_menu', array( $this, 'admin_add_menu' ), 11 );
				add_action( 'admin_head', array( $this, 'admin_head' ) );
				// add the post_ID to the acf[] form
				add_action( 'edit_form_after_editor', array( $this, 'edit_form_after_editor' ) );
			}

			if ( is_admin() || $this->frontend ){
				// validate validated_fields
				add_filter( "acf/validate_value/type=validated_field", array( $this, 'validate_field' ), 10, 4 );
			}
		}
	}

	function fix_upgrade( $field ){

		// the $_POST will tell us if this is an upgrade
		$is_5_upgrade = 
			isset( $_POST['action'] ) && $_POST['action'] == 'acf/admin/data_upgrade' && 
			isset( $_POST['version'] ) && $_POST['version'] == '5.0.0';

		// if it is an upgrade recursively fix the field values
		if ( $is_5_upgrade ){
			$field = $this->do_recursive_slash_fix( $field );
		}

		return $field;
	}

	function fix_post_content( $content ){
		global $post;

		// are we saving a field group?
		$is_field_group = get_post_type() == 'acf-field-group';

		// are we saving a field group?
		$is_field = get_post_type() == 'acf-field';

		// are we upgrading to ACF 5?
		$is_5_upgrade = 
			isset( $_POST['action'] ) && $_POST['action'] == 'acf/admin/data_upgrade' && 
			isset( $_POST['version'] ) && $_POST['version'] == '5.0.0';

		// if we are, we need to check the values for single, but not double, backslashes and make them double
		if ( $is_field || $is_field_group || $is_5_upgrade ){
			$content = $this->do_slash_fix( $content );
		}

		return $content;
	}

	function do_slash_fix( $string ){
		if ( preg_match( '~(?<!\\\\)\\\\(?!\\\\)~', $string ) ){
			$string = str_replace('\\', '\\\\', $string );
		}
		if ( preg_match( '~\\\\\\\\"~', $string ) ){
			$string = str_replace('\\\\"', '\\"', $string );
		}
		return $string;
	}

	function do_recursive_slash_fix( $array ){

		// loop through all levels of the array
		foreach( $array as $key => &$value ){
			if ( is_array( $value ) ){
				// div deeper
				$value = $this->do_recursive_slash_fix( $value );
			} elseif ( is_string( $value ) ){
				// fix single backslashes to double
				$value = $this->do_slash_fix( $value );
			}
		}

		return $array;
	}

	function override_acf_ajax_validation(){
		remove_all_actions( 'wp_ajax_acf/validate_save_post' );
		remove_all_actions( 'wp_ajax_nopriv_acf/validate_save_post' );

		add_action( 'wp_ajax_acf/validate_save_post',			array( $this, 'ajax_validate_save_post') );
		add_action( 'wp_ajax_nopriv_acf/validate_save_post',	array( $this, 'ajax_validate_save_post') );
	}

	function set_post_id_to_acf_form(){
		global $post;
		?>

		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('form.acf-form').append('<input type="hidden" name="acf[post_ID]" value="<?php echo $post->ID; ?>"/>');
			jQuery('form.acf-form').append('<input type="hidden" name="acf[frontend]" value="true"/>');
		});
		</script>

		<?php
	}

	function edit_form_after_editor( $post ){
		echo "<input type='hidden' name='acf[post_ID]' value='{$post->ID}'/>";
	}

	function option_value( $key ){
		return ( false !== $option = get_option( $key ) )?
			$option == $this->config[$key]['default'] :
			$this->strbool[$this->config[$key]['default']];
	}

	function admin_head(){
		$min = ( ! $this->debug )? '.min' : '';
		wp_register_script( 'acf-validated-field-admin', plugins_url( "js/admin{$min}.js", __FILE__ ), array( 'jquery', 'acf-field-group' ), ACF_VF_VERSION );
		wp_register_script( 'acf-validated-field-group', plugins_url( "js/field-group{$min}.js", __FILE__ ), array( 'jquery', 'acf-field-group' ), ACF_VF_VERSION );
		wp_enqueue_script( array(
			'jquery',
			'acf-validated-field-admin',
			'acf-validated-field-group',
		));	
	}

	function admin_add_menu(){
		$page = add_submenu_page( 'edit.php?post_type=acf-field-group', __( 'Validated Field Settings', 'acf_vf' ), __( 'Validated Field Settings', 'acf_vf' ), 'manage_options', $this->slug, array( &$this,'admin_settings_page' ) );		
	}

	function admin_register_settings(){
		foreach ( $this->config as $key => $value ) {
			register_setting( $this->slug, $key );
		}
	}

	function admin_settings_page(){
		?>
		<div class="wrap">
		<h2>Validated Field Settings</h2>
		<form method="post" action="options.php">
		    <?php settings_fields( $this->slug ); ?>
		    <?php do_settings_sections( $this->slug ); ?>
			<table class="form-table">
			<?php foreach ( $this->config as $key => $value ) { ?>
				<tr valign="top">
					<th scope="row"><?php echo $value['label']; ?></th>
					<td>
						<input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo $value['default']; ?>" <?php if ( $this->option_value( $key ) ) echo 'checked'; ?>/>
						<small><em><?php echo $value['help']; ?></em></small>
					</td>
				</tr>
			<?php } ?>
			</table>
		    <?php submit_button(); ?>
		</form>
		</div>
    	<?php
	}

	function remove_acf_form_style(){
		wp_dequeue_style( array( 'colors-fresh' ) );
	}

	function setup_field( $field ){
		// setup booleans, for compatibility
		$field = acf_prepare_field( array_merge( $this->defaults, $field ) );

		// set up the sub_field
		$sub_field = isset( $field['sub_field'] )? 
			$field['sub_field'] :	// already set up
			array();				// create it

		// mask the sub field as the parent by giving it the same key values
		foreach( $field as $key => $value ){
			if ( in_array( $key, array( 'sub_field', 'type' ) ) )
				continue;
			$sub_field[$key] = $value;
		}

		// these fields need some special formatting
		$sub_field['_input'] = $field['prefix'].'['.$sub_field['key'].']';
		$sub_field['name'] = $sub_field['_input'];
		$sub_field['id'] = str_replace( '-acfcloneindex', '', str_replace( ']', '', str_replace( '[', '-', $sub_field['_input'] ) ) );

		// make sure all the defaults are set
		$field['sub_field'] = array_merge( $this->sub_defaults, $sub_field );

		return $field;
	}

	function setup_sub_field( $field ){
		return $field['sub_field'];	
	}

	/*
	*  get_post_statuses()
	*
	*  Get the various post statuses that have been registered
	*
	*  @type		function
	*
	*/
	function get_post_statuses() {
		global $wp_post_statuses;
		return $wp_post_statuses;
	}

	/*
	*  ajax_validate_save_post()
	*
	*  Override the default acf_input()->ajax_validate_save_post() to return a custom validation message
	*
	*  @type		function
	*
	*/
	function ajax_validate_save_post() {
		
		// validate
		if ( ! isset( $_POST['_acfnonce'] ) ) {
			// ignore validation, this form $_POST was not correctly configured
			die();
		}
		
		// success
		if ( acf_validate_save_post() ) {
			$json = array(
				'result'	=> 1,
				'message'	=> __( 'Validation successful', 'acf' ),
				'errors'	=> 0
			);
			
			die( json_encode( $json ) );
		}
		
		// fail
		$json = array(
			'result'	=> 0,
			'message'	=> __( 'Validation failed', 'acf' ),
			'errors'	=> acf_get_validation_errors()
		);

		// update message
		$i = count( $json['errors'] );
		$json['message'] .= '. ' . sprintf( _n( '1 field below is invalid.', '%s fields below are invalid.', $i, 'acf_vf' ), $i ) . ' ' . __( 'Please check your values and submit again.', 'acf_vf' );
		
		die( json_encode( $json ) );
	}

	function validate_field( $valid, $value, $field, $input ) {
		if ( ! $valid )
			return $valid;

		// get ID of the submit post or cpt, allow null for options page
		$post_id = isset( $_POST['acf']['post_ID'] )? $_POST['acf']['post_ID'] : null;

		$post_type = get_post_type( $post_id );				// the type of the submitted post
		$frontend = isset( $_REQUEST['acf']['frontend'] )?
			$_REQUEST['acf']['frontend'] :
			false;

		if ( !empty( $field['parent'] ) ){
			$parent_field = acf_get_field( $field['parent'] );	
		}

		// if it's a repeater field, get the validated field so we can do meta queries...
		if ( $is_repeater = ( isset( $parent_field ) && 'repeater' == $parent_field['type'] ) ){
			$index = explode( '][', $input );
			$index = $index[1];
		}
		
		// the wrapped field
		$field = $this->setup_field( $field );
		$sub_field = $this->setup_sub_field( $field );
		
		//$value = $input['value'];							// the submitted value
		if ( $field['required'] && empty( $value ) ){
			return $valid;									// let the required field handle it
		}

		if ( !$field['drafts'] ){
			return $valid;									// we aren't publishing and we don't want to validate drafts
		}
		
		$function = $field['function'];						// what type of validation?
		$pattern = $field['pattern'];						// string to use for validation
		$message = $field['message'];						// failure message to return to the UI
		if ( ! empty( $function ) && ! empty( $pattern ) ){
			switch ( $function ){							// only run these checks if we have a pattern
				case 'regex':								// check for any matches to the regular expression
					$pattern_fltr = '/' . str_replace( "/", "\/", $pattern ) . '/';
					if ( ! preg_match( $pattern_fltr, $value ) ){
						$valid = false;						// return false if there are no matches
					}
					break;
				case 'sql':									// todo: sql checks?
					break;
				case 'php':									// this code is a little tricky, one bad eval() can break the lot. needs a nonce.
					$this_key = $field['name'];
					if ( $is_repeater ) $this_key .= '_' . $index . '_' . $sub_sub_field['name'];

					// get the fields based on the keys and then index by the meta value for easy of use
					$input_fields = array();
					foreach ( $_POST['acf'] as $key => $val ){
						if ( false !== ( $input_field = get_field_object( $key, $post_id ) ) ){
							$meta_key = $input_field['name'];
							$input_fields[$meta_key] = array(
								'field'=>$input_field,
								'value'=>$val,
								'prev_val'=>get_post_meta( $post_id, $meta_key, true )
							);
						}
					}

					$message = $field['message'];			// the default message

					// not yet saved to the database, so this is the previous value still
					$prev_value = get_post_meta( $post_id, $this_key, true );

					// unique function for this key
					$function_name = 'validate_' . $field['key'] . '_function';
					
					// it gets tricky but we are trying to account for an capture bad php code where possible
					$pattern = addcslashes( trim( $pattern ), '$' );
					if ( substr( $pattern, -1 ) != ';' ) $pattern.= ';';

					$value = addslashes( $value );
					$prev_value = addslashes( $prev_value );

					// this must be left aligned as it contains an inner HEREDOC
					$php = <<<PHP
if ( ! function_exists( '$function_name' ) ):
function $function_name( \$args, &\$message ){
	extract( \$args );
	try {
		\$code = <<<INNERPHP
		$pattern return true;
INNERPHP;
		return @eval( \$code );
	} catch ( Exception \$e ){
		\$message = "Error: ".\$e->getMessage(); return false;
	}
}
endif; // function_exists
\$valid = $function_name( array( 'post_id'=>'$post_id', 'post_type'=>'$post_type', 'this_key'=>'$this_key', 'value'=>'$value', 'prev_value'=>'$prev_value', 'inputs'=>\$input_fields ), \$message );
PHP;

					if ( true !== eval( $php ) ){			// run the eval() in the eval()
						$error = error_get_last();			// get the error from the eval() on failure
						// check to see if this is our error or not.
						if ( strpos( $error['file'], "validated_field_v5.php" ) && strpos( $error['file'], "eval()'d code" ) ){
							preg_match( '/eval\\(\\)\'d code\\((\d+)\\)/', $error['file'], $matches );
							$message = __( 'PHP Error', 'acf_vf' ) . ': ' . $error['message'] . ', line ' . $matches[1] . '.';
							$valid = false;
						} 
					}
					// if a string is returned, return it as the error.
					if ( is_string( $valid ) ){
						$message = $valid;
						$valid = false;
					}
					$message = stripslashes( $message );
					break;
			}
		} elseif ( ! empty( $function ) && $function != 'none' ) {
			$message = __( 'This field\'s validation is not properly configured.', 'acf_vf' );
			$valid = false;
		}
			
		$unique = $field['unique'];
		$field_is_unique = ! empty( $value ) && ! empty( $unique ) && $unique != 'non-unique';
		
		// validate the submitted values since there might be dupes in the form submit that aren't yet in the database
		if ( $valid && $field_is_unique ){
			$value_instances = 0;
			switch ( $unique ){
				case 'global';
				case 'post_type':
				case 'this_post':
					// no duplicates at all allowed
					foreach ( $_REQUEST['acf'] as $submitted ){
						if ( is_array( $submitted ) ){
							foreach ( $submitted as $row ){
								// there is only one, but we don't know the key
								foreach ( $row as $submitted2 ){
									if ( $submitted2 == $value ){
										$value_instances++;
									}
									break;
								}
							}
						} else {
							if ( $submitted == $value ){
								$value_instances++;
							}
						}
					}
					break;
				case 'post_key':
				case 'this_post_key':
					// only check the key for a repeater
					if ( $is_repeater ){
						foreach ( $_REQUEST['acf'] as $key => $submitted ){
							if ( $key == $parent_field['key'] ){	
								foreach ( $submitted as $row ){
									// there is only one, but we don't know the key
									foreach ( $row as $submitted2 ){
										if ( $submitted2 == $value ){
											$value_instances++;
										}
										break;
									}
								}
							}
						}
					}
					break;
			}

			// this value came up more than once, so we need to mark it as an error
			if ( $value_instances > 1 ){
				$message = __( 'The value', 'acf_vf' ) . " '$value' " . __( 'was submitted multiple times and should be unique for', 'acf_vf' ) . " {$field['label']}.";
				$valid = false;
			}
		}

		if ( $valid && $field_is_unique ){
			global $wpdb;
			$status_in = "'" . implode( "','", $field['unique_statuses'] ) . "'";

			// WPML compatibility, get code list of active languages
			if ( function_exists( 'icl_object_id' ) ){
				$languages = $wpdb->get_results( "SELECT code FROM {$wpdb->prefix}icl_languages WHERE active = 1", ARRAY_A );
				$wpml_ids = array();
				foreach( $languages as $lang ){
					$wpml_ids[] = (int) icl_object_id( $post_id, $post_type, true, $lang['code'] );
				}
				$post_ids = array_unique( $wpml_ids );
			} else {
				$post_ids = array( (int) $post_id );
			}

			$sql_prefix = "SELECT pm.meta_id AS meta_id, pm.post_id AS post_id, p.post_title AS post_title FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_status IN ($status_in)";
			switch ( $unique ){
				case 'global': 
					// check to see if this value exists anywhere in the postmeta table
					$sql = $wpdb->prepare( 
						"{$sql_prefix} AND post_id NOT IN ([IN_NOT_IN]) WHERE ( meta_value = %s OR meta_value LIKE %s )",
						$value,
						'%"' . $wpdb->esc_like( $value ) . '"%'
					);
					break;
				case 'post_type':
					// check to see if this value exists in the postmeta table with this $post_id
					$sql = $wpdb->prepare( 
						"{$sql_prefix} AND p.post_type = %s WHERE ( ( post_id IN ([IN_NOT_IN]) AND meta_key != %s ) OR post_id NOT IN ([IN_NOT_IN]) ) AND ( meta_value = %s OR meta_value LIKE %s )", 
						$post_type,
						$field['name'],
						$value,
						'%"' . $wpdb->esc_like( $value ) . '"%'
					);
					break;
				case 'this_post':
					// check to see if this value exists in the postmeta table with this $post_id
					$this_key = $is_repeater ? 
						$parent_field['name'] . '_' . $index . '_' . $field['name'] :
						$field['name'];
					$sql = $wpdb->prepare( 
						"{$sql_prefix} AND post_id IN ([IN_NOT_IN]) AND meta_key != %s AND ( meta_value = %s OR meta_value LIKE %s )",
						$this_key,
						$value,
						'%"' . $wpdb->esc_like( $value ) . '"%'
					);
					break;
				case 'post_key':
				case 'this_post_key':
					// check to see if this value exists in the postmeta table with both this $post_id and $meta_key
					if ( $is_repeater ){
						$this_key = $parent_field['name'] . '_' . $index . '_' . $field['name'];
						$meta_key = $parent_field['name'] . '_%_' . $field['name'];
						if ( 'post_key' == $unique ){
							$sql = $wpdb->prepare(
								"{$sql_prefix} AND p.post_type = %s WHERE ( ( post_id IN ([IN_NOT_IN]) AND meta_key != %s AND meta_key LIKE %s ) OR ( post_id NOT IN ([IN_NOT_IN]) AND meta_key LIKE %s ) ) AND ( meta_value = %s OR meta_value LIKE %s )", 
								$post_type,
								$this_key,
								$meta_key,
								$meta_key,
								$value,
								'%"' . $wpdb->esc_like( $value ) . '"%'
							);
						} else {
							$sql = $wpdb->prepare(
								"{$sql_prefix} WHERE post_id IN ([IN_NOT_IN]) AND meta_key != %s AND meta_key LIKE %s AND ( meta_value = %s OR meta_value LIKE %s )", 
								$this_key,
								$meta_key,
								$meta_key,
								$value,
								'%"' . $wpdb->esc_like( $value ) . '"%'
							);
						}
					} else {
						if ( 'post_key' == $unique ){
							$sql = $wpdb->prepare( 
								"{$sql_prefix} AND p.post_type = %s AND post_id NOT IN ([IN_NOT_IN]) WHERE meta_key = %s AND ( meta_value = %s OR meta_value LIKE %s )", 
								$post_type,
								$field['name'],
								$value,
								'%"' . $wpdb->esc_like( $value ) . '"%'
							);
						} else {
							$sql = $wpdb->prepare( 
								"{$sql_prefix} AND post_id IN ([IN_NOT_IN]) WHERE meta_key = %s AND ( meta_value = %s OR meta_value LIKE %s )", 
								$field['name'],
								$value,
								'%"' . $wpdb->esc_like( $value ) . '"%'
							);
						}
					}
					break;
				case 'this_post_key':
					// check to see if this value exists in the postmeta table with this $post_id
					$sql = $wpdb->prepare( 
						"{$sql_prefix} AND p.post_type = %s WHERE ( ( post_id IN ([IN_NOT_IN]) AND meta_key = %s ) ) AND ( meta_value = %s OR meta_value LIKE %s )", 
						$post_type,
						$field['name'],
						$value,
						'%"' . $wpdb->esc_like( $value ) . '"%'
					);
					break;
				default:
					// no dice, set $sql to null
					$sql = null;
					break;
			}

			// Only run if we hit a condition above
			if ( ! empty( $sql ) ){

				// Update the [IN_NOT_IN] values
				$sql = $this->prepare_in_and_not_in( $sql, $post_ids );

				// Execute the SQL
				$rows = $wpdb->get_results( $sql );
				if ( count( $rows ) ){
					// We got some matches, but there might be more than one so we need to concatenate the collisions
					$conflicts = "";
					foreach ( $rows as $row ){
						$permalink = ( $frontend )? get_permalink( $row->post_id ) : "/wp-admin/post.php?post={$row->post_id}&action=edit";
						$conflicts.= "<a href='{$permalink}' style='color:inherit;text-decoration:underline;'>{$row->post_title}</a>";
						if ( $row !== end( $rows ) ) $conflicts.= ', ';
					}
					$message = __( 'The value', 'acf_vf' ) . " '$value' " . __( 'is already in use by', 'acf_vf' ) . " {$conflicts}.";
					$valid = false;
				}
			}
		}
		
		// ACF will use any message as an error
		if ( ! $valid ) $valid = $message;

		return $valid;
	}

	private function prepare_in_and_not_in( $sql, $post_ids ){
		global $wpdb;
		$not_in_count = substr_count( $sql, '[IN_NOT_IN]' );
		if ( $not_in_count > 0 ){
			$args = array( str_replace( '[IN_NOT_IN]', implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) ), str_replace( '%', '%%', $sql ) ) );
			for ( $i=0; $i < substr_count( $sql, '[IN_NOT_IN]' ); $i++ ) { 
				$args = array_merge( $args, $post_ids );
			}
			$sql = call_user_func_array( array( $wpdb, 'prepare' ), $args );
		}
		return $sql;
	}

	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) {
		//return;
		// defaults?
		$field = $this->setup_field( $field );

		// key is needed in the field names to correctly save the data
		$key = $field['key'];
		$html_key = 'acf_fields-'.$field['ID'];

		$sub_field = $this->setup_sub_field( $field );
		$sub_field['prefix'] = "{$field['prefix']}[sub_field]";

		// remove types that don't jive well with this one
		$fields_names = apply_filters( 'acf/get_field_types', array() );
		unset( $fields_names[__( 'Layout', 'acf' )] );
		unset( $fields_names[__( 'Basic', 'acf' )][ 'validated_field' ] );

		$field_id = str_replace("-temp", "", $field['id'] );
		$field_key = $field['key'];

		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Read Only?', 'acf_vf' ),
			'instructions'	=> __( 'When a field is marked read only, it will be visible but uneditable. Read only fields are marked with ', 'acf_vf' ). '<i class="fa fa-ban" style="color:red;" title="'. __( 'Read only', 'acf_vf' ) . '"></i>.',
			'type'			=> 'radio',
			'name'			=> 'read_only',
			'layout'		=> 'horizontal', 
			'prefix'		=> $field['prefix'],
			'choices'		=> array(
				'' => __( 'No', 'acf_vf' ),
				'1'	=> __( 'Yes', 'acf_vf' ),
			)
		));

		// Validate Drafts
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Validate Drafts/Preview?', 'acf_vf' ),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'drafts',
			'prefix'		=> $field['prefix'],
			'choices' => array(
				'1'	=> __( 'Yes', 'acf_vf' ),
				'' => __( 'No', 'acf_vf' ),
			),
			'layout'		=> 'horizontal',
		));

		if ( false && ! $this->drafts ){
			echo '<em>';
			_e( 'Warning', 'acf_vf' );
			echo ': <code>ACF_VF_DRAFTS</code> ';
			_e( 'has been set to <code>false</code> which overrides field level configurations', 'acf_vf' );
			echo '.</em>';
		}

		?>
		<tr class="acf-field acf-sub_field" data-setting="validated_field" data-name="sub_field">
			<td class="acf-label">
				<label><?php _e( 'Validated Field', 'acf_vf' ); ?></label>
				<p class="description"></p>		
			</td>
			<td class="acf-input">
				<?php
				$atts = array(
					'id' => 'acfcloneindex',
					'class' => "field field_type-{$sub_field['type']}",
					'data-id'	=> $sub_field['id'],
					'data-key'	=> $sub_field['key'],
					'data-type'	=> $sub_field['type'],
				);

				$metas = array(
					'id'			=> $sub_field['id'],
					'key'			=> $sub_field['key'],
					'parent'		=> $sub_field['parent'],
					'save'			=> '',
				);

				?>
				<div <?php echo acf_esc_attr( $atts ); ?>>
					<div class="field-meta acf-hidden">
						<?php 

						// meta		
						foreach( $metas as $k => $v ) {
							acf_hidden_input(array( 'class' => "input-{$k}", 'name' => "{$sub_field['prefix']}[{$k}]", 'value' => $v ));
						}

						?>
					</div>

					<div class="sub-field-settings">			
						<table class="acf-table">
							<tbody>
							<?php 

							if ( ! isset( $sub_field['function'] ) || empty( $sub_field['function'] ) ){
								$sub_field['function'] = 'none';
							}

							// Validated Field Type
							acf_render_field_setting( $sub_field, array(
								'label'			=> __( 'Field Type', 'acf_vf' ),
								'instructions'	=> __( 'The underlying field type that you would like to validate.', 'acf_vf' ),
								'type'			=> 'select',
								'name'			=> 'type',
								'prefix'		=> $sub_field['prefix'],
								'choices' 		=> $fields_names,
								'required'		=> true
							), 'tr' );			

							// Render the Sub Field
							do_action( "acf/render_field_settings/type={$sub_field['type']}", $sub_field );

							?>
							<tr class="field_save acf-field" data-name="conditional_logic" style="display:none;">
								<td class="acf-label"></td>
								<td class="acf-input"></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</td>
		</tr>
		<?php

		if ( !empty( $field['mask'] ) && $sub_field['type'] == 'number' ){

		}

		$mask_error = ( !empty( $field['mask'] ) && $sub_field['type'] == 'number' )? 
			'color:red;' : '';

		// Input Mask
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Input mask', 'acf_vf' ),
			'instructions'	=> __( 'Use &#39;a&#39; to match A-Za-z, &#39;9&#39; to match 0-9, and &#39;*&#39; to match any alphanumeric.', 'acf_vf' ) . 
								' <a href="http://digitalbush.com/projects/masked-input-plugin/" target="_new">' . 
								__( 'More info', 'acf_vf' ) . 
								'</a>.<br/><br/><strong style="' . $mask_error . '">' . 
								__( 'Input masking is not compatible with the "number" field type!', 'acf_vf' ) .
								'</strong>',
			'type'			=> 'text',
			'name'			=> 'mask',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['mask'],
			'layout'		=> 'horizontal',
		));

		// Validation Function
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Validation Function', 'acf_vf' ),
			'instructions'	=> __( 'How should the field be server side validated?', 'acf_vf' ),
			'type'			=> 'select',
			'name'			=> 'function',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['function'],
			'choices' => array(
				'none'	=> __( 'None', 'acf_vf' ),
				'regex' => __( 'Regular Expression', 'acf_vf' ),
				//'sql'	=> __( 'SQL Query', 'acf_vf' ),
				'php'	=> __( 'PHP Statement', 'acf_vf' ),
			),
			'layout'		=> 'horizontal',
			'optgroup' => true,
			'multiple' => '0',
			'class'			=> 'validated_select validation-function',
		));

		?>
		<tr class="acf-field validation-settings" data-setting="validated_field" data-name="pattern" id="field_option_<?php echo $html_key; ?>_validation">
			<td class="acf-label">
				<label><?php _e( 'Pattern', 'acf_vf' ); ?></label>
				<p class="description">	
				<small>
				<div class="validation-info">
					<div class='validation-type regex'>
						<?php _e( 'Pattern match the input using', 'acf_vf' ); ?> <a href="http://php.net/manual/en/function.preg-match.php" target="_new">PHP preg_match()</a>.
						<br />
					</div>
					<div class='validation-type php'>
						<ul>
							<li><?php _e( 'Use any PHP code and return true for success or false for failure. If nothing is returned it will evaluate to true.', 'acf_vf' ); ?></li>
							<li><?php _e( 'Available variables', 'acf_vf' ); ?>:
							<ul>
								<li><code>$post_id = $post->ID</code></li>
								<li><code>$post_type = $post->post_type</code></li>
								<li><code>$name = meta_key</code></li>
								<li><code>$value = form value</code></li>
								<li><code>$prev_value = db value</code></li>
								<li><code>$inputs = array(<blockquote>'field'=>?,<br/>'value'=>?,<br/>'prev_value'=>?<br/></blockquote>)</code></li>
								<li><code>&amp;$message = error message</code></li>
							</ul>
							</li>
							<li><?php _e( 'Example', 'acf_vf' ); ?>: 
							<small><code><pre>if ( empty( $value ) ){
  $message = 'required!'; 
  return false;
}</pre></code></small></li>
						</ul>
					</div>
					<div class='validation-type sql'>
						<?php _e( 'SQL', 'acf_vf' ); ?>.
						<br />
					</div>
				</div> 
				</small>
				</p>		
			</td>
			<td class="acf-input">
				<?php

				// Pattern
				acf_render_field( array(
					'label'			=> __( 'Pattern', 'acf_vf' ),
					'instructions'	=> '',
					'type'			=> 'textarea',
					'name'			=> 'pattern',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['pattern'],
					'layout'		=> 'horizontal',
					'class'			=> 'editor',
				));

				?>
				<div id="<?php echo $field_id; ?>-editor" class='ace-editor' style="height:200px;"><?php echo $field['pattern']; ?></div>
			</td>
		</tr>
		<?php

		// Error Message
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Error Message', 'acf_vf' ),
			'instructions'	=> __( 'The default error message that is returned to the client.', 'acf_vf' ),
			'type'			=> 'text',
			'name'			=> 'message',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['message'],
			'layout'		=> 'horizontal',
			'class'			=> 'validation-settings'
		));

		// Validation Function
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Unique Value?', 'acf_vf' ),
			'instructions'	=> __( "Make sure this value is unique for...", 'acf_vf' ),
			'type'			=> 'select',
			'name'			=> 'unique',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['unique'],
			'choices' 		=> array(
				'non-unique'	=> __( 'Non-Unique Value', 'acf_vf' ),
				'global'		=> __( 'Unique Globally', 'acf_vf' ),
				'post_type'		=> __( 'Unique For Post Type', 'acf_vf' ),
				'post_key'		=> __( 'Unique For Post Type', 'acf_vf' ) . ' + ' . __( 'Field/Meta Key', 'acf_vf' ),
				'this_post'		=> __( 'Unique For Post', 'acf_vf' ),
				'this_post_key'	=> __( 'Unique For Post', 'acf_vf' ) . ' + ' . __( 'Field/Meta Key', 'acf_vf' ),
			),
			'layout'		=> 'horizontal',
			'optgroup' 		=> false,
			'multiple' 		=> '0',
			'class'			=> 'validated_select validation-unique',
		));

		// Unique Status
		$statuses = $this->get_post_statuses();
		$choices = array();
		foreach ( $statuses as $value => $status ) {
			$choices[$value] = $status->label;
		}
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Apply to...?', 'acf_vf' ),
			'instructions'	=> __( "Make sure this value is unique for the checked post statuses.", 'acf_vf' ),
			'type'			=> 'checkbox',
			'name'			=> 'unique_statuses',
			'prefix'		=> $field['prefix'],
			'value'			=> $field['unique_statuses'],
			'choices' 		=> $choices,
		));
	}

	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ) {

		global $post, $pagenow;

		$is_new = $pagenow=='post-new.php';

		$field = $this->setup_field( $field );
		$sub_field = $this->setup_sub_field( $field );

		?>
		<div class="validated-field">
			<?php
			if ( $field['read_only'] && $field['read_only'] != 'false' ){

				?>
				<p>
				<?php 

				// Buffer output
				ob_start();

				// Render the subfield
				echo apply_filters( 'acf/render_field/type='.$sub_field['type'], $sub_field );

				// Try to make the field readonly
				$contents = ob_get_contents();
				$contents = preg_replace("~<(input|textarea|select)~", "<\${1} disabled=true read_only", $contents );
				$contents = preg_replace("~acf-hidden~", "acf-hidden acf-vf-readonly", $contents );

				// Stop buffering
				ob_end_clean();

				// Return our (hopefully) readonly input.
				echo $contents;

				?>
				</p>
				<?php

			} else {
				// wrapper for other fields, especially relationship
				echo "<div class='acf-field acf-field-{$sub_field['type']} field_type-{$sub_field['type']}' data-type='{$sub_field['type']}' data-key='{$sub_field['key']}'><div class='acf-input'>";
				echo apply_filters( 'acf/render_field/type='.$sub_field['type'], $sub_field );
				echo "</div></div>";
			}
			?>
		</div>
		<?php
		if ( ! empty( $field['mask'] ) && ( $is_new || ( isset( $field['read_only'] ) && ( ! $field['read_only'] || $field['read_only'] == 'false' ) ) ) ) {
			// we have to use $sub_field['key'] since new repeater fields don't have a unique ID
			?>
			<script type="text/javascript">
				(function($){
					$(".field_key-<?php echo $sub_field['key']; ?> input").each( function(){
						$(this).mask("<?php echo $field['mask']?>");
					});
				})(jQuery);
			</script>
			<?php
		}
	}

	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add css + javascript to assist your create_field() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	function input_admin_enqueue_scripts(){
		// register acf scripts
		$min = ( ! $this->debug )? '.min' : '';
		wp_register_script( 'acf-validated-field-input', plugins_url( "js/input{$min}.js", __FILE__ ), array( 'acf-validated-field' ), ACF_VF_VERSION );
		wp_register_script( 'jquery-masking', plugins_url( "js/jquery.maskedinput{$min}.js", __FILE__ ), array( 'jquery' ), ACF_VF_VERSION);
		wp_register_script( 'sh-core', plugins_url( 'js/shCore.js', __FILE__ ), array( 'acf-input' ), ACF_VF_VERSION );
		wp_register_script( 'sh-autoloader', plugins_url( 'js/shAutoloader.js', __FILE__ ), array( 'sh-core' ), ACF_VF_VERSION);
		
		// enqueue scripts
		wp_enqueue_script( array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-tabs',
			'jquery-masking',
			'acf-validated-field',
			'acf-validated-field-input',
		));
	}

	/*
	*  input_admin_footer()
	*
	*  This action is called in the wp_head/admin_head action on the edit screen where your field is created.
	*  Use this action to add css and javascript to assist your create_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	function input_admin_footer(){
		wp_deregister_style('font-awesome');
		wp_enqueue_style( 'font-awesome', plugins_url( 'css/font-awesome/css/font-awesome.min.css', __FILE__ ), array(), '4.2.0' ); 
		wp_enqueue_style( 'acf-validated_field', plugins_url( 'css/input.css', __FILE__ ), array(), ACF_VF_VERSION ); 
	}

	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add css + javascript to assist your create_field_options() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	function field_group_admin_enqueue_scripts(){
		wp_enqueue_script( 'ace-editor', plugins_url( 'js/ace/ace.js', __FILE__ ), array(), '1.1.7' );
		wp_enqueue_script( 'ace-ext-language_tools', plugins_url( 'js/ace/ext-language_tools.js', __FILE__ ), array(), '1.1.7' );
	}

	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/
	function load_value( $value, $post_id, $field ){
		$sub_field = $this->setup_sub_field( $this->setup_field( $field ) );
		return apply_filters( 'acf/load_value/type='.$sub_field['type'], $value, $post_id, $sub_field );
	}

	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	function update_value( $value, $post_id, $field ){
		$sub_field = $this->setup_sub_field( $this->setup_field( $field ) );
		return apply_filters( 'acf/update_value/type='.$sub_field['type'], $value, $post_id, $sub_field );
	}

	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	function format_value( $value, $post_id, $field ){
		$sub_field = $this->setup_sub_field( $this->setup_field( $field ) );
		return apply_filters( 'acf/format_value/type='.$sub_field['type'], $value, $post_id, $sub_field );
	}

	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	function format_value_for_api( $value, $post_id, $field ){
		$sub_field = $this->setup_sub_field( $this->setup_field( $field ) );
		return apply_filters( 'acf/format_value_for_api/type='.$sub_field['type'], $value, $post_id, $sub_field );
	}

	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	function load_field( $field ){
		$field = $this->setup_field( $field );
		$sub_field = $this->setup_sub_field( $field );
		$sub_field = apply_filters( 'acf/load_field/type='.$sub_field['type'], $sub_field );

		// The relationship field gets settings from the sub_field so we need to return it since it effectively displays through this method.
		if ( 'relationship' == $sub_field['type'] && isset( $_POST['action'] ) && $_POST['action'] == 'acf/fields/relationship/query' ){
			// the name is the key, so use _name
			$sub_field['name'] = $sub_field['_name'];
			return $sub_field;
		}

		$field['sub_field'] = $sub_field;
		if ( $field['read_only'] && $field['read_only'] != 'false' && get_post_type() != 'acf-field-group' ){
			$field['label'] .= ' <i class="fa fa-ban" style="color:red;" title="'. __( 'Read only', 'acf_vf' ) . '"></i>';
		}

		// Just avoid using any type of quotes in the db values
		$field['pattern'] = str_replace( "%%squot%%", "'", $field['pattern'] );
		$field['pattern'] = str_replace( "%%dquot%%", '"', $field['pattern'] );

		return $field;
	}

	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the modified field
	*/
	function update_field( $field ){
		$sub_field = $this->setup_sub_field( $this->setup_field( $field ) );
		$sub_field = apply_filters( 'acf/update_field/type='.$sub_field['type'], $sub_field );
		$field['sub_field'] = $sub_field;

		// Just avoid using any type of quotes in the db values
		$field['pattern'] = str_replace( "'", "%%squot%%", $field['pattern'] );
		$field['pattern'] = str_replace( '"', "%%dquot%%", $field['pattern'] );

		return $field;
	}
}

new acf_field_validated_field();
endif;
