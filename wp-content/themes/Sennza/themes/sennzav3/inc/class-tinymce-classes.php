<?php

function sennza_mce_buttons( $buttons ) {
	array_unshift( $buttons, 'styleselect' );
	return $buttons;
}
add_filter( 'mce_buttons_2', 'sennza_mce_buttons' );

function sennza_mce_before_init( $init_array ) {
	// Add back some more of styles we want to see in TinyMCE
	$init_array['preview_styles'] = "font-family font-size font-weight font-style text-decoration text-transform color background-color padding";

	if ( version_compare( $GLOBALS['wp_version'], '3.8', '<' ) ) {
		$init_array['theme_advanced_styles'] = "Pull Quote Left=pull-quote-left;Pull Quote Right=pull-quote-right;Statistics Left=statistics-left;Statistics Right=statistics-right;Highlight=highlight;Purple=purple;Two Columns=small-6 columns;Three Columns=small-12 medium-4 columns;Four Columns=small-6 medium-3 columns";
	} else {
		$style_formats = array(
			// Each array child is a format with it's own settings
			array(
				'title'   => 'Code',
				'block'   => 'pre',
				'classes' => 'brush:php',
			),
			array(
				'title'   => 'Pull Quote Left',
				'block'   => 'blockquote',
				'classes' => 'pull-quote-left',
			),
			array(
				'title'   => 'Pull Quote Right',
				'block'   => 'blockquote',
				'classes' => 'pull-quote-right',
			),
			array(
				'title'   => 'Statistics List Left',
				'block'   => 'ul',
				'classes' => 'statistics-left',
			),
			array(
				'title'   => 'Statistics List Right',
				'inline'   => 'ul',
				'classes' => 'statistics-right',
			),
			array(
				'title'   => 'Highlight',
				'inline'   => 'span',
				'classes' => 'highlight',
			),
			array(
				'title'   => 'Purple',
				'inline'   => 'span',
				'classes' => 'purple',
			),
			array(
				'title'    => 'Two Columns',
				'wrapper'  => true,
				'block'    => 'div',
				'classes'  => 'small-6 medium-6 columns',
			),
			array(
				'title'    => 'Three Columns',
				'wrapper'  => true,
				'block'    => 'div',
				'classes'  => 'small-12 medium-4 columns',
			),
			array(
				'title'   => 'Four Columns',
				'wrapper'  => true,
				'block'    => 'div',
				'classes' => 'small-12 medium-3 columns',
			),
		);
		// Insert the array, json encoded, into 'style_formats'
		$init_array['style_formats'] = json_encode( $style_formats );
	}

	return $init_array;
}

add_filter( 'tiny_mce_before_init', 'sennza_mce_before_init' );
