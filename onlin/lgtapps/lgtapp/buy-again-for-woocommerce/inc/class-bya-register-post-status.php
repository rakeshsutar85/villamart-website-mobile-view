<?php
/**
 * Register Custom Post Status.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Register_Post_Status' ) ) {

	/**
	 * BYA_Register_Post_Status Class.
	 */
	class BYA_Register_Post_Status {

		/**
		 * Class initialization.
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'register_custom_post_status' ) );
		}

		/**
		 * Register Custom Post Status.
		 */
		public static function register_custom_post_status() {
			$custom_post_statuses = array(
				'bya_products' => array( 'BYA_Register_Post_Status', 'products_post_status_args' ),
			);

			/**
			 * Filter to Buy Again Custom Post Status.
			 *
			 * @since 1.0
			 * @return Array.
			 * */
			$custom_post_statuses = apply_filters( 'bya_add_custom_post_status', $custom_post_statuses );

			// return if no post status to register.
			if ( ! bya_check_is_array( $custom_post_statuses ) ) {
				return;
			}

			foreach ( $custom_post_statuses as $post_status => $args_function ) {

				$args = array();
				if ( $args_function ) {
					$args = call_user_func_array( $args_function, array() );
				}

				// Register post status.
				register_post_status( $post_status, $args );
			}
		}

		/**
		 * Buy Again Products Custom Post Status arguments.
		 */
		public static function products_post_status_args() {
			$args = array(
				'label'                     => esc_html__( 'Buy Again Products', 'buy-again-for-woocommerce' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => false,
			);

			/**
			 * Filter to Buy Again Custom Post Status Arguments.
			 *
			 * @since 1.0
			 * @param Array $args Arguments.
			 * */
			return apply_filters( 'bya_products_post_status_args', $args );
		}

	}

	BYA_Register_Post_Status::init();
}
