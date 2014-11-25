<?php
// Adds Post Meta Box for Kind Taxonomy
// Plan is to optionally automate filling in of this data from secondary plugins

// Possible extra fields - author name, author url, author profile image, 
// publish date


// Add meta box to new post/post pages only 
add_action('load-post.php', 'kindbox_setup');
add_action('load-post-new.php', 'kindbox_setup');

/* Meta box setup function. */
function kindbox_setup() {

  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'kindbox_add_postmeta_boxes' );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function kindbox_add_postmeta_boxes() {

  add_meta_box(
    'responsebox-meta',      // Unique ID
    esc_html__( 'In Response To', 'kind_taxonomy' ),    // Title
    'response_metabox',   // Callback function
    'post',         // Admin page (or post type)
    'normal',         // Context
    'default'         // Priority
  );

}

function response_metabox( $object, $box ) { ?>

  <?php 
	wp_nonce_field( 'response_metabox', 'response_metabox_nonce' ); 
	$meta = get_post_meta ($object->ID, 'response', true);
  ?>  
  <p>
    <label for="response_url"><?php _e( "URL", 'kind_taxonomy' ); ?></label>
    <br />
    <input type="text" name="response_url" id="response_url" value="<?php if (isset ($meta['url'])) {echo esc_attr( $meta['url'] ); } ?>" size="70" />
    <br />
    <label for="response_title"><?php _e( "Custom Title", 'kind_taxonomy' ); ?></label>
    <br />
    <input type="text" name="response_title" id="response_title" value="<?php if (isset ($meta['title'])) { echo esc_attr($meta['title']); } ?>" size="70" />
	<br />
    <label for="response_content"><?php _e( "Content/Citation", 'kind_taxonomy' ); ?></label>
    <br />
    <textarea name="response_content" id="response_content" cols="70"><?php if (isset ($meta['content'])) { echo esc_attr( $meta['content'] ); } ?></textarea>
  
  </p>

<?php }

/* Save the meta box's post metadata. */
function responsebox_save_post_meta( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['response_metabox_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['response_metabox_nonce'], 'response_metabox' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, its safe for us to save the data now. */
	if( isset( $_POST[ 'response_url' ] ) ) {
            $meta['url'] = esc_url_raw( $_POST[ 'response_url' ] );
	}
	if( isset( $_POST[ 'response_title' ] ) ) {
            $meta['title'] = esc_attr( $_POST[ 'response_title' ] ) ;
        }
	if( isset( $_POST[ 'response_content' ] ) ) {
            $meta['content'] = wp_kses_post( (string) $_POST[ 'response_content' ] );
        }
	if(!empty($meta)) {
	    update_post_meta( $post_id,'response', $meta);
           }

}

add_action( 'save_post', 'responsebox_save_post_meta', 5 );
add_action( 'publish_post', 'responsebox_save_post_meta', 5 );

// add_action( 'add_meta_boxes', 'make_wp_editor_movable', 0 );
function make_wp_editor_movable() {
	global $_wp_post_type_features;
	if (isset($_wp_post_type_features['post']['editor']) && $_wp_post_type_features['post']['editor']) {
		unset($_wp_post_type_features['post']['editor']);
		add_meta_box(
			'content_sectionid',
			__('Content'),
			'movable_inner_custom_box',
			'post', 'normal', 'high'
		);
	}
}

function movable_inner_custom_box( $post ) {
	wp_editor($post->post_content, 'movable-content');
}

	
	

?>
