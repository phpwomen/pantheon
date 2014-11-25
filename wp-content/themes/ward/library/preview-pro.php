<?php
class Bavotasan_Preview_Pro {
	private $theme_url = 'http://themes.bavotasan.com/themes/ward-pro/';
	private $video_url = 'http://www.youtube.com/watch?v=r-ZemVT_0u8';

	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Add a 'Preview Pro' menu item to the admin bar
	 *
	 * This function is attached to the 'admin_bar_menu' action hook.
	 *
	 * @since 1.0.0
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
	    if ( current_user_can( 'edit_theme_options' ) && is_admin_bar_showing() )
	    	$wp_admin_bar->add_node( array( 'parent' => 'bavotasan_toolbar', 'id' => 'preview_pro', 'title' => sprintf( __( 'Upgrade to %s Pro', 'ward' ), BAVOTASAN_THEME_NAME ), 'href' => esc_url( admin_url( 'themes.php?page=bavotasan_preview_pro' ) ) ) );
	}

	/**
	 * Add a 'Preview Pro' menu item to the Appearance panel
	 *
	 * This function is attached to the 'admin_menu' action hook.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_theme_page( sprintf( __( 'Upgrade to %s Pro', 'ward' ), BAVOTASAN_THEME_NAME ), sprintf( __( 'Upgrade to Pro', 'ward' ), BAVOTASAN_THEME_NAME ), 'edit_theme_options', 'bavotasan_preview_pro', array( $this, 'bavotasan_preview_pro' ) );
	}

	public function bavotasan_preview_pro() {
		?>
		<style>
		.about-wrap h1,
		.about-text {
			margin-right: 0;
		}

		.about-wrap .feature-section img {
			max-width: 65%;
		}

		.about-wrap .feature-section.images-stagger-right img {
			float: right;
			margin: 0 0 12px 2em;
		}

		.about-wrap .feature-section.images-stagger-left img {
			float: left;
			margin: 0 2em 12px 0;
		}

		.about-wrap .feature-section img {
			background: #fff;
			border: 1px #ccc solid;
			-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.3);
			box-shadow: 0 1px 3px rgba(0,0,0,0.3);
			-webkit-corder-radius: 3px;
			border-radius: 3px;
		}

		@media (max-width: 768px) {
			.about-wrap .feature-section img {
				max-width: 100%;
			}
		}
		</style>
		<div class="wrap about-wrap" id="custom-background">
			<h1><?php echo get_admin_page_title(); ?></h1>
			<div class="about-text">
				<?php printf( __( 'Take your site to the next level with %s. Check out some of the advanced features that\'ll give you more control over your site\'s layout and design.', 'ward' ), '<em>' . BAVOTASAN_THEME_NAME . ' Pro</em>' ); ?>
				<br /><a href="<?php echo $this->video_url; ?>" target="_blank"><?php _e( 'View the demo video &rarr;', 'ward' ); ?></a>
			</div>
			<h2 class="nav-tab-wrapper">
				<?php _e( 'Features', 'ward' ); ?>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Advanced Color Picker', 'ward' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BAVOTASAN_THEME_URL; ?>/library/images/color-picker.jpg" class="image-66">
					<h4><?php _e( 'So Many Colors to Choose From', 'ward' ); ?></h4>
					<p><?php printf( __( 'Sometimes the default colors just aren\'t working for you. In %s you can use the advanced color picker to make sure you get the exact colors you want.', 'ward' ), '<em>' . BAVOTASAN_THEME_NAME . ' Pro</em>' ); ?></p>
					<p><?php _e( 'Easily select one of the eight preset colors or dive even deeper into your customization by using a more specific hex code.', 'ward' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Google Fonts', 'ward' ); ?></h3>

				<div class="feature-section images-stagger-left">
					<img alt="" src="<?php echo BAVOTASAN_THEME_URL; ?>/library/images/google-fonts.jpg" class="image-66">
					<h4><?php _e( 'Over 20 to Choose From', 'ward' ); ?></h4>
					<p><?php _e( 'Web-safe fonts are a thing of the past, so why not try to spice things up a bit?', 'ward' ); ?></p>
					<p><?php _e( 'Choose from some of Google Fonts\' most popular fonts to improve your site\'s typeface readability and make things look even more amazing.', 'ward' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Custom CSS Editor', 'ward' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BAVOTASAN_THEME_URL; ?>/library/images/custom-css.jpg" class="image-66">
					<h4><?php _e( 'Take Control of Design', 'ward' ); ?></h4>
					<p><?php _e( 'Sometimes the Theme Options don\'t let you control everything you want. That\'s where the Custom CSS Editor comes into play.', 'ward' ); ?></p>
					<p><?php _e( 'Use CSS to style any element without having to worry about losing your changes when you update. All your Custom CSS is safely stored in the database.', 'ward' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Extended Widgetized Footer', 'ward' ); ?></h3>

				<div class="feature-section images-stagger-left">
					<img alt="" src="<?php echo BAVOTASAN_THEME_URL; ?>/library/images/extended-footer.jpg" class="image-66">
					<h4><?php _e( 'Add More Widgets', 'ward' ); ?></h4>
					<p><?php _e( 'If you need to include more widgets on your site, take advantage of the Extended Footer.', 'ward' ); ?></p>
					<p><?php _e( 'Use the Theme Options customizer to set the number of columns you want to appear. You can also customize your site\'s copyright notice.', 'ward' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Even More Theme Options', 'ward' ); ?></h3>
				<div class="feature-section col two-col">
					<div>
						<h4><?php _e( 'Full Width Posts/Pages', 'ward' ); ?></h4>
						<p><?php _e( 'Each page/post has an option to remove both sidebars so you can use the full width of your site to display whatever you want.', 'ward' ); ?></p>
					</div>
					<div class="last-feature">
						<h4><?php _e( 'Multiple Sidebar Layouts', 'ward' ); ?></h4>
						<p><?php _e( 'Sometimes one sidebar just isn\'t enough, so add a second one and place it where you want.', 'ward' ); ?></p>
					</div>
				</div>

				<div class="feature-section col two-col">
					<div>
						<h4><?php _e( 'Twitter Bootstrap Shortcodes', 'ward' ); ?></h4>
						<p><?php printf( __( 'Shortcodes are awesome and easy to use. That\'s why %s comes with a bunch, like a slideshow carousel, alert boxes and more.', 'ward' ), '<em>' . BAVOTASAN_THEME_NAME . ' Pro</em>' ); ?></p>
					</div>
					<div class="last-feature">
						<h4><?php _e( 'Import/Export Tool', 'ward' ); ?></h4>
						<p><?php _e( 'Once you\'ve set up your site exactly how you want, you can easily export the Theme Options and Custom CSS for safe keeping.', 'ward' ); ?></p>
					</div>
				</div>
			</div>

			<p><a href="<?php echo $this->theme_url; ?>" target="_blank" class="button-primary button-large"><?php printf( __( 'Buy %s Now &rarr;', 'ward' ), '<strong>' . BAVOTASAN_THEME_NAME . ' Pro</strong>' ); ?></a></p>
		</div>
		<?php
	}
}
$bavotasan_preview_pro = new Bavotasan_Preview_Pro;