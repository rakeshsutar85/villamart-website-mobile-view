<?php
/**
 * Menu Management
 *
 * @package Buy Again|Menu Management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Menu_Management' ) ) {

	include_once 'class-bya-settings.php';
	include_once 'class-bya-page.php';

	/**
	 * Main Class.
	 */
	class BYA_Menu_Management {

		/**
		 * Plugin slug.
		 *
		 * @var String
		 */
		protected static $plugin_slug = 'bya';

		/**
		 * Menu slug.
		 *
		 * @var String
		 */
		protected static $menu_slug = 'buy_again_list';

		/**
		 * Settings slug.
		 *
		 * @var String
		 */
		protected static $settings_slug = 'bya_settings';

		/**
		 * Product Details slug.
		 *
		 * @var String
		 */
		protected static $product_details_slug = 'bya_product_details';

		/**
		 * Order Details slug.
		 *
		 * @var String
		 */
		protected static $order_details_slug = 'bya_order_details';

		/**
		 * Class initialization.
		 */
		public static function init() {
			// Add Admin Menu Page.
			add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
			// Remove Menu Page.
			add_action( 'admin_head', array( __CLASS__, 'remove_menu_pages' ) );
			// Add Custom Screen Ids.
			add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'add_custom_wc_screen_ids' ), 9, 1 );
		}

		/**
		 * Add Custom Screen IDs in WooCommerce
		 *
		 * @since 1.0
		 * @param Array $wc_screen_ids WooCommerce Screen IDs.
		 * @return Array
		 */
		public static function add_custom_wc_screen_ids( $wc_screen_ids ) {
			$screen_ids = bya_page_screen_ids();

			$newscreenids = get_current_screen();
			$screenid     = str_replace( 'edit-', '', $newscreenids->id );

			// return if current page is not buy angain page.
			if ( ! in_array( $screenid, $screen_ids, true ) ) {
				return $wc_screen_ids;
			}

			$wc_screen_ids[] = $screenid;

			return $wc_screen_ids;
		}

		/**
		 * Remove menu pages.
		 *
		 * @since 1.0
		 */
		public static function remove_menu_pages() {
			remove_submenu_page( self::$menu_slug, self::$product_details_slug );
			remove_submenu_page( self::$menu_slug, self::$order_details_slug );
		}

		/**
		 * Add menu pages
		 *
		 * @since 1.0
		 */
		public static function add_menu_page() {
			$dash_icon_url = BYA_PLUGIN_URL . '/assets/images/dash-icon.png';
			add_menu_page( esc_html__( 'Buy Again', 'buy-again-for-woocommerce' ), esc_html__( 'Buy Again', 'buy-again-for-woocommerce' ), 'manage_options', self::$menu_slug, '', $dash_icon_url );

			// Product Details Submenu.
			add_submenu_page( self::$menu_slug, esc_html__( 'Product Details', 'buy-again-for-woocommerce' ), esc_html__( 'Product Details', 'buy-again-for-woocommerce' ), 'manage_options', self::$product_details_slug, array( __CLASS__, 'product_details_page' ) );

			// Order Details Submenu.
			add_submenu_page( self::$menu_slug, esc_html__( 'Order Details', 'buy-again-for-woocommerce' ), esc_html__( 'Order Details', 'buy-again-for-woocommerce' ), 'manage_options', self::$order_details_slug, array( __CLASS__, 'order_details_page' ) );

			// Settings Submenu.
			$settings_page = add_submenu_page( self::$menu_slug, esc_html__( 'Settings', 'buy-again-for-woocommerce' ), esc_html__( 'Settings', 'buy-again-for-woocommerce' ), 'manage_options', self::$settings_slug, array( __CLASS__, 'settings_page' ) );

			add_action( sanitize_key( 'load-' . $settings_page ), array( __CLASS__, 'settings_page_init' ) );
		}

		/**
		 * Settings page init
		 */
		public static function settings_page_init() {
			global $current_tab, $current_section, $current_sub_section, $current_action;

			// Include settings pages.
			$settings = BYA_Settings::get_settings_pages();
			$tabs     = bya_get_allowed_setting_tabs();

			// Get current tab/section.
			$current_tab = key( $tabs );

			if ( ! empty( $_GET['tab'] ) ) { // @codingStandardsIgnoreLine.
				$sanitize_current_tab = sanitize_title( wp_unslash( $_GET[ 'tab' ] ) ) ; // @codingStandardsIgnoreLine.
				if ( array_key_exists( $sanitize_current_tab, $tabs ) ) {
					$current_tab = $sanitize_current_tab;
				}
			}

			$section             = isset( $settings[ $current_tab ] ) ? $settings[ $current_tab ]->get_sections() : array();
			$current_section     = empty( $_REQUEST[ 'section' ] ) ? key( $section ) : sanitize_title( wp_unslash( $_REQUEST[ 'section' ] ) ) ; // @codingStandardsIgnoreLine.
			$current_section     = empty( $current_section ) ? $current_tab : $current_section;
			$current_sub_section = empty( $_REQUEST[ 'subsection' ] ) ? '' : sanitize_title( wp_unslash( $_REQUEST[ 'subsection' ] ) ) ; // @codingStandardsIgnoreLine.
			$current_action      = empty( $_REQUEST[ 'action' ] ) ? '' : sanitize_title( wp_unslash( $_REQUEST[ 'action' ] ) ) ; // @codingStandardsIgnoreLine.

			/**
			 * Action hook to adjust Buy Again Settings Save.
			 *
			 * @since 1.0
			 * @param String $current_section Current Section ID.
			 */
			do_action( sanitize_key( self::$plugin_slug . '_settings_save_' . $current_tab ), $current_section );

			/**
			 * Action hook to adjust Buy Again Settings Reset.
			 *
			 * @since 1.0
			 * @param String $current_section Current Section ID.
			 */
			do_action( sanitize_key( self::$plugin_slug . '_settings_reset_' . $current_tab ), $current_section );

			add_action( 'woocommerce_admin_field_bya_custom_fields', array( __CLASS__, 'custom_fields_output' ) );
			add_filter( 'woocommerce_admin_settings_sanitize_option_bya_custom_fields', array( __CLASS__, 'save_custom_fields' ), 10, 3 );
		}

		/**
		 * Settings page output
		 *
		 * @since 1.0
		 */
		public static function settings_page() {
			BYA_Settings::output();
		}

		/**
		 * Product Details
		 *
		 * @since 1.0
		 */
		public static function product_details_page() {
			BYA_Page::output();
		}

		/**
		 * Order Details
		 *
		 * @since 1.0
		 */
		public static function order_details_page() {
			BYA_Page::output();
		}

		/**
		 * Output the custom field settings.
		 *
		 * @since 1.0
		 * @param Array $options Options.
		 */
		public static function custom_fields_output( $options ) {
			BYA_Settings::output_fields( $options );
		}

		/**
		 * Save Custom Field settings.
		 *
		 * @since 1.0
		 * @param String  $value Value.
		 * @param String  $option Option.
		 * @param Boolean $raw_value Raw value.
		 */
		public static function save_custom_fields( $value, $option, $raw_value ) {
			BYA_Settings::save_fields( $value, $option, $raw_value );
		}

	}

	BYA_Menu_Management::init();
}
