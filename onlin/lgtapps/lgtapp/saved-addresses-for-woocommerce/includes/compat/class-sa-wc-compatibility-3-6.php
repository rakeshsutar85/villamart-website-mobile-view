<?php
/**
 * Compatibility class for WooCommerce 3.6.0
 *
 * @package     compat
 * @version     1.0.0
 * @since       WooCommerce 3.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_WC_Compatibility_3_6' ) ) {

	/**
	 * Class to check WooCommerce version is greater than and equal to 3.6.0
	 */
	class SA_WC_Compatibility_3_6 {

		/**
		 * Function to check if WooCommerce is Greater Than And Equal To 3.5.8
		 *
		 * @return boolean
		 */
		public static function is_wc_gte_36() {
			return self::is_wc_greater_than( '3.5.8' );
		}

		/**
		 * Function to get WooCommerce version
		 */
		public static function get_wc_version() {
			if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
				return WC_VERSION;
			}
			if ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
				return WOOCOMMERCE_VERSION;
			}
			return null;
		}

		/**
		 * Function to compare current version of WooCommerce on site with active version of WooCommerce
		 *
		 * @param int $version Version number to compare.
		 */
		public static function is_wc_greater_than( $version ) {
			return version_compare( self::get_wc_version(), $version, '>' );
		}

	}

}
