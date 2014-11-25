<?php
class Bavotasan_Custom_Metaboxes {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Add option for full width posts & pages
	 *
	 * This function is attached to the 'add_meta_boxes' action hook.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box( 'alignment-options', __( 'Home Page Alignment', 'ward' ), array( $this, 'alignment_option' ), 'post', 'side', 'high' );
	}

	public function alignment_option( $post ) {
		$alignment = get_post_meta( $post->ID, 'bavotasan_home_page_alignment', true );

		// Use nonce for verification
		wp_nonce_field( 'bavotasan_nonce', 'bavotasan_nonce' );
		?>
		<input id="bavotasan_home_page_alignment" name="bavotasan_home_page_alignment" type="radio" <?php checked( $alignment, 'pull-left' ); ?> value="pull-left" /> <label for="bavotasan_home_page_alignment"><?php _e( 'Left', 'ward' ); ?></label>
		<br />
		<input id="bavotasan_home_page_alignment" name="bavotasan_home_page_alignment" type="radio" <?php checked( $alignment, 'pull-right' ); ?> value="pull-right" /> <label for="bavotasan_home_page_alignment"><?php _e( 'Right', 'ward' ); ?></label>
		<?php
	}

	/**
	 * Save post custom fields
	 *
	 * This function is attached to the 'save_post' action hook.
	 *
	 * @since 1.0.0
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! empty( $_POST['bavotasan_nonce'] ) && ! wp_verify_nonce( $_POST['bavotasan_nonce'], 'bavotasan_nonce' ) )
			return;

		if ( ! empty( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
		}

		$layout = ( empty( $_POST['bavotasan_home_page_alignment'] ) ) ? '' : $_POST['bavotasan_home_page_alignment'];
		if ( $layout )
			update_post_meta( $post_id, 'bavotasan_home_page_alignment', $layout );
		else
			delete_post_meta( $post_id, 'bavotasan_home_page_alignment' );
	}
}
$bavotasan_custom_metaboxes = new Bavotasan_Custom_Metaboxes;