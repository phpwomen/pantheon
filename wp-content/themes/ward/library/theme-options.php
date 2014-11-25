<?php
/**
 * Set up the default theme options
 *
 * @since 1.0.0
 */
function bavotasan_theme_options() {
	//delete_option( 'ward_theme_options' );
	$default_theme_options = array(
		'width' => '1200',
		'layout' => '2',
		'primary' => 'col-md-8',
		'display_author' => 'on',
		'display_date' => 'on',
		'display_comment_count' => 'on',
		'display_categories' => '',
		'excerpt_content' => 'excerpt',
		'jumbo_headline_title' => 'Jumbo Headline!',
		'jumbo_headline_text' => 'Got something important to say? Then make it stand out by using the jumbo headline option and get your visitor\'s attention right away.',
	);

	return get_option( 'ward_theme_options', $default_theme_options );
}

class Bavotasan_Customizer {
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
	}

	/**
	 * Add a 'customize' menu item to the admin bar
	 *
	 * This function is attached to the 'admin_bar_menu' action hook.
	 *
	 * @since 1.0.0
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
	    if ( current_user_can( 'edit_theme_options' ) && is_admin_bar_showing() ) {
	    	$wp_admin_bar->add_node( array( 'parent' => 'bavotasan_toolbar', 'id' => 'customize_theme', 'title' => __( 'Theme Options', 'ward' ), 'href' => admin_url( 'customize.php' ) ) );
   			$wp_admin_bar->add_node( array( 'parent' => 'bavotasan_toolbar', 'id' => 'documentation_faqs', 'title' => __( 'Documentation & FAQs', 'ward' ), 'href' => 'https://themes.bavotasan.com/documentation', 'meta' => array( 'target' => '_blank' ) ) );
   		}
	}

	/**
	 * Adds theme options to the Customizer screen
	 *
	 * This function is attached to the 'customize_register' action hook.
	 *
	 * @param	class $wp_customize
	 *
	 * @since 1.0.0
	 */
	public function customize_register( $wp_customize ) {
		$bavotasan_theme_options = bavotasan_theme_options();

		// Layout section panel
		$wp_customize->add_section( 'bavotasan_layout', array(
			'title' => __( 'Layout', 'ward' ),
			'priority' => 35,
		) );

		$wp_customize->add_setting( 'ward_theme_options[width]', array(
			'default' => $bavotasan_theme_options['width'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_width', array(
			'label' => __( 'Site Width', 'ward' ),
			'section' => 'bavotasan_layout',
			'settings' => 'ward_theme_options[width]',
			'priority' => 10,
			'type' => 'select',
			'choices' => array(
				'1200' => __( '1200px', 'ward' ),
				'992' => __( '992px', 'ward' ),
			),
		) );

		$wp_customize->add_setting( 'ward_theme_options[layout]', array(
			'default' => $bavotasan_theme_options['layout'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_site_layout', array(
			'label' => __( 'Site Layout', 'ward' ),
			'section' => 'bavotasan_layout',
			'settings' => 'ward_theme_options[layout]',
			'priority' => 15,
			'type' => 'radio',
			'choices' => array(
				'1' => __( '1 Sidebar - Left', 'ward' ),
				'2' => __( '1 Sidebar - Right', 'ward' ),
				'6' => __( 'No Sidebars', 'ward' )
			),
		) );

		$choices =  array(
			'col-md-2' => '17%',
			'col-md-3' => '25%',
			'col-md-4' => '34%',
			'col-md-5' => '42%',
			'col-md-6' => '50%',
			'col-md-7' => '58%',
			'col-md-8' => '66%',
			'col-md-9' => '75%',
			'col-md-10' => '83%',
			'col-md-12' => '100%',
		);

		$wp_customize->add_setting( 'ward_theme_options[primary]', array(
			'default' => $bavotasan_theme_options['primary'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_primary_column', array(
			'label' => __( 'Main Content', 'ward' ),
			'section' => 'bavotasan_layout',
			'settings' => 'ward_theme_options[primary]',
			'priority' => 20,
			'type' => 'select',
			'choices' => $choices,
		) );

		$wp_customize->add_setting( 'ward_theme_options[excerpt_content]', array(
			'default' => $bavotasan_theme_options['excerpt_content'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_excerpt_content', array(
			'label' => __( 'Post Content Display', 'ward' ),
			'section' => 'bavotasan_layout',
			'settings' => 'ward_theme_options[excerpt_content]',
			'priority' => 30,
			'type' => 'radio',
			'choices' => array(
				'excerpt' => __( 'Teaser Excerpt', 'ward' ),
				'content' => __( 'Full Content', 'ward' ),
			),
		) );

		// Jumbo headline section panel
		$wp_customize->add_section( 'bavotasan_jumbo', array(
			'title' => __( 'Jumbo Headline', 'ward' ),
			'priority' => 36,
		) );

		$wp_customize->add_setting( 'ward_theme_options[jumbo_headline_title]', array(
			'default' => $bavotasan_theme_options['jumbo_headline_title'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_jumbo_headline_title', array(
			'label' => __( 'Jumbo Headline Title', 'ward' ),
			'section' => 'bavotasan_jumbo',
			'settings' => 'ward_theme_options[jumbo_headline_title]',
			'priority' => 26,
			'type' => 'text',
		) );

		$wp_customize->add_setting( 'ward_theme_options[jumbo_headline_text]', array(
			'default' => $bavotasan_theme_options['jumbo_headline_text'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_jumbo_headline_text', array(
			'label' => __( 'Jumbo Headline Text', 'ward' ),
			'section' => 'bavotasan_jumbo',
			'settings' => 'ward_theme_options[jumbo_headline_text]',
			'priority' => 27,
			'type' => 'text',
		) );

		// Posts panel
		$wp_customize->add_section( 'bavotasan_posts', array(
			'title' => __( 'Posts', 'ward' ),
			'priority' => 45,
		) );

		$wp_customize->add_setting( 'ward_theme_options[display_categories]', array(
			'default' => $bavotasan_theme_options['display_categories'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_display_categories', array(
			'label' => __( 'Display Categories', 'ward' ),
			'section' => 'bavotasan_posts',
			'settings' => 'ward_theme_options[display_categories]',
			'type' => 'checkbox',
		) );

		$wp_customize->add_setting( 'ward_theme_options[display_author]', array(
			'default' => $bavotasan_theme_options['display_author'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_display_author', array(
			'label' => __( 'Display Author', 'ward' ),
			'section' => 'bavotasan_posts',
			'settings' => 'ward_theme_options[display_author]',
			'type' => 'checkbox',
		) );

		$wp_customize->add_setting( 'ward_theme_options[display_date]', array(
			'default' => $bavotasan_theme_options['display_date'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_display_date', array(
			'label' => __( 'Display Date', 'ward' ),
			'section' => 'bavotasan_posts',
			'settings' => 'ward_theme_options[display_date]',
			'type' => 'checkbox',
		) );

		$wp_customize->add_setting( 'ward_theme_options[display_comment_count]', array(
			'default' => $bavotasan_theme_options['display_comment_count'],
			'type' => 'option',
			'capability' => 'edit_theme_options',
		) );

		$wp_customize->add_control( 'bavotasan_display_comment_count', array(
			'label' => __( 'Display Comment Count', 'ward' ),
			'section' => 'bavotasan_posts',
			'settings' => 'ward_theme_options[display_comment_count]',
			'type' => 'checkbox',
		) );
	}
}
$bavotasan_customizer = new Bavotasan_Customizer;