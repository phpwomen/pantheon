<?php
/*
Plugin Name: Sennza Connect Widget
Description: A basic widget for displaying the social media icons for Sennza clients.
Author: Sennza
Version: 0.1
Author URI: http://sennza.com.au/
*/

define('CONNECT_PLUGIN_URL', plugin_dir_url( __FILE__ ));

class ConnectWidget extends WP_Widget
{
	
	

    // Register the widget
    function ConnectWidget()
    {
        // widget options
        $widget_options = array('classname' => 'ConnectWidget', 'description' => 'Displays the social media icons for Sennza clients.');
        // control options (width, height etc)
        $control_options = array( 'width' => 300 );
        // Actually create the widget (widget id, widget name, options...)
        $this->WP_Widget( 'ConnectWidget', 'Sennza Social Media', $widget_options, $control_options );
    }

    // Output the admin options form
    function form($instance)
    {
        $defaults = array( 'title' => 'Connect', 'facebookUrl' => '', 'gplusUrl' => '', 'linkedinUrl' => '');
        $instance = wp_parse_args((array)$instance, $defaults);
        ?>

        <!--<p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
            <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
        </p>-->
        <p>
            <label for="<?php echo $this->get_field_id('facebookUrl'); ?>">Facebook Url: </label>
            <input id="<?php echo $this->get_field_id('facebookUrl'); ?>" name="<?php echo $this->get_field_name('facebookUrl'); ?>" value="<?php echo $instance['facebookUrl']; ?>" type="text" class="widefat" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('gplusUrl'); ?>">gplus Url: </label>
            <input id="<?php echo $this->get_field_id('gplusUrl'); ?>" name="<?php echo $this->get_field_name('gplusUrl'); ?>" value="<?php echo $instance['gplusUrl']; ?>" type="text" class="widefat" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('linkedinUrl'); ?>">linkedin Url: </label>
            <input id="<?php echo $this->get_field_id('linkedinUrl'); ?>" name="<?php echo $this->get_field_name('linkedinUrl'); ?>" value="<?php echo $instance['linkedinUrl']; ?>" type="text" class="widefat" />
        </p>

    <?php
    }

    // Processes the admin options form when saved
    function update($new_instance, $old_instance) {
        // Get the old values
        $instance = $old_instance;

        // Update with any new values (and sanitise input)
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['facebookUrl'] = strip_tags( $new_instance['facebookUrl'] );
        $instance['gplusUrl'] = strip_tags( $new_instance['gplusUrl'] );
        $instance['linkedinUrl'] = strip_tags( $new_instance['linkedinUrl'] );

        return $instance;
    }

    // Output the content of the widget
    function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);
	
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $facebookUrl = $instance['facebookUrl'];
        $gplusUrl = $instance['gplusUrl'];
        $linkedinUrl = $instance['linkedinUrl'];

        echo $before_widget;
        if (!empty($title))
            echo $before_title . $title . $after_title;
        ;
        echo '<ul class="social-networks">';
        if (!empty($facebookUrl))
            echo '<li class="socialfacebook"><a href="' . $facebookUrl . '" title="Facebook"></a></li>';
        if (!empty($gplusUrl))
            echo '<li class="socialgplus"><a href="' . $gplusUrl . '" title="GooglePlus"></a></li>';
        if (!empty($linkedinUrl))
            echo '<li class="sociallinkedin"><a href="' . $linkedinUrl . '" title="LinkedIn"></a></li>';
        echo '</ul>';
        echo $after_widget;
    }

}

add_action('widgets_init', create_function('', 'return register_widget("ConnectWidget");'));?>