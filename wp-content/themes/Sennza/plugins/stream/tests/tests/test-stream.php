<?php
/**
 * Tests stream main class
 *
 * @author X-Team
 * @author Jonathan Bardo <jonathan.bardo@x-team.com>
 */
class Test_WP_Stream extends WP_StreamTestCase {

	/**
	 * Check constructor composition
	 *
	 * @return void
	 */
	public function test_constructor() {
		// Check constant
		$this->assertTrue( defined( 'WP_STREAM_DIR' ), 'WP_STREAM_DIR is not defined' );
		$this->assertTrue( defined( 'WP_STREAM_URL' ), 'WP_STREAM_URL is not defined' );
		$this->assertTrue( defined( 'WP_STREAM_INC_DIR' ), 'WP_STREAM_INC_DIR is not defined' );
		$this->assertTrue( defined( 'WP_STREAM_CLASS_DIR' ), 'WP_STREAM_CLASS_DIR is not defined' );

		$actions_tests = array(
			array( 'init', 'WP_Stream_Settings', 'load', 9 ),
			array( 'plugins_loaded', 'WP_Stream_Log', 'load' ),
			array( 'init', 'WP_Stream_Connectors', 'load', 9 ),
		);

		$this->do_action_validation( $actions_tests );

		$plugin = $this->plugin;
		$this->assertInstanceOf( 'WP_Stream_DB', $plugin::$db );
		$this->assertInstanceOf( 'WP_Stream_API', $plugin::$api );
	}

	/**
	 * Check if get instance function return a valid instance of the strem class
	 *
	 * @return void
	 */
	public function test_get_instance() {
		$instance = WP_Stream::get_instance();
		$this->assertInstanceOf( 'WP_Stream', $instance );
	}

}
