<?php
/**
 * Initialize the Plugin.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Install' ) ) {

	/**
	 * Class.
	 */
	class BYA_Install {

		/**
		 *  Class initialization.
		 */
		public static function init() {
			add_action( 'woocommerce_init', array( __CLASS__, 'check_version' ) );
			add_filter( 'plugin_action_links_' . BYA_PLUGIN_SLUG, array( __CLASS__, 'settings_link' ) );
		}

		/**
		 * Check Version.
		 *
		 * @since 1.0
		 * */
		public static function check_version() {
			if ( version_compare( get_option( 'bya_version', 1.0 ), BYA_VERSION, '>=' ) ) {
				return;
			}

			// Set default values here.
			self::install();
		}

		/**
		 * Install
		 */
		public static function install() {
			BYA_Pages::create_pages(); // Create pages.
			self::set_default_values(); // Default values.
			self::update_version();
		}

		/**
		 * Update current version.
		 */
		private static function update_version() {
			update_option( 'bya_version', BYA_VERSION );
		}

		/**
		 * Settings link.
		 *
		 * @since 1.0
		 * @param Array $links Settings Links.
		 * @return Array
		 */
		public static function settings_link( $links ) {
			$setting_page_link = '<a href="' . bya_get_settings_page_url() . '">' . esc_html__( 'Settings', 'buy-again-for-woocommerce' ) . '</a>';

			array_unshift( $links, $setting_page_link );

			return $links;
		}

		/**
		 *  Set settings default values
		 */
		public static function set_default_values() {
			if ( ! class_exists( 'BYA_Settings' ) ) {
				include_once BYA_PLUGIN_PATH . '/inc/admin/menu/class-bya-settings.php';
			}

			// Default for settings.
			$settings = BYA_Settings::get_settings_pages();

			foreach ( $settings as $setting ) {
				$sections = $setting->get_sections();

				if ( ! bya_check_is_array( $sections ) ) {
					continue;
				}

				foreach ( $sections as $section_key => $section ) {
					$settings_array = $setting->get_settings( $section_key );

					foreach ( $settings_array as $value ) {
						if ( isset( $value['default'] ) && isset( $value['id'] ) && get_option( $value['id'] ) === false ) {
							add_option( $value['id'], $value['default'] );
						}
					}
				}
			}
		}

	}

	BYA_Install::init();
}
