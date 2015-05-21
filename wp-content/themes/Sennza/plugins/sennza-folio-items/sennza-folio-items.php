<?php
/*
Plugin Name: Sennza Folio Items
Plugin URI: http://wwww.sennza.com.au/
Description: This plugin generates a custom post type for Folio Items
Author: Bronson Quick
Version: 2.0
Author URI: http://www.sennza.com.au/
*/

/**
 * SZ Folio Items
 */
class SZ_Folio_Items {

	private static $instance;

	static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new SZ_Folio_Items;

		return self::$instance;
	}

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'init', array( $this, 'init_cpt' ) );
	}

	/**
	 * Setup the sennza portfolio Custom Post Type
	 */
	public function init_cpt()
	{
		$args = array(
			'public' => true,
			'query_var' => 'folio',
			'supports' => array(
				'title',
				'author',
				'thumbnail',
				'editor'),
			'rewrite' => false,
			'labels' => array(
				'name' => 'Folio Items',
				'singular_name' => 'Folio Item',
				'add_new' => 'Add New Folio',
				'add_new_item' => 'Add New Folio Item',
				'edit_item' => 'Edit Folio Item',
				'new_item' => 'New Folio Item',
				'view_item' => 'View Folio',
				'search_items' => 'Search Folio',
				'not_found' =>  'No folio items found',
				'not_found_in_trash' => 'No folio items found in Trash'
			),
		);

		register_post_type('folio', $args);

		add_image_size( 'folio-full-width', 500, 480, true );
		add_image_size( 'folio-list-width', 230, 175, true );
		add_image_size( 'folio-992', 283, 198, true );

	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
		$post_types = array('folio');

		if ( in_array( $post_type, $post_types )) {
			add_meta_box(
				'Project Details'
				,__( 'Project details', 'sz_folio_items' )
				,array( $this, 'render_meta_box_content' )
				,$post_type
				,'advanced'
				,'high'
			);
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['myplugin_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
				//     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$description = sanitize_text_field( $_POST['bq_project_description'] );
		$url = sanitize_text_field( $_POST['bq_project_url'] );

		// Update the meta field.
		update_post_meta( $post_id, 'bq_project_description', $description );
		update_post_meta( $post_id, 'bq_project_url', $url );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$description = get_post_meta( $post->ID, 'bq_project_description', true );
		$url = get_post_meta( $post->ID, 'bq_project_url', true );

		// Display the form, using the current value
		echo '<table class="form-table"><tbody><tr><td>';
		echo '<label for="bq_project_description">';
			_e( 'Project Description', 'sz_folio_items' );
		echo '</label><br>';
		echo '<textarea id="bq_project_description" name="bq_project_description" cols="80" rows="10">';
		echo esc_attr( $description );
		echo '</textarea>';

		echo '</tr></td><tr><td>';
		echo '<label for="bq_project_link">';
			_e( 'Project Link', 'sz_folio_items' );
		echo '</label><br>';
		echo '<input type="text" id="bq_project_url" name="bq_project_url" class="regular-text" placeholder="ie: http://example.com" value="' . esc_url_raw( $url ) . '">';
		echo '</tr></td><tr><td>';
		echo '</tbody></table>';
	}
}

add_action( 'plugins_loaded', array( 'SZ_Folio_Items', 'get_instance' ) );
