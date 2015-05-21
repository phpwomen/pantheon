<?php

abstract class WP_Stream_Connector {

	/**
	 * Connector slug
	 *
	 * @var string
	 */
	public static $name = null;

	/**
	 * Actions registered for this connector
	 *
	 * @var array
	 */
	public static $actions = array();

	/**
	 * Previous Stream entry in same request
	 *
	 * @var int
	 */
	public static $prev_stream = null;

	/**
	 * Register all context hooks
	 *
	 * @return void
	 */
	public static function register() {
		$class = get_called_class();

		foreach ( $class::$actions as $action ) {
			add_action( $action, array( $class, 'callback' ), null, 5 );
		}

		add_filter( 'wp_stream_action_links_' . $class::$name, array( $class, 'action_links' ), 10, 2 );
	}

	/**
	 * Callback for all registered hooks throughout Stream
	 * Looks for a class method with the convention: "callback_{action name}"
	 *
	 * @return void
	 */
	public static function callback() {
		$action   = current_filter();
		$class    = get_called_class();
		$callback = array( $class, 'callback_' . preg_replace( '/[^a-z0-9_\-]/', '_', $action ) );

		// For the sake of testing, trigger an action with the name of the callback
		if ( defined( 'STREAM_TESTS' ) ) {
			/**
			 * Action fires during testing to test the current callback
			 *
			 * @param  array  $callback  Callback name
			 */
			do_action( 'wp_stream_test_' . $callback[1] );
		}

		// Call the real function
		if ( is_callable( $callback ) ) {
			return call_user_func_array( $callback, func_get_args() );
		}
	}

	/**
	 * Add action links to Stream drop row in admin list screen
	 *
	 * @filter wp_stream_action_links_{connector}
	 * @param  array $links      Previous links registered
	 * @param  object $record    Stream record
	 * @return array             Action links
	 */
	public static function action_links( $links, $record ) {
		return $links;
	}

	/**
	 * Log handler
	 *
	 * @param  string $message   sprintf-ready error message string
	 * @param  array  $args      sprintf (and extra) arguments to use
	 * @param  int    $object_id Target object id
	 * @param  string $context   Context of the event
	 * @param  string $action    Action of the event
	 * @param  int    $user_id   User responsible for the event
	 *
	 * @internal param string $action Action performed (stream_action)
	 * @return bool
	 */
	public static function log( $message, $args, $object_id, $context, $action, $user_id = null ) {
		$class     = get_called_class();
		$connector = $class::$name;

		$data = apply_filters(
			'wp_stream_log_data',
			compact( 'connector', 'message', 'args', 'object_id', 'context', 'action', 'user_id' )
		);

		if ( ! $data ) {
			return false;
		} else {
			$connector = $data['connector'];
			$message   = $data['message'];
			$args      = $data['args'];
			$object_id = $data['object_id'];
			$context   = $data['context'];
			$action    = $data['action'];
			$user_id   = $data['user_id'];
		}

		return call_user_func_array( array( WP_Stream_Log::get_instance(), 'log' ), compact( 'connector', 'message', 'args', 'object_id', 'context', 'action', 'user_id' ) );
	}

	/**
	 * Save log data till shutdown, so other callbacks would be able to override
	 *
	 * @param  string $handle Special slug to be shared with other actions
	 *
	 * @internal param mixed $arg1 Extra arguments to sent to log()
	 * @internal param mixed $arg2 , etc..
	 * @return void
	 */
	public static function delayed_log( $handle ) {
		$args = func_get_args();

		array_shift( $args );

		self::$delayed[ $handle ] = $args;

		add_action( 'shutdown', array( __CLASS__, 'delayed_log_commit' ) );
	}

	/**
	 * Commit delayed logs saved by @delayed_log
	 *
	 * @return void
	 */
	public static function delayed_log_commit() {
		foreach ( self::$delayed as $handle => $args ) {
			call_user_func_array( array( __CLASS__, 'log' ) , $args );
		}
	}

	/**
	 * Compare two values and return changed keys if they are arrays
	 *
	 * @param  mixed    $old_value Value before change
	 * @param  mixed    $new_value Value after change
	 * @param  bool|int $deep      Get array children changes keys as well, not just parents
	 *
	 * @return array
	 */
	public static function get_changed_keys( $old_value, $new_value, $deep = false ) {
		if ( ! is_array( $old_value ) && ! is_array( $new_value ) ) {
			return array();
		}

		if ( ! is_array( $old_value ) ) {
			return array_keys( $new_value );
		}

		if ( ! is_array( $new_value ) ) {
			return array_keys( $old_value );
		}

		$diff = array_udiff_assoc(
			$old_value,
			$new_value,
			function( $value1, $value2 ) {
				return maybe_serialize( $value1 ) !== maybe_serialize( $value2 );
			}
		);

		$result = array_keys( $diff );

		// find unexisting keys in old or new value
		$common_keys     = array_keys( array_intersect_key( $old_value, $new_value ) );
		$unique_keys_old = array_values( array_diff( array_keys( $old_value ), $common_keys ) );
		$unique_keys_new = array_values( array_diff( array_keys( $new_value ), $common_keys ) );
		$result = array_merge( $result, $unique_keys_old, $unique_keys_new );

		// remove numeric indexes
		$result = array_filter(
			$result,
			function( $value ) {
				// check if is not valid number (is_int, is_numeric and ctype_digit are not enough)
				return (string) (int) $value !== (string) $value;
			}
		);

		$result = array_values( array_unique( $result ) );

		if ( false === $deep ) {
			return $result; // Return an numerical based array with changed TOP PARENT keys only
		}

		$result = array_fill_keys( $result, null );

		foreach ( $result as $key => $val ) {
			if ( in_array( $key, $unique_keys_old ) ) {
				$result[ $key ] = false; // Removed
			}
			elseif ( in_array( $key, $unique_keys_new ) ) {
				$result[ $key ] = true; // Added
			}
			elseif ( $deep ) { // Changed, find what changed, only if we're allowed to explore a new level
				if ( is_array( $old_value[ $key ] ) && is_array( $new_value[ $key ] ) ) {
					$inner  = array();
					$parent = $key;
					$deep--;
					$changed = self::get_changed_keys( $old_value[ $key ], $new_value[ $key ], $deep );
					foreach ( $changed as $child => $change ) {
						$inner[ $parent . '::' . $child ] = $change;
					}
					$result[ $key ] = 0; // Changed parent which has a changed children
					$result = array_merge( $result, $inner );
				}
			}
		}

		return $result;
	}

	/**
	 * Allow connectors to determine if their dependencies is satisfied or not
	 *
	 * @return bool
	 */
	public static function is_dependency_satisfied() {
		return true;
	}

}
