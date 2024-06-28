<?php
/**
 * Singleton.
 *
 * @package WC_OD/Traits
 * @since   2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Trait WC_OD_Singleton.
 */
trait WC_OD_Singleton_Trait {

	/**
	 * The single instance of the class.
	 *
	 * @var mixed
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	protected function __construct() {}

	/**
	 * Gets the single instance of the class.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed The class instance.
	 */
	final public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Prevents cloning.
	 *
	 * @since 2.0.0
	 */
	private function __clone() {
		wc_doing_it_wrong( __FUNCTION__, 'Cloning is forbidden.', '2.0.0' );
	}

	/**
	 * Prevents unserializing.
	 *
	 * @since 2.0.0
	 */
	final public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, 'Unserializing instances of this class is forbidden.', '2.0.0' );
	}
}
