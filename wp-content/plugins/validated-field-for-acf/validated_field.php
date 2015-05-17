<?php
/*
Plugin Name: Advanced Custom Fields: Validated Field
Plugin URI: http://www.doublesharp.com/
Description: Server side validation, input masking and more for Advanced Custom Fields
Author: Justin Silver
Version: 1.7.4
Author URI: http://doublesharp.com/
*/

if ( ! defined( 'ACF_VF_VERSION' ) )
	define( 'ACF_VF_VERSION', '1.7.4' );

if ( !defined('ACF_VF_PLUGIN_FILE') )
	define( 'ACF_VF_PLUGIN_FILE', __FILE__ );

// Load the add-on field once the plugins have loaded, but before init (this is when ACF registers the fields)
if ( ! function_exists( 'register_acf_validated_field' ) ):	
	function register_acf_validated_field(){
		// create field
		include_once 'validated_field_v4.php';
	}
	function include_acf_validated_field(){
		// create field
		include_once 'validated_field_v5.php';
	}

	add_action( 'acf/include_fields', 'include_acf_validated_field' );
	add_action( 'acf/register_fields', 'register_acf_validated_field' );

	function load_textdomain_acf_vf() {
		load_plugin_textdomain( 'acf_vf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'load_textdomain_acf_vf' );
endif;