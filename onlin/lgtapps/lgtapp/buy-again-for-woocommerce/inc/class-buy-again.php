<?php
/**
 * Buy Again for WooCommerce Main Class
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Buy_Again' ) ) {

	/**
	 * Main Buy_Again Class.
	 * */
	final class Buy_Again {

		/**
		 * Version.
		 *
		 * @var String
		 * */
		private $version = '3.9.0';

		/**
		 * WordPress Require.
		 *
		 * @var string
		 * */
		public static $wp_requires = '4.6';

		/**
		 * WooCommerce Require.
		 *
		 * @var string
		 * */
		public static $wc_requires = '3.5';

		/**
		 * The single instance of the class.
		 *
		 * @var String
		 * */
		protected static $instance = null;

		/**
		 * Load FP_Buy_Again Class in Single Instance.
		 *
		 * @since 1.0
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning has been forbidden.
		 *
		 * @since 1.0
		 * */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', '1.0' );
		}

		/**
		 * Unserialize the class data has been forbidden.
		 *
		 * @since 1.0
		 * */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', '1.0' );
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0
		 * */
		public function __construct() {
			$this->define_constants();
			$this->include_files();
			$this->init_hooks();
		}

		/**
		 * Load plugin the translate files.
		 *
		 * @since 1.0
		 * */
		private function load_plugin_textdomain() {
			if ( function_exists( 'determine_locale' ) ) {
				$locale = determine_locale();
			} else {
				// @todo Remove when start supporting WP 5.0 or later.
				$locale = is_admin() ? get_user_locale() : get_locale();
			}

			/**
			 * Filter to Woocommerce Quantity Field Arguments.
			 *
			 * @since 1.0
			 * @return String.
			 * */
			$locale = apply_filters( 'plugin_locale', $locale, 'buy-again-for-woocommerce' );

			unload_textdomain( 'buy-again-for-woocommerce' );
			load_textdomain( 'buy-again-for-woocommerce', WP_LANG_DIR . '/buy-again-for-woocommerce/buy-again-for-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'buy-again-for-woocommerce', false, dirname( plugin_basename( BYA_PLUGIN_FILE ) ) . '/languages' );
		}

		/**
		 * Prepare the constants value array.
		 *
		 * @since 1.0
		 * */
		private function define_constants() {
			$protocol = 'http://';

			if ( isset( $_SERVER['HTTPS'] ) && ( 'on' === $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS'] ) || isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
				$protocol = 'https://';
			}

			$constant_array = array(
				'BYA_VERSION'        => $this->version,
				'BYA_FOLDER_NAME'    => 'buy-again-for-woocommerce',
				'BYA_ABSPATH'        => dirname( BYA_PLUGIN_FILE ) . '/',
				'BYA_ADMIN_URL'      => admin_url( 'admin.php' ),
				'BYA_ADMIN_AJAX_URL' => admin_url( 'admin-ajax.php' ),
				'BYA_PLUGIN_SLUG'    => plugin_basename( BYA_PLUGIN_FILE ),
				'BYA_PLUGIN_PATH'    => untrailingslashit( plugin_dir_path( BYA_PLUGIN_FILE ) ),
				'BYA_PLUGIN_URL'     => untrailingslashit( plugins_url( '/', BYA_PLUGIN_FILE ) ),
				'BYA_PROTOCOL'       => $protocol,
			);

			/**
			 * Filter to Buy Again Constant.
			 *
			 * @since 1.0
			 * @param Array $constant_array Buy Again Constants.
			 * @return Array.
			 * */
			$constant_array = apply_filters( 'bya_define_constants', $constant_array );

			if ( is_array( $constant_array ) && ! empty( $constant_array ) ) {
				foreach ( $constant_array as $name => $value ) {
					$this->define_constant( $name, $value );
				}
			}
		}

		/**
		 * Define the Constants value.
		 *
		 * @since 1.0
		 * @param String $name Constant Name.
		 * @param String $value Constant Value.
		 * */
		private function define_constant( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0
		 * */
		private function include_files() {
			include_once BYA_ABSPATH . 'inc/bya-common-functions.php'; // Function.
			include_once BYA_ABSPATH . 'inc/abstracts/class-bya-post.php'; // Abstract classes.
			include_once BYA_ABSPATH . 'inc/privacy/class-bya-privacy.php'; // Privacy Policy.
			include_once BYA_ABSPATH . 'inc/class-bya-register-post-types.php'; // Custom Post Types.
			include_once BYA_ABSPATH . 'inc/class-bya-register-post-status.php';
			include_once BYA_ABSPATH . 'inc/class-bya-pages.php'; // Pages.
			include_once BYA_ABSPATH . 'inc/class-bya-install.php'; // Install.
			include_once BYA_ABSPATH . 'inc/class-bya-date-time.php'; // Date Time.
			include_once BYA_ABSPATH . 'inc/entity/class-bya-buy-again-log.php'; // Entity.
			include_once BYA_ABSPATH . 'inc/class-bya-query.php'; // Query Function.
			include_once BYA_ABSPATH . 'inc/class-bya-order-handler.php'; // Order handler.

			if ( is_admin() ) {
				$this->include_admin_files();
			}

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->include_frontend_files();
			}
		}

		/**
		 * Include admin files.
		 *
		 * @since 1.0
		 * */
		private function include_admin_files() {
			include_once BYA_ABSPATH . 'inc/admin/class-bya-admin-assets.php';
			include_once BYA_ABSPATH . 'inc/admin/class-bya-admin-ajax.php';
			include_once BYA_ABSPATH . 'inc/admin/menu/class-bya-menu-management.php';
			include_once BYA_ABSPATH . 'inc/admin/menu/class-bya-buy-again-list-table.php';
		}

		/**
		 * Include frontend files.
		 *
		 * @since 1.0
		 * */
		private function include_frontend_files() {
			include_once BYA_ABSPATH . 'inc/frontend/class-bya-product-notice-handler.php';
			include_once BYA_ABSPATH . 'inc/frontend/class-bya-order-page-item-handler.php';
			include_once BYA_ABSPATH . 'inc/frontend/class-bya-frontend-assets.php';
			include_once BYA_ABSPATH . 'inc/frontend/class-bya-my-account-handler.php';
			include_once BYA_ABSPATH . 'inc/frontend/class-bya-cart-handler.php';
			include_once BYA_ABSPATH . 'inc/frontend/class-bya-shortcodes.php';
		}

		/**
		 * Define the hooks.
		 *
		 * @since 1.0
		 * */
		private function init_hooks() {
						//Compatibility with WC HPOS.
			add_action('before_woocommerce_init', array($this, 'declare_compatibility_with_wc_hpos'));
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
			register_activation_hook( BYA_PLUGIN_FILE, array( 'BYA_Install', 'install' ) ); // Register the plugin.
		}
				
				/**
		 * Declare compatibility with WC HPOS.
		 *
		 * @since 3.9.0
		 * 
		 * @return void
		 * */
		public function declare_compatibility_with_wc_hpos() {
			if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', BYA_PLUGIN_FILE, true);
			}
		}

		/**
		 * Plugins Loaded.
		 *
		 * @since 1.0
		 * */
		public function plugins_loaded() {
			/**
			 * Action hook to trigger on Before plugin load.
			 *
			 * @since 1.0
			 */
			do_action( 'bya_before_plugin_loaded' );

			$this->load_plugin_textdomain();

			/**
			 * Action hook to trigger on plugin load.
			 *
			 * @since 1.0
			 */
			do_action( 'bya_after_plugin_loaded' );
		}

		/**
		 * Templates.
		 *
		 * @since 1.0
		 * */
		public function templates() {
			return BYA_PLUGIN_PATH . '/templates/';
		}

	}

}

