<?php
/**
 * Adding Flowup Labels: https://github.com/ENFOS/FlowupLabels.js
 */

function sz_add_flowup_labels( $content, $field, $value, $lead_id, $form_id ) {

	$find = array( "gfield_label", " id='input_" );

	$replace = array( "gfield_label fl_label", "class='fl_input' id='input_" );

	$new_content = str_replace( $find, $replace, $content );

	return $new_content;
}
/**
 * TODO: Explore this for Lachlan in the future
 */
//add_filter( 'gform_field_content', 'sz_add_flowup_labels', 9, 5 );