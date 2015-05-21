<?php
/**
 * Include the HM Custom Meta boxes
 */
require_once( get_template_directory() . '/inc/cmb/custom-meta-boxes.php' );

function sz_homepage_meta_boxes( array $meta_boxes ) {

	/**
	 * Check if a page is set to show on the front page and if it is then get it's ID so that we only display our meta boxes on the homepage.
	 */
	if ( 'page' == get_option( 'show_on_front') ) {
		$front_page_id = get_option( 'page_on_front');
	}

	/**
	 * Initial a repeatable meta box for the Call To Action Text and link on the homepage header
	 */

	$call_to_action_fields = array(
		array(
			'id'   => 'cta-text',
			'name' => 'Call To Action Text',
			'type' => 'text',
		),
		array(
			'id'       => 'cta-link',
			'name'     => 'Call To Action Link',
			'type'     => 'post_select',
			'use_ajax' => true,
			'query' => array(
				'post_type' => 'page',
				'posts_per_page' => '-1',
			)
		),
	);

	/**
	 * Initialize our meta box for the Call To Action Section
	 */

	$meta_boxes[] = array(
		'title'   => 'Call To Action',
		'pages'   => 'page',
		'show_on' => (int) $front_page_id,
		'fields'  => $call_to_action_fields,
		'context' => 'side'
	);

	/**
	 * Initialize our repeatable field groups for the Client Testimonials
	 */
	$clients_group_fields = array(
		array(
			'id'        => 'client-logo',
			'name'      => 'Client Logo',
			'type'      => 'image',
			'cols'      => 2,
			'size'      => 'height=75&width=225&crop=1',
			'show_size' => true
		),
		array(
			'id'   => 'case-study-text',
			'name' => 'Case Study Text',
			'cols' => 8,
			'type' => 'text'
		),
		array(
			'id' => 'case-study-link',
			'name' => 'Link To Case Study',
			'type' => 'post_select',
			'use_ajax' => true,
			'query' => array(
				'post_type' => 'page',
				'posts_per_page' => '-1',
			),
			'cols' => 2
		),
	);

	/**
	 * Add our client testimonials to out meta box array
	 */
	$meta_boxes[] = array(
		'title'   => 'Our Clients',
		'pages'   => 'page',
		'show_on' => (int) $front_page_id,
		'fields'  => array(
			array(
				'id'         => 'clients',
				'name'       => 'Client',
				'type'       => 'group',
				'repeatable' => true,
				'sortable'   => true,
				'fields'     => $clients_group_fields,
				'desc'       => 'Enter a new client'
			)
		)
	);

	/**
	 * Initialize our repeatable field groups for the Client Testimonials
	 */
	$testimonial_group_fields = array(
		array(
			'id'        => 'testimonial-image',
			'name'      => 'Testimonial Image',
			'type'      => 'image',
			'cols'      => 2,
			'size'      => 'height=180&width=180&crop=1',
			'show_size' => true,
		),
		array(
			'id' => 'testimonial-content',
			'name' => 'Testimonial Content',
			'type' => 'wysiwyg',
			'options' => array(
				'textarea_rows' => '5'
			),
			'cols' => 10
		),
	);

	/**
	 * Add our client testimonials to out meta box array
	 */
	$meta_boxes[] = array(
		'title'   => 'Testimonials',
		'pages'   => 'page',
		'show_on' => (int) $front_page_id,
		'fields'  => array(
			array(
				'id'         => 'testimonial',
				'name'       => 'Testimonials',
				'type'       => 'group',
				'repeatable' => true,
				'sortable'   => true,
				'fields'     => $testimonial_group_fields,
				'desc'       => 'Enter a client testimonial'
			)
		)
	);

	return $meta_boxes;
}

add_filter( 'cmb_meta_boxes', 'sz_homepage_meta_boxes' );