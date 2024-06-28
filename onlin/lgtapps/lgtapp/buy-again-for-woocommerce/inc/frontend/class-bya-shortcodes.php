<?php
/**
 * Shortcodes.
 *
 * @package Buy Again/Shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Shortcodes' ) ) {

	/**
	 * Class.
	 */
	class BYA_Shortcodes {

		/**
		 * Plugin slug.
		 *
		 * @var String
		 * */
		private static $plugin_slug = 'bya';

		/**
		 * Class Initialization.
		 * */
		public static function init() {
			/**
			 * Filter to Buy Again Shortcodes.
			 *
			 * @since 1.0
			 * */
			$shortcodes = apply_filters( 'bya_load_shortcodes', array( 'bya_buy_again_table' ) );

			foreach ( $shortcodes as $shortcode_name ) {
				add_shortcode( $shortcode_name, array( __CLASS__, 'process_shortcode' ) );
			}

			add_filter(
				'bya_add_to_cart_button_condition_to_show',
				function ( $bool ) {
					$my_account_menu_slug    = get_option( 'bya_advanced_my_account_menu_slug', 'buy-again' );
					$view_order_menu_slug    = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );
					$add_to_cart_allow_pages = get_option( 'bya_general_buy_again_add_to_cart_to_show', array( $view_order_menu_slug, $my_account_menu_slug ) );

					if ( in_array( $my_account_menu_slug, $add_to_cart_allow_pages, true ) ) {
						return true;
					}

					return $bool;
				}
			);

			add_filter(
				'bya_buy_now_button_condition_to_show',
				function ( $bool ) {
					$my_account_menu_slug = get_option( 'bya_advanced_my_account_menu_slug', 'buy-again' );
					$view_order_menu_slug = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );
					$buy_now_allow_pages  = get_option( 'bya_general_buy_again_buy_now_to_show', array( $view_order_menu_slug, $my_account_menu_slug ) );

					if ( in_array( $my_account_menu_slug, $buy_now_allow_pages, true ) ) {
						return true;
					}

					return $bool;
				}
			);

		}

		/**
		 * Process Shortcode.
		 *
		 * @since 1.0
		 * @param Array  $atts Attributes.
		 * @param Array  $content Content.
		 * @param String $tag Shortcode Tag.
		 * */
		public static function process_shortcode( $atts, $content, $tag ) {
			$shortcode_name = str_replace( 'bya_', '', $tag );
			$function       = 'shortcode_' . $shortcode_name;

			switch ( $shortcode_name ) {
				case 'buy_again_table':
					ob_start();
					self::$function( $atts, $content ); // output for shortcode.
					$content = ob_get_contents();
					ob_end_clean();
					break;

				default:
					ob_start();
					/**
					 * Action hook to adjust Shortcode content.
					 *
					 * @since 1.0
					 */
					do_action( "bya_shortcode_{$shortcode_name}_content" );
					$content = ob_get_contents();
					ob_end_clean();
					break;
			}

			return $content;
		}

		/**
		 * Shortcode Buy Again List.
		 *
		 * @since 1.0
		 * @param Array $atts Attributes.
		 * @param Array $content Content.
		 */
		public static function shortcode_buy_again_table( $atts, $content ) {
			BYA_My_Account_Handler::display_buy_again_menu_content();
		}

	}

	BYA_Shortcodes::init();
}
