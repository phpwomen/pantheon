<?php
/**
 * Custom Text widget class
 *
 * @since 1.0.0
 */
class Bavotasan_Custom_Text_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array( 'classname' => 'bavotasan_custom_text_widget', 'description' => __( 'Custom Text Widget with Image', 'ward' ) );
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct( 'bavotasan_custom_text_widget', '(' . BAVOTASAN_THEME_NAME . ') ' . __( 'Image & Text', 'ward' ), $widget_ops, $control_ops );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function admin_enqueue_scripts( $hook ) {
	    if ( 'widgets.php' == $hook ) {
    		wp_enqueue_media();
    		wp_enqueue_script( 'bavotasan_image_widget', BAVOTASAN_THEME_URL . '/library/js/admin/image-widget.js', array( 'jquery', 'media-upload', 'media-views' ), '', true );

    		wp_enqueue_style( 'bavotasan_image_widget_css', BAVOTASAN_THEME_URL . '/library/css/admin/image-widget.css' );
        }
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$image = esc_url( $instance['image'] );
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
		$url = esc_url( $instance['url'] );

		$title_string = ( $url ) ? '<a href="' . $url . '">'. $title . '</a>' : $title;
		$image_string = ( $url ) ? '<a href="' . $url . '"><img src="' . $image. '" alt="' . esc_attr( $title ) . '" class="img-circle aligncenter" /></a>' : '<img src="' . $image. '" alt="' . esc_attr( $title ) . '" class="img-circle aligncenter" />';

		echo $before_widget;

		if ( ! empty( $image ) )
			echo $image_string;

		if ( $title )
			echo $before_title . $title_string . $after_title;
		?>

		<div class="textwidget">
			<?php echo ( ! empty( $instance['filter'] ) ) ? wpautop( $text ) : $text; ?>
		</div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['image'] = esc_url( $new_instance['image'] );
		$instance['url'] = esc_url( $new_instance['url'] );

		if ( current_user_can( 'unfiltered_html' ) )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['text'] ) ) ); // wp_filter_post_kses() expects slashed

		$instance['filter'] = isset( $new_instance['filter'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '', 'image' => '', 'url' => '' ) );
		extract( $instance );
		$img_tag = ( $image ) ? '<img src="' . esc_url( $image ) . '" alt="" />' : '';
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ward' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><label><?php _e( 'Image:', 'ward' ); ?></label>
		<span class="custom-image-container"><?php echo $img_tag; ?></span>
		<a href="#" class="select-image"><?php _e( 'Select Image', 'ward' ); ?></a> | <a href="#" class="delete-image"><?php _e( 'Remove Image', 'ward' ); ?></a>
		<input class="image-widget-custom-image" name="<?php echo $this->get_field_name( 'image' ); ?>" type="hidden" value="<?php echo esc_url( $image ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'URL:', 'ward' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>" /></p>

		<textarea class="widefat" rows="8" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo esc_textarea( $text ); ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked( isset( $filter ) ? $filter : 0 ); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e( 'Automatically add paragraphs', 'ward' ); ?></label></p>
		<?php
	}
}
register_widget( 'Bavotasan_Custom_Text_Widget' );